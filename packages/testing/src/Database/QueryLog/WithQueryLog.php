<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\QueryLog;

use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use PHPUnit\Framework\TestCase;
use WeakMap;

use function array_map;
use function is_a;
use function is_string;
use function sprintf;

/**
 * QueryLog.
 *
 * @phpstan-require-extends TestCase
 */
trait WithQueryLog {
    /**
     * @var WeakMap<Connection, QueryLog>|null
     */
    private ?WeakMap $withQueryLog = null;

    /**
     * @before
     * @internal
     */
    public function initWithQueryLog(): void {
        $this->beforeApplicationDestroyed(function (): void {
            foreach ($this->withQueryLog ?? [] as $connection => $log) {
                /** @var Connection $connection */
                $connection->disableQueryLog();
                $connection->flushQueryLog();
            }

            $this->withQueryLog = null;
        });
    }

    /**
     * @param Connection|ConnectionResolverInterface|Model|class-string<Model>|string|null $connection
     */
    protected function getQueryLog(ConnectionResolverInterface|Connection|Model|string $connection = null): QueryLog {
        // Normalize connection
        if (is_string($connection) && is_a($connection, Model::class, true)) {
            $connection = new $connection();
        }

        if ($connection instanceof Model) {
            $connection = $connection->getConnection();
        } elseif ($connection instanceof Connection) {
            // empty
        } elseif ($connection instanceof ConnectionResolverInterface) {
            $connection = $connection->connection();
        } else {
            $connection = Container::getInstance()->make(ConnectionResolverInterface::class)->connection($connection);
        }

        // Valid?
        if (!($connection instanceof Connection)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$connection` is not invalid. Impossible to create `%s`.',
                    QueryLog::class,
                ),
            );
        }

        // Enable
        $connection->enableQueryLog();

        // Exists?
        /** @var WeakMap<Connection, QueryLog> $logs */
        $logs               = $this->withQueryLog ?? new WeakMap();
        $log                = $logs[$connection] ?? new QueryLog($connection);
        $logs[$connection]  = $log;
        $this->withQueryLog = $logs;

        // Return
        return $log;
    }

    /**
     * @param QueryLog|array<array-key, Query|array{query: string, bindings: array<array-key, mixed>}|string> $expected
     * @param QueryLog|array<array-key, Query|array{query: string, bindings: array<array-key, mixed>}>        $actual
     */
    public static function assertQueryLogEquals(
        QueryLog|array $expected,
        QueryLog|array $actual,
        string $message = '',
    ): void {
        $prepare  = Args::getDatabaseQuery(...);
        $expected = array_map($prepare, $expected instanceof QueryLog ? $expected->get() : $expected);
        $actual   = array_map($prepare, $actual instanceof QueryLog ? $actual->get() : $actual);

        self::assertEquals($expected, $actual, $message);
    }
}
