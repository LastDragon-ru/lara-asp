<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Matching\ValidatorInterface;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use function end;
use function in_array;
use function is_array;
use function is_null;
use function mb_strtolower;
use function str_ends_with;
use function str_starts_with;

class AcceptValidator implements ValidatorInterface {
    public const Key = 'accept';

    public function matches(Route $route, Request $request): bool {
        // Not set or accept all?
        $expected = $this->getExpected($route);

        if (is_null($expected) || $expected === Accept::Any) {
            return true;
        }

        // Check
        $accept  = $request->getAcceptableContentTypes();
        $matches = (bool) Arr::first($accept, function (string $accept) use ($request, $expected): bool {
            return $this->isAccepted($request, $expected, $accept);
        });

        // Return
        return $matches;
    }

    private function getExpected(Route $route): ?string {
        $expected = $route->getAction(static::Key);
        $expected = is_array($expected)
            ? (end($expected) ?: null)
            : $expected;

        return $expected;
    }

    private function isAccepted(Request $request, string $expected, string $accept): bool {
        $accepted = false;
        $expected = mb_strtolower($expected);
        $accept   = mb_strtolower($accept);

        switch ($expected) {
            case Accept::Json:
                // Laravel check only first value of Accept header
                $accepted = $request->expectsJson()
                    || str_ends_with($accept, '/json')
                    || str_ends_with($accept, '+json');
                break;
            case Accept::Html:
                $accepted = in_array($accept, [
                    'text/html',
                    'application/xhtml+xml',
                ], true);
                break;
            case Accept::Image:
                $accepted = str_starts_with($accept, 'image/');
                break;
            default:
                $accepted = $expected === $accept;
                break;
        }

        return $accepted;
    }
}
