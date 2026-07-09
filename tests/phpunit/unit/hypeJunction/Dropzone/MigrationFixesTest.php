<?php

namespace hypeJunction\Dropzone;

use Elgg\UnitTestCase;

/**
 * Source-level guards for hypeDropzone migration fixes that have no runtime
 * surface (they are about how files are shaped on disk). Each test pins a
 * specific fixed commit so a regression re-opens exactly one bug.
 */
class MigrationFixesTest extends UnitTestCase {

	public function up() {}

	public function down() {}

	private static function pluginRoot(): string {
		$dir = __DIR__;
		for ($i = 0; $i < 8; $i++) {
			if (is_file($dir . '/elgg-plugin.php') && is_file($dir . '/composer.json')) {
				return $dir;
			}
			$dir = dirname($dir);
		}
		throw new \RuntimeException('plugin root not found');
	}

	/**
	 * df69627 — the composer.json "version" field shadowed the git tag at
	 * install time and had to be dropped. Composer must derive the version from
	 * the tag alone.
	 */
	public function testComposerJsonHasNoVersionField(): void {
		$composer = json_decode(file_get_contents(self::pluginRoot() . '/composer.json'), true);
		$this->assertIsArray($composer);
		$this->assertArrayNotHasKey('version', $composer, 'composer.json "version" shadows the git tag — drop it');
	}

	/**
	 * 94d9e35 — Elgg 6.x removed _elgg_rmdir(); ChunkAssembleAction must clean
	 * the reassembled chunk directory via elgg_delete_directory() instead. Assert
	 * the fatal-on-7.x symbol is gone AND the replacement is actually wired in.
	 */
	public function testChunkAssembleUsesElggDeleteDirectory(): void {
		$src = file_get_contents(self::pluginRoot() . '/classes/hypeJunction/Dropzone/ChunkAssembleAction.php');
		$this->assertDoesNotMatchRegularExpression('/(?<![\w>$:\\\\])_elgg_rmdir\s*\(/', $src, '_elgg_rmdir() was removed in Elgg 6.x');
		$this->assertMatchesRegularExpression('/(?<![\w>$:\\\\])elgg_delete_directory\s*\(/', $src, 'assemble path must clean the chunk dir via elgg_delete_directory()');
	}

	/**
	 * 0d9d5c4 — the dropzone library JS was vendored into vendors/ (asset-packagist
	 * repo dropped). The dropzone/lib.mjs view alias in elgg-plugin.php must point
	 * at a file that really exists on disk, else the importmap alias 404s.
	 */
	public function testVendoredDropzoneLibExistsOnDisk(): void {
		$root = self::pluginRoot();
		$manifest = require $root . '/elgg-plugin.php';
		$target = $manifest['views']['default']['dropzone/lib.mjs'] ?? null;
		$this->assertNotNull($target, 'dropzone/lib.mjs view alias missing from elgg-plugin.php');
		$this->assertFileExists($target, 'dropzone/lib.mjs alias points at a non-existent vendored file');
	}
}
