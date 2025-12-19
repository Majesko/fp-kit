<?php

declare(strict_types=1);

namespace Majesko\FpKit\Tests;

use PHPUnit\Framework\TestCase;

use function Majesko\FpKit\Validation\{valid, invalid, isValid, errors, map, combine, lift, toResult, matchValidation};
use function Majesko\FpKit\Result\{matchResult, isOk};

final class ValidationTest extends TestCase
{
    public function testLiftValid(): void
    {
        $v = lift(fn (int $a, int $b) => $a + $b, [valid(2), valid(3)]);
        $r = toResult($v);

        self::assertSame(5, matchResult($r, fn ($x) => $x, fn () => null));
    }

    public function testLiftAccumulatesErrors(): void
    {
        $v = lift(fn (int $a, int $b) => $a + $b, [
            invalid([['field' => 'a', 'message' => 'Required']]),
            invalid([['field' => 'b', 'message' => 'Must be int']]),
        ]);

        $r = toResult($v);

        $errs = matchResult($r, fn () => [], fn ($e) => $e);
        self::assertCount(2, $errs);
    }

    public function testValidCreatesValidValidation(): void
    {
        $v = valid(42);

        self::assertTrue(isValid($v));
    }

    public function testInvalidCreatesInvalidValidation(): void
    {
        $v = invalid(['error1', 'error2']);

        self::assertFalse(isValid($v));
    }

    public function testErrorsReturnsEmptyForValid(): void
    {
        $v = valid(42);

        self::assertSame([], errors($v));
    }

    public function testErrorsReturnsErrorsForInvalid(): void
    {
        $v = invalid(['error1', 'error2']);

        self::assertSame(['error1', 'error2'], errors($v));
    }

    public function testMapTransformsValidValue(): void
    {
        $v = map(valid(5), fn (int $x) => $x * 2);

        self::assertTrue(isValid($v));
        $r = toResult($v);
        self::assertSame(10, matchResult($r, fn ($x) => $x, fn () => 0));
    }

    public function testMapSkipsInvalid(): void
    {
        $v = map(invalid(['error']), fn (int $x) => $x * 2);

        self::assertFalse(isValid($v));
        self::assertSame(['error'], errors($v));
    }

    public function testCombineAllValid(): void
    {
        $v = combine([valid(1), valid(2), valid(3)]);

        self::assertTrue(isValid($v));
        $r = toResult($v);
        self::assertSame([1, 2, 3], matchResult($r, fn ($x) => $x, fn () => []));
    }

    public function testCombineAccumulatesErrors(): void
    {
        $v = combine([
            valid(1),
            invalid(['error1']),
            invalid(['error2', 'error3']),
        ]);

        self::assertFalse(isValid($v));
        self::assertSame(['error1', 'error2', 'error3'], errors($v));
    }

    public function testCombineEmptyArray(): void
    {
        $v = combine([]);

        self::assertTrue(isValid($v));
        $r = toResult($v);
        self::assertSame([], matchResult($r, fn ($x) => $x, fn () => null));
    }

    public function testLiftWithAllValid(): void
    {
        $v = lift(
            fn (string $a, string $b) => "$a $b",
            [valid('Hello'), valid('World')]
        );

        self::assertTrue(isValid($v));
        $r = toResult($v);
        self::assertSame('Hello World', matchResult($r, fn ($x) => $x, fn () => ''));
    }

    public function testLiftWithPartialErrors(): void
    {
        $v = lift(
            fn (int $a, int $b) => $a + $b,
            [valid(10), invalid(['b is required'])]
        );

        self::assertFalse(isValid($v));
        self::assertSame(['b is required'], errors($v));
    }

    public function testToResultWithValid(): void
    {
        $v = valid('success');
        $r = toResult($v);

        self::assertTrue(isOk($r));
        self::assertSame('success', matchResult($r, fn ($x) => $x, fn () => ''));
    }

    public function testToResultWithInvalid(): void
    {
        $v = invalid(['error1', 'error2']);
        $r = toResult($v);

        self::assertFalse(isOk($r));
        $errs = matchResult($r, fn () => [], fn ($e) => $e);
        self::assertSame(['error1', 'error2'], $errs);
    }

    public function testMatchValidationWithValid(): void
    {
        $v = valid(42);
        $result = matchValidation(
            $v,
            fn (int $x) => "value: $x",
            fn (array $errs) => 'errors: ' . count($errs)
        );

        self::assertSame('value: 42', $result);
    }

    public function testMatchValidationWithInvalid(): void
    {
        $v = invalid(['error1', 'error2', 'error3']);
        $result = matchValidation(
            $v,
            fn (int $x) => "value: $x",
            fn (array $errs) => 'errors: ' . count($errs)
        );

        self::assertSame('errors: 3', $result);
    }
}
