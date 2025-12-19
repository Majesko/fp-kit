<?php

declare(strict_types=1);

namespace Majesko\FpKit\Tests;

use PHPUnit\Framework\TestCase;

use function Majesko\FpKit\Option\{some, none, isSome, map, bind, unwrapOr, fromArray, fromNullable, toResult, matchOption};
use function Majesko\FpKit\Result\{isOk, unwrapOr as resultUnwrapOr, fold};

final class OptionTest extends TestCase
{
    public function testSomeCreatesOption(): void
    {
        $opt = some(42);

        self::assertTrue(isSome($opt));
    }

    public function testNoneCreatesEmptyOption(): void
    {
        $opt = none();

        self::assertFalse(isSome($opt));
    }

    public function testMapTransformsSomeValue(): void
    {
        $opt = some(5);
        $result = map($opt, fn (int $x) => $x * 2);

        self::assertSame(10, unwrapOr($result, 0));
    }

    public function testMapSkipsNone(): void
    {
        $opt = none();
        $result = map($opt, fn (int $x) => $x * 2);

        self::assertFalse(isSome($result));
    }

    public function testBindChainsSomeOperations(): void
    {
        $opt = some(5);
        $result = bind($opt, fn (int $x) => $x > 0 ? some($x * 2) : none());

        self::assertSame(10, unwrapOr($result, 0));
    }

    public function testBindCanReturnNone(): void
    {
        $opt = some(-5);
        $result = bind($opt, fn (int $x) => $x > 0 ? some($x * 2) : none());

        self::assertFalse(isSome($result));
    }

    public function testBindSkipsNone(): void
    {
        $opt = none();
        $result = bind($opt, fn (int $x) => some($x * 2));

        self::assertFalse(isSome($result));
    }

    public function testUnwrapOrReturnsSomeValue(): void
    {
        $opt = some(42);

        self::assertSame(42, unwrapOr($opt, 0));
    }

    public function testUnwrapOrReturnsDefaultForNone(): void
    {
        $opt = none();

        self::assertSame(0, unwrapOr($opt, 0));
    }

    public function testFromArrayWithExistingKey(): void
    {
        $arr = ['name' => 'Alice', 'age' => 30];
        $opt = fromArray($arr, 'name');

        self::assertSame('Alice', unwrapOr($opt, ''));
    }

    public function testFromArrayWithMissingKey(): void
    {
        $arr = ['name' => 'Alice'];
        $opt = fromArray($arr, 'age');

        self::assertFalse(isSome($opt));
    }

    public function testFromArrayWithNumericKey(): void
    {
        $arr = [0 => 'first', 1 => 'second'];
        $opt = fromArray($arr, 1);

        self::assertSame('second', unwrapOr($opt, ''));
    }

    public function testFromNullableWithValue(): void
    {
        $opt = fromNullable(42);

        self::assertTrue(isSome($opt));
        self::assertSame(42, unwrapOr($opt, 0));
    }

    public function testFromNullableWithNull(): void
    {
        $opt = fromNullable(null);

        self::assertFalse(isSome($opt));
    }

    public function testFromNullableWithZero(): void
    {
        $opt = fromNullable(0);

        self::assertTrue(isSome($opt));
        self::assertSame(0, unwrapOr($opt, 99));
    }

    public function testToResultWithSome(): void
    {
        $opt = some(42);
        $result = toResult($opt, 'error');

        self::assertTrue(isOk($result));
        self::assertSame(42, resultUnwrapOr($result, 0));
    }

    public function testToResultWithNone(): void
    {
        $opt = none();
        $result = toResult($opt, 'not found');

        self::assertFalse(isOk($result));
        self::assertSame('not found', fold($result, fn () => '', fn ($e) => $e));
    }

    public function testMatchOptionWithSome(): void
    {
        $opt = some(42);
        $result = matchOption(
            $opt,
            fn (int $x) => "value: $x",
            fn () => 'no value'
        );

        self::assertSame('value: 42', $result);
    }

    public function testMatchOptionWithNone(): void
    {
        $opt = none();
        $result = matchOption(
            $opt,
            fn (int $x) => "value: $x",
            fn () => 'no value'
        );

        self::assertSame('no value', $result);
    }
}
