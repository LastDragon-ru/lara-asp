<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use Http\Factory\Guzzle\ResponseFactory;
use Http\Factory\Guzzle\ServerRequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Factory\Guzzle\UploadedFileFactory;
use Illuminate\Testing\TestResponse;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;
use WeakMap;

class Factory {
    /**
     * @var WeakMap<Response,ResponseInterface>
     */
    protected static WeakMap $cache;

    public static function make(TestResponse|Response $response): ResponseInterface {
        $psrResponse = static::getCache()[$response] ?? null;

        if (!$psrResponse) {
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
     * @return WeakMap<Response,ResponseInterface>
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

        return (new PsrHttpFactory(
            new ServerRequestFactory(),
            new StreamFactory(),
            new UploadedFileFactory(),
            new ResponseFactory(),
        ))->createResponse(clone $response);
    }
}
