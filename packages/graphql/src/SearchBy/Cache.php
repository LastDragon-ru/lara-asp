<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use Closure;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use InvalidArgumentException;
use JsonSerializable;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Stringable;

use function array_key_exists;
use function array_map;
use function is_array;
use function is_scalar;
use function json_encode;
use function ksort;
use function mb_strtolower;

class Cache {
    /**
     * @var array<string, mixed>
     */
    private array $items = [];

    public function __construct() {
        // empty
    }

    public function get(mixed $keys, Closure $closure): mixed {
        $key   = $this->key($keys);
        $value = null;

        if (array_key_exists($key, $this->items)) {
            $value = $this->items[$key];
        } else {
            $value = $this->items[$key] = $closure();
        }

        return $value;
    }

    protected function key(mixed $keys): string {
        return mb_strtolower(json_encode($this->serialize($keys)));
    }

    protected function serialize(mixed $keys): mixed {
        if ($keys instanceof TypeDefinitionNode) {
            $keys = $keys->name->value;
        } elseif ($keys instanceof InputValueDefinitionNode) {
            $keys = ASTHelper::getUnderlyingTypeName($keys).'::'.$keys->name->value;
        } elseif (is_array($keys)) {
            $keys = array_map(function ($key) {
                return $this->key($key);
            }, $keys);

            ksort($keys);
        } elseif (is_scalar($keys) || $keys instanceof JsonSerializable || $keys instanceof Stringable) {
            // empty
        } else {
            throw new InvalidArgumentException('Unsupported key type.');
        }

        return $keys;
    }
}
