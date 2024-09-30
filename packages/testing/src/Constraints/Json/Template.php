<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use OutOfBoundsException;

use function addslashes;
use function array_column;
use function array_unique;
use function preg_match_all;
use function sprintf;
use function str_replace;

use const PREG_SET_ORDER;

class Template {
    public function __construct(
        protected string $content,
    ) {
        // empty
    }

    /**
     * @param array<string,string> $parameters
     */
    public function build(array $parameters): string {
        $result  = $this->content;
        $matches = [];

        if (preg_match_all('/\$\{(?<var>[^}]+)\}/u', $result, $matches, PREG_SET_ORDER) > 0) {
            $variables = array_unique(array_column($matches, 'var'));

            foreach ($variables as $name) {
                if (isset($parameters[$name])) {
                    $result = str_replace("\${{$name}}", addslashes($parameters[$name]), $result);
                } else {
                    throw new OutOfBoundsException(sprintf(
                        'Required parameter `%s` is missed.',
                        $name,
                    ));
                }
            }
        }

        return $result;
    }
}
