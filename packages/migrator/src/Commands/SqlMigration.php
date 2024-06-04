<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Migrator\Migrations\SqlMigrationCreator;
use LastDragon_ru\LaraASP\Migrator\Package;
use Symfony\Component\Console\Attribute\AsCommand;

use function sprintf;
use function trim;

#[AsCommand(
    name       : SqlMigration::Name,
    description: 'Create a new SQL Migration file.',
)]
class SqlMigration extends BaseCommand {
    protected const Name = Package::Name.':sql-migration';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    public $signature = self::Name.' '.<<<'SIGNATURE'
        {name    : The name of the migration}
        {--path= : The path where the file should be created}
        SIGNATURE;

    public function __invoke(SqlMigrationCreator $creator): int {
        $name = Str::snake(trim(Cast::toString($this->input->getArgument('name'))));
        $path = Cast::toStringNullable($this->input->getOption('path')) ?? $this->getMigrationPath();
        $path = Path::getPath($this->laravel->basePath(), $path);
        $file = $creator->create($name, $path);

        $this->components->info(sprintf('SQL Migration `[%s]` created successfully.', $file));

        return static::SUCCESS;
    }
}
