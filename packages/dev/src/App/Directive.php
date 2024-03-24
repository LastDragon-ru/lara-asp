<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\App;

use GraphQL\Language\Parser;
use Illuminate\Console\Command;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Symfony\Component\Console\Attribute\AsCommand;

use function ltrim;

#[AsCommand(
    name       : Directive::Name,
    description: 'Return directive definition in markdown.',
)]
final class Directive extends Command {
    private const Name = 'dev:directive';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $signature = self::Name.<<<'SIGNATURE'
        {directive : name of the directive}
    SIGNATURE;

    public function __invoke(DirectiveLocator $locator, Printer $printer): void {
        $directive  = ltrim(Cast::toString($this->argument('directive')), '@');
        $definition = $locator->resolve($directive)::definition();
        $printed    = (string) $printer->print(Parser::parse($definition));

        $this->output->writeln(
            <<<DERECTIVE
            ```graphql
            {$printed}
            ```
            DERECTIVE,
        );
    }
}
