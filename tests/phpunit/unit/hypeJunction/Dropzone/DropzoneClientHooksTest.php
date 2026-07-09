<?php

namespace hypeJunction\Dropzone;

use Elgg\UnitTestCase;

/**
 * Regression coverage for the Elgg 7.x client-side hooks migration of
 * views/default/dropzone/dropzone.mjs.
 *
 * In Elgg 7 the legacy client globals `elgg.register_hook_handler()` and
 * `elgg.trigger_hook()` were removed from the `elgg` module. Calling them at
 * module-load throws `TypeError: elgg.register_hook_handler is not a function`
 * (dropzone.mjs:274), which aborts the whole module before any dropzone is
 * wired up — breaking the comment-attachment and file uploader write surfaces
 * while the classic form-submit fallback keeps working. The replacement is the
 * dedicated `elgg/hooks` module: `hooks.register()` / `hooks.trigger()`.
 *
 * This only surfaced in the browser JS-console gate; these assertions pin the
 * migration so a static test suite catches a regression.
 */
class DropzoneClientHooksTest extends UnitTestCase {

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

	private static function dropzoneMjs(): string {
		$path = self::pluginRoot() . '/views/default/dropzone/dropzone.mjs';
		$src = file_get_contents($path);
		if ($src === false) {
			throw new \RuntimeException("unable to read {$path}");
		}
		return $src;
	}

	public function testDropzoneMjsDoesNotCallRemovedRegisterHookHandler(): void {
		$src = self::dropzoneMjs();
		$this->assertDoesNotMatchRegularExpression(
			'/\belgg\.register_hook_handler\s*\(/',
			$src,
			'elgg.register_hook_handler() was removed from the elgg client module in Elgg 7.x — it throws "is not a function" at module load. Register via elgg/hooks: hooks.register(name, type, handler).'
		);
	}

	public function testDropzoneMjsDoesNotCallRemovedTriggerHook(): void {
		$src = self::dropzoneMjs();
		$this->assertDoesNotMatchRegularExpression(
			'/\belgg\.trigger_hook\s*\(/',
			$src,
			'elgg.trigger_hook() was removed from the elgg client module in Elgg 7.x — the config-hook read and upload:success emit both throw at runtime. Emit via elgg/hooks: hooks.trigger(name, type, params, value).'
		);
	}

	public function testDropzoneMjsImportsElgg7HooksModule(): void {
		$src = self::dropzoneMjs();
		$this->assertMatchesRegularExpression(
			'/import\s+\w+\s+from\s+[\'"]elgg\/hooks[\'"]/',
			$src,
			'dropzone.mjs must import the Elgg 7 elgg/hooks module to register and trigger client-side hooks.'
		);
	}

	public function testDropzoneMjsRegistersAndTriggersViaHooksModule(): void {
		$src = self::dropzoneMjs();

		// Resolve the local binding the elgg/hooks default import was assigned to,
		// so the assertions follow the actual import name rather than assuming "hooks".
		$this->assertSame(
			1,
			preg_match('/import\s+(\w+)\s+from\s+[\'"]elgg\/hooks[\'"]/', $src, $m),
			'expected a default import from elgg/hooks'
		);
		$binding = preg_quote($m[1], '/');

		$this->assertMatchesRegularExpression(
			'/\b' . $binding . '\.register\s*\(/',
			$src,
			'the config handler must be registered with hooks.register() (Elgg 7 replacement for elgg.register_hook_handler).'
		);
		$this->assertMatchesRegularExpression(
			'/\b' . $binding . '\.trigger\s*\(/',
			$src,
			'the config/upload:success hooks must be emitted with hooks.trigger() (Elgg 7 replacement for elgg.trigger_hook).'
		);
	}
}
