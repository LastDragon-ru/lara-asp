<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Provider\WithViews;
use LastDragon_ru\LaraASP\Documentator\Commands\Commands;
use LastDragon_ru\LaraASP\Documentator\Commands\Preprocess;
use LastDragon_ru\LaraASP\Documentator\Commands\Requirements;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters\SymfonyFileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory as LinkFactoryContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\Factory as LinkFactory;
use Override;

class PackageProvider extends ServiceProvider {
    use WithViews;

    #[Override]
    public function register(): void {
        parent::register();

        $this->app->scopedIf(LinkFactoryContract::class, LinkFactory::class);
        $this->app->scopedIf(MarkdownContract::class, Markdown::class);
        $this->app->bindIf(Adapter::class, SymfonyFileSystem::class);
    }

    public function boot(): void {
        $this->bootViews();
        $this->commands(
            Requirements::class,
            Preprocess::class,
            Commands::class,
        );
    }

    #[Override]
    protected function getName(): string {
        return Package::Name;
    }
}
