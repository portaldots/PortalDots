@extends('v2.layouts.no_drawer')

@section('title', 'メール認証のお願い')

@section('content')
<app-container medium>
    <list-view
        header-title="{{ Auth::user()->is_signed_up ? 'まだユーザー情報の変更は完了していません！' : 'まだユーザー登録は完了していません！' }}"
    >
        <list-view-card>
            以下のメールアドレスに確認メールを送信しました。<strong>メール送信から {{ config('auth.verification.expire', 60) }} 分以内</strong>に、確認メールに記載されている URL にアクセスしてください。
        </list-view-card>
        {{-- 大学提供メールアドレス --}}
        <list-view-item>
            <template v-slot:title>
                {{ Auth::user()->univemail }}
            </template>
            @if (Auth::user()->hasVerifiedUnivemail())
                <i class="fas fa-check text-success"></i>
                認証完了
            @else
                <i class="fas fa-exclamation-circle text-danger"></i>
                メールを確認してください
            @endif
        </list-view-item>
        {{-- 連絡先メールアドレス --}}
        @if (Auth::user()->email !== Auth::user()->univemail)
        <list-view-item>
            <template v-slot:title>
                {{ Auth::user()->email }}
            </template>
            @if (Auth::user()->hasVerifiedEmail())
                <i class="fas fa-check text-success"></i>
                認証完了
            @else
                <i class="fas fa-exclamation-circle text-danger"></i>
                メールを確認してください
            @endif
        </list-view-item>
        @endif
    </list-view>

    <list-view
        header-title="確認メールの再送"
    >
        <list-view-card>
            <p>確認メールが見つからない場合・確認メールの URL にアクセスするとエラーになる場合、以下のボタンより確認メールを再送できます。</p>
            <form action="{{ route('verification.resend') }}" method="post">
                @csrf
                <button class="btn is-primary is-wide">
                    確認メールを再送
                </button>
            </form>
        </list-view-card>
    </list-view>

    <list-view
        header-title="誤った情報でユーザー登録してしまった場合"
    >
        <list-view-action-btn href="{{ route('user.edit') }}" newtab>
            登録情報の変更
        </list-view-action-btn>
        <list-view-action-btn href="{{ route('change_password') }}" newtab>
            パスワードの変更
        </list-view-action-btn>
        <list-view-action-btn href="{{ route('user.delete') }}" newtab>
            アカウントの削除
        </list-view-action-btn>
    </list-view>
</app-container>
@endsection
