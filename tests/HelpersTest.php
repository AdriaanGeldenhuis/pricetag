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

    // -- isUnsafePublicUrl -------------------------------------------------

    public function testUnsafeUrlRejectsFileScheme(): void
    {
        $reason = isUnsafePublicUrl('file:///etc/passwd');
        $this->assertNotNull($reason);
        $this->assertStringContains('disallowed scheme', $reason);
    }

    public function testUnsafeUrlRejectsGopherScheme(): void
    {
        $reason = isUnsafePublicUrl('gopher://example.com/');
        $this->assertNotNull($reason);
    }

    public function testUnsafeUrlRejectsLoopbackIp(): void
    {
        $reason = isUnsafePublicUrl('http://127.0.0.1/admin');
        $this->assertNotNull($reason);
        $this->assertStringContains('private/reserved', $reason);
    }

    public function testUnsafeUrlRejectsAwsMetadataAddress(): void
    {
        // 169.254.169.254 - the link-local cloud-metadata endpoint.
        $reason = isUnsafePublicUrl('http://169.254.169.254/latest/meta-data/');
        $this->assertNotNull($reason);
    }

    public function testUnsafeUrlRejectsRfc1918Range(): void
    {
        $reason = isUnsafePublicUrl('http://10.0.0.5/');
        $this->assertNotNull($reason);
        $reason = isUnsafePublicUrl('http://192.168.1.1/');
        $this->assertNotNull($reason);
    }

    public function testUnsafeUrlRejectsIpv6Loopback(): void
    {
        $reason = isUnsafePublicUrl('http://[::1]/');
        $this->assertNotNull($reason);
    }

    public function testUnsafeUrlRejectsMalformed(): void
    {
        $this->assertNotNull(isUnsafePublicUrl('not a url'));
        $this->assertNotNull(isUnsafePublicUrl('http://'));
    }

    public function testUnsafeUrlAcceptsPublicHttps(): void
    {
        // example.com is reserved by IANA for documentation but resolves
        // to a public address (93.184.216.34). Good test target.
        $this->assertNull(isUnsafePublicUrl('https://example.com/image.jpg'));
    }
}
