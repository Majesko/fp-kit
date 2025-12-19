<?php

declare(strict_types=1);

namespace Majesko\FpKit\Tests;

use PHPUnit\Framework\TestCase;

use function Majesko\FpKit\Functions\{map, filter, reduce, groupBy, indexBy};

final class ArrayFunctionsTest extends TestCase
{
    public function testMap(): void
    {
        $nums = [1, 2, 3, 4];
        $result = map($nums, fn (int $x) => $x * 2);

        self::assertSame([2, 4, 6, 8], $result);
    }

    public function testMapEmptyArray(): void
    {
        $result = map([], fn (int $x) => $x * 2);

        self::assertSame([], $result);
    }

    public function testFilter(): void
    {
        $nums = [1, 2, 3, 4, 5, 6];
        $result = filter($nums, fn (int $x) => $x % 2 === 0);

        self::assertSame([2, 4, 6], $result);
    }

    public function testFilterReindexesArray(): void
    {
        $nums = [0 => 1, 1 => 2, 2 => 3, 3 => 4];
        $result = filter($nums, fn (int $x) => $x > 2);

        // Should reindex to [0 => 3, 1 => 4]
        self::assertSame([3, 4], $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    public function testFilterEmptyResult(): void
    {
        $nums = [1, 3, 5];
        $result = filter($nums, fn (int $x) => $x % 2 === 0);

        self::assertSame([], $result);
    }

    public function testReduce(): void
    {
        $nums = [1, 2, 3, 4];
        $result = reduce($nums, 0, fn (int $acc, int $x) => $acc + $x);

        self::assertSame(10, $result);
    }

    public function testReduceWithDifferentInitial(): void
    {
        $nums = [1, 2, 3];
        $result = reduce($nums, 10, fn (int $acc, int $x) => $acc + $x);

        self::assertSame(16, $result);
    }

    public function testReduceToString(): void
    {
        $words = ['hello', 'world', 'foo'];
        $result = reduce($words, '', fn (string $acc, string $word) => $acc . $word . ' ');

        self::assertSame('hello world foo ', $result);
    }

    public function testReduceEmptyArray(): void
    {
        $result = reduce([], 42, fn (int $acc, int $x) => $acc + $x);

        self::assertSame(42, $result);
    }

    public function testGroupBy(): void
    {
        $items = [
            ['type' => 'fruit', 'name' => 'apple'],
            ['type' => 'vegetable', 'name' => 'carrot'],
            ['type' => 'fruit', 'name' => 'banana'],
        ];

        $result = groupBy($items, fn (array $item) => $item['type']);

        self::assertArrayHasKey('fruit', $result);
        self::assertArrayHasKey('vegetable', $result);
        self::assertCount(2, $result['fruit']);
        self::assertCount(1, $result['vegetable']);
        self::assertSame('apple', $result['fruit'][0]['name']);
        self::assertSame('banana', $result['fruit'][1]['name']);
    }

    public function testGroupByWithNumbers(): void
    {
        $nums = [1, 2, 3, 4, 5, 6];
        $result = groupBy($nums, fn (int $x) => $x % 2 === 0 ? 'even' : 'odd');

        self::assertSame([1, 3, 5], $result['odd']);
        self::assertSame([2, 4, 6], $result['even']);
    }

    public function testGroupByEmpty(): void
    {
        $result = groupBy([], fn ($x) => 'key');

        self::assertSame([], $result);
    }

    public function testIndexBy(): void
    {
        $users = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 3, 'name' => 'Charlie'],
        ];

        $result = indexBy($users, fn (array $user) => $user['id']);

        self::assertArrayHasKey('1', $result);
        self::assertArrayHasKey('2', $result);
        self::assertArrayHasKey('3', $result);

        /** @var array{id: int, name: string} $user1 */
        $user1 = $result['1'];  // @phpstan-ignore offsetAccess.notFound
        /** @var array{id: int, name: string} $user2 */
        $user2 = $result['2'];  // @phpstan-ignore offsetAccess.notFound
        /** @var array{id: int, name: string} $user3 */
        $user3 = $result['3'];  // @phpstan-ignore offsetAccess.notFound

        self::assertSame('Alice', $user1['name']);
        self::assertSame('Bob', $user2['name']);
        self::assertSame('Charlie', $user3['name']);
    }

    public function testIndexByOverwritesDuplicates(): void
    {
        $items = [
            ['key' => 'a', 'value' => 1],
            ['key' => 'b', 'value' => 2],
            ['key' => 'a', 'value' => 3],
        ];

        $result = indexBy($items, fn (array $item) => $item['key']);

        // Last item with key 'a' should win
        self::assertSame(3, $result['a']['value']);
        self::assertSame(2, $result['b']['value']);
        self::assertCount(2, $result);
    }

    public function testIndexByEmpty(): void
    {
        $result = indexBy([], fn ($x) => 'key');

        self::assertSame([], $result);
    }
}
