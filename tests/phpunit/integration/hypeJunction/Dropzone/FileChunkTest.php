<?php

namespace hypeJunction\Dropzone;

use Elgg\IntegrationTestCase;

/**
 * Tests for FileChunk entity class mapping and behavior.
 */
class FileChunkTest extends IntegrationTestCase {

    public function getPluginID(): string {
        return 'hypedropzone';
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
        // Chunks are saved by the upload pipeline under ignore-access because
        // the receiving side may not have the same auth context as the owner.
        // The test mirrors that.
        $chunk = elgg_call(ELGG_IGNORE_ACCESS, function () use ($user) {
            $c = new FileChunk();
            $c->owner_guid = $user->guid;
            $c->container_guid = $user->guid;
            $c->access_id = ACCESS_PRIVATE;
            $c->setFilename('chunks/test-uuid/0');
            $c->open('write');
            $c->close();
            $this->assertTrue($c->save() !== false);
            return $c;
        });

        $loaded = elgg_call(ELGG_IGNORE_ACCESS, fn() => get_entity($chunk->guid));
        $this->assertInstanceOf(FileChunk::class, $loaded);
        $this->assertEquals('file_chunk', $loaded->getSubtype());

        elgg_call(ELGG_IGNORE_ACCESS, fn() => $chunk->delete());
    }

    public function testFileChunkCanSetAndRetrieveFilename(): void {
        $user = $this->createUser();
        $chunk = new FileChunk();
        $chunk->owner_guid = $user->guid;
        $chunk->setFilename('chunks/abc-123/5');
        $this->assertStringContainsString('chunks/abc-123/5', $chunk->getFilename());
    }
}
