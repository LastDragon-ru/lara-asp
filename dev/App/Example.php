<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\App;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;
use LogicException;
use PhpParser\ErrorHandler\Collecting;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Stringable;
use Symfony\Component\Console\Attribute\AsCommand;

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

    private static ?Dumper $dumper = null;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $signature = self::Name.<<<'SIGNATURE'
        {file : example file}
    SIGNATURE;

    protected static function getDumper(): Dumper {
        if (!self::$dumper) {
            throw new LogicException(
                sprintf(
                    'The `%s` can be called only within example context.',
                    __METHOD__,
                ),
            );
        }

        return self::$dumper;
    }

    public function __invoke(Dumper $dumper): void {
        self::$dumper = $dumper;
        $container    = Container::getInstance();
        $config       = $container->make(Repository::class);
        $file         = Cast::toString($this->argument('file'));

        $container[Repository::class] = clone $config;

        try {
            // Run
            (static function () use ($file): void {
                include $file;
            })();

            // Output
            $dumps  = $dumper->getDumps();
            $output = implode("\n\n", array_map(trim(...), $dumps));

            if ($output) {
                $this->output->writeln("<markdown>{$output}</markdown>");
            }
        } finally {
            Container::setInstance($container);

            $container[Repository::class] = $config;
            self::$dumper                 = null;
        }
    }

    public static function dump(mixed $value, string $expression = null): void {
        self::getDumper()->dump($value, $expression ?? self::getExpression(__FUNCTION__));
    }

    public static function raw(Stringable|string $value, string $type, string $expression = null): void {
        self::getDumper()->raw($value, $expression ?? self::getExpression(__FUNCTION__), $type);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public static function config(string $root, array $settings): void {
        // Example?
        self::getDumper();

        // Update
        $repository = Container::getInstance()->make(Repository::class);
        $config     = (array) $repository->get($root, []);
        $config     = (new ConfigMerger())->merge([ConfigMerger::Strict => false], $config, $settings);

        $repository->set([
            $root => $config,
        ]);
    }

    private static function getExpression(string $method): ?string {
        // File?
        $context = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $context = end($context);

        if (!isset($context['file']) || !isset($context['line'])) {
            return null;
        }

        // Extract first arg
        $code   = implode("\n", array_slice((array) file($context['file']), $context['line'] - 1));
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $stmts  = (array) $parser->parse("<?php\n{$code}", new Collecting());
        $call   = (new NodeFinder())->findFirst($stmts, static function (Node $node) use ($method): bool {
            return $node instanceof StaticCall
                && $node->class instanceof Name
                && $node->name instanceof Identifier
                && ($node->class->toString() === 'Example' || $node->class->toString() === self::class)
                && $node->name->toString() === $method;
        });
        $arg    = $call instanceof StaticCall
            ? (new Standard())->prettyPrint(array_slice($call->args, 0, 1))
            : null;

        // Return
        return $arg;
    }
}
