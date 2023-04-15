# Testing Helpers

> This package is the part of Awesome Set of Packages for Laravel.
>
> [Read more](https://github.com/LastDragon-ru/lara-asp).

This package provides various useful asserts for [PHPUnit](https://phpunit.de/) and alternative solution for HTTP tests - testing HTTP response has never been so easy! And this not only about `TestResponse` but any PSR response 😎

# Installation

```shell
composer require lastdragon-ru/lara-asp-testing
```

# Usage

In the general case, you just need to update `tests/TestCase.php` to include almost everything, but you also can include only desired features, please see base [`TestCase`](./src/TestCase.php) to found what is supported.

```php
<?php declare(strict_types = 1);

namespace Tests;

use LastDragon_ru\LaraASP\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
    use CreatesApplication;
}
```

# Extensions

## [`\LastDragon_ru\LaraASP\Testing\Database\RefreshDatabaseIfEmpty`](./src/Database/RefreshDatabaseIfEmpty.php)

This trait is very similar to standard `\Illuminate\Foundation\Testing\RefreshDatabase` but there is one difference: it will refresh the database only if it empty. This is very useful for local testing and allow significantly reduce bootstrap time.

```php
<?php declare(strict_types = 1);

namespace Tests;

use LastDragon_ru\LaraASP\Testing\Database\RefreshDatabaseIfEmpty;
use LastDragon_ru\LaraASP\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
    use CreatesApplication;
    use RefreshDatabaseIfEmpty;

    protected function shouldSeed() {
        return true;
    }
}
```

## [`\LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories\Factory`](./src/Database/Eloquent/Factories/Factory.php)

This class extends standard `\Illuminate\Database\Eloquent\Factories\Factory`:

* Fixes `wasRecentlyCreated` value, by default it will be `true`. In most cases, this is unwanted behavior. The factory will set it to `false` after creating the model;
* Disables all model events while making/creating the instance.

## [`\LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog`](./src/Database/QueryLog/WithQueryLog.php)

Enables query log for the test case.

# Mixins

## `\Illuminate\Testing\TestResponse`

| Name                        | Description                                                    |
|-----------------------------|----------------------------------------------------------------|
| `assertThat()`              | Asserts that response satisfies given constraint.              |
| `assertContentType()`       | Asserts that a response has a specified content type.          |
| `assertStatusCode()`        | Asserts that a response has a specified status code.           |
| `assertJsonMatchesSchema()` | Asserts that a response contains JSON that matches the schema. |
| `assertXmlMatchesSchema()`  | Asserts that a response contains XML that matches the schema.  |

# Assertions

| :warning: | By default package overrides scalar comparator to make it strict! So `assertEquals(true, 1)` is `false`. |
|:---------:|:---------------------------------------------------------------------------------------------------------|

## General

These assertions can be used without Laravel at all (#4).

| Name                                                                   | Description                                           |
|------------------------------------------------------------------------|-------------------------------------------------------|
| [`assertJsonMatchesSchema()`](./src/Assertions/JsonAssertions.php#L17) | Asserts that JSON matches schema                      |
| [`assertXmlMatchesSchema()`](./src/Assertions/XmlAssertions.php#L15)   | Asserts that XML matches schema (XSD or Relax NG).    |
| [`assertThatResponse()`](./src/Assertions/ResponseAssertions.php#L14)  | Asserts that PSR Response satisfies given constraint. |

## Laravel

| Name                                                                                    | Description                                                          |
|-----------------------------------------------------------------------------------------|----------------------------------------------------------------------|
| [`assertDatabaseQueryEquals()`](./src/Assertions/DatabaseAssertions.php)                | Asserts that SQL Query equals SQL Query.                             |
| [`assertScoutQueryEquals()`](./src/Assertions/ScoutAssertions.php)                      | Asserts that Scout Query equals Scout Query.                         |
| [`assertCronableRegistered()`](./src/Assertions/Application/CronableAssertions.php#L30) | Asserts that [Cronable](../queue/README.md#cron-jobs) is registered. |

# Laravel Response Testing

What is wrong with the [Laravel approach](https://laravel.com/docs/http-tests)? Well, there are two big problems.

## Where is the error?

You never know why your test failed and need to debug it to find the reason. Real-life example:

```php
<?php declare(strict_types = 1);

namespace App\Http\Controllers;

use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(IndexController::class)]
class IndexControllerTest extends TestCase {
    public function testIndex() {
        $this->get('/')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');
    }
}
```

<details>
<summary>assertOk() failed</summary>

```text
Testing started at 15:46 ...
PHPUnit 9.5.0 by Sebastian Bergmann and contributors.

Random Seed:   1610451974


Expected status code 200 but received 500.
Failed asserting that 200 is identical to 500.
 vendor/laravel/framework/src/Illuminate/Testing/TestResponse.php:186
 app/Http/Controllers/IndexControllerTest.php:16



Time: 00:01.373, Memory: 26.00 MB
```

</details>

<details>
<summary>assertHeader() failed</summary>

```text
Testing started at 17:57 ...
PHPUnit 9.5.0 by Sebastian Bergmann and contributors.

Random Seed:   1610459878


Header [Content-Type] was found, but value [text/html; charset=UTF-8] does not match [application/json].
Failed asserting that two values are equal.
Expected :'application/json'
Actual   :'text/html; charset=UTF-8'
<Click to see difference>

 vendor/laravel/framework/src/Illuminate/Testing/TestResponse.php:229
 app/Http/Controllers/IndexControllerTest.php:18



Time: 00:01.082, Memory: 24.00 MB


FAILURES!
Tests: 1, Assertions: 3, Failures: 1.

Process finished with exit code 1
```

</details>

> Expected status code 200 but received 500.

Hmmm, 500, probably this is php error? Why? Where? 😰

Compare with:

```php
<?php declare(strict_types = 1);

namespace App\Http\Controllers;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(IndexController::class)]
class IndexControllerTest extends TestCase {
    public function testIndex() {
        $this->get('/')->assertThat(new Response(
            new Ok(),
            new JsonContentType()
        ));
    }
}
```

<details>
<summary>assertThat() failed</summary>

```text
PHPUnit 9.5.0 by Sebastian Bergmann and contributors.

Random Seed:   1610461475


Failed asserting that GuzzleHttp\Psr7\Response Object &000000001ef973410000000013328b0b (
    'reasonPhrase' => 'Internal Server Error'
    'statusCode' => 500
    'headers' => Array &0 (
        'cache-control' => Array &1 (
            0 => 'no-cache, private'
        )
        'date' => Array &2 (
            0 => 'Tue, 12 Jan 2021 14:24:36 GMT'
        )
        'content-type' => Array &3 (
            0 => 'text/html; charset=UTF-8'
        )
    )
    'headerNames' => Array &5 (
        'cache-control' => 'cache-control'
        'date' => 'date'
        'content-type' => 'content-type'
        'set-cookie' => 'Set-Cookie'
    )
    'protocol' => '1.1'
    'stream' => GuzzleHttp\Psr7\Stream Object &000000001ef972d20000000013328b0b (
        'stream' => resource(846) of type (stream)
        'size' => null
        'seekable' => true
        'readable' => true
        'writable' => true
        'uri' => 'php://temp'
        'customMetadata' => Array &6 ()
    )
) has Status Code is equal to 200.

<!doctype html>
<html class="theme-light">
<!--
Error: Call to undefined function App\Http\Controllers\dview() in file app/Http/Controllers/IndexController.php on line 7

#0 vendor/laravel/framework/src/Illuminate/Routing/Controller.php(54): App\Http\Controllers\IndexController-&gt;index()
#1 vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php(45): Illuminate\Routing\Controller-&gt;callAction()
#2 vendor/laravel/framework/src/Illuminate/Routing/Route.php(254): Illuminate\Routing\ControllerDispatcher-&gt;dispatch()
#3 vendor/laravel/framework/src/Illuminate/Routing/Route.php(197): Illuminate\Routing\Route-&gt;runController()
#4 vendor/laravel/framework/src/Illuminate/Routing/Router.php(692): Illuminate\Routing\Route-&gt;run()
#5 vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(128): Illuminate\Routing\Router-&gt;Illuminate\Routing\{closure}()
#6 vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php(41): Illuminate\Pipeline\Pipeline-&gt;Illuminate\Pipeline\{closure}()
#7 vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(167): Illuminate\Routing\Middleware\SubstituteBindings-&gt;handle()
#8 vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php(78): Illuminate\Pipeline\Pipeline-&gt;Illuminate\Pipeline\{closure}()
...


Time: 00:01.356, Memory: 28.00 MB


FAILURES!
Tests: 1, Assertions: 1, Failures: 1.

Process finished with exit code 1
```

</details>

## Reusing the test code is problematic

In most real applications you have multiple roles (eg `guest`, `user`, `admin`), guards, and policies. Very difficult to test all of them and usually you need create many `testRouteIsNotAvailableForGuest()`, `testRouteIsAvailableForAdminOnly()`, etc with a lot of boilerplate code. Also, often you cannot reuse that (boilerplate) code and must write it again and again. That is really annoying.

Resolving this problem is very simple. First, we need to create classes for the required Responses (actually package already provides few most [used responses](./src/Responses/Laravel) 🙄). Let's start with a simple JSON response:

```php
<?php declare(strict_types = 1);

namespace Tests\Responses;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;

class JsonResponse extends Response {
    public function __construct() {
        parent::__construct(
            new Ok(),
            new JsonContentType(),
        );
    }
}
```

Next, lets add JSON Validation Error:

```php
<?php declare(strict_types = 1);

namespace Tests\Responses;

use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Body;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\UnprocessableEntity;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;

class ValidationErrorResponse extends Response {
    use WithTestData;

    public function __construct() {
        parent::__construct(
            new UnprocessableEntity(),
            new JsonContentType(),
            new Body([
                new JsonMatchesSchema(new JsonSchema(self::getTestData(self::class)->file('.json'))),
            ]),
        );
    }
}
```

Finally, the test:

```php
<?php declare(strict_types = 1);

namespace App\Http\Controllers;

use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Responses;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(IndexController::class)]
class IndexControllerTest extends TestCase {
    public function testIndex() {
        $this->getJson('/')->assertThat(new ValidationErrorResponse());
    }

    public function testTest() {
        $this->getJson('/test')->assertThat(new ValidationErrorResponse());
    }
}
```

The same test with default assertions may look something like this:

```php
<?php declare(strict_types = 1);

namespace App\Http\Controllers;

use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(IndexController::class)]
class IndexControllerTest extends TestCase {
    public function testIndex() {
        $this->getJson('/')
            ->assertStatus(422)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function testTest() {
        $this->getJson('/test')
            ->assertStatus(422)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'message',
                'errors',
            ]);;
    }
}
```

Feel the difference 😉

# PSR Response Testing

Internally package uses `PSR-7` so you can test any `Psr\Http\Message\ResponseInterface` 🤩

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Testing\Assertions\ResponseAssertions;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use PHPUnit\Framework\TestCase;

class ResponseInterfaceTest extends TestCase {
    use ResponseAssertions;

    public function testResponse() {
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = null;

        self::assertThatResponse($response, new Response(
            new Ok(),
            new JsonContentType(),
        ));
    }
}
```

# Data Providers on steroids

There is another cool feature that allows us to test a lot of use cases without code duplication - the [`CompositeDataProvider`](./src/Providers/CompositeDataProvider.php). It's merging multiple provides into one in the following way:

```text
Providers:
[
    ['expected a', 'value a'],
    ['expected final', 'value final'],
]
[
    ['expected b', 'value b'],
    ['expected c', 'value c'],
]
[
    ['expected d', 'value d'],
    ['expected e', 'value e'],
]
 *
Merged:
[
    '0 / 0 / 0' => ['expected d', 'value a', 'value b', 'value d'],
    '0 / 0 / 1' => ['expected e', 'value a', 'value b', 'value e'],
    '0 / 1 / 0' => ['expected d', 'value a', 'value c', 'value d'],
    '0 / 1 / 1' => ['expected e', 'value a', 'value c', 'value e'],
    '1'         => ['expected final', 'value final'],
]
```

So we can organize our tests like this:

```php
<?php declare(strict_types = 1);

namespace Tests\Feature;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\NotFound;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Unauthorized;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\DataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\ExpectedFinal;
use LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json\ValidationErrorResponse;
use Tests\TestCase;

class ExampleTest extends TestCase {
    // <editor-fold desc="Prepare">
    // =========================================================================
    public function setUp(): void {
        parent::setUp();

        Route::get('/users/{user}', function (User $user) {
            return $user->email;
        })->middleware(['auth', SubstituteBindings::class]);

        Route::post('/users/{user}', function (Request $request, User $user) {
            $user->email = $request->validate([
                'email' => 'required|email',
            ]);

            return $user->email;
        })->middleware(['auth', SubstituteBindings::class]);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderGet
     */
    public function testGet(Response $expected, Closure $actingAs = null, Closure $user = null): void {
        $user = $user ? $user()->getKey() : 0;

        if ($actingAs) {
            $this->actingAs($actingAs());
        }

        $this->getJson("/users/{$user}")->assertThat($expected);
    }

    /**
     * @dataProvider dataProviderUpdate
     */
    public function testUpdate(Response $expected, Closure $actingAs = null, Closure $user = null, array $data = []) {
        $user = $user ? $user()->getKey() : 0;

        if ($actingAs) {
            $this->actingAs($actingAs());
        }

        $this->postJson("/users/{$user}", $data)->assertThat($expected);
    }

    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    public static function dataProviderGet(): array {
        return (new CompositeDataProvider(
            self::getUserDataProvider(),
            self::getModelDataProvider(),
        ))->getData();
    }

    public static function dataProviderUpdate(): array {
        return (new CompositeDataProvider(
            self::getUserDataProvider(),
            self::getModelDataProvider(),
            new ArrayDataProvider([
                'no email'      => [
                    new ValidationErrorResponse(['email' => null]),
                    [],
                ],
                'invalid email' => [
                    new ValidationErrorResponse([
                        'email' => 'The email must be a valid email address.',
                    ]),
                    [
                        'email' => '123',
                    ],
                ],
                'valid email'   => [
                    new Ok(),
                    [
                        'email' => 'test@example.com',
                    ],
                ],
            ])
        ))->getData();
    }
    // </editor-fold>

    // <editor-fold desc="Shared">
    // =========================================================================
    protected static function getUserDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'guest'         => [
                new ExpectedFinal(new Unauthorized()),
                null,
            ],
            'authenticated' => [
                new Ok(),
                function () {
                    return User::factory()->create();
                },
            ],
        ]);
    }

    protected static function getModelDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'user not exists' => [
                new ExpectedFinal(new NotFound()),
                null,
            ],
            'user exists'     => [
                new Ok(),
                function () {
                    return User::factory()->create();
                },
            ],
        ]);
    }
    // </editor-fold>
}
```

Enjoy 😸
