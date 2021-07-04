<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\QueryLog;

use Countable;
use Illuminate\Database\Connection;
use LogicException;

use function count;
use function sprintf;

class QueryLog implements Countable {
    public function __construct(
        protected Connection $connection,
    ) {
        // empty
    }

    public function getName(): string {
        return $this->getConnection()->getName();
    }

    public function getConnection(): Connection {
        if (!$this->connection->logging()) {
            throw new LogicException(sprintf(
                'QueryLog disabled for connection `%s`.',
                $this->connection->getName(),
            ));
        }

        return $this->connection;
    }

    /**
     * @return array<array{query: string, bindings: array<mixed>, time: float|null}>
     */
    public function get(): array {
        return $this->getConnection()->getQueryLog();
    }

    public function flush(): void {
        $this->getConnection()->flushQueryLog();
    }

    public function count(): int {
        return count($this->getConnection()->getQueryLog());
    }
}
