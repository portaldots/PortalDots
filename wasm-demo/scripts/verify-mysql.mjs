import { cp, mkdir, rm, writeFile } from 'node:fs/promises';
import { resolve, dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawn, execFileSync } from 'node:child_process';

const demo = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const root = resolve(demo, '..');
const app = join(demo, '.work/mysql-tests');
const mysql = `portaldots-wasm-test-mysql-${process.pid}`;
const runner = `portaldots-wasm-tests-${process.pid}`;
const mount = ['--mount', `type=bind,src=${root},dst=/src`];
let active;
const deadline = Date.now() + 600000;
function run(command, args, stdio = 'inherit') {
  const remaining = deadline - Date.now();
  if (remaining <= 0) throw new Error('MySQL verification exceeded ten-minute budget');
  console.log('$', command, args.join(' '));
  return new Promise((resolve, reject) => {
    active = spawn(command, args, { cwd: root, stdio });
    const timer = setTimeout(() => { active.kill('SIGTERM'); reject(new Error('MySQL verification exceeded ten-minute budget')); }, remaining);
    active.on('error', error => { clearTimeout(timer); reject(error); });
    active.on('exit', code => { clearTimeout(timer); code === 0 ? resolve() : reject(new Error(`${command} exited ${code}`)); });
  });
}
await mkdir(app, { recursive: true });
for (const entry of ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'public', 'storage', 'lang', 'artisan', 'composer.json', 'composer.lock']) {
  await cp(join(demo, '.work/app', entry), join(app, entry), { recursive: true });
}
for (const entry of ['tests', 'phpunit.xml', '.env.testing']) await cp(join(root, entry), join(app, entry), { recursive: true });
await writeFile(join(demo, '.scratch/test-resources.json'), JSON.stringify({ containers: [mysql, runner], volumes: [] }));
let started = false;
try {
  await run('docker', ['run', '--rm', '--name', runner, ...mount, '-w', '/src/wasm-demo/.work/mysql-tests',
    'portaldots-wasm-php83-poc', 'composer', 'install', '--no-scripts', '--no-interaction', '--prefer-dist']);
  // A fresh manifest must not retain the production build's no-dev discovery list.
  await rm(join(app, 'bootstrap/cache/packages.php'), { force: true });
  await rm(join(app, 'bootstrap/cache/services.php'), { force: true });
  await run('docker', ['run', '-d', '--rm', '--name', mysql, '--network', 'none', '--tmpfs', '/var/lib/mysql:rw',
    '-e', 'MYSQL_ROOT_PASSWORD=wasm-build-only', '-e', 'MYSQL_DATABASE=portaldots_test', 'mysql:8.4']);
  started = true;
  let ready = false;
  for (let i = 0; i < 40; i++) {
    try {
      await run('docker', ['exec', mysql, 'mysql', '-uroot', '-pwasm-build-only', '-e', 'SELECT 1', 'portaldots_test'], 'ignore');
      ready = true; break;
    } catch { await new Promise(resolve => setTimeout(resolve, 3000)); }
  }
  if (!ready) throw new Error('Test MySQL did not become ready');
  await run('docker', ['run', '--rm', '--name', runner, '--network', `container:${mysql}`, ...mount,
    '-w', '/src/wasm-demo/.work/mysql-tests',
    '-e', 'DB_CONNECTION=mysql', '-e', 'DB_HOST=127.0.0.1', '-e', 'DB_DATABASE=portaldots_test',
    '-e', 'DB_USERNAME=root', '-e', 'DB_PASSWORD=wasm-build-only',
    '-e', 'PORTAL_ENABLE_DEMO_MODE=false', '-e', 'PORTAL_DEMO_ALLOW_WRITES=false',
    'portaldots-wasm-php83-poc', 'php', 'vendor/bin/phpunit',
    'tests/Feature/GridMakers',
    'tests/Feature/Services/Pages/PagesServiceTest.php',
    'tests/Feature/Http/Controllers/Pages/IndexActionTest.php',
    'tests/Feature/Http/Middleware/DemoModeTest.php',
    'tests/Feature/Http/Controllers/Staff/Forms/Editor',
    'tests/Feature/Services/Forms/FormsServiceTest.php']);
} finally {
  // Cleanup must also run after a suite timeout.
  for (const name of [runner, ...(started ? [mysql] : [])]) {
    const exists = execFileSync('docker', ['ps', '-aq', '--filter', `name=^/${name}$`], { encoding: 'utf8' }).trim();
    if (exists) await new Promise((resolve, reject) => {
      const process = spawn('docker', ['rm', '-f', '-v', name], { stdio: 'inherit' });
      process.on('exit', code => code === 0 ? resolve() : reject(new Error(`Cleanup failed for ${name}`)));
    });
  }
}
