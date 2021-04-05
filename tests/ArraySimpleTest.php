<?php

/*
 * Copyright (C) 2018-2019 Natan Felles <natanfelles@gmail.com>
 *
 * Licensed under the MIT license.
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
}
