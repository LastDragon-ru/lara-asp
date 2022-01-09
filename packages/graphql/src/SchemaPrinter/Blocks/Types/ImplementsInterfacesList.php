<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\InterfaceType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use Traversable;

use function mb_strlen;

/**
 * @internal
 * @extends BlockList<TypeBlock>
 */
class ImplementsInterfacesList extends BlockList {
    /**
     * @param Traversable<InterfaceType>|array<InterfaceType> $fields
     */
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        Traversable|array $fields,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);

        foreach ($fields as $field) {
            $this[$field->name] = new TypeBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel() + 1,
                $this->getUsed(),
                $field,
            );
        }
    }

    protected function getSeparator(): string {
        return "{$this->space()}&{$this->space()}";
    }

    protected function getMultilineItemPrefix(): string {
        return "&{$this->space()}";
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeInterfaces();
    }

    protected function content(): string {
        $prefix  = 'implements';
        $content = parent::content();

        if ($content) {
            if ($this->isStringMultiline($content)) {
                $eol     = $this->eol();
                $indent  = $this->indent();
                $content = "{$prefix}{$eol}{$indent}{$content}";
            } else {
                $space   = $this->space();
                $content = "{$prefix}{$space}{$content}";
            }
        }

        return $content;
    }

    protected function getUsed(): int {
        return parent::getUsed() + mb_strlen("implements{$this->space()}");
    }
}
