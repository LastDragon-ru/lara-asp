<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

use function array_filter;
use function count;
use function mb_strlen;

use const ARRAY_FILTER_USE_BOTH;

/**
 * @internal
 *
 * @extends DefinitionBlock<Schema>
 */
#[GraphQLDefinition(Schema::class)]
class SchemaDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        Schema $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'schema';
    }

    protected function content(): string {
        $content = parent::content();

        if ($this->isUseDefaultRootOperationTypeNames()) {
            $content = '';
        }

        return $content;
    }

    protected function body(int $used): Block|string|null {
        return null;
    }

    protected function fields(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $fields     = new RootOperationTypesDefinition(
            $this->getContext(),
            $this->getLevel(),
            $used + mb_strlen($space),
        );
        $types      = [
            ['query', $definition->getQueryType()],
            ['mutation', $definition->getMutationType()],
            ['subscription', $definition->getSubscriptionType()],
        ];

        foreach ($types as $config) {
            [$operation, $type] = $config;

            if ($type) {
                $fields[] = new RootOperationTypeDefinition(
                    $this->getContext(),
                    $this->getLevel() + 1,
                    $this->getUsed(),
                    $operation,
                    $type,
                );
            }
        }

        return $this->addUsed($fields);
    }

    public function isUseDefaultRootOperationTypeNames(): bool {
        // Directives?
        if (count($this->getDefinitionDirectives()) > 0) {
            return false;
        }

        // Names?
        $definition  = $this->getDefinition();
        $rootTypes   = [
            'Query'        => $definition->getQueryType(),
            'Mutation'     => $definition->getMutationType(),
            'Subscription' => $definition->getSubscriptionType(),
        ];
        $nonStandard = array_filter(
            $rootTypes,
            static function (?ObjectType $type, string $name): bool {
                return $type !== null && $type->name !== $name;
            },
            ARRAY_FILTER_USE_BOTH,
        );

        return !$nonStandard;
    }
}
