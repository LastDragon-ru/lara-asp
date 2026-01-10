<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use PHPUnit\Framework\Constraint\LogicalOr;
use Symfony\Component\Mime\MimeTypes;

class MimeType extends Response {
    /**
     * @param array<string, list<string>> $map
     */
    public function __construct(string $extension, array $map = []) {
        $types       = (new MimeTypes($map))->getMimeTypes($extension);
        $constraints = [];

        foreach ($types as $type) {
            $constraints[] = new ContentType($type);
        }

        parent::__construct(LogicalOr::fromConstraints(...$constraints));
    }
}
