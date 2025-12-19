<?php

declare(strict_types=1);

namespace Majesko\FpKit\Tests;

use PHPUnit\Framework\TestCase;

use function Majesko\FpKit\Result\{ok, err, isOk, map as rmap, bind, mapError, fold, unwrapOr, matchResult};

final class ResultTest extends TestCase
{
    public function testOkMapBind(): void
    {
        $r = bind(
            rmap(ok(2), fn (int $x) => $x + 1),
            fn (int $x) => ok($x * 3)
        );

        self::assertSame(9, unwrapOr($r, 0));
    }

    public function testErrSkipsMapBind(): void
    {
        $r = bind(
            rmap(err('nope'), fn () => 123),
            fn () => ok(999)
        );

        self::assertSame('nope', fold($r, fn () => '', fn ($e) => $e));
    }

    public function testOkCreatesSuccessResult(): void
    {
        $result = ok(42);

        self::assertTrue(isOk($result));
    }

    public function testErrCreatesErrorResult(): void
    {
        $result = err('error message');

        self::assertFalse(isOk($result));
    }

    public function testMapTransformsOkValue(): void
    {
        $result = rmap(ok(5), fn (int $x) => $x * 2);

        self::assertSame(10, unwrapOr($result, 0));
    }

    public function testMapSkipsErr(): void
    {
        $result = rmap(err('error'), fn (int $x) => $x * 2);

        self::assertFalse(isOk($result));
        self::assertSame('error', fold($result, fn () => '', fn ($e) => $e));
    }

    public function testBindChainsOkOperations(): void
    {
        $result = bind(ok(10), fn (int $x) => ok($x / 2));

        self::assertSame(5, unwrapOr($result, 0));
    }

    public function testBindCanReturnErr(): void
    {
        $result = bind(
            ok(0),
            fn (int $x) => $x === 0 ? err('division by zero') : ok(10 / $x)
        );

        self::assertFalse(isOk($result));
        self::assertSame('division by zero', fold($result, fn () => '', fn ($e) => $e));
    }

    public function testMapErrorTransformsError(): void
    {
        $result = mapError(err('not found'), fn (string $e) => strtoupper($e));

        self::assertSame('NOT FOUND', fold($result, fn () => '', fn ($e) => $e));
    }

    public function testMapErrorSkipsOk(): void
    {
        $result = mapError(ok(42), fn (string $e) => strtoupper($e));

        self::assertTrue(isOk($result));
        self::assertSame(42, unwrapOr($result, 0));
    }

    public function testUnwrapOrReturnsOkValue(): void
    {
        $result = ok(100);

        self::assertSame(100, unwrapOr($result, 0));
    }

    public function testUnwrapOrReturnsDefaultForErr(): void
    {
        $result = err('error');

        self::assertSame(999, unwrapOr($result, 999));
    }

    public function testFoldWithOk(): void
    {
        $result = ok(42);
        $value = fold(
            $result,
            fn (int $x) => $x * 2,
            fn () => 0
        );

        self::assertSame(84, $value);
    }

    public function testFoldWithErr(): void
    {
        $result = err('error message');
        $value = fold(
            $result,
            fn () => 0,
            fn (string $e) => strlen($e)
        );

        self::assertSame(13, $value);
    }

    public function testMatchResultWithOk(): void
    {
        $result = ok('success');
        $message = matchResult(
            $result,
            fn (string $s) => "OK: $s",
            fn (string $e) => "ERROR: $e"
        );

        self::assertSame('OK: success', $message);
    }

    public function testMatchResultWithErr(): void
    {
        $result = err('failed');
        $message = matchResult(
            $result,
            fn (string $s) => "OK: $s",
            fn (string $e) => "ERROR: $e"
        );

        self::assertSame('ERROR: failed', $message);
    }
}
