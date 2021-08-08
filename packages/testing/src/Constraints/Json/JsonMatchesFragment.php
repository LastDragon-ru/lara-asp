<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Illuminate\Support\Arr;
use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\JsonMatches;
use SplFileInfo;
use stdClass;

use function json_encode;
use function sprintf;

class JsonMatchesFragment extends Constraint {
    /**
     * @param JsonSerializable|SplFileInfo|stdClass|array<mixed>|string|int|float|bool|null $json
     */
    public function __construct(
        protected string $path,
        protected JsonSerializable|SplFileInfo|stdClass|array|string|int|float|bool|null $json,
    ) {
        // empty
    }

    /**
     * @inheritdoc
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool {
        return parent::evaluate($other, $description, $returnResult)
            && (new JsonMatches(json_encode(Args::getJson($this->json))))->evaluate(
                json_encode(Arr::get(Args::getJson($other, true), $this->path)),
                $description,
                $returnResult,
            );
    }

    /**
     * @inheritdoc
     */
    protected function matches($other): bool {
        return Arr::has(Args::getJson($other, true), $this->path);
    }

    public function toString(): string {
        return sprintf(
            'contains fragment "%s" matches JSON string "%s"',
            $this->path,
            json_encode(Args::getJson($this->json)),
        );
    }
}
