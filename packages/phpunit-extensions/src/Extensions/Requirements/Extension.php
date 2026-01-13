<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\Requirements;

use LastDragon_ru\PhpUnit\Extensions\Requirements\Contracts\Requirement;
use Override;
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
        // Unfortunately, a listener may implement only one interface. This is
        // why we are register multiple instances.
        $checker = new Checker();

        $facade->registerSubscribers(
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
