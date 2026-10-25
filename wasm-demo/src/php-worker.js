import { PHP, PHPRequestHandler, setPhpIniEntries } from '@php-wasm/universal';
import { loadWebRuntime } from '@php-wasm/web';
import { unzipSync } from 'fflate';

let handler;
let queue = Promise.resolve();

async function initialize(appUrl, ownerToken) {
  if (!/^[a-z0-9-]+$/i.test(ownerToken)) throw new Error('Invalid owner token');
  const php = new PHP(await loadWebRuntime('8.3'));
  await setPhpIniEntries(php, { memory_limit: '256M' });
  const response = await fetch(new URL('portaldots.zip', self.location.href));
  if (!response.ok) throw new Error(`App download failed: ${response.status}`);
  const files = unzipSync(new Uint8Array(await response.arrayBuffer()));
  php.mkdir('/app');
  for (const [name, data] of Object.entries(files)) {
    if (name.includes('..') || name.startsWith('/')) throw new Error('Invalid archive path');
    const path = `/app/${name}`;
    if (name.endsWith('/')) php.mkdir(path);
    else {
      php.mkdir(path.slice(0, path.lastIndexOf('/')));
      php.writeFile(path, data);
    }
  }
  php.chdir('/app');
  php.writeFile('/app/.env', php.readFileAsText('/app/.env')
    .replace(/^APP_URL=.*$/m, `APP_URL=${appUrl}`) + `\nASSET_URL=${appUrl}\nPORTAL_WASM_OWNER=${ownerToken}\n`);
  // Symfony derives the base URL from SCRIPT_NAME, whereas PHPRequestHandler
  // uses a document-root-relative script name. Preserve the browser app prefix.
  const script = php.readFileAsText('/app/public/index.php');
  const scriptName = `${new URL(appUrl).pathname}/index.php`;
  php.writeFile('/app/public/index.php', `<?php
$_SERVER['SCRIPT_NAME'] = ${JSON.stringify(scriptName)};
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
?>${script}`);
  const info = await php.run({ code: '<?php echo json_encode(["version" => PHP_VERSION, "extensions" => get_loaded_extensions()]);' });
  if (info.exitCode) throw new Error(info.errors || info.text);
  handler = new PHPRequestHandler({
    php, documentRoot: '/app/public', absoluteUrl: appUrl,
    getFileNotFoundAction: () => ({ type: 'internal-redirect', uri: '/index.php' }),
  });
  return JSON.parse(info.text);
}

self.onmessage = ({ data, ports }) => {
  if (data.type === 'init') {
    initialize(data.appUrl, data.ownerToken).then(info => self.postMessage({ type: 'ready', info }))
      .catch(error => self.postMessage({ type: 'failed', error: error.stack || String(error) }));
    return;
  }
  if (data.type !== 'request') return;
  const port = ports[0];
  // A single interpreter owns the SQLite file and the cookie jar. Requests must
  // finish before another starts, including simultaneous Vue editor API calls.
  queue = queue.then(async () => {
    try {
      if (!handler) throw new Error('PHP is not ready');
      const result = await handler.request(data.request);
      if (result.errors) self.postMessage({ type: 'php-error', error: result.errors });
      port.postMessage({ status: result.httpStatusCode, headers: result.headers, body: result.bytes });
    } catch (error) {
      port.postMessage({ error: error.stack || String(error) });
    } finally {
      port.close();
    }
  });
};
