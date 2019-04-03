<?php

/*
 * Copyright (C) 2018-2019 Natan Felles <natanfelles@gmail.com>
 *
 * Licensed under the MIT license.
 */

/**
 * Class ArraySimple.
 *
 * The ArraySimple class contains methods that work with PHP arrays using "simple keys" (strings
 * with brackets).
 *
 * @see https://www.php.net/manual/en/language.types.array.php
 */
class ArraySimple
{
	protected static function extractKeys(string $simple_key) : array
	{
		preg_match_all('#\[(.*?)\]#', $simple_key, $matches);
		return $matches[1] ?? [];
	}

	/**
	 * Reverts an associative array of simple keys to an native array.
	 *
	 * @param array $array_simple An array with simple keys
	 *
	 * @return array An array with native keys and their corresponding values
	 */
	public static function revert(array $array_simple) : array
	{
		$array = [];
		foreach ($array_simple as $simple_key => $value) {
			$parent_key = static::getParentKey($simple_key);
			if ($parent_key === null) {
				$array[$simple_key] = is_array($array_simple[$simple_key])
					? static::revert($array_simple[$simple_key])
					: $array_simple[$simple_key];
				continue;
			}
			$parent = [];
			static::addChild(
				$parent,
				array_merge([$parent_key], static::extractKeys($simple_key)),
				$value
			);
			$array = array_replace_recursive($array, $parent);
		}
		return $array;
	}

	/**
	 * Converts an array to an associative array with simple keys.
	 *
	 * @param array $array Array to be converted
	 *
	 * @return array An associative array with the simple keys as keys and their corresponding
	 *               values
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
	 * @param array  $array      The array to search in
	 *
	 * @return mixed|null The item value or null if not found
	 */
	public static function value(string $simple_key, array $array)
	{
		$array = static::revert($array);
		$parent_key = static::getParentKey($simple_key);
		if ($parent_key) {
			$value = $array[$parent_key] ?? null;
			foreach (static::extractKeys($simple_key) as $key) {
				if ( ! (is_array($value) && array_key_exists($key, $value))) {
					return null;
				}
				$value = $value[$key];
			}
			return $value;
		}
		return $array[$simple_key] ?? null;
	}

	/**
	 * Gets the keys of an array in the simple keys format.
	 *
	 * @param array $array The array to get the simple keys
	 *
	 * @return array An indexed array containing the simple keys as values
	 */
	public static function keys(array $array) : array
	{
		return static::getKeys($array);
	}

	protected static function getKeys(array $array, string $child_key = '') : array
	{
		$all_keys = [];
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$all_keys = $child_key === ''
					? array_merge($all_keys, static::getKeys($value, $key))
					: array_merge(
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

	protected static function addChild(array &$parent, array $childs, $value) : void
	{
		$key = array_shift($childs);
		$parent[$key] = [];
		if ($childs === []) {
			$parent[$key] = $value;
			return;
		}
		static::addChild($parent[$key], $childs, $value);
	}

	protected static function getParentKey(string $key) : ?string
	{
		$pos_open = strpos($key, '[');
		$pos_close = $pos_open ? strpos($key, ']') : false;
		if ($pos_close === false || $pos_open > $pos_close) {
			return null;
		}
		return substr($key, 0, $pos_open);
	}

	protected static function getChildKey(string $key) : string
	{
		$parent_key = static::getParentKey($key);
		if ($parent_key === null) {
			return '[' . $key . ']';
		}
		$key = explode('[', $key, 2);
		$key = '[' . $key[0] . '][' . $key[1];
		return $key;
	}
}
