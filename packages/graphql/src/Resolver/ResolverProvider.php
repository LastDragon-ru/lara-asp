<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Resolver;

use Closure;
use Illuminate\Contracts\Container\Container;
use Nuwave\Lighthouse\Schema\ResolverProvider as DefaultResolverProvider;
use Nuwave\Lighthouse\Schema\Values\FieldValue;

class ResolverProvider extends DefaultResolverProvider {
    public function __construct(
        protected Container $container,
    ) {
        // empty
    }

    public function provideResolver(FieldValue $fieldValue): Closure {
        if ($fieldValue->parentIsRootType()) {
            // Valid?
            $class = $this->findResolverClass($fieldValue, '__invoke');

            if ($class === null) {
                $this->throwMissingResolver($fieldValue);
            }

            // Create
            $container = $this->container;
            $resolver  = static function (...$args) use ($container, $class) {
                return $container->make(Resolver::class)->resolve($class, ...$args);
            };

            // Return
            return $resolver;
        }

        return parent::provideResolver($fieldValue);
    }
}
