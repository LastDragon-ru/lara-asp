<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Resolver;

use ArrayAccess;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Container\Container;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Nuwave\Lighthouse\Exceptions\ValidationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

use function implode;
use function is_object;
use function sprintf;

class Resolver {
    public function __construct(
        protected Container $container,
    ) {
        // empty
    }

    /**
     * @param array<string,mixed> $args
     */
    public function resolve(
        string $callback,
        mixed $root,
        array $args,
        GraphQLContext $context,
        ResolveInfo $resolveInfo,
    ): mixed {
        try {
            // Bind
            $this->container->bind(Root::class, static function () use ($root): Root {
                return new Root($root);
            });
            $this->container->bind(Args::class, static function () use ($args): Args {
                return new Args($args);
            });

            // Parameters
            $parameters = [
                GraphQLContext::class => $context,
                ResolveInfo::class    => $resolveInfo,
            ];

            if (is_object($root)) {
                $parameters[$root::class] = $root;
            }

            // Compatibility
            $parameters += [
                '_'           => $root,
                'root'        => $root,
                'args'        => $args,
                'context'     => $context,
                'resolveInfo' => $resolveInfo,
            ];

            // Call
            return $this->container->call($callback, $parameters);
        } catch (IlluminateValidationException $exception) {
            throw new ValidationException(sprintf(
                'Validation failed for the field [%s].',
                implode('.', $resolveInfo->path),
            ), $exception->validator);
        } finally {
            // Container Contract doesn't allow to remove instances.
            $container = $this->container;

            if ($container instanceof ArrayAccess) {
                unset($container[Root::class]);
                unset($container[Args::class]);
            }
        }
    }
}
