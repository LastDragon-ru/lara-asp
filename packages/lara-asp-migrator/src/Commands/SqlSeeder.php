<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use Illuminate\Console\GeneratorCommand;
use LastDragon_ru\LaraASP\Migrator\Package;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;

use function basename;
use function dirname;
use function mb_trim;

#[AsCommand(
    name       : Package::Name.':sql-seeder',
    description: 'Create a new SQL Seeder class',
    aliases    : [
        'make:sql-seeder',
    ],
)]
class SqlSeeder extends GeneratorCommand {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $type = 'Seeder';

    private ?SqlSeederHelper $sqlSeederHelper = null;

    #[Override]
    protected function getStub(): string {
        return $this->resolveStubPath('stubs/seeder.sql.stub');
    }

    protected function resolveStubPath(string $stub): string {
        $custom = $this->laravel->basePath(mb_trim($stub, '/'));
        $path   = !$this->files->exists($custom)
            ? __DIR__.'/../../'.$stub
            : $custom;

        return $path;
    }

    /**
     * @inheritDoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    #[Override]
    protected function makeDirectory($path) {
        // FIXME [lara-asp-migrator] `make:seeder` hack: would be good to use another
        //      way to add file(s) after the command finished.
        $path    = parent::makeDirectory($path);
        $dir     = dirname($path);
        $name    = basename($path, '.php');
        $message = '-- TODO: Replace to SQL query(s).';

        $this->files->put("{$dir}/{$name}.sql", $message);

        return $path;
    }

    #[Override]
    protected function getPath(mixed $name): string {
        return $this->getSqlSeederHelper()->getSeedersPath($name);
    }

    #[Override]
    protected function rootNamespace(): string {
        return $this->getSqlSeederHelper()->getRootNamespace();
    }

    private function getSqlSeederHelper(): SqlSeederHelper {
        if (!isset($this->sqlSeederHelper)) {
            $this->sqlSeederHelper = new SqlSeederHelper($this->files);
            $this->sqlSeederHelper->setLaravel($this->laravel);
        }

        return $this->sqlSeederHelper;
    }
}
