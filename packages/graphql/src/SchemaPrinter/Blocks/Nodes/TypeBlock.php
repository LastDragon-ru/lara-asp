<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes;

use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @template TType of Type
 */
abstract class TypeBlock extends Block implements Named {
    /**
     * @param TType $type
     */
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        private Type $type,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);
    }

    public function getName(): string {
        return $this->getType()->name;
    }

    /**
     * @return TType
     */
    protected function getType(): Type {
        return $this->type;
    }

    protected function content(): string {
        $type        = $this->getType();
        $directives  = new Directives(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $type->astNode?->directives,
        );
        $description = new Description(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $type->description,
            $directives,
        );

        $eol     = $this->eol();
        $indent  = $this->indent();
        $content = $this->body($this->getUsed() + mb_strlen($indent));

        if ($description->getLength()) {
            $content = "{$description}{$eol}{$indent}{$content}";
        }

        if ($directives->getLength() && $this->getSettings()->isIncludeDirectives()) {
            $content = "{$content}{$eol}{$directives}";
        }

        return "{$indent}{$content}";
    }

    abstract protected function body(int $used): string;
}
