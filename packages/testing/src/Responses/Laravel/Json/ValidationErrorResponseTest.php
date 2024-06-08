<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use Illuminate\Http\Request;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Factory as ResponseFactory;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(ValidationErrorResponse::class)]
final class ValidationErrorResponseTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array<string,string>      $rules
     * @param array<string,string>|null $errors
     */
    #[DataProvider('dataProviderEvaluate')]
    public function testEvaluate(bool $expected, array $rules, ?array $errors): void {
        $this->app()->make(Registrar::class)
            ->get(__FUNCTION__, function (Request $request) use ($rules) {
                return $this->app()->make(ValidatorFactory::class)
                    ->validate($request->all(), $rules);
            });

        $response   = ResponseFactory::make($this->getJson(__FUNCTION__));
        $constraint = new ValidationErrorResponse($errors);

        self::assertEquals($expected, $constraint->evaluate($response, '', true));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderEvaluate(): array {
        return [
            'no rules'                     => [
                false,
                [],
                null,
            ],
            'failed + any error'           => [
                true,
                [
                    'title' => 'required|min:3|max:5',
                ],
                null,
            ],
            'failed + error + any message' => [
                true,
                [
                    'title' => 'required|min:3|max:5',
                ],
                [
                    'title' => null,
                ],
            ],
            'failed + error + message'     => [
                true,
                [
                    'title' => 'required|min:3|max:5',
                ],
                [
                    'title' => 'The title field is required.',
                ],
            ],
        ];
    }

    // </editor-fold>
}
