const loading = document.querySelector('#loading');
const frame = document.querySelector('iframe');
const intro = document.querySelector('#intro');
const help = document.querySelector('#help');
const id = crypto.randomUUID();
const base = new URL('./', location.href);
const appUrl = new URL('app', base).href;
const clientIds = new Set();
const worker = new Worker(new URL('./php-worker.js', import.meta.url), { type: 'module' });
const DEMO_PASSWORD = 'PortalDemo123!';
const INTRO_SEEN_KEY = 'portaldots-demo-intro-seen';

let resolveLoginReady;
const loginReady = new Promise(resolve => { resolveLoginReady = resolve; });

navigator.serviceWorker.addEventListener('message', event => {
  if (event.data.type === 'identify-instance') event.ports[0].postMessage({ owns: event.data.token === id || clientIds.has(event.data.clientId) });
  if (event.data.type === 'php-request') {
    for (const clientId of event.data.clientIds) clientIds.add(clientId);
    worker.postMessage({ type: 'request', request: event.data.request }, event.ports);
  }
});

function introSeen() {
  try { return localStorage.getItem(INTRO_SEEN_KEY) === '1'; } catch { return false; }
}

function closeIntro() {
  intro.close();
  try { localStorage.setItem(INTRO_SEEN_KEY, '1'); } catch { /* Private browsing may block storage. */ }
}

async function logIn(loginId) {
  closeIntro();
  await loginReady;
  let form = frame.contentDocument?.querySelector('input[name="login_id"]')?.form;
  if (!form) {
    await new Promise(resolve => {
      frame.addEventListener('load', resolve, { once: true });
      frame.src = `${appUrl}/login`;
    });
    form = frame.contentDocument?.querySelector('input[name="login_id"]')?.form;
    // Already signed in: Laravel redirects /login to the dashboard.
    if (!form) return;
  }
  form.querySelector('input[name="login_id"]').value = loginId;
  form.querySelector('input[name="password"]').value = DEMO_PASSWORD;
  form.requestSubmit();
}

intro.querySelectorAll('[data-login]').forEach(button => {
  button.addEventListener('click', () => logIn(button.dataset.login));
});
intro.querySelector('.close').addEventListener('click', closeIntro);
intro.addEventListener('cancel', closeIntro);
help.addEventListener('click', () => intro.showModal());

async function start() {
  if (!('serviceWorker' in navigator)) throw new Error('このブラウザでは動きません。最新の Chrome、Edge、Safari、Firefox で開いてください。');
  const registration = await navigator.serviceWorker.register(new URL('service-worker.js', base), { scope: base.pathname });
  const installing = registration.installing || registration.waiting;
  if (installing && installing.state !== 'activated') {
    await new Promise((resolve, reject) => installing.addEventListener('statechange', () => {
      if (installing.state === 'activated') resolve();
      if (installing.state === 'redundant') reject(new Error('起動に失敗しました。ページを再読み込みしてください。'));
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
  await new Promise((resolve, reject) => {
    worker.addEventListener('error', event => reject(new Error(event.message)), { once: true });
    worker.onmessage = event => {
      if (event.data.type === 'failed') reject(new Error(event.data.error));
      if (event.data.type === 'php-error') console.error('[PHP]', event.data.error);
      if (event.data.type === 'ready') {
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
        console.info('PortalDots login ready', window.portalDemo.loginReadyMs, 'ms');
        loading.classList.add('done');
        help.hidden = false;
        resolveLoginReady();
      }
    } catch { /* An ended session can replace the iframe with a browser error document. */ }
  });
  frame.src = `${appUrl}/login?__portalOwner=${id}`;
  frame.hidden = false;
}

if (!introSeen()) intro.showModal();
start().catch(error => {
  console.error(error);
  if (intro.open) intro.close();
  loading.classList.add('failed');
  document.querySelector('#loading-error').textContent = error.message;
});
window.addEventListener('pagehide', () => worker.terminate(), { once: true });
