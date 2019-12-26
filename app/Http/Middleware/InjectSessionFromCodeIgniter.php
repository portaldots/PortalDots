<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Auth;

/**
 * CodeIgniter 側で保存されたセッションを Laravel 側で扱えるようにする
 *
 * CodeIgniter のコードを全て排除できたら、このミドルウェアは廃止する
 *
 * @SuppressWarnings(PHPMD)
 */
class InjectSessionFromCodeIgniter
{
    private const CI_SESSION_TABLE_NAME = 'ci_sessions';

    /**
     * Laravel 側と CodeIgniter 側のどちらのセッションが最新なのかを判断できるようにするための
     * セッションのキー
     *
     * CodeIgniter のコードを完全に排除でき次第、このタイムスタンプセッションは廃止する
     */
    private const LARAVEL_SESSION_TIMESTAMP_NAME = 'inaka_portal_laravel_session_timestamp';

    /**
     * CodeIgniter と Laravel で共有するセッションのキー(ユーザーID以外)
     */
    private const SHARED_SESSION_KEY = [
        'staff_authorized',
    ];

    /**
     * CodeIgniter 側で、ログイン中のユーザーIDが格納されているセッションのキー名
     */
    private const CI_SESSION_USER_ID_KEY = 'user_id';

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        // CodeIgniter のセッションを管理している ci_sessions テーブルからデータを読み出し、
        // 特定のセッションについては Laravel 側でも扱えるようにする
        $ciSessionRecord = null;
        if (!empty($_COOKIE['session_id'])) {
            $ciSessionRecord = DB::table(self::CI_SESSION_TABLE_NAME)
                ->where('id', $_COOKIE['session_id'])
                ->where('ip_address', $ipAddress)
                ->first();
        }

        // CodeIgniter 側でセッションが保存されていない場合、作成
        $ciSessionRecord = $ciSessionRecord ?? $this->createCISessionRecord($ipAddress);

        // SHARED_SESSION_KEY と一致するキーのセッションを Laravel のセッションに格納していく
        // CodeIgniter 側のセッションが最新のときのみ処理する
        $sessionArray = (!empty($ciSessionRecord->data)) ? unserialize($ciSessionRecord->data) : [];
        $sessionTimestamp = (int)session(self::LARAVEL_SESSION_TIMESTAMP_NAME) ?? 0;

        if ($sessionTimestamp < (int)$ciSessionRecord->timestamp) {
            // CodeIgniter からのセッションを Laravel のセッションに格納する
            foreach (self::SHARED_SESSION_KEY as $key) {
                session([
                    $key => $sessionArray[$key] ?? null,
                ]);
            }

            if (empty($sessionArray[self::CI_SESSION_USER_ID_KEY])) {
                // ユーザー ID が空の場合、ログアウト
                Auth::logout();
            }
        }

        // CodeIgniter 側と Laravel 側で、どちらのセッションが最新か判断できるよう
        // Laravel 側にタイムスタンプ情報を持たせる
        session([self::LARAVEL_SESSION_TIMESTAMP_NAME => time()]);

        $response = $next($request);

        // SHARED_SESSION_KEY と一致するキーのセッションを ci_sessions に保存する
        foreach (self::SHARED_SESSION_KEY as $key) {
            $sessionArray[$key] = session($key, null);
        }

        // ログイン中のユーザー ID 情報を ci_sessions に保存する
        // なぜか CodeIgniter 側ではユーザー ID が string になっているので、ユーザー ID を string にして保存
        $sessionArray[self::CI_SESSION_USER_ID_KEY] = !empty($userId = Auth::id()) ? (string)$userId : null;

        DB::table(self::CI_SESSION_TABLE_NAME)
            ->where('id', $ciSessionRecord->id)
            ->update([
                'timestamp' => time(),
                'data' => serialize($sessionArray),
            ]);

        return $response;
    }

    private function createCISessionRecord(string $ipAddress): \stdClass
    {
        $newSessionId = $this->getCISid();

        DB::table(self::CI_SESSION_TABLE_NAME)
            ->insert([
                'id' => $newSessionId,
                'ip_address' => $ipAddress,
                'timestamp' => time(),
                'data' => serialize([
                    '__ci_last_regenerate' => time(),
                ])
            ]);

        $ciSessionRecord = DB::table(self::CI_SESSION_TABLE_NAME)->where('id', $newSessionId)->first();

        setcookie(
            'session_id',
            $ciSessionRecord->id,
            0,
            '/',
            '',
            false,
            true
        );

        return $ciSessionRecord;
    }

    /**
     * CodeIgniter フォーマットのセッションIDを生成して返す
     */
    private function getCISid(): string
    {
        $length = $this->getCISidLength();
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    /**
     * CodeIgniterで使用されるセッションIDの長さを取得する
     *
     * system/libraries/Session/Session.php 由来のコード
     */
    private function getCISidLength(): int
    {
        $bits_per_character = (int)ini_get('session.sid_bits_per_character');
        $sid_length = (int)ini_get('session.sid_length');
        if (($bits = $sid_length * $bits_per_character) < 160) {
            // Add as many more characters as necessary to reach at least 160 bits
            $sid_length += (int)ceil((160 % $bits) / $bits_per_character);
        }
        return $sid_length;
    }
}
