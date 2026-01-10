<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use LastDragon_ru\LaraASP\Core\Application\ConfigResolver;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;
use LastDragon_ru\LaraASP\Spa\PackageConfig;

class SpaController extends Controller {
    public function __construct(
        protected readonly ConfigResolver $config,
        protected readonly PackageConfig $configuration,
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
        $config   = $this->config->getInstance();
        $default  = [
            ConfigMerger::Strict => false,
            'title'              => $config->get('app.name'),
            'upload'             => [
                'max' => UploadedFile::getMaxFilesize(),
            ],
        ];
        $custom   = $this->configuration->getInstance()->spa;
        $settings = (new ConfigMerger())->merge($default, $custom);

        return $settings;
    }
    //</editor-fold>
}
