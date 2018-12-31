<?php

/**
 * Get multidimensional array keys as simple keys.
 *
 * @param array  $array      The array to get the simple keys.
 * @param string $simple_key A simple key child. Used by the function itself to mount the keys
 *                           recursively.
 *
 * @return array An array containing the simple keys as values.
 */
function array_simple_keys(array $array, string $simple_key = ''): array
{
	static $function_get_child;

	if ($function_get_child === null)
	{
		$function_get_child = function (string $key): string {
			$pos_open  = \strpos($key, '[');
			$pos_close = $pos_open ? \strpos($key, ']') : false;

			if ($pos_open === false || $pos_close === false || $pos_open > $pos_close)
			{
				return '[' . $key . ']';
			}

			if ($pos_open !== 0)
			{
				$key = \explode('[', $key, 2);
				$key = '[' . $key[0] . '][' . $key[1];
			}

			return $key;
		};
	}

	$all_keys = [];

	foreach ($array as $key => $value)
	{
		if (\is_array($value))
		{
			if ($simple_key === '')
			{
				$all_keys = \array_merge(
					$all_keys,
					\array_simple_keys($value, $key)
				);
			}
			else
			{
				$all_keys = \array_merge(
					$all_keys,
					\array_simple_keys($value, $simple_key . $function_get_child($key))
				);
			}
		}
		else
		{
			if ($simple_key === '')
			{
				$all_keys[] = $key;
			}
			else
			{
				$all_keys[] = $simple_key . $function_get_child($key);
			}
		}
	}

	return $all_keys;
}

/**
 * Get values by a simple key.
 *
 * @param string $simple_key A string in the simple key format.
 * @param array  $array      The array to search in.
 *
 * @return mixed|null The value of the simple key or null if not found.
 */
function array_simple_value(string $simple_key, array $array)
{
	$array = \array_simple_revert($array);

	$pos = \strpos($simple_key, '[');

	if ($pos && $pos < \strpos($simple_key, ']'))
	{
		\preg_match_all('#\[(.*?)\]#', $simple_key, $matches);
		$simple_key = \substr($simple_key, 0, $pos);
		$value      = $array[$simple_key] ?? null;

		foreach ($matches[1] as $match)
		{
			if (\is_array($value) && \array_key_exists($match, $value))
			{
				$value = $value[$match];
			}
			else
			{
				return null;
			}
		}

		return $value;
	}

	return $array[$simple_key] ?? null;
}

/**
 * Get an array with simple keys.
 *
 * @param array $array A multidimensional array to be converted into associative simple keys array.
 *
 * @return array An associative array with the simple keys as keys and their corresponding values.
 */
function array_simple(array $array): array
{
	$array = \array_simple_revert($array);

	$data = [];

	foreach (\array_simple_keys($array) as $key)
	{
		$data[$key] = \array_simple_value($key, $array);
	}

	return $data;
}

/**
 * Converts an array with simple keys format into a PHP multidimensional array.
 *
 * @param array $array_simple An array with simple keys.
 *
 * @return array An array with native PHP array keys and their corresponding values.
 */
function array_simple_revert(array $array_simple): array
{
	static $function_add_child;

	if ($function_add_child === null)
	{
		$function_add_child = function (
			array &$array,
			array $items,
			$value,
			callable $self
		): void {
			$key = \array_shift($items);

			$array[$key] = [];

			if ($items === [])
			{
				$array[$key] = $value;

				return;
			}

			$self($array[$key], $items, $value, $self);
		};
	}

	$array = [];

	foreach ($array_simple as $simple_key => $value)
	{
		$pos_open  = \strpos($simple_key, '[');
		$pos_close = $pos_open ? \strpos($simple_key, ']') : false;

		if ($pos_open === false || $pos_close === false || $pos_open > $pos_close)
		{
			if (\is_array($array_simple[$simple_key]))
			{
				$value = \array_simple_revert($array_simple[$simple_key]);
			}
			else
			{
				$value = $array_simple[$simple_key];
			}

			$array[$simple_key] = $value;
		}
		elseif ($pos_open && $pos_open < $pos_close)
		{
			\preg_match_all('#\[(.*?)\]#', $simple_key, $matches);
			$parent_key = \substr($simple_key, 0, $pos_open);

			$items = [$parent_key];

			foreach ($matches[1] as $match)
			{
				$items[] = $match === '' ? 0 : $match;
			}

			$tree = [];

			$function_add_child($tree, $items, $value, $function_add_child);

			$array = \array_replace_recursive($array, $tree);
		}
	}

	return $array;
}
