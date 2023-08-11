<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers;

use Closure;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use phpDocumentor\Reflection\Types\ContextFactory;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

use function array_unique;
use function class_exists;
use function is_string;

final class SerializableNormalizer extends AbstractObjectNormalizer {
    /**
     * @param Closure(object): class-string|null $objectClassResolver
     * @param array<string, mixed>               $defaultContext
     */
    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        callable $objectClassResolver = null,
        array $defaultContext = [],
    ) {
        if (class_exists(ContextFactory::class) && class_exists(PhpDocParser::class)) {
            $propertyTypeExtractor ??= new PropertyInfoExtractor(
                typeExtractors: [
                    new PhpStanExtractor(),
                    new ReflectionExtractor(
                        magicMethodsFlags: ReflectionExtractor::DISALLOW_MAGIC_METHODS,
                    ),
                ],
            );
        }

        parent::__construct(
            $classMetadataFactory,
            $nameConverter,
            $propertyTypeExtractor,
            $classDiscriminatorResolver,
            $objectClassResolver,
            $defaultContext,
        );
    }

    /**
     * @return array<class-string, bool>
     */
    public function getSupportedTypes(?string $format): array {
        return [
            Serializable::class => true,
        ];
    }

    /**
     * @param array<array-key, mixed> $context
     *
     * @return array<array-key, string>
     */
    protected function extractAttributes(object $object, string $format = null, array $context = []): array {
        $class      = new ReflectionObject($object);
        $attributes = [];

        foreach ($class->getProperties() as $property) {
            if ($this->isAllowedAttribute($object::class, $property->name, $format, $context)) {
                $attributes[] = $property->name;
            }
        }

        return array_unique($attributes);
    }

    /**
     * @param array<array-key, mixed> $context
     */
    protected function isAllowedAttribute(
        object|string $classOrObject,
        string $attribute,
        string $format = null,
        array $context = [],
    ): bool {
        if (!parent::isAllowedAttribute($classOrObject, $attribute, $format, $context)) {
            return false;
        }

        if (is_string($classOrObject) && !class_exists($classOrObject)) {
            return false;
        }

        $allowed = false;

        try {
            $property = (new ReflectionClass($classOrObject))->getProperty($attribute);
            $allowed  = $property->isPublic() && !$property->isStatic();
        } catch (ReflectionException) {
            // skip
        }

        return $allowed;
    }

    /**
     * @param array<array-key, mixed> $context
     */
    protected function getAttributeValue(
        object $object,
        string $attribute,
        string $format = null,
        array $context = [],
    ): mixed {
        return $object->{$attribute};
    }

    /**
     * @param array<array-key, mixed> $context
     */
    protected function setAttributeValue(
        object $object,
        string $attribute,
        mixed $value,
        string $format = null,
        array $context = [],
    ): void {
        $object->{$attribute} = $value;
    }
}
