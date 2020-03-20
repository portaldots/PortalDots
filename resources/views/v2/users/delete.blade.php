@extends('v2.layouts.app')

@section('title', 'ユーザー設定')

@section('content')
@include('v2.includes.user_settings_tab_strip')
<app-container>
    <list-view>
        <template v-slot:title>アカウント削除</template>
        <list-view-card class="text-center">
            @if ($belong)
                <p class="card-text">団体に所属しているため、アカウント削除はできません。</p>
                <p class="card-text">詳細については「{{ config('portal.admin_name') }}」までお問い合わせください</p>
                <div>
                    <a href="{{ route('home') }}" class="btn is-primary">ホームに戻る</a>
                </div>
            @else
                <p class="card-text">アカウントを削除した場合、申請の手続きなどができなくなります。</p>
                <form-with-confirm
                    action="{{ route('user.destroy') }}"
                    method="post"
                    confirm-message="本当にアカウントを削除しますか？"
                >
                    @method('delete')
                    @csrf
                    <button type="submit" class="btn is-danger">
                        <strong>アカウントを削除</strong>
                    </button>
                </form-with-confirm>
            @endif
        </list-view-card>
    </list-view>
</app-container>
@endsection
