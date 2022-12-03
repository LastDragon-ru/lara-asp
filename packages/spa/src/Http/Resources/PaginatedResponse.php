<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use Illuminate\Http\Resources\Json\PaginatedResourceResponse;
use LastDragon_ru\LaraASP\Core\Utils\Cast;

/**
 * @internal
 */
class PaginatedResponse extends PaginatedResourceResponse {
    /**
     * @inheritDoc
     *
     * @return array<mixed>
     */
    protected function paginationInformation($request): array {
        return [
            'meta' => parent::paginationInformation($request)['meta'],
        ];
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $paginated
     *
     * @return array<mixed>
     */
    protected function meta($paginated): array {
        return [
            'current_page' => Cast::toInt($paginated['current_page']),
            'last_page'    => isset($paginated['last_page'])
                ? Cast::toInt($paginated['last_page'])
                : null,
            'per_page'     => Cast::toInt($paginated['per_page']),
            'total'        => isset($paginated['total'])
                ? Cast::toInt($paginated['total'])
                : null,
            'from'         => Cast::toInt($paginated['from']),
            'to'           => Cast::toInt($paginated['to']),
        ];
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $data
     */
    protected function haveDefaultWrapperAndDataIsUnwrapped($data): bool {
        // Our Resources always unwrapped and we always need a wrapper for
        // paginated results.
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function wrapper() {
        return 'items';
    }
}
