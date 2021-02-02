<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use Illuminate\Http\Resources\Json\PaginatedResourceResponse;

/**
 * @internal
 */
class PaginatedResponse extends PaginatedResourceResponse {
    /**
     * @inheritdoc
     */
    protected function paginationInformation($request) {
        return [
            'meta' => parent::paginationInformation($request)['meta'],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function meta($paginated) {
        return [
            'current_page' => (int) $paginated['current_page'],
            'last_page'    => isset($paginated['last_page'])
                ? (int) $paginated['last_page']
                : null,
            'per_page'     => (int) $paginated['per_page'],
            'total'        => isset($paginated['total'])
                ? (int) $paginated['total']
                : null,
            'from'         => (int) $paginated['from'],
            'to'           => (int) $paginated['to'],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function haveDefaultWrapperAndDataIsUnwrapped($data) {
        // Our Resources always unwrapped and we always need a wrapper for
        // paginated results.
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function wrapper() {
        return 'items';
    }
}
