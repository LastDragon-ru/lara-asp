<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use LastDragon_ru\LaraASP\Core\Helpers\Translator;

class PackageTranslator extends Translator {
    public function __construct(TranslatorContract $translator) {
        parent::__construct($translator, Package::Name);
    }
}
