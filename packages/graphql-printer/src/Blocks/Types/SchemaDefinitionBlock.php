<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

use function array_filter;
use function count;
use function mb_strlen;

use const ARRAY_FILTER_USE_BOTH;

/**
 * @internal
 *
 * @extends DefinitionBlock<Schema>
 */
class SchemaDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        Schema $definition,
    ) {
        parent::__construct($settings, $level, $used, $definition);
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
        $fields     = new RootOperationTypesDefinitionList(
            $this->getSettings(),
            $this->getLevel(),
            $used + mb_strlen($space),
        );
        $types      = [
            [OperationType::query(), $definition->getQueryType()],
            [OperationType::mutation(), $definition->getMutationType()],
            [OperationType::subscription(), $definition->getSubscriptionType()],
        ];

        foreach ($types as $config) {
            [$operation, $type] = $config;

            if ($type) {
                $fields[] = new RootOperationTypeDefinitionBlock(
                    $this->getSettings(),
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
