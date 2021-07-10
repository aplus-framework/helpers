<?php declare(strict_types=1);
/*
 * This file is part of The Framework Helpers Library.
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
 */
class ArraySimple
{
	/**
	 * @param string $simple_key
	 *
	 * @return array<int,string>
	 */
	protected static function extractKeys(string $simple_key) : array
	{
		\preg_match_all('#\[(.*?)\]#', $simple_key, $matches);
		return $matches[1] ?? [];
	}

	/**
	 * Reverts an associative array of simple keys to an native array.
	 *
	 * @param array<int|string,mixed> $array_simple An array with simple keys
	 *
	 * @return array<string,mixed> An array with their corresponding values
	 */
	public static function revert(array $array_simple) : array
	{
		$array = [];
		foreach ($array_simple as $simple_key => $value) {
			$simple_key = (string) $simple_key;
			$parent_key = static::getParentKey($simple_key);
			if ($parent_key === null) {
				$array[$simple_key] = \is_array($value)
					? static::revert($value)
					: $value;
				continue;
			}
			$parent = [];
			static::addChild(
				$parent,
				\array_merge([$parent_key], static::extractKeys($simple_key)),
				$value
			);
			$array = \array_replace_recursive($array, $parent);
		}
		return $array;
	}

	/**
	 * Converts an array to an associative array with simple keys.
	 *
	 * @param array<int|string,mixed> $array Array to be converted
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
	 * @param string $simple_key A string in the simple key format
	 * @param array<int|string,mixed> $array The array to search in
	 *
	 * @return mixed The item value or null if not found
	 */
	public static function value(string $simple_key, array $array) : mixed
	{
		$array = static::revert($array);
		$parent_key = static::getParentKey($simple_key);
		if ($parent_key === null) {
			return $array[$simple_key] ?? null;
		}
		$value = $array[$parent_key] ?? null;
		foreach (static::extractKeys($simple_key) as $key) {
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
	 * @param array<int|string,mixed> $array The array to get the simple keys
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
	 * @param array<int|string,mixed> $array
	 * @param string $child_key
	 *
	 * @return array<int,string>
	 */
	#[Pure]
	protected static function getKeys(array $array, string $child_key = '') : array
	{
		$all_keys = [];
		foreach ($array as $key => $value) {
			$key = (string) $key;
			if (\is_array($value)) {
				$all_keys = $child_key === ''
					? \array_merge($all_keys, static::getKeys($value, $key))
					: \array_merge(
						$all_keys,
						static::getKeys($value, $child_key . static::getChildKey($key))
					);
				continue;
			}
			$all_keys[] = $child_key === ''
				? $key
				: $child_key . static::getChildKey($key);
		}
		return $all_keys;
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
		$pos_open = \strpos($key, '[');
		$pos_close = $pos_open ? \strpos($key, ']', $pos_open) : false;
		if ($pos_close === false) {
			return null;
		}
		return \substr($key, 0, $pos_open); // @phpstan-ignore-line
	}

	#[Pure]
	protected static function getChildKey(string $key) : string
	{
		$parent_key = static::getParentKey($key);
		if ($parent_key === null) {
			return '[' . $key . ']';
		}
		$key = \explode('[', $key, 2);
		$key = '[' . $key[0] . '][' . $key[1];
		return $key;
	}

	/**
	 * Get $_FILES in a re-organized way.
	 *
	 * NOTE: Do not use file input names as `name`, `type`, `tmp_name`, `error`
	 * and `size` to avoid overwrite of arrays.
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
			foreach ($values as $info_key => $sub_array) {
				$files[$name] = \array_replace_recursive(
					$files[$name],
					static::filesWalker($sub_array, $info_key)
				);
			}
		}
		return $files;
	}

	/**
	 * @see https://stackoverflow.com/a/33261775/6027968
	 *
	 * @param array<int|string,mixed> $array
	 * @param string $info_key
	 *
	 * @return array<string,mixed>
	 */
	#[Pure]
	protected static function filesWalker(array $array, string $info_key) : array
	{
		$return = [];
		foreach ($array as $key => $value) {
			$key = (string) $key;
			if (\is_array($value)) {
				$return[$key] = static::filesWalker($value, $info_key);
				continue;
			}
			$return[$key][$info_key] = $value;
		}
		return $return;
	}
}
