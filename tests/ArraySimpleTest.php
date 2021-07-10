<?php
/*
 * This file is part of ArraySimple Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class ArraySimpleTest extends TestCase
{
	protected array $array;

	protected function setUp() : void
	{
		$this->array = [
			'a' => [
				'a' => [
					'a' => 'aaa',
					'b' => 'aab',
				],
				'b' => [
					'a' => 'aba',
					'b' => 'abb',
					'c' => [
						'a' => 'abca',
						'b' => 'abcb',
						'c[a]' => 'abcca',
					],
				],
			],
			'b' => [
				'a' => [
					'a' => 'baa',
					'b' => 'bab',
				],
				'b' => [
					'a' => 'bba',
					'b' => 'bbb',
					'c' => [
						'a' => 'bbca',
						'b' => 'bbcb',
					],
				],
			],
		];
	}

	public function testArraySimpleKeys()
	{
		$this->assertEquals([
			'a[a][a]',
			'a[a][b]',
			'a[b][a]',
			'a[b][b]',
			'a[b][c][a]',
			'a[b][c][b]',
			'a[b][c][c][a]',
			'b[a][a]',
			'b[a][b]',
			'b[b][a]',
			'b[b][b]',
			'b[b][c][a]',
			'b[b][c][b]',
		], ArraySimple::keys($this->array));
		$this->assertEquals([
			'a[a]',
			'a[b]',
			'b[a]',
			'b[b]',
			'b[c][a]',
			'b[c][b]',
			'b[c][c][a]',
		], ArraySimple::keys($this->array['a']));
		$this->assertEquals([
			'a',
			'b',
			'c[a]',
			'c[b]',
			'c[c][a]',
		], ArraySimple::keys($this->array['a']['b']));
		$this->assertEquals([], ArraySimple::keys([]));
	}

	public function testArraySimpleValue()
	{
		$this->assertEquals([
			'a' => [
				'a' => 'aaa',
				'b' => 'aab',
			],
			'b' => [
				'a' => 'aba',
				'b' => 'abb',
				'c' => [
					'a' => 'abca',
					'b' => 'abcb',
					'c' => [
						'a' => 'abcca',
					],
				],
			],
		], ArraySimple::value('a', $this->array));
		$this->assertEquals([
			'a' => 'aba',
			'b' => 'abb',
			'c' => [
				'a' => 'abca',
				'b' => 'abcb',
				'c' => [
					'a' => 'abcca',
				],
			],
		], ArraySimple::value('a[b]', $this->array));
		$this->assertEquals([
			'a' => 'abca',
			'b' => 'abcb',
			'c' => [
				'a' => 'abcca',
			],
		], ArraySimple::value('a[b][c]', $this->array));
		$this->assertEquals('abca', ArraySimple::value('a[b][c][a]', $this->array));
		$this->assertEquals('abcca', ArraySimple::value('a[b][c][c][a]', $this->array));
		$this->assertEquals('bbcb', ArraySimple::value('b[b][c][b]', $this->array));
		$this->assertNull(ArraySimple::value('a[x]', $this->array));
		$this->assertNull(ArraySimple::value('c', $this->array));
		$this->assertNull(ArraySimple::value('z', []));
	}

	public function testArraySimpleConvert()
	{
		$this->assertEquals([
			'a[a][a]' => 'aaa',
			'a[a][b]' => 'aab',
			'a[b][a]' => 'aba',
			'a[b][b]' => 'abb',
			'a[b][c][a]' => 'abca',
			'a[b][c][b]' => 'abcb',
			'a[b][c][c][a]' => 'abcca',
			'b[a][a]' => 'baa',
			'b[a][b]' => 'bab',
			'b[b][a]' => 'bba',
			'b[b][b]' => 'bbb',
			'b[b][c][a]' => 'bbca',
			'b[b][c][b]' => 'bbcb',
		], ArraySimple::convert($this->array));
		$this->assertEquals([
			'a[a]' => 'aaa',
			'a[b]' => 'aab',
			'b[a]' => 'aba',
			'b[b]' => 'abb',
			'b[c][a]' => 'abca',
			'b[c][b]' => 'abcb',
			'b[c][c][a]' => 'abcca',
		], ArraySimple::convert($this->array['a']));
		$this->assertEquals([
			'a' => 'bba',
			'b' => 'bbb',
			'c[a]' => 'bbca',
			'c[b]' => 'bbcb',
		], ArraySimple::convert($this->array['b']['b']));
		$this->assertEquals([], ArraySimple::convert([]));
	}

	public function testArraySimpleRevert()
	{
		$array = [
			'[a]' => '[a]',
			']a[' => ']a[',
			'a' => 'a',
			'b' => 'b',
			'c' => [
				'a' => 'c[a]',
			],
			'd' => [
				'' => [
					'' => 'd[][]',
					1 => 'd[][1]',
					0 => 'd[][x]',
				],
			],
			'dd' => [
				1 => [
					2 => [
						'foo bar' => 'dd[1][2][foo bar]',
					],
				],
			],
			'e[x' => 'e[x',
			'e]x' => 'e]x',
			'f' => [
				0 => '0',
				'one' => 'f[one]',
				'two' => [
					0 => 'f[two][0]',
					'one' => 'f[two][one]',
					1 => 'f[two][1]',
				],
				'g[' => 'f[g[]',
			],
		];
		$array_simple = [
			'[a]' => '[a]',
			']a[' => ']a[',
			'a' => 'a',
			'b' => 'b',
			'c[a]' => 'c[a]',
			'd[][]' => 'd[][]',
			'd[][1]' => 'd[][1]',
			'd[][0]' => 'd[][x]',
			'dd[1][2][foo bar]' => 'dd[1][2][foo bar]',
			'e[x' => 'e[x',
			'e]x' => 'e]x',
			'f' => [
				'0',
				'one' => 'f[one]',
				'two' => [
					'f[two][0]',
					'one' => 'f[two][one]',
					'f[two][1]',
				],
				'g[' => 'f[g[]',
			],
		];
		$this->assertEquals($array, ArraySimple::revert($array_simple));
	}

	public function testArraySimpleFiles()
	{
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
			],
			'foo' => [
				'name' => 'Bar.jpg',
				'type' => 'image/jpeg',
				'tmp_name' => '/tmp/phpS7OLMn',
				'error' => 0,
				'size' => 10663,
			],
		];
		$this->assertEquals([
			'party' => [
				1 => [
					'aa' => [
						0 => [
							'name' => 'Blue Sky.png',
							'type' => 'image/png',
							'tmp_name' => '/tmp/phpP0AhMI',
							'error' => 0,
							'size' => 41706,
						],
						1 => [
							'name' => '',
							'type' => '',
							'tmp_name' => '',
							'error' => 4,
							'size' => 0,
						],
					],
				],
				2 => [
					'name' => 'music.png',
					'type' => 'image/png',
					'tmp_name' => '/tmp/phpK5PJNm',
					'error' => 0,
					'size' => 62820,
				],
			],
			'foo' => [
				'name' => 'Bar.jpg',
				'type' => 'image/jpeg',
				'tmp_name' => '/tmp/phpS7OLMn',
				'error' => 0,
				'size' => 10663,
			],
		], ArraySimple::files());
	}
}
