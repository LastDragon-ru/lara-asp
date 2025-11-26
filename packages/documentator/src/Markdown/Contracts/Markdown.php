<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Contracts;

use LastDragon_ru\Path\FilePath;

interface Markdown {
    public function parse(string $content, ?FilePath $path = null): Document;

    public function render(Document $document): string;
}
