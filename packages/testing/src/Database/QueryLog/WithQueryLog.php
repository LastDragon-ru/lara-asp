<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\QueryLog;

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase;
use InvalidArgumentException;
use WeakMap;
use function is_a;
use function is_string;
use function sprintf;

/**
 * QueryLog.
 *
 * @mixin TestCase
 */
trait WithQueryLog {
    /**
     * @var WeakMap<Connection, QueryLog>
     */
    private WeakMap $withQueryLog;

    /**
     * @before
     * @internal
     */
    public function initWithQueryLog(): void {
        $this->withQueryLog = new WeakMap();

        $this->beforeApplicationDestroyed(function (): void {
            foreach ($this->withQueryLog as $connection => $log) {
                /** @var Connection $connection */
                $connection->disableQueryLog();
                $connection->flushQueryLog();
            }

            unset($this->withQueryLog);
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
        } elseif ($connection instanceof ConnectionResolverInterface) {
            $connection = $connection->connection();
        } else {
            $connection = $this->app->make('db')->connection($connection);
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
        $log                             = $this->withQueryLog[$connection] ?? new QueryLog($connection);
        $this->withQueryLog[$connection] = $log;

        // Return
        return $log;
    }
}
