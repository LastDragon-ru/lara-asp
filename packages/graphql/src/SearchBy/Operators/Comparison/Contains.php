<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Builder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\Utils\Property;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

use function strtr;

class Contains extends BaseOperator {
    public static function getName(): string {
        return 'contains';
    }

    public function getFieldDescription(): string {
        return 'Contains.';
    }

    /**
     * @inheritDoc
     */
    public function call(Builder $search, object $builder, Property $property, Argument $argument): object {
        if (!($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        $property  = $builder->getGrammar()->wrap((string) $property);
        $value     = (string) Cast::toStringable($argument->toPlain());
        $character = $this->getEscapeCharacter();

        $builder->whereRaw(
            "{$property} LIKE ? ESCAPE '{$character}'",
            [
                $this->value($this->escape($builder, $value)),
            ],
        );

        return $builder;
    }

    protected function value(string $value): string {
        return "%{$value}%";
    }

    /**
     * @param EloquentBuilder<Model>|QueryBuilder $builder
     */
    protected function escape(EloquentBuilder|QueryBuilder $builder, string $string): string {
        // See:
        // - MySQL      https://dev.mysql.com/doc/refman/8.0/en/string-comparison-functions.html#operator_like
        //   % _
        // - SQLite     https://sqlite.org/lang_expr.html#the_like_glob_regexp_and_match_operators
        //   % _
        // - PostgreSQL https://www.postgresql.org/docs/current/functions-matching.html
        //   % _
        // - SQL Server https://docs.microsoft.com/en-us/sql/t-sql/language-elements/like-transact-sql
        //   % _ [] [^]

        $grammar      = $builder->getGrammar();
        $character    = $this->getEscapeCharacter();
        $replacements = [
            '%'        => "{$character}%",
            '_'        => "{$character}_",
            $character => "{$character}{$character}",
        ];

        if ($grammar instanceof SqlServerGrammar) {
            $replacements += [
                '[' => "{$character}[",
                ']' => "{$character}]",
            ];
        }

        return strtr($string, $replacements);
    }

    protected function getEscapeCharacter(): string {
        return '!';
    }
}
