<?php

/** @noinspection PhpExpressionResultUnusedInspection */

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Helper;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class ReflectionHelper
{
    public static function getPropertyValue(object $object, string $propertyName): mixed
    {
        $property = self::getPropertyReflection($object, $propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    public static function setPropertyValue(object $object, string $propertyName, mixed $value): void
    {
        $property = self::getPropertyReflection($object, $propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    private static function getPropertyReflection(object $object, string $propertyName): ReflectionProperty
    {
        $reflectionClass = new ReflectionClass($object);
        while (true) {
            try {
                return $reflectionClass->getProperty($propertyName);
            } catch (ReflectionException $e) {
                if (!$reflectionClass = $reflectionClass->getParentClass()) {
                    throw $e;
                }
            }
        }
    }
}
