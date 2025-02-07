<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataError;

class ClassObjectNotFound extends MetadataError {
    public function __construct() {
        parent::__construct('Class not found.');
    }
}
