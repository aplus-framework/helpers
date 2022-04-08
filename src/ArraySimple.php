<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework Helpers Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\Helpers;

use JetBrains\PhpStorm\Pure;

/**
 * Class ArraySimple.
 *
 * Contains methods to work with PHP arrays using "simple keys" (strings with
 * square brackets).
 *
 * Simple key format example: `parent[child1][child2]`.
 *
 * `user[country][state]` gets 'rs' in
 * `array('user' => ['country' => ['state' => 'rs']])`
 *
 * @see https://www.php.net/manual/en/language.types.array.php
 *
 * @package helpers
 */
class ArraySimple
{
    /**
     * @param string $simpleKey
     *
     * @return array<int,string>
     */
    protected static function extractKeys(string $simpleKey) : array
    {
        \preg_match_all('#\[(.*?)\]#', $simpleKey, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Reverts an associative array of simple keys to an native array.
     *
     * @param array<mixed> $arraySimple An array with simple keys
     *
     * @return array<string,mixed> An array with their corresponding values
     */
    public static function revert(array $arraySimple) : array
    {
        $array = [];
        foreach ($arraySimple as $simpleKey => $value) {
            $simpleKey = (string) $simpleKey;
            $parentKey = static::getParentKey($simpleKey);
            if ($parentKey === null) {
                $array[$simpleKey] = \is_array($value)
                    ? static::revert($value)
                    : $value;
                continue;
            }
            $parent = [];
            static::addChild(
                $parent,
                \array_merge([$parentKey], static::extractKeys($simpleKey)),
                $value
            );
            $array = \array_replace_recursive($array, $parent);
        }
        return $array;
    }

    /**
     * Converts an array to an associative array with simple keys.
     *
     * @param array<mixed> $array Array to be converted
     *
     * @return array<string,mixed> An associative array with the simple keys as
     * keys and their corresponding values
     */
    public static function convert(array $array) : array
    {
        $array = static::revert($array);
        $data = [];
        foreach (static::keys($array) as $key) {
            $data[$key] = static::value($key, $array);
        }
        return $data;
    }

    /**
     * Gets the value of an array item through a simple key.
     *
     * @param string $simpleKey A string in the simple key format
     * @param array<mixed> $array The array to search in
     *
     * @return mixed The item value or null if not found
     */
    public static function value(string $simpleKey, array $array) : mixed
    {
        $array = static::revert($array);
        $parentKey = static::getParentKey($simpleKey);
        if ($parentKey === null) {
            return $array[$simpleKey] ?? null;
        }
        $value = $array[$parentKey] ?? null;
        foreach (static::extractKeys($simpleKey) as $key) {
            if ( ! (\is_array($value) && \array_key_exists($key, $value))) {
                return null;
            }
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * Gets the keys of an array in the simple keys format.
     *
     * @param array<mixed> $array The array to get the simple keys
     *
     * @return array<int,string> An indexed array containing the simple keys as
     * values
     */
    #[Pure]
    public static function keys(array $array) : array
    {
        return static::getKeys($array);
    }

    /**
     * @param array<mixed> $array
     * @param string $childKey
     *
     * @return array<int,string>
     */
    #[Pure]
    protected static function getKeys(array $array, string $childKey = '') : array
    {
        $allKeys = [];
        foreach ($array as $key => $value) {
            $key = (string) $key;
            if (\is_array($value)) {
                $allKeys = $childKey === ''
                    ? \array_merge($allKeys, static::getKeys($value, $key))
                    : \array_merge(
                        $allKeys,
                        static::getKeys($value, $childKey . static::getChildKey($key))
                    );
                continue;
            }
            $allKeys[] = $childKey === ''
                ? $key
                : $childKey . static::getChildKey($key);
        }
        return $allKeys;
    }

    /**
     * @param array<int,string> $parent
     * @param array<int,string> $childs
     * @param mixed $value
     */
    protected static function addChild(array &$parent, array $childs, mixed $value) : void
    {
        $key = \array_shift($childs);
        $key = (string) $key;
        $parent[$key] = [];
        if ($childs === []) {
            $parent[$key] = $value;
            return;
        }
        static::addChild($parent[$key], $childs, $value);
    }

    #[Pure]
    protected static function getParentKey(string $key) : ?string
    {
        $posOpen = \strpos($key, '[');
        $posClose = $posOpen ? \strpos($key, ']', $posOpen) : false;
        if ($posClose === false) {
            return null;
        }
        return \substr($key, 0, $posOpen); // @phpstan-ignore-line
    }

    #[Pure]
    protected static function getChildKey(string $key) : string
    {
        $parentKey = static::getParentKey($key);
        if ($parentKey === null) {
            return '[' . $key . ']';
        }
        $key = \explode('[', $key, 2);
        $key = '[' . $key[0] . '][' . $key[1];
        return $key;
    }

    /**
     * Get $_FILES in a re-organized way.
     *
     * NOTE: Do not use file input names as `name`, `type`, `tmp_name`, `error`,
     * `full_path` and `size` to avoid overwrite of arrays.
     *
     * @return array<string,mixed> An array ready to be used with
     * {@see ArraySimple::value()}
     */
    #[Pure]
    public static function files() : array
    {
        $files = [];
        foreach ($_FILES as $name => $values) {
            if ( ! isset($files[$name])) {
                $files[$name] = [];
            }
            if ( ! \is_array($values['error'])) {
                $files[$name] = $values;
                continue;
            }
            foreach ($values as $infoKey => $subArray) {
                $files[$name] = \array_replace_recursive(
                    $files[$name],
                    static::filesWalker($subArray, $infoKey)
                );
            }
        }
        return $files;
    }

    /**
     * @see https://stackoverflow.com/a/33261775/6027968
     *
     * @param array<mixed> $array
     * @param string $infoKey
     *
     * @return array<string,mixed>
     */
    #[Pure]
    protected static function filesWalker(array $array, string $infoKey) : array
    {
        $return = [];
        foreach ($array as $key => $value) {
            $key = (string) $key;
            if (\is_array($value)) {
                $return[$key] = static::filesWalker($value, $infoKey);
                continue;
            }
            $return[$key][$infoKey] = $value;
        }
        return $return;
    }
}
