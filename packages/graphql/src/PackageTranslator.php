<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use LastDragon_ru\LaraASP\Core\Translator;

class PackageTranslator extends Translator {
    public function __construct(TranslatorContract $translator) {
        parent::__construct($translator, Package::Name);
    }
}
