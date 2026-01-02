<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals;

use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Output;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Flag;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Message;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Status;

use function array_key_last;
use function in_array;
use function iterator_to_array;
use function max;
use function mb_str_pad;
use function str_repeat;

/**
 * @internal
 */
class Renderer {
    protected Width $width;

    public function __construct(
        protected Output $output,
        protected Formatter $formatter,
    ) {
        $mark        = $this->output->length($this->mark(Mark::Dot));
        $padding     = $this->output->length($this->padding(1));
        $this->width = new Width($this->output, $this->formatter, $mark, $padding);
    }

    /**
     * @param int<0, max> $padding
     *
     * @return iterable<mixed, string>
     */
    public function title(Message|string $title, int $padding = 0, ?Mark $mark = null): iterable {
        return $this->split($this->message($title), null, $padding, $mark);
    }

    /**
     * @param iterable<mixed, array{Mark, Message|string, string}> $properties
     * @param int<0, max>                                          $padding
     *
     * @return iterable<mixed, string>
     */
    public function properties(iterable $properties, int $padding = 0): iterable {
        // Just in case
        yield from [];

        // Width
        $width = 0;
        $items = [];

        foreach ($properties as [$mark, $message, $value]) {
            $title   = "{$this->mark($mark)}{$this->message($message)} ";
            $width   = max($width, $this->width->width($title));
            $items[] = [$title, $value];
        }

        if ($items === []) {
            return;
        }

        // Render
        $limit     = max(1, $this->width->total - $this->width->padding * $padding - $width);
        $length    = $this->width->total - $limit;
        $initial   = $this->padding($padding);
        $continues = $this->padding($padding + 1);

        foreach ($items as [$iTitle, $iValue]) {
            $parts = $this->output->split($iValue, $limit);
            $first = true;

            foreach ($parts as $part) {
                $line  = mb_str_pad($first ? $initial.$iTitle : $continues, $length).$part;
                $first = false;

                yield $line;
            }
        }
    }

    /**
     * @param int<0, max> $padding
     * @param ?list<Flag> $flags
     *
     * @return iterable<mixed, string>
     */
    public function run(
        Message|string $title,
        int $padding = 0,
        ?Mark $mark = null,
        ?string $value = null,
        ?array $flags = null,
        ?Status $status = null,
        ?float $duration = null,
    ): iterable {
        $suffix = "{$this->flags($flags)} {$this->duration($duration)} {$this->status($status)}";
        $limit  = $this->width->total - $this->width->flags - $this->width->status - $this->width->duration - 3;
        $title  = $this->message($title);
        $lines  = iterator_to_array($this->split($title, $limit, $padding, $mark));
        $last   = array_key_last($lines);

        if ($last === null) {
            yield from $lines;
        }

        $fill   = $this->formatter->mark(Mark::Fill);
        $value  = $value !== null ? ' '.$value : '';
        $length = $this->output->length($value);
        $filler = max(0, $limit - $this->output->length($lines[$last]));

        if ($filler >= $length) {
            $filler       = $filler - $length - 1;
            $filler       = $filler >= 0 ? ' '.str_repeat($fill, $filler) : '';
            $lines[$last] = $lines[$last].$filler.$value.' '.$suffix;
        } else {
            $filler  = $limit - $length - $this->width->padding * ($padding + 1);
            $filler  = $filler >= 0 ? str_repeat($fill, $filler) : '';
            $lines[] = $this->padding($padding + 1).$filler.$value.' '.$suffix;
        }

        yield from $lines;
    }

    /**
     * @param ?list<Flag> $flags
     */
    protected function flags(?array $flags): string {
        $fill   = $this->formatter->mark(Mark::Fill);
        $flags  = (array) $flags;
        $cases  = Flag::cases();
        $string = '';

        foreach ($cases as $case) {
            if (in_array($case, $flags, true)) {
                $string .= $this->formatter->flag($case);
            } else {
                $string .= $fill;
            }
        }

        return $string;
    }

    protected function status(?Status $status): string {
        $value = $status !== null ? $this->formatter->status($status) : '';
        $value = str_repeat(' ', max(0, $this->width->status - $this->output->length($value))).$value;

        return $value;
    }

    protected function duration(?float $duration): string {
        $value = $duration !== null ? $this->formatter->duration($duration) : '';
        $value = str_repeat(' ', max(0, $this->width->duration - $this->output->length($value))).$value;

        return $value;
    }

    protected function mark(?Mark $mark): string {
        return $mark !== null ? "{$this->formatter->mark($mark)} " : '';
    }

    /**
     * @param int<0, max> $padding
     */
    protected function padding(int $padding): string {
        return str_repeat($this->mark(Mark::Dot), $padding);
    }

    protected function message(Message|string $message): string {
        return $message instanceof Message
            ? $this->formatter->message($message)
            : $message;
    }

    /**
     * @param int<0, max> $padding
     *
     * @return iterable<mixed, string>
     */
    protected function split(string $string, ?int $limit, int $padding = 0, ?Mark $mark = null): iterable {
        $continues = $this->padding($padding + ($mark !== null ? 1 : 0));
        $initial   = $mark !== null
            ? $this->padding($padding).$this->mark($mark)
            : $this->padding($padding);
        $limit     = ($limit ?? $this->width->total)
            - $this->width->padding * $padding
            - ($mark !== null ? $this->width->mark : 0);
        $parts     = $this->output->split($string, max(1, $limit));
        $first     = true;

        foreach ($parts as $part) {
            $line  = ($first ? $initial : $continues).$part;
            $first = false;

            yield $line;
        }

        yield from [];
    }
}
