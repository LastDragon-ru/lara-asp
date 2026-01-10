<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use JsonSerializable;
use LastDragon_ru\LaraASP\Eloquent\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(WithDateSerialization::class)]
final class WithDateSerializationTest extends TestCase {
    public function testSerializeDate(): void {
        // Prepare
        $trait = new class() extends Model {
            use WithDateSerialization {
                serializeDate as public;
            }
        };

        // Regular date
        $date = Mockery::mock(DateTime::class, ['now']);
        $date->makePartial();
        $date
            ->shouldReceive('jsonSerialize')
            ->never();

        self::assertSame(Date::make($date)?->toJSON(), $trait->serializeDate($date));

        // Carbon/JsonSerializable date
        $date = Mockery::mock(DateTime::class, JsonSerializable::class);
        $date
            ->shouldReceive('jsonSerialize')
            ->once()
            ->andReturn('json');

        self::assertSame('json', $trait->serializeDate($date));

        // Carbon/JsonSerializable but not a string
        $date = new class() extends DateTime implements JsonSerializable {
            #[Override]
            public function jsonSerialize(): mixed {
                return ['json'];
            }
        };

        self::assertSame(Date::make($date)?->toJSON(), $trait->serializeDate($date));
    }
}
