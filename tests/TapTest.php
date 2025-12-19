<?php

declare(strict_types=1);

namespace Majesko\FpKit\Tests;

use PHPUnit\Framework\TestCase;

use function Majesko\FpKit\Functions\tap;

final class TapTest extends TestCase
{
    public function testTapExecutesSideEffectAndReturnsValue(): void
    {
        $sideEffect = [];

        $result = tap(42, function (int $x) use (&$sideEffect): void {
            $sideEffect[] = $x;
        });

        self::assertSame(42, $result);
        self::assertSame([42], $sideEffect);
    }

    public function testTapWithArrayValue(): void
    {
        $log = [];

        $result = tap(['a', 'b', 'c'], function (array $arr) use (&$log): void {
            $log['count'] = count($arr);
        });

        self::assertSame(['a', 'b', 'c'], $result);
        self::assertSame(['count' => 3], $log);
    }

    public function testTapCanBeUsedInPipeline(): void
    {
        $log = [];

        $value = 5;
        $value = tap($value, function ($x) use (&$log): void {
            $log[] = "before: $x";
        });
        $value = $value * 2;
        $value = tap($value, function ($x) use (&$log): void {
            $log[] = "after: $x";
        });

        self::assertSame(10, $value);
        self::assertSame(['before: 5', 'after: 10'], $log);
    }
}
