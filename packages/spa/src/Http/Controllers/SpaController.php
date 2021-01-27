<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Controllers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\View\Factory;
use LastDragon_ru\LaraASP\Core\Utils\ConfigRecursiveMerger;
use LastDragon_ru\LaraASP\Spa\Provider;

class SpaController extends Controller {
    // <editor-fold desc="Actions">
    // =========================================================================
    public function index(Factory $view, Repository $config, UrlGenerator $url): View {
        return $view->first(['spa.index', 'index'], [
            'settings' => $this->getSettings($config, $url),
        ]);
    }

    public function settings(Repository $config, UrlGenerator $url): array {
        return $this->getSettings($config, $url);
    }
    // </editor-fold>

    // <editor-fold desc="Extensions">
    // =========================================================================
    protected function getSettings(Repository $config, UrlGenerator $url): array {
        $package  = Provider::Package;
        $default  = [
            'url'    => $url->to('/'),
            'title'  => $config->get('app.name'),
            'upload' => [
                'max' => UploadedFile::getMaxFilesize(),
            ],
        ];
        $custom   = $config->get("{$package}.spa");
        $settings = (new ConfigRecursiveMerger(false))->merge($default, $custom);

        return $settings;
    }
    //</editor-fold>
}
