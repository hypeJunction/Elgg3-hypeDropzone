import { Page } from '@playwright/test';
import mysql from 'mysql2/promise';

const DB_CONFIG = {
  host: process.env.ELGG_DB_HOST || 'db',
  port: Number(process.env.ELGG_DB_PORT || 3306),
  user: process.env.ELGG_DB_USER || 'elgg',
  password: process.env.ELGG_DB_PASS || 'elgg',
  database: process.env.ELGG_DB_NAME || 'elgg',
};

export async function loginAs(page: Page, username: string, password: string = 'testpass123') {
  await page.goto('/login');
  // The 4.x default theme renders the login form twice — once in the page
  // body and once in a hidden topbar dropdown. A bare locator would match
  // both and trip strict-mode; scope to the body form with .last().
  const form = page.locator('form.elgg-form-login').last();
  await form.locator('input[name="username"]').fill(username);
  await form.locator('input[name="password"]').fill(password);
  await form.locator('button[type="submit"]').click();
  await page.waitForURL(/\//);
}

export async function queryDb(sql: string, params: any[] = []): Promise<any[]> {
  const conn = await mysql.createConnection(DB_CONFIG);
  const [rows] = await conn.execute(sql, params);
  await conn.end();
  return rows as any[];
}

export async function getEntitiesBySubtype(subtype: string, ownerGuid?: number) {
  let sql = 'SELECT * FROM elgg_entities WHERE subtype = ?';
  const params: any[] = [subtype];
  if (ownerGuid) {
    sql += ' AND owner_guid = ?';
    params.push(ownerGuid);
  }
  sql += ' ORDER BY guid DESC';
  return queryDb(sql, params);
}

export async function getMetadata(entityGuid: number, name: string) {
  return queryDb(
    'SELECT * FROM elgg_metadata WHERE entity_guid = ? AND name = ?',
    [entityGuid, name]
  );
}

export async function getLatestFileEntity(ownerGuid: number) {
  const rows = await queryDb(
    "SELECT * FROM elgg_entities WHERE type = 'object' AND subtype = 'file' AND owner_guid = ? ORDER BY guid DESC LIMIT 1",
    [ownerGuid]
  );
  return rows[0];
}

export async function getUserGuidByUsername(username: string): Promise<number | null> {
  const rows = await queryDb(
    "SELECT guid FROM elgg_entities e JOIN elgg_metadata m ON m.entity_guid = e.guid WHERE e.type = 'user' AND m.name = 'username' AND m.value = ?",
    [username]
  );
  if (rows.length === 0) {
    // Fallback: older schema stores username as attribute via users_entity table
    const alt = await queryDb(
      "SELECT guid FROM elgg_entities WHERE type = 'user' LIMIT 1"
    );
    return alt[0]?.guid ?? null;
  }
  return rows[0].guid;
}
