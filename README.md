# Array Simple

[![Build Status](https://travis-ci.org/natanfelles/array-simple.svg)](https://travis-ci.org/natanfelles/array-simple) [![Coverage Status](https://coveralls.io/repos/github/natanfelles/array-simple/badge.svg)](https://coveralls.io/github/natanfelles/array-simple)

The [ArraySimple](https://github.com/natanfelles/array-simple/blob/master/src/ArraySimple.php) class
contains methods that work with [PHP arrays](https://www.php.net/manual/en/language.types.array.php)
using *simple keys* (strings with brackets).

## Installation

```
composer require natanfelles/array-simple
```

## Methods

### ArraySimple::keys

#### Description

Gets the keys of an array in the simple keys format.

```php
ArraySimple::keys(array $array): array
```

#### Parameters

- **$array** The array to get the simple keys

#### Return Values

An indexed array containing the simple keys as values

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

$keys = ArraySimple::keys($_POST);

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

### ArraySimple::value

#### Description

Gets the value of an array item through a simple key.

```php
ArraySimple::value(string $simple_key, array $array)
```

#### Parameters

- **$simple_key** A string in the simple key format
- **$array** The array to search in

#### Return Values

The item value or `null` if not found

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

echo ArraySimple::value('student[name]', $_POST); // prints John Doe

```

---

### ArraySimple::convert

#### Description

Converts an array to an associative array with simple keys.

```php
ArraySimple::convert(array $array): array
```

#### Parameters

- **$array** Array to be converted

#### Return Values

An associative array with the simple keys as keys and their corresponding values

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

$simple = ArraySimple::convert($_POST);

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

---

### ArraySimple::revert

#### Description

Reverts an associative array of simple keys to an native array.

```php
ArraySimple::revert(array $array_simple): array
```

#### Parameters

- **$array_simple** An array with simple keys

#### Return Values

An array with native keys and their corresponding values

#### Examples

```php
<?php
$_POST = [
	'masters[0]' => 'Siddhartha',
	'masters[1]' => 'Jesus',
	'masters[2]' => 'Muhammad',
	'student[name]' => 'John Doe',
	'student[age]' => '23',
];

$simple = ArraySimple::revert($_POST);

print_r($simple);
```

The above example will output:

```
Array
(
    [masters] => Array
        (
            [0] => Siddhartha
            [1] => Jesus
            [2] => Muhammad
        )

    [student] => Array
        (
            [name] => John Doe
            [age] => 23
        )

)
```

---

### ArraySimple::files

#### Description

Get `$_FILES` in a re-organized way.

```php
ArraySimple::files(): array
```

#### Return Values

An array ready to be used with [ArraySimple::value](#arraysimplevalue)

#### Examples

```php
<?php
$_FILES = [
	'party' => [
				'name' => [
					1 => [
						'aa' => [
							0 => 'Blue Sky.png',
							1 => '',
						],
					],
					2 => 'music.png',
				],
				'type' => [
					1 => [
						'aa' => [
							0 => 'image/png',
							1 => '',
						],
					],
					2 => 'image/png',
				],
				'tmp_name' => [
					1 => [
						'aa' => [
							0 => '/tmp/phpP0AhMI',
							1 => '',
						],
					],
					2 => '/tmp/phpK5PJNm',
				],
				'error' => [
					1 => [
						'aa' => [
							0 => 0,
							1 => 4,
						],
					],
					2 => 0,
				],
				'size' => [
					1 => [
						'aa' => [
							0 => 41706,
							1 => 0,
						],
					],
					2 => 62820,
				],
			]
];

$files = ArraySimple::files();

print_r($files);
```

The above example will output:

```
Array
(
    [party] => Array
        (
            [1] => Array
                (
                    [aa] => Array
                        (
                            [0] => Array
                                (
                                    [name] => Blue Sky.png
                                    [type] => image/png
                                    [tmp_name] => /tmp/phpP0AhMI
                                    [error] => 0
                                    [size] => 41706
                                )

                            [1] => Array
                                (
                                    [name] => 
                                    [type] => 
                                    [tmp_name] => 
                                    [error] => 4
                                    [size] => 0
                                )

                        )

                )

            [2] => Array
                (
                    [name] => music.png
                    [type] => image/png
                    [tmp_name] => /tmp/phpK5PJNm
                    [error] => 0
                    [size] => 62820
                )

        )

)
```

> NOTE: Do not use file input names as `name`, `type`, `tmp_name`, `error` and
> `size` to avoid overwrite of arrays.
