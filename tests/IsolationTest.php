<?php
/*
 * This file is part of The Framework Helpers Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Helpers;

use Framework\Helpers\Isolation;
use PHPUnit\Framework\TestCase;

class IsolationTest extends TestCase
{
    protected string $dir = __DIR__ . '/isolation/';

    public function testNoReturn() : void
    {
        self::assertSame(1, Isolation::require($this->dir . 'noreturn.php'));
    }

    public function testReturnVar() : void
    {
        self::assertSame(
            18,
            Isolation::require($this->dir . 'return-var.php', ['var' => 18])
        );
    }

    public function testReturnData() : void
    {
        self::assertSame([], Isolation::require($this->dir . 'return-data.php'));
        self::assertSame(
            ['foo', 'bar'],
            Isolation::require($this->dir . 'return-data.php', ['foo', 'bar'])
        );
    }

    public function testReturnDataOverwrite() : void
    {
        $data = ['var' => 'foo', '__data' => 'baz'];
        self::assertEquals(
            $data,
            Isolation::require($this->dir . 'return-data.php', $data)
        );
        self::assertEquals(
            'foo',
            Isolation::require($this->dir . 'return-var.php', $data)
        );
    }

    public function testIsolationIntoClass() : void
    {
        $class = new class() {
            protected string $filename = __DIR__ . '/isolation/into-class.php';

            public function test() : string
            {
                return 'test';
            }

            public function nonIsolated() : mixed
            {
                return require $this->filename;
            }

            public function isolated() : mixed
            {
                return Isolation::require($this->filename);
            }
        };
        self::assertSame('test', $class->nonIsolated());
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Using $this when not in object context');
        $class->isolated();
    }
}
