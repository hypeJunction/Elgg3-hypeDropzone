<?php

namespace hypeJunction\Dropzone;

use Elgg\IntegrationTestCase;

/**
 * Language string availability tests.
 */
class LanguageTest extends IntegrationTestCase {

    public function getPluginID(): string {
        return 'hypedropzone';
    }

    public function up() {}
    public function down() {}

    public function testDefaultMessageTranslationExists(): void {
        $str = elgg_echo('dropzone:default_message');
        $this->assertNotEquals('dropzone:default_message', $str);
    }

    public function testFallbackMessageTranslationExists(): void {
        $str = elgg_echo('dropzone:fallback_message');
        $this->assertNotEquals('dropzone:fallback_message', $str);
    }

    public function testInvalidFiletypeTranslationExists(): void {
        $str = elgg_echo('dropzone:invalid_filetype');
        $this->assertNotEquals('dropzone:invalid_filetype', $str);
    }

    public function testFileTooBigTranslationExists(): void {
        $str = elgg_echo('dropzone:file_too_big');
        $this->assertNotEquals('dropzone:file_too_big', $str);
    }
}
