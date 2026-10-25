import { cp, readFile, writeFile, stat, readdir } from 'node:fs/promises';
import { resolve, dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { build } from 'esbuild';
const demo = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const dist = join(demo, 'dist');
const work = join(demo, '.work');
export async function buildBrowser(siteBase = process.env.SITE_BASE || '/') {
  const only83 = {
    name: 'only-php83',
    setup(builder) {
      builder.onResolve({ filter: /^@php-wasm\/web-\d/ }, args => args.path === '@php-wasm/web-8-3' ? undefined : ({ path: 'unsupported-php', namespace: 'empty' }));
      builder.onLoad({ filter: /.*/, namespace: 'empty' }, () => ({
        contents: 'export function getPHPLoaderModule(){throw new Error("Only PHP 8.3 is packaged")}; export function getIntlExtensionPath(){throw new Error("intl is not packaged")}',
      }));
      builder.onResolve({ filter: /\.(so|dat|la)(\?url)?$/ }, () => ({ path: 'unused-extension', namespace: 'asset' }));
      builder.onLoad({ filter: /.*/, namespace: 'asset' }, () => ({ contents: 'export default ""' }));
    },
  };
  await build({
    entryPoints: [join(demo, 'src/php-worker.js'), join(demo, 'src/landing.js')],
    outdir: dist, bundle: true, format: 'esm', splitting: true, platform: 'browser',
    external: ['worker_threads'], loader: { '.wasm': 'file' }, assetNames: 'assets/[name]-[hash]', plugins: [only83],
    define: { 'process.env.NODE_ENV': '"production"' },
  });
  await cp(join(demo, 'src/service-worker.js'), join(dist, 'service-worker.js'));
  await cp(join(demo, 'index.html'), join(dist, 'index.html'));
  const wasm = (await readdir(join(dist, 'assets'))).filter(name => name.endsWith('.wasm'));
  const sizes = Object.fromEntries(await Promise.all(wasm.map(async name => [name, (await stat(join(dist, 'assets', name))).size])));
  const manifest = {
    php: '8.3', siteBase, phpWasm: JSON.parse(await readFile(join(demo, 'node_modules/@php-wasm/web/package.json'))).version,
    archiveBytes: (await stat(join(dist, 'portaldots.zip'))).size, wasmBytes: sizes,
    conversion: JSON.parse(await readFile(join(work, 'conversion.json'))),
  };
  await writeFile(join(dist, 'build-info.json'), JSON.stringify(manifest, null, 2));
  console.log(JSON.stringify(manifest, null, 2));
}

if (process.argv[1] === fileURLToPath(import.meta.url)) await buildBrowser();
