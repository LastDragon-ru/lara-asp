<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes;

use GraphQL\Type\Definition\ScalarType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

class Scalar extends Block implements Named {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        private ScalarType $scalar,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);
    }

    protected function getScalar(): ScalarType {
        return $this->scalar;
    }

    public function getName(): string {
        return "scalar {$this->getScalar()->name}";
    }

    protected function content(): string {
        $name        = $this->getName();
        $scalar      = $this->getScalar();
        $directives  = new Directives(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $scalar->astNode?->directives,
        );
        $description = new Description(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $scalar->description,
            $directives,
        );

        $eol     = $this->eol();
        $indent  = $this->indent();
        $content = $name;

        if ($description->getLength()) {
            $content = "{$indent}{$description}{$eol}{$indent}{$content}";
        }

        if ($directives->getLength() && $this->getSettings()->isIncludeDirectives()) {
            $content = "{$content}{$eol}{$directives}";
        }

        return $content;
    }
}
