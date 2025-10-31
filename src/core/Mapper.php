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
            self::$propertyCache[$dtoClass] = array_flip(array_map(fn($p) => $p->getName(), $dtoProperties));
        }

        $dtoProps = self::$propertyCache[$dtoClass];
        $dto = new $dtoClass();
        $sourceProps = get_object_vars($source);

        foreach ($sourceProps as $key => $value) {
            if (isset($dtoProps[$key])) {
                $dto->{$key} = $value;
                continue;
            }

            $camelKey = self::snakeToCamel($key);
            if (isset($dtoProps[$camelKey])) {
                $dto->{$camelKey} = $value;
                continue;
            }

            if (is_object($value) && isset($dtoProps[$key])) {
                $dto->{$key} = $value;
            }
        }

        return $dto;
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

    private static function snakeToCamel(string $str): string {
        return lcfirst(str_replace('_', '', ucwords($str, '_')));
    }
}