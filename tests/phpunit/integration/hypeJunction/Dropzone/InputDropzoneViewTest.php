<?php

namespace hypeJunction\Dropzone;

use Elgg\IntegrationTestCase;

/**
 * Tests that the input/dropzone view renders without errors and produces expected markup.
 */
class InputDropzoneViewTest extends IntegrationTestCase {

    public function getPluginID(): string {
        return 'hypedropzone';
    }

    public function up() {}
    public function down() {}

    public function testDropzoneInputRendersDefaults(): void {
        $output = elgg_view('input/dropzone', []);
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('elgg-dropzone', $output);
        $this->assertStringContainsString('elgg-input-dropzone', $output);
    }

    public function testDropzoneInputRendersWithContainerGuid(): void {
        $output = elgg_view('input/dropzone', ['container_guid' => 42]);
        $this->assertStringContainsString('data-container-guid="42"', $output);
    }

    public function testDropzoneInputRendersWithSubtype(): void {
        $output = elgg_view('input/dropzone', ['subtype' => 'file']);
        $this->assertStringContainsString('data-subtype="file"', $output);
    }

    public function testDropzoneInputRendersWithMaxFiles(): void {
        $output = elgg_view('input/dropzone', ['max' => 5]);
        $this->assertStringContainsString('data-max-files="5"', $output);
    }

    public function testDropzoneInputRendersWithAcceptedTypes(): void {
        $output = elgg_view('input/dropzone', ['accept' => 'image/*']);
        $this->assertStringContainsString('data-accepted-files="image/*"', $output);
    }

    public function testDropzoneInputMultipleAppendsBracketsToName(): void {
        $output = elgg_view('input/dropzone', ['multiple' => true, 'name' => 'my_files']);
        $this->assertStringContainsString('data-name="my_files[]"', $output);
    }

    public function testDropzoneInputDefaultNameIsFileGuids(): void {
        $output = elgg_view('input/dropzone', []);
        $this->assertStringContainsString('data-name="file_guids"', $output);
    }

    public function testDropzoneTemplateRenders(): void {
        $output = elgg_view('dropzone/template');
        $this->assertIsString($output);
        $this->assertStringContainsString('elgg-dropzone-preview', $output);
        $this->assertStringContainsString('data-dz-thumbnail', $output);
        $this->assertStringContainsString('data-dz-name', $output);
        $this->assertStringContainsString('data-dz-remove', $output);
    }

    public function testDropzoneInputIncludesFallbackHiddenField(): void {
        $output = elgg_view('input/dropzone', []);
        $this->assertStringContainsString('dropzone_fields[]', $output);
    }

    public function testDropzoneInputRendersCustomQuery(): void {
        $output = elgg_view('input/dropzone', ['query' => ['foo' => 'bar']]);
        $this->assertStringContainsString('data-query', $output);
        $this->assertStringContainsString('foo', $output);
    }
}
