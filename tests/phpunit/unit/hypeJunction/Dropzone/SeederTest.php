<?php

namespace hypeJunction\Dropzone;

use Elgg\UnitTestCase;

/**
 * Pure-logic coverage for the Seeder subclass added to satisfy the Elgg 6.1+
 * Seed contract (migration fixes 85d7e48 / 1d109b9). getType()/getCountOptions()
 * are the two abstract members that fatal on every page load when missing.
 */
class SeederTest extends UnitTestCase {

	public function up() {}

	public function down() {}

	public function testGetTypeReturnsFileChunk(): void {
		// Static abstract member of \Elgg\Database\Seeds\Seed — must resolve to
		// the plugin's transient subtype, not 'object' or an empty string.
		$this->assertSame('file_chunk', Seeder::getType());
	}

	public function testGetCountOptionsTargetsFileChunkObjects(): void {
		$seeder = new Seeder();
		$this->assertSame([
			'type' => 'object',
			'subtype' => 'file_chunk',
		], $seeder->getCountOptions());
	}
}
