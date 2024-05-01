<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeGraphqlDirective;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ProcessableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DependencyIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotDirective;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer;
use Override;

use function mb_substr;

class Instruction implements ProcessableInstruction {
    public function __construct(
        protected readonly ?Printer $printer = null,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:graphql-directive';
    }

    #[Override]
    public static function getDescription(): string {
        return <<<'DESC'
            Includes the definition of the directive as a Markdown code block.
            DESC;
    }

    #[Override]
    public static function getTargetDescription(): ?string {
        return 'Directive name (started with `@` sign)';
    }

    #[Override]
    public function process(string $path, string $target): string {
        // Dependencies?
        if (!$this->printer) {
            throw new DependencyIsMissing($path, $target, Printer::class);
        }

        // Directive?
        $directive  = mb_substr($target, 1);
        $definition = $this->printer->getDirectiveResolver()?->getDefinition($directive);

        if ($definition === null) {
            throw new TargetIsNotDirective($path, $target);
        }

        // Print
        $printed  = $this->printer->print($definition);
        $markdown = <<<MARKDOWN
            ```graphql
            {$printed}
            ```
            MARKDOWN;

        // Return
        return $markdown;
    }
}
