<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Metadata\MetadataFactory;
use Override;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use function array_fill_keys;
use function array_map;
use function array_unshift;
use function class_exists;
use function is_array;
use function is_object;
use function is_string;

/**
 * Special serializer for {@see Serializable}.
 *
 * Only public properties will be serialized. Accessors/mutators/magic/etc
 * doesn't supported. If you need it, please consider using one of Symfony's
 * normalizer like {@see ObjectNormalizer}.
 *
 * @see SerializableNormalizerContextBuilder
 * @see ObjectNormalizer
 * @see MetadataFactory
 */
class SerializableNormalizer extends AbstractObjectNormalizer {
    /**
     * @var array<string, array<string, true>>
     */
    private array $attributes = [];

    /**
     * @var array<string, string>
     */
    private array $discriminators = [];

    /**
     * @param array<string, mixed> $defaultContext
     */
    public function __construct(
        MetadataFactory $metadata,
        array $defaultContext = [],
    ) {
        if (!class_exists(PropertyAccess::class)) {
            /**
             * The class is required for {@see AbstractObjectNormalizer}. We
             * need to add the package to our 'composer.json' since
             * `symfony/serializer` doesn't require it directly. But it
             * will lead to "unused package error" during CI checks. So the
             * condition needs only for CI :)
             */
        }

        parent::__construct(
            classMetadataFactory : $metadata,
            propertyTypeExtractor: $metadata,
            defaultContext       : $defaultContext,
        );
    }

    /**
     * @return array<class-string, bool>
     */
    public function getSupportedTypes(?string $format): array {
        return [
            Serializable::class => self::class === static::class,
        ];
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed {
        /**
         * I'm not sure if it is bug or not, but out the box symfony call
         * {@see AbstractObjectNormalizer::getMappedClass()} after the
         * {@see AbstractObjectNormalizer::getAllowedAttributes()}. So it uses
         * an invalid set of allowed attributes. It may lead to "extra property"
         * error while deserialization of abstract classes/interfaces. To avoid
         * it, the {@see ObjectNormalizer} adds all properties from all known
         * classes into the array of allowed properties. It looks strange...
         *
         * This is why we are trying to replace the `$type` into actual class
         * before deserialization.
         */
        $mapping  = $this->classDiscriminatorResolver?->getMappingForClass($type);
        $property = $mapping?->getTypeProperty();
        $class    = $property && is_array($data) && isset($data[$property]) && is_string($data[$property])
            ? $mapping->getClassForType($data[$property])
            : null;
        $object   = parent::denormalize($data, $class ?? $type, $format, $context);

        return $object;
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    protected function getAllowedAttributes(
        object|string $classOrObject,
        array $context,
        bool $attributesAsString = false,
    ): array|bool {
        $attributes = parent::getAllowedAttributes($classOrObject, $context, $attributesAsString);

        if (is_array($attributes)) {
            $class                    = is_object($classOrObject) ? $classOrObject::class : $classOrObject;
            $mapping                  = $this->classDiscriminatorResolver?->getMappingForMappedObject($classOrObject);
            $properties               = array_map(
                static function (mixed $attribute): string {
                    return is_object($attribute) ? $attribute->getName() : $attribute;
                },
                $attributes,
            );
            $this->attributes[$class] = array_fill_keys($properties, true);

            if ($mapping) {
                $property                     = $mapping->getTypeProperty();
                $this->discriminators[$class] = $property;

                if (!isset($this->attributes[$class][$property])) {
                    array_unshift($attributes, $attributesAsString ? $property : new AttributeMetadata($property));
                }
            }
        }

        return $attributes;
    }

    /**
     * @param array<array-key, mixed> $context
     *
     * @return array<array-key, string>
     */
    #[Override]
    protected function extractAttributes(object $object, ?string $format = null, array $context = []): array {
        return [
            /**
             * The method is never called because the {@see static::$classMetadataFactory} is always defined.
             */
        ];
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    protected function getAttributeValue(
        object $object,
        string $attribute,
        ?string $format = null,
        array $context = [],
    ): mixed {
        return $this->isDiscriminator($object::class, $attribute)
            ? $this->classDiscriminatorResolver?->getTypeForMappedObject($object)
            : $object->{$attribute};
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    protected function setAttributeValue(
        object $object,
        string $attribute,
        mixed $value,
        ?string $format = null,
        array $context = [],
    ): void {
        if ($this->isAttribute($object::class, $attribute)) {
            $object->{$attribute} = $value;
        }
    }

    /**
     * @param class-string $class
     */
    private function isDiscriminator(string $class, string $attribute): bool {
        return ($this->discriminators[$class] ?? null) === $attribute;
    }

    /**
     * @param class-string $class
     */
    private function isAttribute(string $class, string $attribute): bool {
        return $this->attributes[$class][$attribute] ?? false;
    }
}
