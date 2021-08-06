<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\QueryLog;

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use WeakMap;

use function is_a;
use function is_string;
use function sprintf;

/**
 * QueryLog.
 *
 * @required {@link \Illuminate\Foundation\Testing\TestCase}
 * @required {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits}
 *
 * @property-read \Illuminate\Foundation\Application $app
 *
 * @mixin \PHPUnit\Framework\TestCase
 */
trait WithQueryLog {
    /**
     * @var \WeakMap<\Illuminate\Database\Connection, \LastDragon_ru\LaraASP\Testing\Database\QueryLog\QueryLog>
     */
    private WeakMap $withQueryLogConnections;

    protected function setUpWithQueryLog(): void {
        $this->withQueryLogConnections = new WeakMap();
    }

    protected function tearDownWithQueryLog(): void {
        foreach ($this->withQueryLogConnections as $connection => $log) {
            /** @var \Illuminate\Database\Connection $connection */
            $connection->disableQueryLog();
            $connection->flushQueryLog();
        }

        unset($this->withQueryLogConnections);
    }

    /**
     * @param \Illuminate\Database\Connection
     *          |\Illuminate\Database\ConnectionResolverInterface
     *          |\Illuminate\Database\Eloquent\Model
     *          |class-string<\Illuminate\Database\Eloquent\Model>
     *          |string
     *          |null $connection
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
            throw new InvalidArgumentException(sprintf(
                'The `$connection` is not invalid. Impossible to create `%s`.',
                QueryLog::class,
            ));
        }

        // Enable
        $connection->enableQueryLog();

        // Exists?
        if (!isset($this->withQueryLogConnections[$connection])) {
            $this->withQueryLogConnections[$connection] = new QueryLog($connection);
        }

        // Return
        return $this->withQueryLogConnections[$connection];
    }
}
