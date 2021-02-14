<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Queueables;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\Factory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use LastDragon_ru\LaraASP\Queue\Concerns\Configurable;
use LastDragon_ru\LaraASP\Queue\Concerns\WithConfig;
use LastDragon_ru\LaraASP\Queue\Concerns\WithInitialization;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;

abstract class Mail extends Mailable implements ShouldQueue, ConfigurableQueueable {
    use Queueable;
    use SerializesModels;
    use Configurable;
    use WithConfig;
    use WithInitialization;

    // <editor-fold desc="\Illuminate\Contracts\Mail\Mailable">
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function send($mailer) {
        $this->ifInitialized(function () use ($mailer): mixed {
            parent::send($mailer);
        });
    }

    /**
     * @inheritdoc
     */
    public function queue(Factory $queue) {
        return $this->ifInitialized(function () use ($queue): mixed {
            return parent::queue($queue);
        });
    }

    /**
     * @inheritdoc
     */
    public function later($delay, Factory $queue) {
        return $this->ifInitialized(function () use ($delay, $queue): mixed {
            return parent::later($delay, $queue);
        });
    }
    // </editor-fold>

    // <editor-fold desc="\Illuminate\Contracts\Support\Renderable">
    // =========================================================================
    public function render(): string {
        return $this->ifInitialized(function () {
            return parent::render();
        });
    }
    // </editor-fold>
}
