<?php

declare(strict_types=1);

namespace Majesko\FpKit\Tests;

use PHPUnit\Framework\TestCase;

use function Majesko\FpKit\Functions\partial;

final class PartialTest extends TestCase
{
    public function testPartialBindsFirstArgument(): void
    {
        $add = fn (int $a, int $b) => $a + $b;
        $add5 = partial($add, 5);

        self::assertSame(8, $add5(3));
    }

    public function testPartialBindsMultipleArguments(): void
    {
        $sum = fn (int $a, int $b, int $c) => $a + $b + $c;
        $sumWith5And10 = partial($sum, 5, 10);

        self::assertSame(18, $sumWith5And10(3));
    }

    public function testPartialWithStringConcatenation(): void
    {
        $concat = fn (string $a, string $b, string $c) => $a . $b . $c;
        $greet = partial($concat, 'Hello', ' ');

        self::assertSame('Hello World', $greet('World'));
    }

    public function testPartialWithAllArgumentsBound(): void
    {
        $multiply = fn (int $a, int $b) => $a * $b;
        $multiplySpecific = partial($multiply, 3, 4);

        self::assertSame(12, $multiplySpecific());
    }
}
