<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Controllers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use LastDragon_ru\LaraASP\Core\Utils\ConfigRecursiveMerger;
use LastDragon_ru\LaraASP\Spa\Http\Resources\NullResource;
use LastDragon_ru\LaraASP\Spa\Http\Resources\UserResource;
use LastDragon_ru\LaraASP\Spa\Provider;

class SpaController extends Controller {
    // <editor-fold desc="Actions">
    // =========================================================================
    /**
     * Returns SPA settings.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     *
     * @return array
     */
    public function settings(Repository $config): array {
        return $this->getSettings($config);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \LastDragon_ru\LaraASP\Spa\Http\Resources\NullResource|\LastDragon_ru\LaraASP\Spa\Http\Resources\UserResource
     */
    public function user(Request $request) {
        return $request->user()
            ? new UserResource($request->user())
            : new NullResource();
    }
    // </editor-fold>

    // <editor-fold desc="Extensions">
    // =========================================================================
    protected function getSettings(Repository $config): array {
        $package  = Provider::Package;
        $default  = [
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
