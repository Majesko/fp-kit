<?php

declare(strict_types=1);

namespace Majesko\FpKit\Tests;

use PHPUnit\Framework\TestCase;

use function Majesko\FpKit\Functions\pipe;

final class PipeTest extends TestCase
{
    public function testPipe(): void
    {
        $r = pipe(
            2,
            fn (int $x) => $x + 1,
            fn (int $x) => $x * 3
        );

        self::assertSame(9, $r);
    }
}
