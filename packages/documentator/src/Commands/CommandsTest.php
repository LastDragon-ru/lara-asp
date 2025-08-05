<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function array_merge;
use function array_reduce;
use function file_put_contents;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Commands::class)]
final class CommandsTest extends TestCase {
    // <editor-fold desc="Prepare">
    // =========================================================================
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array {
        return array_merge(parent::getPackageProviders($app), [
            CommandsTest_Provider::class,
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    public function testInvoke(): void {
        $directory = (new DirectoryPath(self::getTempDirectory()))->getNormalizedPath();

        self::assertNotFalse(
            file_put_contents((string) $directory->getFilePath('file.txt'), self::class),
        );

        $result = $this
            ->withoutMockingConsoleOutput()
            ->artisan("lara-asp-documentator:commands test-namespace {$directory}");

        self::assertEquals(Command::SUCCESS, $result);

        $files = iterator_to_array(Finder::create()->in((string) $directory)->files(), false);
        $files = array_reduce($files, static function (array $combined, SplFileInfo $file): array {
            return array_merge($combined, [
                $file->getFilename() => $file->getContents(),
            ]);
        }, []);

        self::assertEquals(
            [
                'command-a.md' => self::getTestData()->content('~a.md'),
                'command-b.md' => self::getTestData()->content('~b.md'),
            ],
            $files,
        );
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class CommandsTest_Provider extends ServiceProvider {
    public function boot(): void {
        $this->commands(
            CommandsTest_CommandA::class,
            CommandsTest_CommandB::class,
            CommandsTest_CommandC::class,
        );
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class CommandsTest_CommandA extends Command {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $signature = <<<'SIGNATURE'
        test-namespace:command-a
        {arg-a        : Argument a}
        {arg-b?       : Optional argument b}
        {--a|option-a : Option A}
        {--option-b=  : Option B}
        SIGNATURE;

    public function __invoke(): void {
        throw new Exception('Should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[AsCommand(
    name: 'test-namespace:command-b',
)]
class CommandsTest_CommandB extends Command {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $description = 'Command B description.';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var array<array-key, mixed>
     */
    protected $aliases = ['command-b-alias'];

    public function __invoke(): void {
        throw new Exception('Should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[AsCommand(
    name: 'test-namespace:command-c',
)]
class CommandsTest_CommandC extends Command {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var bool
     */
    protected $hidden = true;

    public function __invoke(): void {
        throw new Exception('Should not be called.');
    }
}
