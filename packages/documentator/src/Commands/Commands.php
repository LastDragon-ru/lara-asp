<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Utils\ArtisanSerializer;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function is_dir;

// @phpcs:disable Generic.Files.LineLength.TooLong

#[AsCommand(
    name       : Commands::Name,
    description: 'Saves help for each command in the `namespace` into a separate file in the `target` directory.',
)]
class Commands extends Command {
    public const Name = Package::Name.':commands';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    public $signature = self::Name.<<<'SIGNATURE'
        {namespace  : The namespace of the commands.}
        {target     : Directory to save generated files. It will be created if not exist. All files/directories inside it will be removed otherwise.}
        {--defaults : Include application default arguments/options like `--help`, etc.}
    SIGNATURE;

    public function __invoke(PackageViewer $viewer, Filesystem $filesystem, ArtisanSerializer $serializer): void {
        // Options
        $application = Cast::to(Application::class, $this->getApplication());
        $namespace   = $application->findNamespace(Cast::toString($this->argument('namespace')));
        $target      = Cast::toString($this->argument('target'));
        $defaults    = Cast::toBool($this->option('defaults'));
        $commands    = $application->all($namespace);

        // Cleanup
        $this->components->task(
            'Prepare',
            static function () use ($filesystem, $target): void {
                if (is_dir($target)) {
                    $filesystem->remove(
                        Finder::create()->in($target),
                    );
                } else {
                    $filesystem->mkdir($target);
                }
            },
        );

        // Process
        foreach ($commands as $command) {
            if ($command->isHidden()) {
                continue;
            }

            $this->components->task(
                "Command: {$command->getName()}",
                static function () use (
                    $filesystem,
                    $serializer,
                    $viewer,
                    $namespace,
                    $target,
                    $defaults,
                    $command,
                ): void {
                    // Default options?
                    if ($defaults) {
                        $command->mergeApplicationDefinition();
                    } else {
                        $command->setDefinition(
                            $command->getNativeDefinition(),
                        );
                    }

                    // Render
                    $name    = Str::after((string) $command->getName(), "{$namespace}:");
                    $path    = Path::getPath($target, "{$name}.md");
                    $content = $viewer->render('commands.markdown', [
                        'serializer' => $serializer,
                        'command'    => $command,
                    ]);

                    $filesystem->dumpFile($path, $content);
                },
            );
        }
    }
}
