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

    // -- sanitizeUntrustedHtml --------------------------------------------

    public function testSanitizeKeepsAllowedFormattingTags(): void
    {
        $html = '<h3>Title</h3><p>Hello <strong>world</strong></p>';
        $this->assertSame($html, sanitizeUntrustedHtml($html));
    }

    public function testSanitizeDropsScriptBlockAndContents(): void
    {
        $html = '<p>Safe</p><script>alert(1)</script>';
        $this->assertSame('<p>Safe</p>', sanitizeUntrustedHtml($html));
    }

    public function testSanitizeDropsStyleBlockAndContents(): void
    {
        $html = '<style>body{display:none}</style><p>Visible</p>';
        $this->assertSame('<p>Visible</p>', sanitizeUntrustedHtml($html));
    }

    public function testSanitizeStripsEventHandlerAttributes(): void
    {
        $html = '<p onclick="alert(1)">click me</p>';
        // strip_tags keeps the tag; the attribute-stripper drops onclick.
        $this->assertSame('<p>click me</p>', sanitizeUntrustedHtml($html));
    }

    public function testSanitizeStripsStyleAttribute(): void
    {
        $html = '<p style="background:url(javascript:alert(1))">x</p>';
        $this->assertSame('<p>x</p>', sanitizeUntrustedHtml($html));
    }

    public function testSanitizeStripsDisallowedTags(): void
    {
        $html = '<p>ok</p><iframe src="https://evil.com"></iframe>';
        $this->assertSame('<p>ok</p>', sanitizeUntrustedHtml($html));
    }

    public function testSanitizeReturnsEmptyForNull(): void
    {
        $this->assertSame('', sanitizeUntrustedHtml(null));
    }
}
