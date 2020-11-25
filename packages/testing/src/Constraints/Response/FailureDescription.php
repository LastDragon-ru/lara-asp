<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

trait FailureDescription {
    /**
     * @param \Psr\Http\Message\ResponseInterface $other
     *
     * @return string
     */
    protected function failureDescription($other): string {
        return 'Response '.$this->toString();
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $other
     *
     * @return string
     */
    protected function additionalFailureDescription($other): string {
        return 'TODO'; // FIXME [!] Add failure description
    }
}
