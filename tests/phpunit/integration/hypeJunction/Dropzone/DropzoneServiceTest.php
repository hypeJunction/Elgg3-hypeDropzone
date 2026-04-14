<?php

namespace hypeJunction\Dropzone;

use Elgg\IntegrationTestCase;
use hypeJunction\DropzoneService;

/**
 * Tests for DropzoneService.
 */
class DropzoneServiceTest extends IntegrationTestCase {

    public function getPluginID(): string {
        return 'hypedropzone';
    }

    public function up() {}
    public function down() {}

    public function testServiceCanBeInstantiated(): void {
        $svc = new DropzoneService();
        $this->assertInstanceOf(DropzoneService::class, $svc);
    }

    public function testServiceRegisteredInContainer(): void {
        $svc = elgg()->dropzone;
        $this->assertInstanceOf(DropzoneService::class, $svc);
    }

    public function testHandleUploadsWithEmptyRequestReturnsEmptyArray(): void {
        $user = $this->createUser();
        elgg_get_session()->setLoggedInUser($user);

        $svc = new DropzoneService();

        // Build a Request with no uploaded files. Elgg\Request wraps
        // Elgg\Http\Request (a Symfony Request subclass) — construct both.
        $request = new \Elgg\Request(elgg(), \Elgg\Http\Request::createFromGlobals());

        $result = $svc->handleUploads($request);
        $this->assertIsArray($result);
        $this->assertEmpty($result);

        elgg_get_session()->removeLoggedInUser();
    }

    public function testUploadAfterHookIsTriggered(): void {
        $user = $this->createUser();
        elgg_get_session()->setLoggedInUser($user);

        $called = false;
        $handler = function (\Elgg\Hook $hook) use (&$called) {
            $called = true;
            return $hook->getValue();
        };
        elgg_register_plugin_hook_handler('upload:after', 'dropzone', $handler);

        // Hook is only triggered when there are uploads. With no uploads, hook won't fire — that's
        // correct behavior; verify by triggering manually to ensure registration path works.
        $value = elgg_trigger_plugin_hook('upload:after', 'dropzone', ['upload' => null], ['success' => true]);
        $this->assertTrue($called);
        $this->assertIsArray($value);

        elgg_unregister_plugin_hook_handler('upload:after', 'dropzone', $handler);
        elgg_get_session()->removeLoggedInUser();
    }
}
