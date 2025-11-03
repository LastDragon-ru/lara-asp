<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use LastDragon_ru\DiyParser\Iterables\StringifyIterable;
use LastDragon_ru\DiyParser\Iterables\TokenEscapeIterable;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use LastDragon_ru\DiyParser\Tokenizer\Tokenizer;
use LastDragon_ru\DiyParser\Utils;
use LastDragon_ru\GlobMatcher\BraceExpander\BraceExpander;
use LastDragon_ru\GlobMatcher\BraceExpander\Parser\Name as BraceExpanderName;
use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\GlobMatcher\Glob\Glob;
use LastDragon_ru\GlobMatcher\Glob\Options as GlobOptions;
use LastDragon_ru\GlobMatcher\Glob\Parser\Name as GlobName;
use Override;
use Stringable;

use function implode;

readonly class GlobMatcher implements Matcher {
    public Regex   $regex;
    public Options $options;

    public function __construct(
        public string $pattern,
        ?Options $options = null,
    ) {
        $this->options = self::options($options);
        $this->regex   = $this->regex();
    }

    #[Override]
    public function match(Stringable|string $string): bool {
        return $this->regex->match($string);
    }

    private function regex(): Regex {
        $regex    = [];
        $options  = new GlobOptions(
            globstar: $this->options->globstar,
            extended: $this->options->extended,
            hidden  : $this->options->hidden,
        );
        $patterns = $this->options->braces ? new BraceExpander($this->pattern) : [$this->pattern];

        foreach ($patterns as $pattern) {
            $regex[] = (new Glob($pattern, $options))->regex->pattern;
        }

        return new Regex(
            '(?:'.implode('|', $regex).')',
            $this->options->matchMode,
            $this->options->matchCase,
        );
    }

    public static function escape(string $pattern, ?Options $options = null): string {
        $options  = self::options($options);
        $tokens   = $options->braces ? [BraceExpanderName::class, GlobName::class] : [GlobName::class];
        $iterable = (new Tokenizer($tokens))->tokenize([$pattern]);
        $iterable = new readonly class($iterable, GlobName::Backslash) extends TokenEscapeIterable {
            #[Override]
            protected function isEscapable(Token $token): bool {
                return parent::isEscapable($token)
                    && $token->name !== GlobName::Colon
                    && $token->name !== GlobName::Dot
                    && $token->name !== GlobName::Equal
                    && $token->name !== GlobName::Slash
                    && $token->name !== BraceExpanderName::Comma
                    && $token->name !== BraceExpanderName::DoubleDot;
            }
        };
        $iterable = new StringifyIterable($iterable);
        $escaped  = Utils::toString($iterable);

        return $escaped;
    }

    private static function options(?Options $options): Options {
        return $options ?? new Options();
    }
}
