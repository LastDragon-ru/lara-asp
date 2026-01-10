<?php declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration

namespace LastDragon_ru\LaraASP\Serializer\Docs\Examples\Attribute;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Serializer\Casts\Serialized;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class UserSettings implements Serializable {
    public function __construct(
        public int $perPage,
        public bool $showSidebar,
    ) {
        // empty
    }
}

class User extends Model {
    /**
     * @return Attribute<?UserSettings, ?UserSettings>
     */
    protected function settings(): Attribute {
        return app()->make(Serialized::class)->attribute(UserSettings::class);
    }
}

$user           = new User();
$user->settings = new UserSettings(35, false);

Example::dump($user->settings);
Example::dump($user->getAttributes());
