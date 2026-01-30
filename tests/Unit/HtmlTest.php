<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Html;
use PHPUnit\Framework\TestCase;

final class HtmlTest extends TestCase
{
    public function testEscapeBasic(): void
    {
        $this->assertSame('&amp;', Html::escape('&'));
        $this->assertSame('&lt;script&gt;', Html::escape('<script>'));
        $this->assertSame('&quot;', Html::escape('"'));
    }

    public function testEscapePreservesSafeContent(): void
    {
        $this->assertSame('Hello World', Html::escape('Hello World'));
    }

    public function testEscapeEmpty(): void
    {
        $this->assertSame('', Html::escape(''));
    }
}
