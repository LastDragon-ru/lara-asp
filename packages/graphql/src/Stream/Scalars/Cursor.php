<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Scalars;

use GraphQL\Type\Definition\StringType;
use LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive;

class Cursor extends StringType {
    public const Name = Directive::Name.'Cursor';

    public string $name = self::Name;

    public ?string $description = <<<'DESCRIPTION'
        Represents a cursor for the `@stream` directive. The value can be a
        positive `Int` or a `String`. The `Int` value represents the offset
        (zero-based) to navigate to any position within the stream (= cursor
        pagination). And the `String` value represents the cursor and allows
        navigation only to the previous/next pages (= offset pagination).
        DESCRIPTION;
}
