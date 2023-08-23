<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use Illuminate\Console\Command;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Preprocessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Finder\Finder;

use function file_put_contents;
use function getcwd;

#[AsCommand(name: Preprocess::Name)]
class Preprocess extends Command {
    public const Name = Package::Name.':preprocess';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string|null
     */
    public $description = 'Preprocess Markdown files.';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    public $signature = self::Name.<<<'SIGNATURE'
        {path? : directory to process}
    SIGNATURE;

    public function __invoke(Preprocessor $preprocessor): void {
        $cwd    = getcwd();
        $path   = Cast::toString($this->argument('path') ?? $cwd);
        $finder = Finder::create()
            ->ignoreVCSIgnored(true)
            ->in($path)
            ->exclude('vendor')
            ->exclude('node_modules')
            ->files()
            ->name('*.md');

        foreach ($finder as $file) {
            $this->components->task($file->getPathname(), static function () use ($preprocessor, $file): bool {
                $path    = $file->getPathname();
                $content = $file->getContents();
                $result  = $preprocessor->process($path, $content);

                return $content === $result
                    || file_put_contents($path, $result) !== false;
            });
        }
    }
}
