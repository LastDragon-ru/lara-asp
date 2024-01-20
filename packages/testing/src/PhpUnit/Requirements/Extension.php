<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\PhpUnit\Requirements;

use Override;
use PHPUnit\Event\Test\BeforeFirstTestMethodCalledSubscriber;
use PHPUnit\Event\Test\BeforeTestMethodCalledSubscriber;
use PHPUnit\Event\Test\PreConditionCalledSubscriber;
use PHPUnit\Event\Test\PreparedSubscriber;
use PHPUnit\Runner\Extension\Extension as PHPUnitExtension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Marks test skipped if requirements don't meet.
 *
 * @see Requirement
 */
class Extension implements PHPUnitExtension {
    public function __construct() {
        // empty
    }

    #[Override]
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void {
        $checker = new Checker();

        $facade->registerSubscribers(
            new class ($checker) extends Listener implements BeforeFirstTestMethodCalledSubscriber {
                // empty
            },
            new class ($checker) extends Listener implements BeforeTestMethodCalledSubscriber {
                // empty
            },
            new class ($checker) extends Listener implements PreConditionCalledSubscriber {
                // empty
            },
            new class ($checker) extends Listener implements PreparedSubscriber {
                // empty
            },
        );
    }
}
