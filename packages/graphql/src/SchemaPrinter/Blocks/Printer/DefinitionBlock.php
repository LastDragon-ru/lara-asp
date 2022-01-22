<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\DirectiveUsed;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\Event;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\TypeUsed;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\DirectiveDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\EnumTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\InputObjectTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\InterfaceTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\ObjectTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\ScalarTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\SchemaDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\UnionTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Exceptions\TypeUnsupported;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

/**
 * @internal
 */
class DefinitionBlock extends Block implements Named {
    private Block $block;

    /**
     * @var array<string, string>
     */
    private array $usedTypes = [];

    /**
     * @var array<string, string>
     */
    private array $usedDirectives = [];

    public function __construct(
        Settings $settings,
        int $level,
        Schema|Type|Directive $definition,
    ) {
        $this->block = $this->getDefinitionBlock($definition);
        $dispatcher  = new Dispatcher();
        $dispatcher->attach(function (Event $event): void {
            if ($event instanceof DirectiveUsed) {
                $this->usedDirectives[$event->name] = $event->name;
            }

            if ($event instanceof TypeUsed) {
                $this->usedTypes[$event->name] = $event->name;
            }
        });

        parent::__construct($dispatcher, $settings, $level);
    }

    public function getName(): string {
        $name  = '';
        $block = $this->getBlock();

        if ($block instanceof Named) {
            $name = $block->getName();
        }

        return $name;
    }

    /**
     * @return array<string,string>
     */
    public function getUsedTypes(): array {
        return $this->resolve(fn (): array => $this->usedTypes);
    }

    /**
     * @return array<string,string>
     */
    public function getUsedDirectives(): array {
        return $this->resolve(fn (): array => $this->usedDirectives);
    }

    protected function getBlock(): Block {
        return $this->block;
    }

    protected function reset(): void {
        $this->usedTypes      = [];
        $this->usedDirectives = [];

        parent::reset();
    }

    protected function content(): string {
        return (string) $this->getBlock();
    }

    protected function getDefinitionBlock(Schema|Type|Directive $definition): Block {
        $block = null;

        if ($definition instanceof ObjectType) {
            $block = new ObjectTypeDefinitionBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof InputObjectType) {
            $block = new InputObjectTypeDefinitionBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof ScalarType) {
            $block = new ScalarTypeDefinitionBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof InterfaceType) {
            $block = new InterfaceTypeDefinitionBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof UnionType) {
            $block = new UnionTypeDefinitionBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof EnumType) {
            $block = new EnumTypeDefinitionBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof Directive) {
            $block = new DirectiveDefinitionBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof Schema) {
            $block = new SchemaDefinitionBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } else {
            throw new TypeUnsupported($definition);
        }

        return $block;
    }
}
