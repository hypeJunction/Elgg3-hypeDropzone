<?php

namespace hypeJunction\Dropzone;

use Elgg\IntegrationTestCase;

/**
 * Tests that the input/dropzone view renders without errors and produces expected markup.
 */
class InputDropzoneViewTest extends IntegrationTestCase {

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
    public function testDropzoneInputRendersDefaults(): void {
        $output = elgg_view('input/dropzone', []);
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('elgg-dropzone', $output);
        $this->assertStringContainsString('elgg-input-dropzone', $output);
    }

    /**
     * @return void
     */
    public function testDropzoneInputRendersWithContainerGuid(): void {
        $output = elgg_view('input/dropzone', ['container_guid' => 42]);
        $this->assertStringContainsString('data-container-guid="42"', $output);
    }

    /**
     * @return void
     */
    public function testDropzoneInputRendersWithSubtype(): void {
        $output = elgg_view('input/dropzone', ['subtype' => 'file']);
        $this->assertStringContainsString('data-subtype="file"', $output);
    }

    /**
     * @return void
     */
    public function testDropzoneInputRendersWithMaxFiles(): void {
        $output = elgg_view('input/dropzone', ['max' => 5]);
        $this->assertStringContainsString('data-max-files="5"', $output);
    }

    /**
     * @return void
     */
    public function testDropzoneInputRendersWithAcceptedTypes(): void {
        $output = elgg_view('input/dropzone', ['accept' => 'image/*']);
        $this->assertStringContainsString('data-accepted-files="image/*"', $output);
    }

    /**
     * @return void
     */
    public function testDropzoneInputMultipleAppendsBracketsToName(): void {
        $output = elgg_view('input/dropzone', ['multiple' => true, 'name' => 'my_files']);
        $this->assertStringContainsString('data-name="my_files[]"', $output);
    }

    /**
     * @return void
     */
    public function testDropzoneInputDefaultNameIsFileGuids(): void {
        $output = elgg_view('input/dropzone', []);
        $this->assertStringContainsString('data-name="file_guids"', $output);
    }

    /**
     * @return void
     */
    public function testDropzoneTemplateRenders(): void {
        $output = elgg_view('dropzone/template');
        $this->assertIsString($output);
        $this->assertStringContainsString('elgg-dropzone-preview', $output);
        $this->assertStringContainsString('data-dz-thumbnail', $output);
        $this->assertStringContainsString('data-dz-name', $output);
        $this->assertStringContainsString('data-dz-remove', $output);
    }

    /**
     * @return void
     */
    public function testDropzoneInputIncludesFallbackHiddenField(): void {
        $output = elgg_view('input/dropzone', []);
        $this->assertStringContainsString('dropzone_fields[]', $output);
    }

    /**
     * @return void
     */
    public function testDropzoneInputRendersCustomQuery(): void {
        $output = elgg_view('input/dropzone', ['query' => ['foo' => 'bar']]);
        $this->assertStringContainsString('data-query', $output);
        $this->assertStringContainsString('foo', $output);
    }
}
