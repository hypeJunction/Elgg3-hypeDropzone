<?php

namespace hypeJunction\Dropzone;

use Elgg\Database\Seeds\Seed;

/**
 * Seeder for hypedropzone
 *
 * file_chunk entities are internal implementation details created during chunked
 * file uploads and assembled into the final file by ChunkAssembleAction. They are
 * transient by nature and should not be seeded artificially. This seeder is a
 * no-op placeholder required by the Seed subclass contract.
 */
class Seeder extends Seed {

	public static function getType(): string {
		return 'file_chunk';
	}

	public function getCountOptions(): array {
		return [
			'type' => 'object',
			'subtype' => 'file_chunk',
		];
	}

	public function seed(): void {
		// file_chunk entities are created during upload assembly, not seeded directly.
	}

	public function unseed(): void {
		// No seeded file_chunk entities to remove.
	}

	public static function addSeed(\Elgg\Event $event) {
		$seeds = $event->getValue();
		$seeds[] = self::class;
		return $seeds;
	}
}
