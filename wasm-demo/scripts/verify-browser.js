async (page) => {
  const requests = [];
  const errors = [];
  const requestIds = new Map();
  const context = page.context();
  const onRequest = request => {
    requestIds.set(request, requests.length);
    requests.push({ method: request.method(), url: request.url(), status: null });
  };
  const onResponse = response => {
    const index = requestIds.get(response.request());
    if (index !== undefined) requests[index].status = response.status();
  };
  const onFailure = request => {
    const index = requestIds.get(request);
    if (index !== undefined) requests[index].failure = request.failure()?.errorText;
  };
  const onConsole = message => {
    if (message.type() === 'error') errors.push(message.text());
  };
  const onError = error => errors.push(error.message);
  context.on('request', onRequest);
  context.on('response', onResponse);
  context.on('requestfailed', onFailure);
  page.on('console', onConsole);
  page.on('pageerror', onError);
  const evidence = 'wasm-demo/dist/verification';
  const capture = name => page.screenshot({ path: `${evidence}/${name}.png`, fullPage: true });
  const results = [];
  try {
    const landing = page.url().startsWith('http://localhost:') ? page.url() : 'http://localhost:8787/';
    await page.goto(landing);
    const f = page.frameLocator('iframe');
    await f.locator('input[name=login_id]').waitFor({ timeout: 30000 });
    const runtime = await page.evaluate(() => window.portalDemo);
    const brave = await page.evaluate(async () => !!navigator.brave && await navigator.brave.isBrave());
    if (!brave) throw new Error('Verification requires Brave');
    await capture('01-login');
    const intro = page.getByRole('button', { name: '自分で入力する', exact: true });
    if (await intro.isVisible()) await intro.click();
    await f.locator('input[name=login_id]').fill('admin@example.test');
    await f.locator('input[name=password]').fill('PortalDemo123!');
    await f.getByRole('button', { name: 'ログイン', exact: true }).click();
    await f.getByRole('link', { name: '軽音楽部ライブ', exact: true }).click();
    await f.getByRole('heading', { name: '対応が必要なもの', exact: true }).waitFor();
    await capture('02-dashboard');
    results.push({ step: 'admin login and dashboard', passed: true });
    await f.getByRole('link', { name: 'スタッフモードへ', exact: true }).click();
    await f.getByRole('link', { name: 'スタッフモード ホーム', exact: true }).waitFor();
    await capture('03-staff');
    results.push({ step: 'staff dashboard', passed: true });
    for (const [link, expected, file] of [
      ['申請管理', '電源使用申請', '04-forms'],
      ['企画情報管理', '軽音楽部ライブ', '05-circles'],
      ['ユーザー情報管理', 'admin@example.test', '06-users'],
      ['お知らせ管理', '搬入スケジュールのお知らせ', '07-pages'],
    ]) {
      await f.getByRole('link', { name: link, exact: true }).click();
      if (link === '企画情報管理') await f.getByRole('link', { name: 'すべての企画を表示', exact: true }).click();
      await f.getByText(expected, { exact: true }).first().waitFor();
      await capture(file);
      results.push({ step: file, passed: true });
    }
    await f.getByRole('link', { name: '申請管理', exact: true }).click();
    await f.getByRole('link', { name: '新規フォーム', exact: false }).click();
    await f.locator('input[name=name]').fill('Browser acceptance form');
    await f.locator('input[name=open_at]').fill('2026-10-24T10:00');
    await f.locator('input[name=close_at]').fill('2027-10-25T10:00');
    await f.getByRole('button', { name: '保存', exact: true }).click();
    const e = f.frameLocator('iframe');
    await e.getByText('Browser acceptance form', { exact: true }).waitFor();
    results.push({ step: 'create form and open editor', passed: true });
    const added = page.waitForResponse(response => response.url().endsWith('/api/add_question'));
    await e.getByRole('button', { name: '一行入力', exact: true }).click();
    if ((await added).status() !== 200) throw new Error('Question creation failed');
    const updated = page.waitForResponse(response => response.url().endsWith('/api/update_question'));
    await e.getByRole('textbox', { name: '設問名', exact: true }).fill('Question survives editor reload');
    await e.getByRole('textbox', { name: '設問名', exact: true }).blur();
    if ((await updated).status() !== 200) throw new Error('Question save failed');
    await e.getByText('保存しました', { exact: true }).waitFor();
    await capture('08-editor-saved');
    const appFrame = page.frames().find(frame => /\/editor$/.test(frame.url()));
    await Promise.all([
      appFrame.waitForNavigation({ waitUntil: 'load' }),
      appFrame.evaluate(() => location.reload()),
    ]);
    await f.frameLocator('iframe').getByText('Question survives editor reload', { exact: true }).waitFor();
    await capture('09-editor-reloaded');
    results.push({ step: 'question survives editor reload', passed: true });
    const origin = new URL(landing).origin;
    const external = requests.filter(request => new URL(request.url).origin !== origin);
    const failures = requests.filter(request => request.status >= 400 || request.failure || request.status === null);
    if (errors.length || external.length || failures.length) {
      throw new Error(JSON.stringify({ errors, external, failures }));
    }
    return { verifiedAt: new Date().toISOString(), brave, runtime, results, errors, external, requests };
  } finally {
    context.off('request', onRequest);
    context.off('response', onResponse);
    context.off('requestfailed', onFailure);
    page.off('console', onConsole);
    page.off('pageerror', onError);
  }
}
