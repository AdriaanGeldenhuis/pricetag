<?php
/**
 * Unit tests for app/helpers.php pure functions that don't need DB or
 * session state.
 */

declare(strict_types=1);

final class HelpersTest extends TestCase
{
    public function testSlugifyLowercases(): void
    {
        $this->assertSame('hello-world', slugify('Hello World'));
    }

    public function testSlugifyStripsPunctuation(): void
    {
        $this->assertSame('air-jordan-1-retro', slugify("Air Jordan 1 (Retro!)"));
    }

    public function testSlugifyCollapsesWhitespaceAndUnderscores(): void
    {
        $this->assertSame('a-b-c', slugify("a   b__c"));
    }

    public function testSlugifyTrimsLeadingTrailingHyphens(): void
    {
        $this->assertSame('product-name', slugify('---product---name---'));
    }

    public function testSlugifyEmptyReturnsPlaceholder(): void
    {
        // slugify falls back to 'n-a' rather than emitting an empty slug,
        // so callers always get a usable URL fragment.
        $this->assertSame('n-a', slugify(''));
    }
}
