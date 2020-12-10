<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Queueables;

use Exception;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use LastDragon_ru\LaraASP\Queue\Contracts\Initializable;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use RuntimeException;
use function sprintf;


/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Queue\Queueables\Mail
 */
class MailTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::queue
     */
    public function testQueueUninitializedInitializable() {
        $this->expectExceptionObject($this->getException());

        (new MailTest_Mail())->queue($this->app->make(QueueFactory::class));
    }

    public function testLaterUninitializedInitializable() {
        $this->expectExceptionObject($this->getException());

        (new MailTest_Mail())->later(10, $this->app->make(QueueFactory::class));
    }

    public function testSendUninitializedInitializable() {
        $this->expectExceptionObject($this->getException());

        (new MailTest_Mail())->send($this->app->make(MailFactory::class));
    }

    public function testRenderUninitializedInitializable() {
        $this->expectExceptionObject($this->getException());

        (new MailTest_Mail())->render();
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    private function getException(string $class = MailTest_Mail::class): Exception {
        return new RuntimeException(sprintf('The `%s` is not initialized.', $class));
    }
    // </editor-fold>
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class MailTest_Mail extends Mail implements Initializable {
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct() {
        // empty
    }
}
