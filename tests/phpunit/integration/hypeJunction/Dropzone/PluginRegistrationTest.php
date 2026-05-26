<?php

namespace hypeJunction\Dropzone;

use Elgg\IntegrationTestCase;

/**
 * Tests that the plugin registers entities, actions, views, and settings as declared in elgg-plugin.php.
 */
class PluginRegistrationTest extends IntegrationTestCase {

    /**
     * @return string
     */
    public function getPluginID(): string {
        return 'hypedropzone';
    }

    public function up() {}
    public function down() {}

    /**
     * @return void
     */
    public function testPluginIsActive(): void {
        $plugin = \elgg_get_plugin_from_id('hypedropzone');
        $this->assertNotNull($plugin);
        $this->assertTrue($plugin->isActive());
    }

    /**
     * @return void
     */
    public function testFileChunkEntityRegistered(): void {
        $class = \elgg_get_entity_class('object', 'file_chunk');
        $this->assertEquals(FileChunk::class, $class);
    }

    /**
     * @return void
     */
    public function testUploadActionRegistered(): void {
        $actions = \_elgg_services()->actions->getAllActions();
        $this->assertArrayHasKey('dropzone/upload', $actions);
    }

    /**
     * @return void
     */
    public function testChunkUploadActionRegistered(): void {
        $actions = \_elgg_services()->actions->getAllActions();
        $this->assertArrayHasKey('dropzone/upload_chunk', $actions);
    }

    /**
     * @return void
     */
    public function testChunkAssembleActionRegistered(): void {
        $actions = \_elgg_services()->actions->getAllActions();
        $this->assertArrayHasKey('dropzone/assemble_chunks', $actions);
    }

    /**
     * @return void
     */
    public function testChunkedUploadsSettingDefault(): void {
        // default setting from elgg-plugin.php is true
        $value = \elgg_get_plugin_setting('chunked_uploads', 'hypedropzone');
        $this->assertNotNull($value);
    }

    /**
     * @return void
     */
    public function testDropzoneServiceRegistered(): void {
        $svc = elgg()->dropzone;
        $this->assertInstanceOf(\hypeJunction\DropzoneService::class, $svc);
    }

    /**
     * @return void
     */
    public function testDropzoneStylesheetViewExists(): void {
        $this->assertTrue(\elgg_view_exists('css/dropzone/stylesheet'));
    }

    /**
     * @return void
     */
    public function testDropzoneLibJsViewExists(): void {
        $this->assertTrue(\elgg_view_exists('dropzone/lib.js'));
    }

    /**
     * @return void
     */
    public function testDropzoneTemplateViewExists(): void {
        $this->assertTrue(\elgg_view_exists('dropzone/template'));
    }

    /**
     * @return void
     */
    public function testInputDropzoneViewExists(): void {
        $this->assertTrue(\elgg_view_exists('input/dropzone'));
    }
}
