<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;

use function is_a;
use function sprintf;

class ResourceCollection extends AnonymousResourceCollection implements SafeResource {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string|null
     */
    public static $wrap = null;

    public function __construct(string $class, mixed $resource) {
        if (!is_a($class, SafeResource::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'The `$class` must be instance of `%s`.',
                SafeResource::class,
            ));
        }

        parent::__construct($resource, $class);
    }

    /**
     * @inheritdoc
     */
    protected function preparePaginatedResponse($request) {
        // Our PaginatedResponse does not return any links, so we shouldn't
        // worry about query parameters like the parent method do.
        return (new PaginatedResponse($this))->toResponse($request);
    }

    public function getResourceClass(): string {
        return $this->collects;
    }
}
