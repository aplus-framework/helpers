<?php

class ArraySimpleTest extends \PHPUnit\Framework\TestCase
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
			'b[a][a]',
			'b[a][b]',
			'b[b][a]',
			'b[b][b]',
			'b[b][c][a]',
			'b[b][c][b]',
		], \array_simple_keys($this->array));

		$this->assertEquals([
			'a[a]',
			'a[b]',
			'b[a]',
			'b[b]',
			'b[c][a]',
			'b[c][b]',
		], \array_simple_keys($this->array['a']));

		$this->assertEquals([
			'a',
			'b',
			'c[a]',
			'c[b]',
		], \array_simple_keys($this->array['a']['b']));

		$this->assertEquals([], \array_simple_keys([]));
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
				],
			],
		], \array_simple_value('a', $this->array));

		$this->assertEquals([
			'a' => 'aba',
			'b' => 'abb',
			'c' => [
				'a' => 'abca',
				'b' => 'abcb',
			],
		], \array_simple_value('a[b]', $this->array));

		$this->assertEquals([
			'a' => 'abca',
			'b' => 'abcb',
		], \array_simple_value('a[b][c]', $this->array));

		$this->assertEquals('abca', \array_simple_value('a[b][c][a]', $this->array));

		$this->assertEquals('bbcb', \array_simple_value('b[b][c][b]', $this->array));

		$this->assertEquals(null, \array_simple_value('a[x]', $this->array));

		$this->assertEquals(null, \array_simple_value('c', $this->array));

		$this->assertEquals(null, \array_simple_value('z', []));
	}

	public function testArraySimple()
	{
		$this->assertEquals([
			'a[a][a]'    => 'aaa',
			'a[a][b]'    => 'aab',
			'a[b][a]'    => 'aba',
			'a[b][b]'    => 'abb',
			'a[b][c][a]' => 'abca',
			'a[b][c][b]' => 'abcb',
			'b[a][a]'    => 'baa',
			'b[a][b]'    => 'bab',
			'b[b][a]'    => 'bba',
			'b[b][b]'    => 'bbb',
			'b[b][c][a]' => 'bbca',
			'b[b][c][b]' => 'bbcb',
		], \array_simple($this->array));

		$this->assertEquals([
			'a[a]'    => 'aaa',
			'a[b]'    => 'aab',
			'b[a]'    => 'aba',
			'b[b]'    => 'abb',
			'b[c][a]' => 'abca',
			'b[c][b]' => 'abcb',
		], \array_simple($this->array['a']));

		$this->assertEquals([
			'a'    => 'bba',
			'b'    => 'bbb',
			'c[a]' => 'bbca',
			'c[b]' => 'bbcb',
		], \array_simple($this->array['b']['b']));

		$this->assertEquals([], \array_simple([]));
	}
}
