<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use LastDragon_ru\LaraASP\Core\Application\ConfigResolver;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;
use LastDragon_ru\LaraASP\Spa\Package;

class SpaController extends Controller {
    public function __construct(
        protected readonly ConfigResolver $config,
    ) {
        // empty
    }

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
        $repository = $this->config->getInstance();
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
