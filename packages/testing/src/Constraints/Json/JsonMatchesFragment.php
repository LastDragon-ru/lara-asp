<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Illuminate\Support\Arr;
use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\JsonMatches;
use SplFileInfo;
use stdClass;

use function is_array;
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

    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool {
        if (parent::evaluate($other, $description, $returnResult) === false) {
            return false;
        }

        $expected = Args::getJsonString(Args::getJson($this->json));
        $actual   = Args::getJson($other, true);
        $actual   = is_array($actual) ? Arr::get($actual, $this->path) : null;
        $actual   = Args::getJsonString($actual);
        $result   = (new JsonMatches($expected))->evaluate(
            $actual,
            $description,
            $returnResult,
        );

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function matches($other): bool {
        $json    = Args::getJson($other, true);
        $matches = is_array($json) && Arr::has($json, $this->path);

        return $matches;
    }

    public function toString(): string {
        return sprintf(
            'contains fragment "%s" matches JSON string "%s"',
            $this->path,
            Args::getJsonString(Args::getJson($this->json)),
        );
    }
}
