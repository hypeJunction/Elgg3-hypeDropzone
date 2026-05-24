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

	/**
	 * {@inheritdoc}
	 */
	public static function getType(): string {
		return 'file_chunk';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCountOptions(): array {
		return [
			'type' => 'object',
			'subtype' => 'file_chunk',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function seed(): void {
		// file_chunk entities are created during upload assembly, not seeded directly.
	}

	/**
	 * {@inheritdoc}
	 */
	public function unseed(): void {
		// No seeded file_chunk entities to remove.
	}

	/**
	 * Register this seeder with the seeds,database event.
	 *
	 * @param \Elgg\Event $event Event
	 * @return array
	 */
	public static function addSeed(\Elgg\Event $event) {
		$seeds = $event->getValue();
		$seeds[] = self::class;
		return $seeds;
	}
}
