<?php

namespace hypeJunction\Dropzone;

use Elgg\IntegrationTestCase;

/**
 * Tests that the plugin registers entities, actions, views, and settings as declared in elgg-plugin.php.
 */
class PluginRegistrationTest extends IntegrationTestCase {

    public function getPluginID(): string {
        return 'hypeDropzone';
    }

    public function up() {}
    public function down() {}

    public function testPluginIsActive(): void {
        $plugin = elgg_get_plugin_from_id('hypeDropzone');
        $this->assertNotNull($plugin);
        $this->assertTrue($plugin->isActive());
    }

    public function testFileChunkEntityRegistered(): void {
        $class = elgg_get_entity_class('object', 'file_chunk');
        $this->assertEquals(FileChunk::class, $class);
    }

    public function testUploadActionRegistered(): void {
        $actions = _elgg_services()->actions->getAllActions();
        $this->assertArrayHasKey('dropzone/upload', $actions);
    }

    public function testChunkUploadActionRegistered(): void {
        $actions = _elgg_services()->actions->getAllActions();
        $this->assertArrayHasKey('dropzone/upload_chunk', $actions);
    }

    public function testChunkAssembleActionRegistered(): void {
        $actions = _elgg_services()->actions->getAllActions();
        $this->assertArrayHasKey('dropzone/assemble_chunks', $actions);
    }

    public function testChunkedUploadsSettingDefault(): void {
        // default setting from elgg-plugin.php is true
        $value = elgg_get_plugin_setting('chunked_uploads', 'hypeDropzone');
        $this->assertNotNull($value);
    }

    public function testDropzoneServiceRegistered(): void {
        $svc = elgg()->dropzone;
        $this->assertInstanceOf(\hypeJunction\DropzoneService::class, $svc);
    }

    public function testDropzoneStylesheetViewExists(): void {
        $this->assertTrue(elgg_view_exists('css/dropzone/stylesheet'));
    }

    public function testDropzoneLibJsViewExists(): void {
        $this->assertTrue(elgg_view_exists('dropzone/lib.js'));
    }

    public function testDropzoneTemplateViewExists(): void {
        $this->assertTrue(elgg_view_exists('dropzone/template'));
    }

    public function testInputDropzoneViewExists(): void {
        $this->assertTrue(elgg_view_exists('input/dropzone'));
    }
}
