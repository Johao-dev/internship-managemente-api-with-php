<?php

namespace App\core;

use ReflectionClass;
use ReflectionProperty;

final class Mapper {

    private static array $propertyCache = [];

    public static function mapToDto(string $dtoClass, object $source): ?object {
        if (!class_exists($dtoClass)) {
            return null;
        }

        if (!isset(self::$propertyCache[$dtoClass])) {
            $reflectionDto = new ReflectionClass($dtoClass);
            $dtoProperties = $reflectionDto->getProperties(ReflectionProperty::IS_PUBLIC);
            
            self::$propertyCache[$dtoClass] = array_flip(array_map(fn($prop) => $prop->getName(), $dtoProperties));
        }

        $dtoPropertyNames = self::$propertyCache[$dtoClass];
        $dtoInstance = new $dtoClass();
        $sourceProperties = get_object_vars($source);

        foreach ($sourceProperties as $key => $value) {
            if (isset($dtoPropertyNames[$key])) {
                $dtoInstance->{$key} = $value;
            }
        }

        return $dtoInstance;
    }

    public static function mapToDtoArray(string $dtoClass, array $sourceArray): array {
        $result = [];
        foreach ($sourceArray as $source) {
            if (is_object($source)) {
                $mapped = self::mapToDto($dtoClass, $source);
                if ($mapped) {
                    $result[] = $mapped;
                }
            }
        }
        return $result;
    }
}
