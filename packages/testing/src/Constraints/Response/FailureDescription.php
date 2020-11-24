<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

trait FailureDescription {
    /**
     * @param \Illuminate\Testing\TestResponse $other
     *
     * @return string
     */
    protected function failureDescription($other): string {
        return 'Response '.$this->toString();
    }

    /**
     * @param \Illuminate\Testing\TestResponse $other
     *
     * @return string
     */
    protected function additionalFailureDescription($other): string {
        return (string) $other->baseResponse;
    }
}
