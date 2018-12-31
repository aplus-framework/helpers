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
					\array_simple_keys($value, $simple_key . '[' . $key . ']')
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
				$all_keys[] = $simple_key . '[' . $key . ']';
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
	$data = [];

	foreach (\array_simple_keys($array) as $key)
	{
		$data[$key] = \array_simple_value($key, $array);
	}

	return $data;
}
