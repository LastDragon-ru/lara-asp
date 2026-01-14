<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\GraphQL;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;

readonly class Expected {
    public function __construct(
        public Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|string $value,
        /**
         * @var list<non-empty-string>|null
         */
        public ?array $types = null,
        /**
         * @var list<non-empty-string>|null
         */
        public ?array $directives = null,
        public ?Settings $settings = null,
        public ?Schema $schema = null,
    ) {
        // empty
    }
}
