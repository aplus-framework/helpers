# Array Simple 

[![Build Status](https://travis-ci.org/natanfelles/array_simple.svg)](https://travis-ci.org/natanfelles/array_simple) [![Coverage Status](https://coveralls.io/repos/github/natanfelles/array_simple/badge.svg)](https://coveralls.io/github/natanfelles/array_simple)

The [array_simple.php](https://github.com/natanfelles/array_simple/blob/master/src/array_simple.php) file contains three functions ([`array_simple_keys`](#array_simple_keys), [`array_simple_value`](#array_simple_value) and [`array_simple`](#array_simple)) that works with PHP arrays using *simple keys* (strings with brackets).

## Functions

### array_simple_keys

#### Description

Get multidimensional array keys as simple keys.

```php
array_simple_keys(array $array, string $simple_key = ''): array
```

#### Parameters

- **$array** The array to get the simple keys.
- **$simple_key** A simple key child. Used by the function itself to mount the keys recursively.


#### Return Values

An array containing the simple keys as values.

#### Examples

```php
<?php
$_POST = [
	'masters' => [
		'Siddhartha',
		'Jesus',
		'Muhammad'	
	],
	'student' => [
		'name' => 'John Doe',
		'age'  => '23'
	],
];

$keys = array_simple_keys($_POST);

print_r($keys);
```

The above example will output:

```
Array
(
    [0] => masters[0]
    [1] => masters[1]
    [2] => masters[2]
    [3] => student[name]
    [4] => student[age]
)
```

---

### array_simple_value

#### Description

Get values by a simple key.

```php
array_simple_value(string $simple_key, array $array)
```

#### Parameters

- **$simple_key** A string in the simple key format.
- **$array** The array to search in.


#### Return Values

The value of the simple key or `null` if not found.

#### Examples

```php
<?php
$_POST = [
	'masters' => [
		'Siddhartha',
		'Jesus',
		'Muhammad'	
	],
	'student' => [
		'name' => 'John Doe',
		'age'  => '23'
	],
];

echo array_simple_value('student[name]', $_POST); // prints John Doe

```

---

### array_simple

#### Description

Get an array with simple keys.

```php
array_simple(array $array): array
```

#### Parameters

- **$array** A multidimensional array to be converted into associative simple keys array.

#### Return Values

An associative array with the simple keys as keys and their corresponding values.

#### Examples

```php
<?php
$_POST = [
	'masters' => [
		'Siddhartha',
		'Jesus',
		'Muhammad'	
	],
	'student' => [
		'name' => 'John Doe',
		'age'  => '23'
	],
];

$simple = array_simple($_POST);

print_r($simple);
```

The above example will output:

```
Array
(
    [masters[0]] => Siddhartha
    [masters[1]] => Jesus
    [masters[2]] => Muhammad
    [student[name]] => John Doe
    [student[age]] => 23
)
```

