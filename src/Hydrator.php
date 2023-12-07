<?php

namespace Beesofts\Hydrator;

use Beesofts\Hydrator\Attribute\HydratedField;
use Beesofts\Hydrator\Attribute\HydratedObject;
use Beesofts\Hydrator\Exception\HydratorException;
use Beesofts\Hydrator\Exception\NoDataForPathException;

class Hydrator
{
    public static function hydrate(object $object, mixed $data, callable $defaultPathfinder = null): void
    {
        $reflectionClass = new \ReflectionClass($object);
        $dataBag = new DataBag($data);

        foreach ($reflectionClass->getProperties() as $property) {
            foreach ($property->getAttributes(HydratedField::class) as $attribute) {
                $path = $attribute->getArguments()['path'] ??
                    (is_callable($defaultPathfinder) ? $defaultPathfinder($property->getName()) : $property->getName())
                ;

                try {
                    $value = $dataBag->get($path);

                    if ($factory = $attribute->getArguments()['factory'] ?? null) {
                        if (!method_exists($object, $factory)) {
                            $message = sprintf(
                                'factory "%s" not found for property "%s"',
                                $factory,
                                $property->getName()
                            );

                            throw new HydratorException($message);
                        }

                        $value = $object->{$factory}($value);
                    }

                    if ($collectionOf = $attribute->getArguments()['collectionOf'] ?? null) {
                        if (!class_exists($collectionOf)) {
                            throw new HydratorException(sprintf('Class "%s" does not exists', $collectionOf));
                        }

                        if (!is_null($value) && !is_iterable($value) && !$value instanceof \stdClass) {
                            throw new HydratorException(sprintf('Value found at "%s" is not iterable', $path));
                        }

                        if (is_null($value)) {
                            $value = [];
                        } else {
                            $collection = [];
                            foreach ($value as $element) {
                                $collection[] = self::build($collectionOf, $element, $defaultPathfinder);
                            }
                            $value = $collection;
                        }
                    } elseif (
                        !is_null($propertyType = $property->getType()) &&
                        ($propertyType instanceof \ReflectionNamedType)
                    ) {
                        if (
                            !$propertyType->isBuiltin() &&
                            ('stdClass' !== $propertyType->getName()) &&
                            class_exists($propertyType->getName())
                        ) {
                            $value = self::build($propertyType->getName(), $value, $defaultPathfinder);
                        }
                    }

                    if (!$property->isReadOnly() || !$property->isInitialized($object)) {
                        $property->setValue($object, $value);
                    }
                } catch (NoDataForPathException) {
                    if ($property->hasDefaultValue()) {
                        $property->setValue($object, $property->getDefaultValue());
                    } elseif ($property->getType()?->allowsNull() ?? false) {
                        $property->setValue($object, null);
                    }
                }
            }
        }
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $classname
     *
     * @return T
     */
    public static function build(string $classname, mixed $data, callable $defaultPathfinder = null): ?object
    {
        if (!class_exists($classname)) {
            throw new HydratorException(sprintf('Class "%s" does not exists', $classname));
        }

        if (is_null($data)) {
            return null;
        }

        $reflectionClass = new \ReflectionClass($classname);

        if (0 === count($reflectionClass->getAttributes(HydratedObject::class))) {
            return new $classname($data);
        }

        $object = $reflectionClass->newInstanceWithoutConstructor();
        self::hydrate($object, $data, $defaultPathfinder);

        return $object;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $classname
     *
     * @return list<T|null>
     */
    public static function buildArrayOf(string $classname, array $data, callable $defaultPathfinder = null): array
    {
        $values = [];

        foreach ($data as $datum) {
            $values[] = self::build($classname, $datum, $defaultPathfinder);
        }

        return $values;
    }
}
