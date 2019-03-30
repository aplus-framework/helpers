<?php

/*
 * Copyright (C) 2018-2019 Natan Felles <natanfelles@gmail.com>
 *
 * Licensed under the MIT license.
 */

use PHPUnit\Framework\TestCase;

class ArraySimpleTest extends TestCase
{
	/**
	 * @var array
	 */
	protected $array;

	protected function setUp()
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
		], array_simple_keys($this->array));
		$this->assertEquals([
			'a[a]',
			'a[b]',
			'b[a]',
			'b[b]',
			'b[c][a]',
			'b[c][b]',
			'b[c][c][a]',
		], array_simple_keys($this->array['a']));
		$this->assertEquals([
			'a',
			'b',
			'c[a]',
			'c[b]',
			'c[c][a]',
		], array_simple_keys($this->array['a']['b']));
		$this->assertEquals([], array_simple_keys([]));
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
		], array_simple_value('a', $this->array));
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
		], array_simple_value('a[b]', $this->array));
		$this->assertEquals([
			'a' => 'abca',
			'b' => 'abcb',
			'c' => [
				'a' => 'abcca',
			],
		], array_simple_value('a[b][c]', $this->array));
		$this->assertEquals('abca', array_simple_value('a[b][c][a]', $this->array));
		$this->assertEquals('abcca', array_simple_value('a[b][c][c][a]', $this->array));
		$this->assertEquals('bbcb', array_simple_value('b[b][c][b]', $this->array));
		$this->assertNull(array_simple_value('a[x]', $this->array));
		$this->assertNull(array_simple_value('c', $this->array));
		$this->assertNull(array_simple_value('z', []));
	}

	public function testArraySimple()
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
		], array_simple($this->array));
		$this->assertEquals([
			'a[a]' => 'aaa',
			'a[b]' => 'aab',
			'b[a]' => 'aba',
			'b[b]' => 'abb',
			'b[c][a]' => 'abca',
			'b[c][b]' => 'abcb',
			'b[c][c][a]' => 'abcca',
		], array_simple($this->array['a']));
		$this->assertEquals([
			'a' => 'bba',
			'b' => 'bbb',
			'c[a]' => 'bbca',
			'c[b]' => 'bbcb',
		], array_simple($this->array['b']['b']));
		$this->assertEquals([], array_simple([]));
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
				0 => [
					0 => 'd[0][x]',
					1 => 'd[0][1]',
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
			],
		];
		$array_simple = [
			'[a]' => '[a]',
			']a[' => ']a[',
			'a' => 'a',
			'b' => 'b',
			'c[a]' => 'c[a]',
			'd[][]' => 'd[0][0]',
			'd[][1]' => 'd[0][1]',
			'd[][0]' => 'd[0][x]',
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
			],
		];
		$this->assertEquals($array, array_simple_revert($array_simple));
	}
}
