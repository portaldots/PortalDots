<?php

declare(strict_types=1);

namespace PortalDots\Updater;

final class RecoveryApplication
{
    private const COOKIE = 'portaldots_updater_session';

    private StateStore $store;
    private RecoveryAuth $auth;
    private Engine $engine;
    private string $scriptPath;
    private string $homePath;

    public function __construct(private readonly Config $config)
    {
        $this->store = new StateStore($config);
        $this->auth = new RecoveryAuth($this->store);
        $this->engine = new Engine($config, $this->store);
        $this->scriptPath = '/updater.php';
        $this->homePath = '/';
    }

    public function run(): void
    {
        $this->scriptPath = (new HttpProbe($this->config))->canonicalUpdaterPath();
        $directory = rtrim(str_replace('\\', '/', dirname($this->scriptPath)), '/.');
        $this->homePath = ($directory === '' ? '' : $directory) . '/';
        header('Cache-Control: no-store, private, max-age=0');
        header('Pragma: no-cache');
        header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; "
            . "form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: no-referrer');

        $id = $this->store->activeId();
        if ($id === null) {
            $this->render(null, null, '進行中の更新処理はありません。');
            return;
        }
        $state = $this->store->load($id);
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
        $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        $token = is_string($_COOKIE[self::COOKIE] ?? null) ? $_COOKIE[self::COOKIE] : '';

        if ($method === 'POST' && $action === 'authenticate') {
            try {
                $token = $this->auth->authenticate($id, (string) ($_POST['recovery_code'] ?? ''), $userAgent);
                setcookie(self::COOKIE, $token, [
                    'expires' => time() + 86_400,
                    'path' => $this->scriptPath,
                    'secure' => $this->isHttps(),
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
                header('Location: ' . $this->scriptPath, true, 303);
                return;
            } catch (\Throwable $exception) {
                $this->render($state, null, $exception->getMessage());
                return;
            }
        }

        if (!$this->auth->authorize($state, $token, $userAgent)) {
            $this->render($state, null, null);
            return;
        }
        if ($method === 'POST' && $action === 'step') {
            $csrf = (string) ($_POST['csrf_token'] ?? '');
            if (!hash_equals($this->auth->csrf($id, $token), $csrf)) {
                http_response_code(419);
                $this->render($state, $token, '操作トークンが一致しません。ページを再読み込みしてください。');
                return;
            }
            try {
                $state = $this->engine->step($id);
            } catch (\Throwable $exception) {
                // 別タブや再読み込みで同じジョブのロックが取れない場合も、
                // 汎用の503画面ではなく進行画面を返して再試行できるようにする。
                $this->render($this->store->load($id), $token, get_class($exception) === \RuntimeException::class
                    ? $exception->getMessage() : '更新処理を実行できませんでした。再試行してください。');
                return;
            }
        }
        $this->render($state, $token, null);
    }

    /** @param array<string, mixed>|null $state */
    private function render(?array $state, ?string $token, ?string $message): void
    {
        $authorized = $state !== null && $token !== null;
        $phase = (string) ($state['phase'] ?? 'idle');
        $terminal = in_array($phase, ['completed', 'rolled_back', 'failed'], true);
        $titles = [
            'updating' => 'PortalDots を更新しています',
            'restoring' => '更新前の状態へ自動復元しています',
            'completed' => '更新が完了しました',
            'rolled_back' => '更新前の状態へ復元しました',
            'failed' => '更新を開始できませんでした',
            'idle' => 'PortalDots 更新',
        ];
        $steps = ['更新の取得', '更新前の確認', 'バックアップ', '更新', '復元', '完了'];
        $title = $titles[$phase] ?? 'PortalDots 更新';
        $current = (string) ($state['current_step'] ?? '');
        $progress = match (true) {
            $phase === 'restoring' => 4,
            $phase === 'completed', $phase === 'rolled_back' => 5,
            in_array($current, ['fetch_manifest', 'download_package'], true) => 0,
            in_array($current, ['inspect_package', 'extract_package', 'preflight',
                'enter_maintenance', 'wait_for_drain'], true) => 1,
            in_array($current, ['backup_database', 'backup_files'], true) => 2,
            in_array($current, ['apply_files', 'migrate_database', 'health_check', 'finalize'], true) => 3,
            default => 0,
        };
        $error = $authorized
            ? ($message ?? ($state['restore']['last_error'] ?? $state['last_error'] ?? null))
            : $message;
        $csrf = $authorized ? $this->auth->csrf((string) $state['id'], $token) : '';
        $h = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        ?>
<!doctype html>
<html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $h($title) ?></title>
<style>
:root{color-scheme:light dark;font-family:system-ui,-apple-system,sans-serif;background:#f2f4f7;color:#17202a}body{margin:0}
main{max-width:760px;margin:7vh auto;padding:32px;background:#fff;border-radius:14px;box-shadow:0 8px 30px #17202a1c}
h1{font-size:1.6rem;margin-top:0}.muted{color:#59636e}.alert{padding:14px;border-radius:8px;background:#fff1d6;color:#613d00;margin:18px 0}
.error{background:#fde8e8;color:#7b1a1a;white-space:pre-wrap}label{display:block;font-weight:650;margin:18px 0 7px}
input{box-sizing:border-box;width:100%;padding:12px;border:1px solid #9aa4af;border-radius:7px;font:inherit}button,a.button{display:inline-block;border:0;border-radius:7px;padding:12px 20px;background:#1769aa;color:#fff;text-decoration:none;font:inherit;cursor:pointer}
ol{padding-left:1.4rem}.current{font-weight:700;color:#1769aa}.done{color:#39734a}.spinner{display:inline-block;width:14px;height:14px;border:3px solid #1769aa44;border-top-color:#1769aa;border-radius:50%;animation:s 1s linear infinite}@keyframes s{to{transform:rotate(360deg)}}
code{overflow-wrap:anywhere}@media(prefers-color-scheme:dark){:root{background:#12171d;color:#e9eef3}main{background:#1d252d}.muted{color:#abb5bf}.alert{background:#4c3510;color:#ffe4a8}.error{background:#4d2020;color:#ffd4d4}}
</style></head><body><main>
<h1><?= $h($title) ?></h1>
<?php if ($error): ?><div class="alert error"><?= $h($error) ?></div><?php endif; ?>
<?php if ($state === null): ?><p class="muted"><?= $h($message) ?></p><a class="button" href="<?= $h($this->homePath) ?>">PortalDotsへ戻る</a>
<?php elseif (!$authorized): ?>
<p>更新開始時に保存した復旧コードを入力してください。</p>
<form method="post" action="<?= $h($this->scriptPath) ?>" autocomplete="off"><input type="hidden" name="action" value="authenticate">
<label for="recovery_code">復旧コード</label><input id="recovery_code" name="recovery_code" type="password" required autofocus>
<p><button type="submit">更新処理を開く</button></p></form>
<?php else: ?>
<p class="muted">開始版: <?= $h($state['from_version']) ?><?php if ($state['target_version']): ?> ／ 更新版: <?= $h($state['target_version']) ?><?php endif; ?></p>
<?php if (!$terminal): ?>
<ol><?php foreach ($steps as $index => $label): $isCurrent = $index === $progress; ?>
<li class="<?= $isCurrent ? 'current' : ($index < $progress ? 'done' : '') ?>"><?= $h($label) ?><?php if ($isCurrent): ?> <span class="spinner" aria-label="処理中"></span><?php endif; ?></li>
<?php endforeach; ?></ol>
<p>この画面を閉じた場合は、復旧コードを使って戻ると処理を再開できます。</p>
<form id="step-form" method="post" action="<?= $h($this->scriptPath) ?>"><input type="hidden" name="action" value="step"><input type="hidden" name="csrf_token" value="<?= $h($csrf) ?>"><button type="submit">次の処理を実行</button></form>
<?php if (!($state['restore']['paused'] ?? false)): ?><script>setTimeout(function(){document.getElementById('step-form').requestSubmit()},120)</script><?php endif; ?>
<?php else: ?>
<?php if ($phase === 'completed'): ?><p>PortalDots を再び利用できるようになりました。</p><?php endif; ?>
<?php if ($phase === 'rolled_back'): ?><p>更新前の状態に戻しました。PortalDots は更新前と同じように利用できます。表示された原因を確認してから、必要に応じてサーバーの管理者へ連絡してください。</p><?php endif; ?>
<a class="button" href="<?= $h($this->homePath) ?>">PortalDotsへ戻る</a>
<?php endif; ?>
<?php endif; ?>
</main></body></html>
<?php
    }

    private function isHttps(): bool
    {
        return strtolower((string) parse_url((string) $this->config->applicationUrl, PHP_URL_SCHEME)) === 'https';
    }
}
