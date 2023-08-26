<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\App;

use Closure;
use Illuminate\Console\Command;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LogicException;
use PhpParser\ErrorHandler\Collecting;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;
use Symfony\Component\VarDumper\Dumper\CliDumper;

use function array_map;
use function array_slice;
use function debug_backtrace;
use function end;
use function file;
use function implode;
use function sprintf;
use function trim;

use const DEBUG_BACKTRACE_IGNORE_ARGS;

#[AsCommand(
    name       : Example::Name,
    description: 'Executes example file within Application context.',
)]
final class Example extends Command {
    private const Name = 'dev:example';

    /**
     * @var Closure(mixed, string, ?string): void|null
     */
    private static ?Closure $dump = null;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $signature = self::Name.<<<'SIGNATURE'
        {file : example file}
    SIGNATURE;

    public function __invoke(): void {
        $file       = Cast::toString($this->argument('file'));
        $dumps      = [];
        $cloner     = new VarCloner();
        $dumper     = new CliDumper(
            flags: AbstractDumper::DUMP_LIGHT_ARRAY
                | AbstractDumper::DUMP_COMMA_SEPARATOR
                | AbstractDumper::DUMP_TRAILING_COMMA,
        );
        self::$dump = static function (
            mixed $value,
            string $type,
            ?string $expression,
        ) use (
            $dumper,
            $cloner,
            &$dumps,
        ): void {
            $dump    = trim((string) $dumper->dump($cloner->cloneVar($value), true));
            $dump    = ($expression ? "The `{$expression}` is:\n\n" : '')."```{$type}\n{$dump}\n```\n";
            $dumps[] = $dump;
        };

        try {
            // Run
            (static function () use ($file): void {
                include $file;
            })();

            // Output
            $output = implode("\n\n", array_map(trim(...), $dumps));

            if ($output) {
                $this->output->writeln("<markdown>{$output}</markdown>");
            }
        } finally {
            self::$dump = null;
        }
    }

    public static function dump(mixed $value, string $type = 'plain', string $expression = null): void {
        // Example?
        if (!self::$dump) {
            throw new LogicException(
                sprintf(
                    'The `%s` can be called only within example context.',
                    __METHOD__,
                ),
            );
        }

        // Call
        (self::$dump)($value, $type, $expression ?? self::getExpression());
    }

    private static function getExpression(): ?string {
        // File?
        $context = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $context = end($context);

        if (!isset($context['file']) || !isset($context['line'])) {
            return null;
        }

        // Extract first arg
        $code   = implode("\n", array_slice((array) file($context['file']), $context['line'] - 1));
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $stmts  = (array) $parser->parse("<?php\n{$code}", new Collecting());
        $call   = (new NodeFinder())->findFirst($stmts, static function (Node $node): bool {
            return $node instanceof StaticCall
                && $node->class instanceof Name
                && $node->name instanceof Identifier
                && ($node->class->toString() === 'Example' || $node->class->toString() === self::class)
                && $node->name->toString() === 'dump';
        });
        $arg    = $call instanceof StaticCall
            ? (new Standard())->prettyPrint(array_slice($call->args, 0, 1))
            : null;

        // Return
        return $arg;
    }
}
