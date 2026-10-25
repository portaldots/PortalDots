const status = document.querySelector('#status');
const frame = document.querySelector('iframe');
const id = crypto.randomUUID();
const base = new URL('./', location.href);
const appUrl = new URL('app', base).href;
const clientIds = new Set();
const worker = new Worker(new URL('./php-worker.js', import.meta.url), { type: 'module' });

navigator.serviceWorker.addEventListener('message', event => {
  if (event.data.type === 'identify-instance') event.ports[0].postMessage({ owns: event.data.token === id || clientIds.has(event.data.clientId) });
  if (event.data.type === 'php-request') {
    for (const clientId of event.data.clientIds) clientIds.add(clientId);
    worker.postMessage({ type: 'request', request: event.data.request }, event.ports);
  }
});

async function start() {
  if (!('serviceWorker' in navigator)) throw new Error('This browser needs Service Worker support. Use localhost or HTTPS.');
  status.textContent = 'Installing request router…';
  const registration = await navigator.serviceWorker.register(new URL('service-worker.js', base), { scope: base.pathname });
  const installing = registration.installing || registration.waiting;
  if (installing && installing.state !== 'activated') {
    await new Promise((resolve, reject) => installing.addEventListener('statechange', () => {
      if (installing.state === 'activated') resolve();
      if (installing.state === 'redundant') reject(new Error('Request router installation failed'));
    }));
  }
  await navigator.serviceWorker.ready;
  await new Promise(resolve => {
    const check = () => {
      if (navigator.serviceWorker.controller === registration.active) {
        navigator.serviceWorker.removeEventListener('controllerchange', check);
        resolve();
      }
    };
    navigator.serviceWorker.addEventListener('controllerchange', check);
    registration.active.postMessage({ type: 'claim-clients' });
    check();
  });
  await new Promise(resolve => {
    const channel = new MessageChannel();
    channel.port1.onmessage = () => { channel.port1.close(); resolve(); };
    navigator.serviceWorker.controller.postMessage({ type: 'register-instance', id }, [channel.port2]);
  });
  status.textContent = 'Loading PHP 8.3 and PortalDots…';
  await new Promise((resolve, reject) => {
    worker.addEventListener('error', event => reject(new Error(event.message)), { once: true });
    worker.onmessage = event => {
      if (event.data.type === 'failed') reject(new Error(event.data.error));
      if (event.data.type === 'php-error') console.error('[PHP]', event.data.error);
      if (event.data.type === 'ready') {
        document.querySelector('#runtime').textContent = `PHP ${event.data.info.version}: ${event.data.info.extensions.join(', ')}`;
        window.portalDemo = { appUrl, runtime: event.data.info };
        resolve();
      }
    };
    worker.postMessage({ type: 'init', appUrl, ownerToken: id });
  });
  frame.addEventListener('load', () => {
    try {
      if (frame.contentWindow.location.search.includes('__portalOwner')) {
        const url = new URL(frame.contentWindow.location.href);
        url.searchParams.delete('__portalOwner');
        frame.contentWindow.history.replaceState(null, '', url);
      }
      if (frame.contentDocument.querySelector('input[name="login_id"]') && !window.portalDemo.loginReadyMs) {
        window.portalDemo.loginReadyMs = Math.round(performance.now());
        status.textContent = `Ready (${(window.portalDemo.loginReadyMs / 1000).toFixed(2)} seconds)`;
        console.info('PortalDots login ready', window.portalDemo.loginReadyMs, 'ms');
      }
    } catch { /* An ended session can replace the iframe with a browser error document. */ }
  });
  frame.src = `${appUrl}/login?__portalOwner=${id}`;
  frame.hidden = false;
}
start().catch(error => { status.textContent = error.message; console.error(error); });
window.addEventListener('pagehide', () => worker.terminate(), { once: true });
