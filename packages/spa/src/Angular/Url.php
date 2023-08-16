<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Angular;

use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Utils\Cast;

use function array_diff_key;
use function array_fill_keys;
use function array_keys;
use function array_map;
use function explode;
use function http_build_query;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function mb_substr;
use function parse_url;
use function preg_replace;
use function sprintf;
use function str_replace;
use function str_starts_with;

use const PHP_QUERY_RFC3986;
use const PHP_URL_PATH;

class Url {
    private string $template;
    /**
     * @var array<array-key, string>
     */
    private array $parameters;

    public function __construct(string $template) {
        $this->template   = $template;
        $this->parameters = $this->extract($template);
    }

    public function getTemplate(): string {
        return $this->template;
    }

    /**
     * @return array<array-key, string>
     */
    public function getParameters(): array {
        return $this->parameters;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function build(array $parameters = []): string {
        // Check
        $params = $this->getParameters();
        $missed = array_diff_key(array_fill_keys($params, null), $parameters);

        if ($missed) {
            throw new InvalidArgumentException(
                sprintf('Url requires the following parameters: %s.', implode(', ', array_keys($missed))),
            );
        }

        // Replace params
        $url        = Utils::uriFor($this->getTemplate());
        $parameters = (array) $this->serialize($parameters);

        foreach ($params as $param) {
            if (isset($parameters[$param])) {
                $url = $url->withPath(str_replace(":{$param}", $parameters[$param], $url->getPath()));
            }

            unset($parameters[$param]);
        }

        // Add query params
        if ($parameters) {
            $query = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
            $query = (string) preg_replace('/%5B\d+%5D/ui', '', $query);

            if ($url->getQuery()) {
                $url = $url->withQuery("{$url->getQuery()}&{$query}");
            } else {
                $url = $url->withQuery($query);
            }
        }

        // Return
        return (string) $url;
    }

    /**
     * @return list<string>
     */
    private function extract(string $template): array {
        $names = [];
        $parts = explode('/', (string) parse_url($template, PHP_URL_PATH));

        foreach ($parts as $part) {
            if (str_starts_with($part, ':')) {
                $names[] = mb_substr($part, 1);
            }
        }

        return $names;
    }

    /**
     * @return string|array<array-key, string|array<array-key, mixed>>
     */
    private function serialize(mixed $value): string|array {
        if (is_array($value)) {
            $value = array_map(function ($value) {
                return $this->serialize($value);
            }, $value);
        } else {
            $value = $this->value($value);
        }

        return $value;
    }

    private function value(mixed $value): string {
        if ($value === null) {
            $value = '';
        } elseif (is_float($value)) {
            $value = str_replace(',', '.', (string) $value);
        } elseif (is_bool($value)) {
            $value = (string) ((int) $value);
        } elseif (is_int($value)) {
            $value = (string) $value;
        } else {
            $value = (string) Cast::toStringable($value);
        }

        return $value;
    }
}
