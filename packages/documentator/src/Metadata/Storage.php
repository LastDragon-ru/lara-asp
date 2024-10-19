<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Metadata;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Utils\Sorter;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Symfony\Component\Serializer\Context\Encoder\JsonEncoderContextBuilder;

use function file_get_contents;
use function file_put_contents;
use function is_file;
use function trim;
use function uksort;
use function usort;

use const JSON_BIGINT_AS_STRING;
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_LINE_TERMINATORS;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @internal
 */
class Storage {
    private const Format  = 'json';
    private const Options = JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_LINE_TERMINATORS
        | JSON_BIGINT_AS_STRING
        | JSON_PRESERVE_ZERO_FRACTION
        | JSON_PRETTY_PRINT
        | JSON_THROW_ON_ERROR;

    public function __construct(
        private readonly Serializer $serializer,
        private readonly Sorter $sorter,
        private readonly DirectoryPath $path,
    ) {
        // empty
    }

    protected function getPath(): string {
        return (string) $this->path->getFilePath('metadata.json');
    }

    public function load(): Metadata {
        $metadata = is_file($this->getPath()) ? file_get_contents($this->getPath()) : false;
        $metadata = $metadata !== false
            ? $this->serializer->deserialize(Metadata::class, $metadata, self::Format)
            : new Metadata();

        return $metadata;
    }

    public function save(Metadata $metadata): bool {
        $metadata = $this->normalize($metadata);
        $context  = (new JsonEncoderContextBuilder())->withEncodeOptions(self::Options)->toArray();
        $content  = $this->serializer->serialize($metadata, self::Format, $context);
        $result   = file_put_contents($this->getPath(), trim($content)."\n") !== false;

        return $result;
    }

    protected function normalize(Metadata $metadata): Metadata {
        $stringComparator  = $this->sorter->forString(SortOrder::Asc);
        $versionComparator = $this->sorter->forVersion(SortOrder::Desc);

        uksort($metadata->requirements, $versionComparator);

        foreach ($metadata->requirements as &$requirement) {
            uksort($requirement, $stringComparator);

            foreach ($requirement as &$versions) {
                usort($versions, $versionComparator);
            }
        }

        return $metadata;
    }
}
