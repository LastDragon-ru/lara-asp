<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Helpers;

use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Contracts\View\View as ViewContract;

/**
 * Special wrapper around {@see ViewFactoryContract} to help render package's views.
 */
abstract class Viewer {
    public function __construct(
        protected readonly ViewFactoryContract $factory,
    ) {
        // empty
    }

    /**
     * Should return the name of the package.
     */
    abstract protected function getName(): string;

    /**
     * @param array<string, mixed> $data
     */
    public function get(string $view, array $data = []): ViewContract {
        return $this->factory->make("{$this->getName()}::{$view}", $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = []): string {
        return $this->get($view, $data)->render();
    }
}
