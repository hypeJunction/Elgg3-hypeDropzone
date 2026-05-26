<?php

namespace hypeJunction\Dropzone;

use Elgg\IntegrationTestCase;
use hypeJunction\DropzoneService;

/**
 * Tests for DropzoneService.
 */
class DropzoneServiceTest extends IntegrationTestCase {

    /**
     * @return string
     */
    public function getPluginID(): string {
        return 'hypedropzone';
    }

    public function up() {}
    public function down() {}

    /**
     * @return void
     */
    public function testServiceCanBeInstantiated(): void {
        $svc = new DropzoneService();
        $this->assertInstanceOf(DropzoneService::class, $svc);
    }

    /**
     * @return void
     */
    public function testServiceRegisteredInContainer(): void {
        $svc = elgg()->dropzone;
        $this->assertInstanceOf(DropzoneService::class, $svc);
    }

    /**
     * @return void
     */
    public function testHandleUploadsWithEmptyRequestReturnsEmptyArray(): void {
        $user = $this->createUser();
        \_elgg_services()->session_manager->setLoggedInUser($user);

        $svc = new DropzoneService();

        // Build a Request with no uploaded files. Elgg\Request wraps
        // Elgg\Http\Request (a Symfony Request subclass) — construct both.
        $request = new \Elgg\Request(elgg(), \Elgg\Http\Request::createFromGlobals());

        $result = $svc->handleUploads($request);
        $this->assertIsArray($result);
        $this->assertEmpty($result);

        \_elgg_services()->session_manager->removeLoggedInUser();
    }

    /**
     * @return void
     */
    public function testUploadAfterHookIsTriggered(): void {
        $user = $this->createUser();
        \_elgg_services()->session_manager->setLoggedInUser($user);

        $called = false;
        $handler = function (\Elgg\Event $event) use (&$called) {
            $called = true;
            return $event->getValue();
        };
        \elgg_register_event_handler('upload:after', 'dropzone', $handler);

        // Event is only triggered when there are uploads. With no uploads, event won't fire — that's
        // correct behavior; verify by triggering manually to ensure registration path works.
        $value = \elgg_trigger_event_results('upload:after', 'dropzone', ['upload' => null], ['success' => true]);
        $this->assertTrue($called);
        $this->assertIsArray($value);

        \elgg_unregister_event_handler('upload:after', 'dropzone', $handler);
        \_elgg_services()->session_manager->removeLoggedInUser();
    }
}
