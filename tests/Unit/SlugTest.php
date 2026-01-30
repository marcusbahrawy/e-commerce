<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Slug;
use PHPUnit\Framework\TestCase;

final class SlugTest extends TestCase
{
    public function testFromSimpleTitle(): void
    {
        $this->assertSame('hello-world', Slug::from('Hello World'));
    }

    public function testFromNorwegianCharacters(): void
    {
        $this->assertSame('a-o', Slug::from('Æ Ø'));
    }

    public function testFromStripsSpecialChars(): void
    {
        $this->assertSame('produkt-navn', Slug::from('Produkt & Navn!'));
    }

    public function testFromCollapsesSpacesAndDashes(): void
    {
        $this->assertSame('a-b-c', Slug::from('a   b - c'));
    }

    public function testFromTrimsDashes(): void
    {
        $this->assertSame('inner', Slug::from('--- inner ---'));
    }

    public function testFromEmptyBecomesEmpty(): void
    {
        $this->assertSame('', Slug::from(''));
    }
}
