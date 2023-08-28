<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Helpers;

use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Contracts\View\View as ViewContract;

/**
 * Special wrapper around {@see ViewFactoryContract} to help render package's views.
 */
abstract class Viewer {
    public function __construct(
        protected readonly Translator $translator,
        protected readonly ViewFactoryContract $factory,
        protected readonly string $package,
    ) {
        // empty
    }

    /**
     * @param array<string, mixed> $data
     */
    public function get(string $view, array $data = []): ViewContract {
        return $this->factory->make(
            "{$this->package}::{$view}",
            $this->getDefaultData() + $data,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = []): string {
        return $this->get($view, $data)->render();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultData(): array {
        return [
            'translator' => $this->translator,
        ];
    }
}
