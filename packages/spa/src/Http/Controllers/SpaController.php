<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Controllers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use LastDragon_ru\LaraASP\Core\Utils\ConfigRecursiveMerger;
use LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar\NullResource;
use LastDragon_ru\LaraASP\Spa\Http\Resources\UserResource;
use LastDragon_ru\LaraASP\Spa\Package;

class SpaController extends Controller {
    // <editor-fold desc="Actions">
    // =========================================================================
    /**
     * Returns SPA settings.
     *
     * @return array<string, mixed>
     */
    public function settings(Repository $config): array {
        return $this->getSettings($config);
    }

    public function user(Request $request): UserResource|NullResource {
        return $request->user()
            ? new UserResource($request->user())
            : new NullResource();
    }
    // </editor-fold>

    // <editor-fold desc="Extensions">
    // =========================================================================
    /**
     * @return array<string, mixed>
     */
    protected function getSettings(Repository $config): array {
        $package  = Package::Name;
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
