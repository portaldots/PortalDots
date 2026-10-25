<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class RecoveryAuth
{
    public function __construct(private readonly StateStore $store)
    {
    }

    public function authenticate(string $id, string $code, string $userAgent): string
    {
        $lock = $this->store->lock($id);
        try {
            $state = $this->store->load($id);
            $lockedUntil = isset($state['auth_locked_until']) && is_string($state['auth_locked_until'])
                ? strtotime($state['auth_locked_until']) : false;
            if ($lockedUntil !== false && $lockedUntil > time()) {
                throw new RuntimeException('復旧コードの試行回数が上限に達しました。時間をおいて再試行してください。');
            }
            $hash = $state['recovery_hash'] ?? null;
            if (!is_string($hash) || !password_verify(strtoupper(trim($code)), $hash)) {
                $state['auth_failures'] = (int) ($state['auth_failures'] ?? 0) + 1;
                if ($state['auth_failures'] >= 5) {
                    $state['auth_locked_until'] = gmdate(DATE_ATOM, time() + 900);
                    $state['auth_failures'] = 0;
                }
                $this->store->save($state);
                $this->store->appendAudit($id, 'authentication_failed');
                throw new RuntimeException('復旧コードが一致しません。');
            }
            $token = bin2hex(random_bytes(32));
            $state['sessions'] = [[
                'hash' => hash('sha256', $token),
                'user_agent_hash' => hash('sha256', $userAgent),
                'expires_at' => gmdate(DATE_ATOM, time() + 86_400),
            ]];
            $state['auth_failures'] = 0;
            $state['auth_locked_until'] = null;
            $this->store->save($state);
            $this->store->appendAudit($id, 'authenticated');
            return $token;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /** @param array<string, mixed> $state */
    public function authorize(array $state, string $token, string $userAgent): bool
    {
        if ($token === '') {
            return false;
        }
        $tokenHash = hash('sha256', $token);
        $userAgentHash = hash('sha256', $userAgent);
        foreach (($state['sessions'] ?? []) as $session) {
            if (is_array($session) && strtotime((string) ($session['expires_at'] ?? '')) > time()
                && hash_equals((string) ($session['hash'] ?? ''), $tokenHash)
                && hash_equals((string) ($session['user_agent_hash'] ?? ''), $userAgentHash)) {
                return true;
            }
        }
        return false;
    }

    public function csrf(string $id, string $token): string
    {
        return hash_hmac('sha256', 'csrf:' . $id, $token);
    }
}
