<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\App;

use Stringable;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;
use Symfony\Component\VarDumper\Dumper\CliDumper;

use function trim;

class Dumper {
    private readonly VarCloner      $cloner;
    private readonly AbstractDumper $dumper;

    /**
     * @var list<string>
     */
    private array $dumps = [];

    public function __construct() {
        $this->cloner = new VarCloner([
            '*' => static function (object $obj, array $a, Stub $stub, bool $isNested, int $filter = 0): array {
                return $filter > 0 ? Caster::filter($a, $filter) : $a;
            },
        ]);
        $this->dumper = new CliDumper(
            flags: AbstractDumper::DUMP_LIGHT_ARRAY
                | AbstractDumper::DUMP_COMMA_SEPARATOR
                | AbstractDumper::DUMP_TRAILING_COMMA,
        );
    }

    /**
     * @return list<string>
     */
    public function getDumps(): array {
        return $this->dumps;
    }

    public function dump(mixed $value, ?string $expression): void {
        $filter = Caster::EXCLUDE_PRIVATE | Caster::EXCLUDE_PROTECTED;
        $clone  = $this->cloner->cloneVar($value, $filter)->withRefHandles(false);
        $dump   = $this->dumper->dump($clone, true);

        $this->raw((string) $dump, $expression, 'plain');
    }

    public function raw(Stringable|string $value, ?string $expression, string $type): void {
        $dump          = trim((string) $value);
        $dump          = ($expression !== null ? "The `{$expression}` is:\n\n" : '')."```{$type}\n{$dump}\n```\n";
        $this->dumps[] = $dump;
    }
}
