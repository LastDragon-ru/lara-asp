<?php declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration

namespace LastDragon_ru\LaraASP\Testing\Docs\Examples\MockProperties;

use LastDragon_ru\LaraASP\Testing\Mockery\PropertiesMock;
use LastDragon_ru\LaraASP\Testing\Mockery\WithProperties;
use Mockery;

readonly class A {
    public function __construct(
        protected B $b,
    ) {
        // empty
    }

    public function a(): void {
        $this->b->b();
    }
}

class B {
    public function b(): void {
        echo 1;
    }
}

$mock = Mockery::mock(A::class, new WithProperties(), PropertiesMock::class);
$mock
    ->shouldUseProperty('b')
    ->value(
        Mockery::mock(B::class), // or just `new B()`.
    );

$mock->a();
