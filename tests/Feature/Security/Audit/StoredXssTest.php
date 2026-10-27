<?php

namespace Tests\Feature\Security\Audit;

use App\LandingPageCustomSection;
use App\OrganizationCustomSection;
use App\Services\HtmlSanitizer;
use App\Services\LandingPageCustomSectionService;
use App\Services\OrganizationCustomSectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * H10 / N4 / N5: stored XSS in custom-section rich text and in plain-text mentorship
 * comments and service descriptions that were printed with {!! !!}.
 */
class StoredXssTest extends TestCase
{
    use SecurityActors;

    private const PAYLOAD = '<p>Hello</p><script>alert(1)</script><img src="x" onerror="alert(2)">';
    private const VIDEO = '<iframe src="https://www.youtube.com/embed/abc123" allowfullscreen></iframe>';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
    }

    private function assertClean(?string $html): void
    {
        $this->assertStringContainsString('Hello', (string) $html);
        $this->assertStringNotContainsString('<script', (string) $html);
        $this->assertStringNotContainsString('onerror', (string) $html);
    }

    private function landingPage(): int
    {
        return DB::table('landing_pages')->insertGetId([
            'created_by' => $this->actor('admin')->id,
            'title' => 'Audit LP',
            'slug' => 'audit-lp-' . Str::lower(Str::random(8)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_organization_custom_section_is_sanitized_when_saved(): void
    {
        $org = $this->organization();

        app(OrganizationCustomSectionService::class)->update($org, [
            ['title' => 'About', 'content' => self::PAYLOAD . self::VIDEO],
        ]);

        $content = OrganizationCustomSection::where('organization_id', $org)->value('content');
        $this->assertClean($content);
        $this->assertStringContainsString('youtube.com/embed/abc123', $content);
    }

    public function test_landing_page_custom_section_is_sanitized_when_saved(): void
    {
        $page = $this->landingPage();

        app(LandingPageCustomSectionService::class)->update($page, [
            ['title' => 'Block', 'content' => self::PAYLOAD, 'content_ar' => self::PAYLOAD, 'visibility' => 1],
        ]);

        $section = LandingPageCustomSection::where('landing_page_id', $page)->firstOrFail();
        $this->assertClean($section->content);
        $this->assertClean($section->content_ar);
    }

    public function test_legacy_unsanitized_rows_are_cleaned_when_rendered(): void
    {
        $org = OrganizationCustomSection::forceCreate(['organization_id' => $this->organization(), 'title' => 'Old', 'content' => self::PAYLOAD]);
        $lp = LandingPageCustomSection::forceCreate(['landing_page_id' => $this->landingPage(), 'content' => self::PAYLOAD, 'content_ar' => self::PAYLOAD]);

        $this->assertClean($org->safe_content);
        $this->assertClean($lp->safe_content);
        $this->assertClean($lp->safe_content_ar);
    }

    public function test_pasted_webp_images_survive_but_fake_images_do_not(): void
    {
        $image = imagecreatetruecolor(2, 2);
        ob_start();
        imagewebp($image);
        $webp = base64_encode(ob_get_clean());
        imagedestroy($image);

        $real = HtmlSanitizer::clean('<p>Hello</p><img src="data:image/webp;base64,' . $webp . '">');
        $fake = HtmlSanitizer::clean('<p>Hello</p><img src="data:image/webp;base64,' . base64_encode('<svg onload=alert(1)>not an image</svg>') . '">');

        $this->assertStringContainsString('data:image/webp;base64,', $real);
        $this->assertStringNotContainsString('data:', $fake);
    }

    /** The views must print the sanitized accessors / escaped plain text, never the raw columns. */
    public function test_views_do_not_print_user_text_raw(): void
    {
        $views = [
            'resources/views/public/organization-landing.blade.php' => ['/\{!!\s*\$customSection->content\s*!!\}/'],
            'resources/views/public/landing-page.blade.php' => ['/\$sectionContent\s*=\s*\$isAr\s*\?\s*\$section->content_ar\s*:\s*\$section->content\s*;/'],
            'resources/views/admin/settings/mentorship/sessions.blade.php' => ['/\{!!\s*\$session->(rate_)?comment\s*!!\}/'],
            'resources/views/admin/clients/provider-profile.blade.php' => ['/\{!!\s*\$service->description\s*!!\}/'],
            'resources/views/admin/settings/services/index.blade.php' => ['/\{!!\s*Str::limit\(\s*\$service->description/'],
            'resources/views/includes/dashboard.blade.php' => ["/'<br>'\\s*\\.\\s*\\\$session->rate_comment/", '/:\s*\$session->comment\s*!!\}/'],
            'resources/views/admin/hackathon/admin/dashboard_admin.blade.php' => ["/'<br>'\\s*\\.\\s*\\\$session->rate_comment/", '/:\s*\$session->comment\s*!!\}/'],
            'resources/views/admin/hackathon/partials/mentorship-tab.blade.php' => ["/'<br>'\\s*\\.\\s*\\\$session->rate_comment/", '/:\s*\$session->comment\s*!!\}/'],
        ];

        $offenders = [];
        foreach ($views as $file => $patterns) {
            $source = file_get_contents(base_path($file));
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $source)) {
                    $offenders[] = "{$file} {$pattern}";
                }
            }
        }

        $this->assertSame([], $offenders);
    }
}
