<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator;

use Illuminate\Contracts\View\Factory as ViewFactory;
use LastDragon_ru\LaraASP\Core\Helpers\Viewer;

class PackageViewer extends Viewer {
    public function __construct(PackageTranslator $translator, ViewFactory $factory) {
        parent::__construct($translator, $factory, Package::Name);
    }
}
