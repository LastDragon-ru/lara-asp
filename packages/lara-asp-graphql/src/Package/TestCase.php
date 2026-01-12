<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Package;

use GraphQL\Type\Schema;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\Core\PackageProvider as CoreProvider;
use LastDragon_ru\LaraASP\GraphQL\Package\Directives\ExposeBuilderDirective;
use LastDragon_ru\LaraASP\GraphQL\Package\Provider as TestProvider;
use LastDragon_ru\LaraASP\GraphQL\Package\SchemaPrinter\LighthouseDirectiveFilter;
use LastDragon_ru\LaraASP\GraphQL\PackageProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Assertions;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use LastDragon_ru\LaraASP\Serializer\PackageProvider as SerializerProvider;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase as PackageTestCase;
use LastDragon_ru\PhpUnit\GraphQL\PrinterSettings;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Testing\TestingServiceProvider as LighthouseTestingServiceProvider;
use Nuwave\Lighthouse\Validation\ValidationServiceProvider as LighthouseValidationServiceProvider;
use Override;
use SplFileInfo;

use function array_merge;
use function mb_substr;

/**
 * @internal
 */
abstract class TestCase extends PackageTestCase {
    use Assertions {
        getGraphQLPrinter as private getDefaultGraphQLPrinter;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array {
        return array_merge(parent::getPackageProviders($app), [
            PackageProvider::class,
            CoreProvider::class,
            TestProvider::class,
            CoreProvider::class,
            SerializerProvider::class,
            LighthouseServiceProvider::class,
            LighthouseTestingServiceProvider::class,
            LighthouseValidationServiceProvider::class,
        ]);
    }

    protected function getGraphQLPrinter(?Settings $settings = null): Printer {
        $settings ??= (new PrinterSettings())
            ->setDirectiveDefinitionFilter($this->app()->make(LighthouseDirectiveFilter::class));
        $printer    = $this->getDefaultGraphQLPrinter($settings);

        return $printer;
    }

    protected function getGraphQLArgument(
        string $type,
        mixed $value,
        Schema|SplFileInfo|string|null $schema = null,
    ): Argument {
        try {
            if ($schema !== null) {
                $this->useGraphQLSchema($schema);
            }

            $factory  = $this->app()->make(ArgumentFactory::class);
            $argument = $factory->getArgument($type, $value);

            return $argument;
        } finally {
            $this->resetGraphQLSchema();
        }
    }

    /**
     * @template M of EloquentModel
     *
     * @param QueryBuilder|EloquentBuilder<M>|ScoutBuilder<M> $builder
     */
    protected function getExposeBuilderDirective(
        QueryBuilder|EloquentBuilder|ScoutBuilder $builder,
    ): ExposeBuilderDirective {
        $directive = new class() extends ExposeBuilderDirective {
            // empty
        };

        $directive::$builder = $builder;
        $directive::$result  = $builder;

        $this->app()->make(DirectiveLocator::class)
            ->setResolved(mb_substr($directive::getName(), 1), $directive::class);

        return $directive;
    }
}
