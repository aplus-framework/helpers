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
	$keys = [];

	foreach ($array as $k => $value)
	{
		if (\is_array($value))
		{
			if ($simple_key === '')
			{
				$keys = \array_merge(
					$keys,
					\array_simple_keys($value, $k)
				);
			}
			else
			{
				$keys = \array_merge(
					$keys,
					\array_simple_keys($value, $simple_key . '[' . $k . ']')
				);
			}
		}
		else
		{
			if ($simple_key === '')
			{
				$keys[] = $k;
			}
			else
			{
				$keys[] = $simple_key . '[' . $k . ']';
			}
		}
	}

	return $keys;
}

/**
 * Get values by a simple key.
 *
 * @param string $simple_key A string in the simple key format.
 * @param array  $array      The array to search in.
 *
 * @return array|mixed|null The value of the simple key or null if not found.
 */
function array_simple_value(string $simple_key, array $array)
{
	$pos = \strpos($simple_key, '[');

	if ($pos && $pos < \strpos($simple_key, ']'))
	{
		\preg_match_all('#\[(.*?)\]#', $simple_key, $matches);
		$simple_key = \substr($simple_key, 0, $pos);
		$value      = $array[$simple_key] ?? [];

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
