<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use Illuminate\Testing\TestResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;
use WeakMap;

class Factory {
    /**
     * @var WeakMap<TestResponse<*>|Response,ResponseInterface>
     */
    protected static WeakMap $cache;

    /**
     * @param TestResponse<*>|Response $response
     */
    public static function make(TestResponse|Response $response): ResponseInterface {
        $psrResponse = static::getCache()[$response] ?? null;

        if ($psrResponse === null) {
            if ($response instanceof TestResponse) {
                $psrResponse = static::create($response->baseResponse);
            } else {
                $psrResponse = static::create($response);
            }

            static::getCache()[$response] = $psrResponse;
        }

        return $psrResponse;
    }

    /**
     * @return WeakMap<TestResponse<*>|Response,ResponseInterface>
     */
    protected static function getCache(): WeakMap {
        if (!isset(static::$cache)) {
            static::$cache = new WeakMap();
        }

        return static::$cache;
    }

    protected static function create(Response $response): ResponseInterface {
        // Some responses (eg StreamedResponse) should be read only
        // one time, so we should use a cloned response and cache the
        // created PSR response (to avoid double code execution).

        $psr17Factory = new Psr17Factory();
        $psrFactory   = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $psrResponse  = $psrFactory->createResponse(clone $response);

        return $psrResponse;
    }
}
