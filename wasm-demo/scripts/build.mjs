import { cp, mkdir, readFile, writeFile, rm, stat, readdir } from 'node:fs/promises';
import { resolve, dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawn, execFileSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import { buildBrowser } from './bundle.mjs';

const demo = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const root = resolve(demo, '..');
const work = join(demo, '.work');
const app = join(work, 'app');
const dist = join(demo, 'dist');
const image = 'portaldots-wasm-php83-poc';
export const siteBase = process.env.SITE_BASE || '/';
if (!/^\/(?:[a-zA-Z0-9_-]+\/)*$/.test(siteBase)) throw new Error('SITE_BASE must be / or /path/ with a trailing slash');
const appBase = `${siteBase}app`;
const mysql = `portaldots-wasm-mysql-${process.pid}`;
const composer = `portaldots-wasm-composer-${process.pid}`;
const phpName = `portaldots-wasm-php-${process.pid}`;
const relativeApp = '/src/wasm-demo/.work/app';
const mount = ['--mount', `type=bind,src=${root},dst=/src`];

function run(command, args, options = {}) {
  console.log('$', command, args.join(' '));
  return new Promise((resolve, reject) => {
    const child = spawn(command, args, { cwd: root, stdio: 'inherit', ...options });
    child.on('error', reject);
    child.on('exit', code => code === 0 ? resolve() : reject(new Error(`${command} exited ${code}`)));
  });
}
function php(args, env = {}) {
  const vars = Object.entries(env).flatMap(([key, value]) => ['-e', `${key}=${value}`]);
  return run('docker', ['run', '--rm', '--name', phpName, '--network', `container:${mysql}`,
    ...mount, '-w', relativeApp, ...vars, image, ...args]);
}
await mkdir(work, { recursive: true });
await mkdir(join(demo, '.scratch'), { recursive: true });
await writeFile(join(demo, '.scratch/resources.json'), JSON.stringify({
  containers: [mysql, composer, phpName], volumes: [],
  images: [image, 'php:8.3-cli', 'composer:2', 'mysql:8.4'],
  directories: [work, dist, join(demo, 'node_modules')],
}, null, 2));
await rm(app, { recursive: true, force: true });
await mkdir(app, { recursive: true });
for (const entry of ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'public',
  'lang', 'artisan', 'composer.json', 'composer.lock', 'package.json', 'yarn.lock', 'vite.config.mjs']) {
  await cp(join(root, entry), join(app, entry), { recursive: true });
}
for (const folder of ['storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache']) {
  await mkdir(join(app, folder), { recursive: true });
}
// Never package a checkout's cached configuration, sessions, views, logs, or uploads.
for (const folder of ['bootstrap/cache', 'storage']) {
  await rm(join(app, folder), { recursive: true, force: true });
}
for (const folder of ['storage/app/public', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache']) {
  await mkdir(join(app, folder), { recursive: true });
}
const keyPath = join(work, 'app-key');
let key;
try { key = (await readFile(keyPath, 'utf8')).trim(); }
catch { key = `base64:${randomBytes(32).toString('base64')}`; await writeFile(keyPath, key); }
const env = `APP_NOT_INSTALLED=false
APP_NAME=PortalDots
APP_ENV=production
APP_KEY=${key}
APP_DEBUG=false
APP_URL=http://localhost${appBase}
APP_FORCE_HTTPS=false
PORTAL_ENABLE_DEMO_MODE=true
PORTAL_DEMO_ALLOW_WRITES=true
PORTAL_ADMIN_NAME="デモ大学祭実行委員会"
PORTAL_CONTACT_EMAIL=demo@example.test
PORTAL_UNIVEMAIL_DOMAIN_PART=example.test
PORTAL_UNIVEMAIL_LOCAL_PART=student_id
PORTAL_STUDENT_ID_NAME=StudentID
PORTAL_UNIVEMAIL_NAME=UniversityEmail
PORTAL_USERS_NUMBER_TO_SUBMIT_CIRCLE=2
LOG_CHANNEL=single
DB_CONNECTION=sqlite
DB_DATABASE=/app/database/demo.sqlite
DB_FOREIGN_KEYS=true
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync
MAIL_MAILER=log
MAIL_FROM_ADDRESS=demo@example.test
MAIL_FROM_NAME=PortalDots
BROADCAST_DRIVER=log
`;
await writeFile(join(app, '.env'), env);
const frontendPackage = JSON.parse(await readFile(join(app, 'package.json'), 'utf8'));
delete frontendPackage.scripts.prepare;
await writeFile(join(app, 'package.json'), JSON.stringify(frontendPackage, null, 2));
const dockerArch = execFileSync('docker', ['info', '--format', '{{.Architecture}}'], { encoding: 'utf8' }).trim();
const nativePlatform = ['aarch64', 'arm64'].includes(dockerArch) ? 'linux/arm64' : 'linux/amd64';
await run('docker', ['pull', '--platform', nativePlatform, 'mysql:8.4']);
await run('docker', ['build', '--force-rm', '-t', image, '-f', join(demo, 'Dockerfile'), demo], {
  env: { ...process.env, DOCKER_BUILDKIT: '0' },
});
await run('docker', ['run', '--rm', '--name', composer, ...mount, '-w', relativeApp, image,
  'composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--prefer-dist', '--no-scripts']);
await run('docker', ['run', '--rm', '--name', phpName, ...mount, '-w', relativeApp, '-e', 'DB_DATABASE=:memory:', image,
  'php', 'artisan', 'package:discover']);
await run('yarn', ['install', '--frozen-lockfile', '--non-interactive', '--cache-folder', join(work, 'yarn-cache')], {
  cwd: app, env: { ...process.env, HUSKY: '0' },
});
// These source URLs are legacy absolute application links, independent of Vite's asset base.
// Adapt the staged sources before compilation; never rewrite emitted JS or CSS.
for (const [file, before, after] of [
  ['resources/views/staff/forms/editor.blade.php', "['form' => $form]", "['form' => $form, '__portalOwner' => env('PORTAL_WASM_OWNER')]"],
  ['resources/views/includes/drawer.blade.php', 'href="/"', `href="${appBase}/"`],
  ['resources/js/v2/components/AppFooter.vue', 'href="/support"', `href="${appBase}/support"`],
  ['resources/js/v2/components/MarkdownEditor.vue', 'href="/staff/markdown-guide"', `href="${appBase}/staff/markdown-guide"`],
  ['resources/js/forms_editor/components/EditorHeader.vue', '`/staff/forms/', '`' + appBase + '/staff/forms/'],
]) {
  const path = join(app, file);
  const source = await readFile(path, 'utf8');
  if (!source.includes(before)) throw new Error(`Staged URL adaptation needs review: ${file}`);
  await writeFile(path, source.replace(before, after));
}
const markdownPath = join(app, 'resources/js/v2/components/MarkdownEditor.vue');
await writeFile(markdownPath, (await readFile(markdownPath, 'utf8')).replace(
  '      no-iconfont',
  '      no-iconfont\n      no-highlight\n      no-katex\n      no-mermaid\n      no-echarts\n      no-prettier\n      no-upload-img',
));
await run('yarn', ['build'], { cwd: app, env: { ...process.env, ASSET_URL: appBase, YARN_CACHE_FOLDER: join(work, 'yarn-cache') } });
const mysqlEnv = {
  DB_CONNECTION: 'mysql', DB_HOST: '127.0.0.1', DB_DATABASE: 'portaldots',
  DB_USERNAME: 'root', DB_PASSWORD: 'wasm-build-only',
};
let started = false;
try {
  await run('docker', ['run', '-d', '--rm', '--name', mysql, '--network', 'none',
    '--tmpfs', '/var/lib/mysql:rw', '--platform', nativePlatform, '-e', 'MYSQL_ROOT_PASSWORD=wasm-build-only',
    '-e', 'MYSQL_DATABASE=portaldots', 'mysql:8.4']);
  started = true;
  let ready = false;
  for (let attempt = 0; attempt < 40; attempt++) {
    try {
      await run('docker', ['exec', mysql, 'mysql', '-uroot', '-pwasm-build-only', '-e', 'SELECT 1', 'portaldots'], { stdio: 'ignore' });
      ready = true; break;
    } catch { await new Promise(resolve => setTimeout(resolve, 3000)); }
  }
  if (!ready) throw new Error('Throwaway MySQL did not become ready in 120 seconds');
  await php(['php', 'artisan', 'migrate', '--force'], mysqlEnv);
  await php(['php', '/src/wasm-demo/scripts/seed.php'], mysqlEnv);
  await php(['php', '/src/wasm-demo/scripts/mysql-to-sqlite.php'], mysqlEnv);
  await php(['php', '/src/wasm-demo/scripts/sqlite-smoke.php'], {
    DB_CONNECTION: 'sqlite', DB_DATABASE: `${relativeApp}/database/demo.sqlite`,
  });
} finally {
  if (started) await run('docker', ['rm', '-f', '-v', mysql]);
}
// Source maps and dev files are unnecessary in the downloadable runtime.
async function removeMaps(dir) {
  for (const item of await readdir(dir, { withFileTypes: true })) {
    const path = join(dir, item.name);
    if (item.isDirectory()) await removeMaps(path);
    else if (item.name.endsWith('.map')) await rm(path);
  }
}
await removeMaps(join(app, 'public'));
for (const folder of ['storage/framework/views', 'storage/framework/sessions', 'storage/logs']) {
  await rm(join(app, folder), { recursive: true, force: true });
  await mkdir(join(app, folder), { recursive: true });
}
await rm(dist, { recursive: true, force: true });
await mkdir(dist, { recursive: true });
await run('docker', ['run', '--rm', '--name', phpName, ...mount, '-w', relativeApp, image,
  'zip', '-q', '-r', '/src/wasm-demo/dist/portaldots.zip',
  'app', 'bootstrap', 'config', 'database', 'resources/views', 'lang', 'routes', 'vendor',
  'public', 'storage', '.env', 'composer.json', 'composer.lock', '-x', '*/.gitignore', '*/.gitkeep', '*.map', '*/tests/*', '*/Tests/*', '*/docs/*', '*/.git/*', '*.md']);
await buildBrowser(siteBase);

