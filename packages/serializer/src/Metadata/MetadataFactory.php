<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Metadata;

use InvalidArgumentException;
use phpDocumentor\Reflection\Types\ContextFactory;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Mapping\ClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;

use function class_exists;
use function get_debug_type;
use function is_object;
use function is_string;
use function sprintf;

class MetadataFactory implements ClassMetadataFactoryInterface, PropertyTypeExtractorInterface {
    /**
     * @var array<class-string, ClassMetadata>
     */
    private array                           $metadata  = [];
    private ?PropertyInfoExtractorInterface $extractor = null;

    public function __construct() {
        // empty
    }

    /**
     * @phpstan-assert-if-true object|class-string $value
     */
    public function hasMetadataFor(mixed $value): bool {
        return is_object($value) || (is_string($value) && class_exists($value));
    }

    public function getMetadataFor(object|string $value): ClassMetadata {
        if (!$this->hasMetadataFor($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Impossible to load metadata for `%s`.',
                    get_debug_type($value),
                ),
            );
        }

        $name = is_object($value) ? $value::class : $value;

        if (!isset($this->metadata[$name])) {
            $class                 = new ReflectionClass($value);
            $metadata              = new ClassMetadata($name);
            $this->metadata[$name] = $metadata;

            $metadata->setClassDiscriminatorMapping(
                $this->getDiscriminatorMapping($class),
            );

            foreach ($class->getProperties() as $property) {
                if ($property->isPublic() && !$property->isStatic()) {
                    $metadata->addAttributeMetadata(
                        new AttributeMetadata($property->getName()),
                    );
                }
            }
        }

        return $this->metadata[$name];
    }

    /**
     * @param array<array-key, mixed> $context
     *
     * @return array<array-key, Type>|null
     */
    public function getTypes(string $class, string $property, array $context = []): ?array {
        /**
         * todo(serializer): Should we add types to {@see AttributeMetadata}?
         *      It will allow cache all metadata in one place. Not very actual
         *      now though.
         */
        return $this->hasMetadataFor($class) && isset($this->getMetadataFor($class)->getAttributesMetadata()[$property])
            ? $this->getTypeExtractor()->getTypes($class, $property, $context)
            : null;
    }

    protected function getTypeExtractor(): PropertyTypeExtractorInterface {
        if (!$this->extractor) {
            if (!class_exists(ContextFactory::class) || !class_exists(PhpDocParser::class)) {
                /**
                 * These classes are required for {@see PhpStanExtractor}. We
                 * need to add these packages to our 'composer.json' since
                 * `symfony/serializer` doesn't require them directly. But it
                 * will lead to "unused package error" during CI checks. So the
                 * condition needs only for CI :)
                 */
            }

            $this->extractor = new PropertyInfoExtractor(
                typeExtractors: [
                    // Empty arrays are required to prevent unwanted fetching of
                    // accessors/mutators.
                    new PhpStanExtractor(
                        mutatorPrefixes     : [],
                        accessorPrefixes    : [],
                        arrayMutatorPrefixes: [],
                    ),
                    new ReflectionExtractor(
                        mutatorPrefixes            : [],
                        accessorPrefixes           : [],
                        arrayMutatorPrefixes       : [],
                        enableConstructorExtraction: false,
                        accessFlags                : ReflectionExtractor::ALLOW_PUBLIC,
                        magicMethodsFlags          : ReflectionExtractor::DISALLOW_MAGIC_METHODS,
                    ),
                ],
            );
        }

        return $this->extractor;
    }

    /**
     * @param ReflectionClass<object> $class
     */
    protected function getDiscriminatorMapping(ReflectionClass $class): ?ClassDiscriminatorMapping {
        $attributes = $class->getAttributes(DiscriminatorMap::class, ReflectionAttribute::IS_INSTANCEOF);
        $mapping    = null;

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $mapping  = new ClassDiscriminatorMapping(
                $instance->getTypeProperty(),
                $instance->getMapping(),
            );

            break;
        }

        return $mapping;
    }
}
