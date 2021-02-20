<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use JsonSerializable;
use LastDragon_ru\LaraASP\Spa\Package;
use LogicException;

use function array_merge;
use function count;
use function end;
use function is_array;
use function is_null;
use function is_scalar;

abstract class Resource extends JsonResource implements SafeResource {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string|null
     */
    public static $wrap        = null;
    private int   $filterLevel = 0;

    // <editor-fold desc="JsonResource">
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function toArray($request): array {
        if ($this->resource instanceof Model) {
            throw new LogicException(
                'Implicit conversions of Models is not supported, please redefine this method to make it explicit.',
            );
        }

        return parent::toArray($request);
    }

    /**
     * @inheritdoc
     */
    public function with($request): array {
        return $this->mapResourceData(parent::with($request), []);
    }

    /**
     * @inheritdoc
     */
    public function additional(array $data): self {
        return parent::additional($this->mapResourceData($data, []));
    }

    /**
     * @inheritdoc
     */
    protected function filter($data): array {
        // Why do we need this? Resources can contain different types, and we
        // cannot be sure that all of them will be serialized properly :(

        $this->filterLevel++;
        $data = parent::filter($data);
        $this->filterLevel--;

        if ($this->filterLevel <= 0) {
            $data = $this->mapResourceData($data, []);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public static function collection($resource): AnonymousResourceCollection {
        // TODO [spa]: I'm definitely not sure that we need to support $preserveKeys
        //      (see parent method) because:
        //      - right now Laravel 8.25.0 doesn't support it without $wrap but
        //        all our resources without it (https://github.com/laravel/framework/issues/30052);
        //      - we cannot create a resource like the original method does
        //        (`new static([])`) - resources are strongly typed;
        //      - based on my practice numeric keys are not important in almost
        //        all cases.
        return $resource instanceof AbstractPaginator
            ? new PaginatedCollection(static::class, $resource)
            : new ResourceCollection(static::class, $resource);
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    /**
     * @param array<mixed> $data
     * @param array<mixed> $path
     *
     * @return array<mixed>
     */
    protected function mapResourceData(array $data, array $path): array {
        foreach ($data as $key => $value) {
            $data[$key] = $this->mapResourceValue($key, $value, array_merge($path, [$key]));
        }

        return $data;
    }

    /**
     * @param array<string> $path
     */
    protected function mapResourceValue(string|int $key, mixed $value, array $path): mixed {
        // Scalars, null and our Resources can be returned as is
        if (is_scalar($value) || is_null($value) || $value instanceof SafeResource) {
            return $value;
        }

        // Returning the Model is not a good idea because we never know what
        // data will be returned and what format will be used.
        if ($value instanceof Model) {
            throw new LogicException('Please do not return Models directly, use our Resources instead.');
        }

        // Returning `Illuminate\Http\Resources\Json\JsonResource` is also not safe.
        if ($value instanceof JsonResource) {
            throw new LogicException('Please do not return JsonResource directly, use our Resources instead.');
        }

        // Convert
        if ($value instanceof DateTimeInterface) {
            // Laravel cannot serialize Date and DateTime in different formats.
            // Also, it is really weird that they want to do it inside Model -
            // it is definitely not a Model responsibility.
            if ($this->mapResourceIsDate($value, $path)) {
                $value = $this->mapResourceDate($value);
            } else {
                $value = $this->mapResourceDateTime($value);
            }
        } elseif ($value instanceof Collection) {
            $value = $this->mapResourceValue($key, $value->all(), $path);
        } elseif ($value instanceof JsonSerializable) {
            $value = $this->mapResourceValue($key, $value->jsonSerialize(), $path);
        } elseif (is_array($value)) {
            $value = $this->mapResourceData($value, $path);
        } else {
            // All other values is not supported.
            throw new LogicException('Value cannot be converted to JSON.');
        }

        // Return
        return $value;
    }

    /**
     * @param array<string> $path
     */
    protected function mapResourceIsDate(DateTimeInterface $value, array $path): bool {
        return count($path) === 1
            && $this->resource instanceof Model
            && $this->resource->hasCast(end($path), 'date');
    }

    protected function mapResourceDate(DateTimeInterface $value): string {
        return $value->format(Package::DateFormat);
    }

    protected function mapResourceDateTime(DateTimeInterface $value): string {
        return $value->format(Package::DateTimeFormat);
    }
    // </editor-fold>
}
