<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\App;

use Stringable;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;
use Symfony\Component\VarDumper\Dumper\CliDumper;

use function trim;

class Dumper {
    private readonly ClonerInterface $cloner;
    private readonly AbstractDumper  $dumper;

    /**
     * @var list<string>
     */
    private array $dumps = [];

    public function __construct() {
        $this->cloner = new VarCloner();
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
        $this->raw(
            (string) $this->dumper->dump($this->cloner->cloneVar($value), true),
            $expression,
            'plain',
        );
    }

    public function raw(Stringable|string $value, ?string $expression, string $type): void {
        $dump          = trim((string) $value);
        $dump          = ($expression ? "The `{$expression}` is:\n\n" : '')."```{$type}\n{$dump}\n```\n";
        $this->dumps[] = $dump;
    }
}
