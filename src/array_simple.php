<?php

/*
 * Copyright (C) 2018-2019 Natan Felles <natanfelles@gmail.com>
 *
 * Licensed under the MIT license.
 */

function array_simple_child_key(string $key) : string
{
	$pos_open = strpos($key, '[');
	$pos_close = $pos_open ? strpos($key, ']') : false;
	if ($pos_close === false || $pos_open > $pos_close) {
		return '[' . $key . ']';
	}
	if ($pos_open !== 0) {
		$key = explode('[', $key, 2);
		$key = '[' . $key[0] . '][' . $key[1];
	}
	return $key;
}

/**
 * Get multidimensional array keys as simple keys.
 *
 * @param array  $array      The array to get the simple keys
 * @param string $simple_key A simple key child. Used by the function itself to mount the keys
 *                           recursively.
 *
 * @return array An array containing the simple keys as values
 */
function array_simple_keys(array $array, string $simple_key = '') : array
{
	$all_keys = [];
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$all_keys = $simple_key === ''
				? array_merge($all_keys, array_simple_keys($value, $key))
				: array_merge(
					$all_keys,
					array_simple_keys($value, $simple_key . array_simple_child_key($key))
				);
			continue;
		}
		$all_keys[] = $simple_key === ''
			? $key
			: $simple_key . array_simple_child_key($key);
	}
	return $all_keys;
}

/**
 * Get values by a simple key.
 *
 * @param string $simple_key A string in the simple key format
 * @param array  $array      The array to search in
 *
 * @return mixed|null The value of the simple key or null if not found
 */
function array_simple_value(string $simple_key, array $array)
{
	$array = array_simple_revert($array);
	$pos = strpos($simple_key, '[');
	if ($pos && $pos < strpos($simple_key, ']')) {
		preg_match_all('#\[(.*?)\]#', $simple_key, $matches);
		$value = $array[substr($simple_key, 0, $pos)] ?? null;
		foreach ($matches[1] as $match) {
			if ( ! (is_array($value) && array_key_exists($match, $value))) {
				return null;
			}
			$value = $value[$match];
		}
		return $value;
	}
	return $array[$simple_key] ?? null;
}

/**
 * Get an array with simple keys.
 *
 * @param array $array A multidimensional array to be converted into associative simple keys array
 *
 * @return array An associative array with the simple keys as keys and their corresponding values
 */
function array_simple(array $array) : array
{
	$array = array_simple_revert($array);
	$data = [];
	foreach (array_simple_keys($array) as $key) {
		$data[$key] = array_simple_value($key, $array);
	}
	return $data;
}

function array_add_child(array &$parent, array $childs, $value) : void
{
	$key = array_shift($childs);
	$parent[$key] = [];
	if ($childs === []) {
		$parent[$key] = $value;
		return;
	}
	array_add_child($parent[$key], $childs, $value);
}

/**
 * Converts an array with simple keys format into a PHP multidimensional array.
 *
 * @param array $array_simple An array with simple keys
 *
 * @return array An array with native PHP array keys and their corresponding values
 */
function array_simple_revert(array $array_simple) : array
{
	$array = [];
	foreach ($array_simple as $simple_key => $value) {
		$pos_open = strpos($simple_key, '[');
		$pos_close = $pos_open ? strpos($simple_key, ']') : false;
		if ($pos_close === false || $pos_open > $pos_close) {
			$array[$simple_key] = is_array($array_simple[$simple_key])
				? array_simple_revert($array_simple[$simple_key])
				: $array_simple[$simple_key];
			continue;
		}
		preg_match_all('#\[(.*?)\]#', $simple_key, $matches);
		$childs = [substr($simple_key, 0, $pos_open)];
		foreach ($matches[1] as $match) {
			$childs[] = $match === '' ? 0 : $match;
		}
		$parent = [];
		array_add_child($parent, $childs, $value);
		$array = array_replace_recursive($array, $parent);
	}
	return $array;
}
