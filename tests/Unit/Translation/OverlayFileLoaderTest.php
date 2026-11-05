<?php

namespace Tests\Unit\Translation;

use App\Translation\OverlayFileLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\Translator;
use PHPUnit\Framework\TestCase;

/**
 * LOCALIZATION_PATH: resources/lang/{locale}/{overlay}/ overrides the normal files key by key,
 * and anything missing from the overlay falls back to the normal translation.
 */
class OverlayFileLoaderTest extends TestCase
{
    private Filesystem $files;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem;
        $this->path = sys_get_temp_dir().'/overlay-loader-'.bin2hex(random_bytes(6));

        $this->write('en/admin.php', "<?php return ['Startups' => 'Startups', 'Founder' => 'Founder', 'Save' => 'Save', 'nested' => ['a' => 'Startup A', 'b' => 'Keep B']];");
        $this->write('en/isf/admin.php', "<?php return ['Startups' => 'Ideas', 'nested' => ['a' => 'Idea A']];");
        $this->write('en/cycles.php', "<?php return ['Program' => 'Program'];");
        $this->write('ar.json', '{"Startups": "الشركات الناشئة", "Save": "حفظ"}');
        $this->write('ar/isf/ar.json', '{"Startups": "الأفكار"}');
        $this->write('en/isf/en.json', '{"Startups": "Ideas"}');
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->path);

        parent::tearDown();
    }

    private function write(string $relative, string $contents): void
    {
        $this->files->ensureDirectoryExists(dirname($this->path.'/'.$relative));
        $this->files->put($this->path.'/'.$relative, $contents);
    }

    private function translator(?string $overlay, string $locale = 'en'): Translator
    {
        return new Translator(new OverlayFileLoader($this->files, [$this->path], $overlay), $locale);
    }

    /** @test */
    public function overlay_key_replaces_the_normal_translation()
    {
        $this->assertSame('Ideas', $this->translator('isf')->get('admin.Startups'));
    }

    /** @test */
    public function key_missing_from_the_overlay_falls_back_to_the_normal_file()
    {
        $translator = $this->translator('isf');

        $this->assertSame('Founder', $translator->get('admin.Founder'));
        $this->assertSame('Save', $translator->get('admin.Save'));
    }

    /** @test */
    public function nested_keys_are_merged_not_replaced_wholesale()
    {
        $translator = $this->translator('isf');

        $this->assertSame('Idea A', $translator->get('admin.nested.a'));
        $this->assertSame('Keep B', $translator->get('admin.nested.b'));
    }

    /** @test */
    public function group_without_an_overlay_file_uses_the_normal_file()
    {
        $this->assertSame('Program', $this->translator('isf')->get('cycles.Program'));
    }

    /** @test */
    public function overlay_folder_that_does_not_exist_changes_nothing()
    {
        $this->assertSame('Startups', $this->translator('missing')->get('admin.Startups'));
    }

    /** @test */
    public function no_overlay_keeps_the_default_wording()
    {
        $this->assertSame('Startups', $this->translator(null)->get('admin.Startups'));
        $this->assertSame('Startups', $this->translator('')->get('admin.Startups'));
    }

    /** @test */
    public function json_strings_are_overlaid_with_fallback()
    {
        $ar = $this->translator('isf', 'ar');

        $this->assertSame('الأفكار', $ar->get('Startups'));
        $this->assertSame('حفظ', $ar->get('Save'));
        // English has no base en.json: the overlay alone translates the key.
        $this->assertSame('Ideas', $this->translator('isf')->get('Startups'));
    }

    /** @test */
    public function overlay_name_that_is_not_a_plain_folder_name_is_ignored()
    {
        $this->write('evil/admin.php', "<?php return ['Startups' => 'Hacked'];");

        foreach (['../evil', 'isf/../../evil', '/tmp', 'a b'] as $name) {
            $loader = new OverlayFileLoader($this->files, [$this->path], $name);

            $this->assertNull($loader->overlay(), $name);
            $this->assertSame('Startups', (new Translator($loader, 'en'))->get('admin.Startups'), $name);
        }
    }

    /** @test */
    public function vendor_namespaced_translations_are_not_overlaid()
    {
        $this->write('vendor-pkg/en/messages.php', "<?php return ['hello' => 'Startup'];");
        $this->write('en/isf/messages.php', "<?php return ['hello' => 'Idea'];");

        $translator = $this->translator('isf');
        $translator->addNamespace('pkg', $this->path.'/vendor-pkg');

        $this->assertSame('Startup', $translator->get('pkg::messages.hello'));
    }
}
