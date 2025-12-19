<?php

declare(strict_types=1);

namespace Majesko\FpKit\Tests;

use PHPUnit\Framework\TestCase;

use function Majesko\FpKit\Functions\compose;

final class ComposeTest extends TestCase
{
    public function testComposeAppliesRightToLeft(): void
    {
        $add1 = fn (int $x) => $x + 1;
        $mul3 = fn (int $x) => $x * 3;

        $composed = compose($add1, $mul3);

        // compose(add1, mul3)(2) => add1(mul3(2)) => add1(6) => 7
        self::assertSame(7, $composed(2));
    }

    public function testComposeWithThreeFunctions(): void
    {
        $add2 = fn (int $x) => $x + 2;
        $mul3 = fn (int $x) => $x * 3;
        $sub1 = fn (int $x) => $x - 1;

        $composed = compose($add2, $mul3, $sub1);

        // compose(add2, mul3, sub1)(5) => add2(mul3(sub1(5))) => add2(mul3(4)) => add2(12) => 14
        self::assertSame(14, $composed(5));
    }

    public function testComposeSingleFunction(): void
    {
        $double = fn (int $x) => $x * 2;
        $composed = compose($double);

        self::assertSame(10, $composed(5));
    }

    public function testComposeWithStringTransformations(): void
    {
        $uppercase = fn (string $s) => strtoupper($s);
        $trim = fn (string $s) => trim($s);

        $composed = compose($uppercase, $trim);

        self::assertSame('HELLO', $composed('  hello  '));
    }
}
