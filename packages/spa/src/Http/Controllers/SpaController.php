<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Controllers;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;
use LastDragon_ru\LaraASP\Spa\Package;

class SpaController extends Controller {
    // <editor-fold desc="Actions">
    // =========================================================================
    /**
     * Returns SPA settings.
     *
     * @return array<string, mixed>
     */
    public function settings(): array {
        return $this->getSettings();
    }
    // </editor-fold>

    // <editor-fold desc="Extensions">
    // =========================================================================
    /**
     * @return array<string, mixed>
     */
    protected function getSettings(): array {
        $repository = Container::getInstance()->make(Repository::class);
        $package    = Package::Name;
        $default    = [
            ConfigMerger::Strict => false,
            'title'              => $repository->get('app.name'),
            'upload'             => [
                'max' => UploadedFile::getMaxFilesize(),
            ],
        ];
        $custom     = $repository->get("{$package}.spa");
        $settings   = (new ConfigMerger())->merge($default, $custom);

        return $settings;
    }
    //</editor-fold>
}
