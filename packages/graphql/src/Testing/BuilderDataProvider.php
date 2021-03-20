<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\Unknown;

class BuilderDataProvider extends ArrayDataProvider {
    public function __construct() {
        parent::__construct([
            'Query Builder'    => [
                new Unknown(),
                static function (TestCase $test): QueryBuilder {
                    return $test->getApplication()->make('db')->table('tmp');
                },
            ],
            'Eloquent Builder' => [
                new Unknown(),
                static function (TestCase $test): EloquentBuilder {
                    return (new class() extends Model {
                        /**
                         * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
                         *
                         * @var string
                         */
                        public $table = 'tmp';
                    })->query();
                },
            ],
        ]);
    }
}
