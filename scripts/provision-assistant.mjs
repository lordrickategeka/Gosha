// Run only on the shared production host after reviewing the exported knowledge.
// node scripts/provision-assistant.mjs <assistant-directory> <garagehq-directory> <private-backup-directory>
import { randomBytes } from 'node:crypto';
import { readFile, writeFile, copyFile, mkdir, chmod } from 'node:fs/promises';
import { resolve, join } from 'node:path';
import { pathToFileURL } from 'node:url';
import { parseEnv } from 'node:util';
import { DatabaseSync, backup } from 'node:sqlite';

if (process.argv.length !== 5) throw new Error('Supply assistant directory, GarageHQ directory and a new private backup directory.');
const [agentDir, garageDir, backupDir] = process.argv.slice(2).map(path => resolve(path));
const agentEnvPath = join(agentDir, '.env');
const garageEnvPath = join(garageDir, '.env');
const agentText = await readFile(agentEnvPath, 'utf8');
const garageText = await readFile(garageEnvPath, 'utf8');
const env = parseEnv(agentText);
const keys = JSON.parse(env.SUPPORT_AGENT_API_KEYS_JSON || '{}');
const mappings = JSON.parse(env.SUPPORT_AGENT_API_TENANT_SLUGS_JSON || '{}');
for (const object of [keys, mappings]) {
    if (!object || typeof object !== 'object' || Array.isArray(object)) throw new Error('Invalid shared service integration mapping.');
}
if (mappings.garagehq && mappings.garagehq !== 'garagehq') throw new Error('An existing GarageHQ mapping points at another tenant; review before changing it.');
const { guides } = JSON.parse(await readFile(join(garageDir, 'storage/app/private/assistant-knowledge.json'), 'utf8'));
if (!Array.isArray(guides) || !guides.length || guides.some(guide => typeof guide.content !== 'string' || typeof guide.title !== 'string' || !/^\/user-guide#[a-z0-9-]+$/.test(guide.primaryPath))) {
    throw new Error('Exported knowledge is invalid.');
}
await mkdir(backupDir, { mode: 0o700 }); // A fresh directory prevents overwriting previous backups.
await chmod(backupDir, 0o700);
for (const [file, name] of [[agentEnvPath, 'assistant.env.before'], [garageEnvPath, 'garagehq.env.before']]) {
    await copyFile(file, join(backupDir, name));
    await chmod(join(backupDir, name), 0o600);
}
const databasePath = join(agentDir, 'data/assistant.sqlite');
const readDatabase = new DatabaseSync(databasePath, { readOnly: true });
const administrator = readDatabase.prepare('SELECT id FROM users WHERE active = 1 AND platform_admin = 1 ORDER BY id LIMIT 1').get();
if (!administrator) throw new Error('An active shared-assistant platform administrator is required.');
await backup(readDatabase, join(backupDir, 'assistant-before.sqlite'));
readDatabase.close();
await chmod(join(backupDir, 'assistant-before.sqlite'), 0o600);

const { createStore } = await import(pathToFileURL(join(agentDir, 'store.js')).href);
const store = createStore(databasePath, Date.now, { defaultModel: env.OLLAMA_MODEL });
try {
    const existingTenant = store.getTenantBySlug('garagehq');
    const tenant = existingTenant || store.createTenant({ slug: 'garagehq', name: 'GarageHQ' });
    if (!tenant.active) throw new Error('The GarageHQ tenant is inactive; review before enabling it.');
    if (!existingTenant) {
        // The live default may have been changed in the admin UI since .env was set.
        const defaultTenant = store.getTenantBySlug('default');
        if (defaultTenant) {
            store.updateTenantSettings(tenant.id, { ...store.getTenantSettings(tenant.id), model: store.getTenantSettings(defaultTenant.id).model });
        }
    }
    const source = 'GarageHQ user guide (managed import)';
    const previous = store.listKnowledge(tenant.id).filter(doc => doc.tenant_id === tenant.id && doc.source === source && !doc.is_global);
    for (const guide of guides) {
        store.addKnowledge({ title: guide.title, source, content: `${guide.content}\n\nGuide: https://garagehq.loganate.cc${guide.primaryPath}`, createdBy: administrator.id, tenantId: tenant.id, isGlobal: false });
    }
    for (const doc of previous) store.deleteKnowledge(doc.id);
    keys.garagehq = typeof keys.garagehq === 'string' && keys.garagehq.length >= 32 ? keys.garagehq : randomBytes(32).toString('hex');
    mappings.garagehq = 'garagehq';
    const update = (text, values) => {
        for (const [name, value] of Object.entries(values)) {
            const pattern = new RegExp(`^${name}=[^\\r\\n]*`, 'm');
            text = pattern.test(text) ? text.replace(pattern, () => `${name}=${value}`) : `${text.trimEnd()}\n${name}=${value}\n`;
        }
        return text;
    };
    await writeFile(agentEnvPath, update(agentText, { SUPPORT_AGENT_API_KEYS_JSON: JSON.stringify(keys), SUPPORT_AGENT_API_TENANT_SLUGS_JSON: JSON.stringify(mappings) }));
    await chmod(agentEnvPath, 0o600);
    await writeFile(garageEnvPath, update(garageText, { ASSISTANT_ENABLED: 'true', ASSISTANT_BASE_URL: 'https://cot.loganate.cc', ASSISTANT_API_KEY: keys.garagehq }));
    console.log(`GarageHQ tenant provisioned; ${guides.length} approved chapters imported privately. Integration secrets updated without displaying them.`);
    console.log('Restart only salord-system-assistant and rebuild GarageHQ configuration cache to activate.');
} finally {
    store.close();
}
