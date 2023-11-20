<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Queueables;

use AllowDynamicProperties;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\Factory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use LastDragon_ru\LaraASP\Queue\Concerns\WithConfig;
use LastDragon_ru\LaraASP\Queue\Concerns\WithInitialization;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use Override;

#[AllowDynamicProperties]
abstract class Mail extends Mailable implements ShouldQueue, ConfigurableQueueable {
    use Queueable;
    use SerializesModels;
    use WithConfig;
    use WithInitialization;

    public function __construct() {
        // empty
    }

    // <editor-fold desc="ConfigurableQueueable">
    // =========================================================================
    /**
     * @inheritDoc
     */
    #[Override]
    public function getQueueConfig(): array {
        return [];
    }
    // </editor-fold>

    // <editor-fold desc="\Illuminate\Contracts\Mail\Mailable">
    // =========================================================================
    /**
     * @inheritDoc
     */
    #[Override]
    public function send($mailer) {
        $this->ifInitialized(function () use ($mailer): mixed {
            parent::send($mailer);
        });
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function queue(Factory $queue) {
        return $this->ifInitialized(function () use ($queue): mixed {
            return parent::queue($queue);
        });
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function later($delay, Factory $queue) {
        return $this->ifInitialized(function () use ($delay, $queue): mixed {
            return parent::later($delay, $queue);
        });
    }
    // </editor-fold>

    // <editor-fold desc="\Illuminate\Contracts\Support\Renderable">
    // =========================================================================
    #[Override]
    public function render(): string {
        return $this->ifInitialized(function (): string {
            return parent::render();
        });
    }
    // </editor-fold>
}
