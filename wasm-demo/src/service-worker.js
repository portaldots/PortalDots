self.addEventListener('install', event => event.waitUntil(self.skipWaiting()));
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));

const base = new URL('./', self.location.href).pathname;
const appRoot = `${base}app`;
const appPrefix = `${appRoot}/`;
const owners = new Map();
const clientsToOwners = new Map();
self.addEventListener('message', event => {
  if (event.data.type === 'claim-clients') {
    event.waitUntil(self.clients.claim());
    return;
  }
  if (event.data.type === 'register-instance' && event.source) {
    owners.set(event.data.id, event.source.id);
    event.ports[0]?.postMessage({ registered: true });
  }
});
async function findOwner(clientId, token) {
  const known = token ? owners.get(token) : clientsToOwners.get(clientId);
  if (known) {
    const client = await self.clients.get(known);
    if (client) return client;
  }
  // A restarted SW can recover ownership from the page, which retains the IDs
  // of requests it served. Never select an arbitrary live instance.
  const windows = await self.clients.matchAll({ type: 'window' });
  const candidates = windows.filter(client => {
    const url = new URL(client.url);
    return url.pathname.startsWith(base) && url.pathname !== appRoot && !url.pathname.startsWith(appPrefix);
  });
  const matches = await Promise.all(candidates.map(client => new Promise(resolve => {
    const channel = new MessageChannel();
    const timer = setTimeout(() => { channel.port1.close(); resolve(null); }, 1500);
    channel.port1.onmessage = event => {
      clearTimeout(timer); channel.port1.close(); resolve(event.data.owns ? client : null);
    };
    client.postMessage({ type: 'identify-instance', clientId, token }, [channel.port2]);
  })));
  const owner = matches.find(Boolean);
  // Some browsers leave clientId empty on iframe navigations. With a single
  // landing page open, it is the only instance that can own the request.
  if (!owner && !clientId && !token && candidates.length === 1) return candidates[0];
  return owner;
}
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  if (url.origin !== self.location.origin || (url.pathname !== appRoot && !url.pathname.startsWith(appPrefix))) return;
  event.respondWith(forward(event));
});

async function forward(event) {
  const request = event.request;
  try {
    const url = new URL(request.url);
    if (url.pathname === appRoot) url.pathname += '/';
    const token = url.searchParams.get('__portalOwner');
    url.searchParams.delete('__portalOwner');
    const clientId = event.clientId || event.replacesClientId;
    const owner = await findOwner(clientId, token);
    if (!owner) return new Response('このデモは終了しています。デモのトップページを開き直してください。', { status: 503, headers: { 'Content-Type': 'text/plain; charset=utf-8' } });
    const clientIds = [clientId, event.resultingClientId].filter(Boolean);
    for (const id of clientIds) clientsToOwners.set(id, owner.id);
    const headers = Object.fromEntries(request.headers);
    // PHPRequestHandler keeps a private cookie jar for each PHP Worker.
    delete headers.cookie;
    const phpRequest = { url: url.href, method: request.method, headers };
    if (request.method !== 'GET' && request.method !== 'HEAD') {
      phpRequest.body = new Uint8Array(await request.arrayBuffer());
    }
    const result = await new Promise((resolve, reject) => {
      const channel = new MessageChannel();
      const timeout = setTimeout(() => { channel.port1.close(); reject(new Error('PHP request timed out')); }, 60000);
      channel.port1.onmessage = event => {
        clearTimeout(timeout); channel.port1.close(); resolve(event.data);
      };
      owner.postMessage({ type: 'php-request', request: phpRequest, clientIds }, [channel.port2]);
    });
    if (result.error) throw new Error(result.error);
    const responseHeaders = new Headers();
    for (const [key, values] of Object.entries(result.headers)) {
      if (['set-cookie', 'content-length', 'transfer-encoding', 'content-encoding'].includes(key.toLowerCase())) continue;
      for (const value of values) responseHeaders.append(key, value);
    }
    responseHeaders.set('Cache-Control', 'no-store');
    responseHeaders.set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; connect-src 'self'; form-action 'self'; frame-src 'self' blob:");
    return new Response(request.method === 'HEAD' || [204, 205, 304].includes(result.status) ? null : result.body, {
      status: result.status, headers: responseHeaders,
    });
  } catch (error) {
    console.error(error);
    return new Response('エラーが発生しました。デモのトップページを開き直してください。', {
      status: 502, headers: { 'Content-Type': 'text/plain; charset=utf-8' },
    });
  }
}
