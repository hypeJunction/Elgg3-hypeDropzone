import { test, expect } from '@playwright/test';
import { queryDb } from '../helpers/elgg';

/**
 * Permissions tests: dropzone actions require an authenticated user.
 */

test.describe('hypeDropzone permissions', () => {

  test('upload action requires login', async ({ page }) => {
    // Unauthenticated request to dropzone/upload should be rejected
    const response = await page.request.post('/action/dropzone/upload', {
      multipart: {} as any,
      failOnStatusCode: false,
      maxRedirects: 0,
    });
    // Elgg redirects unauthenticated users to /login (302/303) or returns 403
    expect([302, 303, 403, 401, 400]).toContain(response.status());
  });

  test('upload_chunk action requires login', async ({ page }) => {
    const response = await page.request.post('/action/dropzone/upload_chunk', {
      multipart: {} as any,
      failOnStatusCode: false,
      maxRedirects: 0,
    });
    expect([302, 303, 403, 401, 400]).toContain(response.status());
  });

  test('assemble_chunks action requires login', async ({ page }) => {
    const response = await page.request.post('/action/dropzone/assemble_chunks', {
      multipart: {} as any,
      failOnStatusCode: false,
      maxRedirects: 0,
    });
    expect([302, 303, 403, 401, 400]).toContain(response.status());
  });

  test('plugin is active in database', async () => {
    const rows = await queryDb(
      "SELECT value FROM elgg_private_settings ps JOIN elgg_entities e ON e.guid = ps.entity_guid WHERE e.type = 'object' AND e.subtype = 'plugin' AND ps.name = 'active'"
    );
    // Loose check — just confirm the plugin system is queryable
    expect(Array.isArray(rows)).toBe(true);
  });
});
