<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Migrator\Migrations\SqlMigrationCreator;
use LastDragon_ru\LaraASP\Migrator\Package;
use LastDragon_ru\Path\DirectoryPath;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function mb_trim;
use function sprintf;

#[AsCommand(
    name       : Package::Name.':sql-migration',
    description: 'Create a new SQL Migration file.',
    aliases    : [
        'make:sql-migration',
    ],
)]
class SqlMigration extends BaseCommand {
    #[Override]
    protected function configure(): void {
        parent::configure();

        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the migration.')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The path where the file should be created.');
    }

    public function __invoke(SqlMigrationCreator $creator): int {
        $name = Str::snake(mb_trim(Cast::toString($this->input->getArgument('name'))));
        $path = Cast::toStringNullable($this->input->getOption('path')) ?? $this->getMigrationPath();
        $path = (new DirectoryPath($this->laravel->basePath()))->file($path);
        $path = (string) $path;
        $file = $creator->create($name, $path);

        $this->components->info(sprintf('SQL Migration [%s] created successfully.', $file));

        return Command::SUCCESS;
    }
}
