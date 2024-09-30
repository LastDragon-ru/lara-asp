<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\App;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Core\Application\ApplicationResolver;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Contracts\Runner;
use LogicException;
use Override;
use PhpParser\ErrorHandler\Collecting;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Stringable;

use function array_map;
use function array_slice;
use function debug_backtrace;
use function end;
use function implode;
use function preg_split;
use function sprintf;
use function str_contains;
use function trim;

use const DEBUG_BACKTRACE_IGNORE_ARGS;

final class Example implements Runner {
    private static ?Application $app    = null;
    private static ?File        $file   = null;
    private static ?Dumper      $dumper = null;

    public function __construct(
        private readonly ApplicationResolver $appResolver,
    ) {
        // empty
    }

    protected static function getDumper(): Dumper {
        if (self::$dumper === null) {
            throw new LogicException(
                sprintf(
                    'The `%s` can be called only within example context.',
                    __METHOD__,
                ),
            );
        }

        return self::$dumper;
    }

    #[Override]
    public function __invoke(File $file): ?string {
        // Runnable?
        if ($file->getExtension() !== 'php' || !str_contains($file->getContent(), 'Example::')) {
            return null;
        }

        // Run
        $result       = null;
        self::$app    = $this->appResolver->getInstance();
        self::$file   = $file;
        self::$dumper = self::$app->make(Dumper::class);

        try {
            // Execute
            (static function () use ($file): void {
                include $file->getPath();
            })();

            // Output
            $dumps  = self::$dumper->getDumps();
            $output = implode("\n\n", array_map(trim(...), $dumps));

            if ($output !== '') {
                $result = "<markdown>{$output}</markdown>";
            }
        } finally {
            self::$app    = null;
            self::$file   = null;
            self::$dumper = null;
        }

        return $result;
    }

    public static function dump(mixed $value, ?string $expression = null): void {
        self::getDumper()->dump($value, $expression ?? self::getExpression(__FUNCTION__));
    }

    public static function raw(Stringable|string $value, string $type, ?string $expression = null): void {
        self::getDumper()->raw($value, $expression ?? self::getExpression(__FUNCTION__), $type);
    }

    protected static function app(): Application {
        if (self::$app === null) {
            throw new LogicException(
                sprintf(
                    'The `%s` can be called only within example context.',
                    __METHOD__,
                ),
            );
        }

        return self::$app;
    }

    /**
     * @param array<string, mixed> $settings
     */
    public static function config(string $root, array $settings): void {
        // Update
        $repository = self::app()->make(Repository::class);
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
        $lines  = preg_split('/\R/u', self::$file?->getContent() ?? '') ?: [];
        $code   = implode("\n", array_slice($lines, $context['line'] - 1));
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
