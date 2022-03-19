<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Resolver;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @implements Arrayable<string,mixed>
 */
abstract class ArgsValidated implements Arrayable, ValidatesWhenResolved {
    protected Validator|null $validator = null;

    public function __construct(
        protected Container $container,
        protected Factory $factory,
        protected Args $args,
    ) {
        // empty
    }

    /**
     * @return array<string,mixed>
     */
    public function get(): array {
        return $this->getValidator()->validated();
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array {
        return $this->get();
    }

    public function validateResolved(): void {
        if ($this->getValidator()->fails()) {
            throw new ValidationException($this->getValidator());
        }
    }

    protected function getValidator(): Validator {
        if (!$this->validator) {
            $this->validator = $this->createValidator();
        }

        return $this->validator;
    }

    protected function createValidator(): Validator {
        $args       = $this->args->get();
        $rules      = $this->container->call([$this, 'rules']); /** @phpstan-ignore-line */
        $messages   = $this->messages();
        $attributes = $this->attributes();
        $validator  = $this->factory->make($args, $rules, $messages, $attributes);

        return $validator;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string,string>
     */
    public function messages(): array {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string,string>
     */
    public function attributes(): array {
        return [];
    }
}
