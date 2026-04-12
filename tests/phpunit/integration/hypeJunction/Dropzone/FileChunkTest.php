<?php

namespace hypeJunction\Dropzone;

use Elgg\IntegrationTestCase;

/**
 * Tests for FileChunk entity class mapping and behavior.
 */
class FileChunkTest extends IntegrationTestCase {

    public function getPluginID(): string {
        return 'hypeDropzone';
    }

    public function up() {}
    public function down() {}

    public function testFileChunkSubtypeConstant(): void {
        $this->assertEquals('file_chunk', FileChunk::SUBTYPE);
    }

    public function testFileChunkExtendsElggFile(): void {
        $chunk = new FileChunk();
        $this->assertInstanceOf(\ElggFile::class, $chunk);
    }

    public function testFileChunkInitializedWithCorrectSubtype(): void {
        $chunk = new FileChunk();
        $this->assertEquals('file_chunk', $chunk->getSubtype());
    }

    public function testFileChunkClassMappedViaElggPlugin(): void {
        $class = elgg_get_entity_class('object', 'file_chunk');
        $this->assertEquals(FileChunk::class, $class);
    }

    public function testFileChunkPersists(): void {
        $user = $this->createUser();
        $chunk = new FileChunk();
        $chunk->owner_guid = $user->guid;
        $chunk->container_guid = $user->guid;
        $chunk->access_id = ACCESS_PRIVATE;
        $chunk->setFilename('chunks/test-uuid/0');
        $chunk->open('write');
        $chunk->close();
        $this->assertTrue($chunk->save() !== false);

        $loaded = get_entity($chunk->guid);
        $this->assertInstanceOf(FileChunk::class, $loaded);
        $this->assertEquals('file_chunk', $loaded->getSubtype());

        $chunk->delete();
    }

    public function testFileChunkCanSetAndRetrieveFilename(): void {
        $user = $this->createUser();
        $chunk = new FileChunk();
        $chunk->owner_guid = $user->guid;
        $chunk->setFilename('chunks/abc-123/5');
        $this->assertStringContainsString('chunks/abc-123/5', $chunk->getFilename());
    }
}
