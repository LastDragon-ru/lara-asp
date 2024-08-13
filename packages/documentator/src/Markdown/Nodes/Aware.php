<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes;

use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use Override;

/**
 * @internal
 *
 * @phpstan-require-implements EnvironmentAwareInterface
 * @phpstan-require-implements ConfigurationAwareInterface
 */
trait Aware {
    #[Override]
    public function setEnvironment(EnvironmentInterface $environment): void {
        $object = $this->getObject();

        if ($object instanceof EnvironmentAwareInterface) {
            $object->setEnvironment($environment);
        }
    }

    #[Override]
    public function setConfiguration(ConfigurationInterface $configuration): void {
        $object = $this->getObject();

        if ($object instanceof ConfigurationAwareInterface) {
            $object->setConfiguration($configuration);
        }
    }

    abstract protected function getObject(): object;
}
