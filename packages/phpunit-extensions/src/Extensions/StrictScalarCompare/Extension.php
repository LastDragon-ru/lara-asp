<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare;

use Override;
use PHPUnit\Event\TestRunner\ExtensionBootstrapped;
use PHPUnit\Event\TestRunner\ExtensionBootstrappedSubscriber;
use PHPUnit\Runner\Extension\Extension as PHPUnitExtension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use SebastianBergmann\Comparator\Factory;

/**
 * Registers {@see Comparator} to make scalar compare strict.
 */
class Extension implements PHPUnitExtension {
    public function __construct() {
        // empty
    }

    #[Override]
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void {
        $facade->registerSubscribers(
            new class () implements ExtensionBootstrappedSubscriber {
                #[Override]
                public function notify(ExtensionBootstrapped $event): void {
                    Factory::getInstance()->register(new Comparator());
                }
            },
        );
    }
}
