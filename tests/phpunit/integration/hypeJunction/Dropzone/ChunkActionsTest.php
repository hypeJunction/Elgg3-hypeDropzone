<?php

namespace hypeJunction\Dropzone;

use Elgg\Http\ErrorResponse;
use Elgg\IntegrationTestCase;

/**
 * Runtime coverage for hypeDropzone migration fixes that only manifest on a
 * booted Elgg 7: the ESM importmap rename, the relocated HTTP exceptions on the
 * chunk-upload reject path, and the callable-as-key seeds registration.
 */
class ChunkActionsTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypedropzone';
	}

	public function up() {}

	public function down() {}

	/**
	 * f9f7d15 — Elgg 7 only registers .mjs views in the importmap, so the client
	 * module was renamed dropzone.js -> dropzone.mjs (imported via
	 * elgg_import_esm('dropzone/dropzone')) and the vendored lib to
	 * dropzone/lib.mjs. Both extensionless import targets must resolve as views.
	 */
	public function testEsmModuleViewsResolve(): void {
		$this->assertTrue(elgg_view_exists('dropzone/dropzone'), 'elgg_import_esm("dropzone/dropzone") target must exist as a .mjs view');
		$this->assertTrue(elgg_view_exists('dropzone/lib.mjs'), 'vendored dropzone lib must be registered as a .mjs view alias');
	}

	/**
	 * 82510cb — the HTTP exceptions were relocated to
	 * \Elgg\Exceptions\Http\BadRequestException / \Elgg\Exceptions\HttpException.
	 * An empty upload must hit that catch and yield an elgg_error_response, not
	 * fatal on an unresolved class or fall through to an ok response.
	 */
	public function testChunkUploadRejectsEmptyUploadWithErrorResponse(): void {
		$user = $this->createUser();
		_elgg_services()->session_manager->setLoggedInUser($user);

		$request = new \Elgg\Request(elgg(), \Elgg\Http\Request::createFromGlobals());
		$action = new ChunkUploadAction();
		$result = $action($request);

		$this->assertInstanceOf(ErrorResponse::class, $result);

		_elgg_services()->session_manager->removeLoggedInUser();
	}

	/**
	 * 6120518 — events.seeds.database was rewritten to the callable-as-key format
	 * (Seeder::class.'::addSeed' => []). Firing the seeds event must route through
	 * that handler and append Seeder::class to the seed list.
	 */
	public function testSeedsDatabaseEventAppendsSeeder(): void {
		$seeds = elgg_trigger_event_results('seeds', 'database', [], []);
		$this->assertIsArray($seeds);
		$this->assertContains(Seeder::class, $seeds);
	}

	/**
	 * 85d7e48 / 1d109b9 — chunks are transient, so Seeder::seed() is a no-op and
	 * must not create any object/file_chunk entities when the database seeder runs.
	 */
	public function testSeederSeedCreatesNoEntities(): void {
		$countBefore = elgg_call(ELGG_IGNORE_ACCESS, fn() => elgg_count_entities([
			'type' => 'object',
			'subtype' => 'file_chunk',
		]));

		(new Seeder())->seed();

		$countAfter = elgg_call(ELGG_IGNORE_ACCESS, fn() => elgg_count_entities([
			'type' => 'object',
			'subtype' => 'file_chunk',
		]));

		$this->assertSame($countBefore, $countAfter, 'Seeder::seed() must not create transient file_chunk entities');
	}
}
