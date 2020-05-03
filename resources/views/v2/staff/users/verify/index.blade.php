@extends('v2.layouts.no_drawer')

@section('title', 'スタッフによるメール認証')

@section('navbar')
    <app-nav-bar-back inverse href="{{ url("home_staff/users") }}" data-turbolinks="false">
        ユーザー情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container medium>
        @if ($user->hasVerifiedUnivemail())
            <list-view>
                <template v-slot:title>
                    {{ $user->name }}（{{ $user->student_id }}）
                </template>
                <list-view-card>
                    <p>{{ $user->is_verified_by_staff ? 'スタッフによって本人確認が済んでいるユーザーです' : '学校発行のメールによって本人確認が済んでいるユーザーです' }}</p>
                </list-view-card>
                <list-view-action-btn href="{{ url("home_staff/users/read/{$user->id}") }}" data-turbolinks="false">
                    ユーザーの詳細に戻る
                </list-view-action-btn>
            </list-view>
        @else
            <list-view>
                <template v-slot:title>
                    {{ $user->name }}（{{ $user->student_id }}）
                </template>
                <list-view-card>
                    <p>本人確認は済んでいますか？</p>
                    <ul>
                        @if ($user->email === $user->univemail)
                            <li><strong>連絡先メールアドレスを学校発行メールアドレス以外に変更するよう、ユーザーに伝えてください</strong></li>
                        @endif
                        <li>このユーザーは「{{ config('app.name') }}」の連絡先メールアドレスとして学校発行のメールアドレスを使用できなくなります</li>
                        <li>ユーザーが「{{ config('app.name') }}」に登録している学籍番号を変更した場合、本人確認未完了状態に戻ります</li>
                    </ul>
                </list-view-card>
                <form action="{{ route('staff.users.verify.update', ['user' => $user]) }}" method="post">
                    @method('patch')
                    @csrf
                    <list-view-action-btn button submit>
                        本人確認を完了する
                    </list-view-action-btn>
                </form>
            </list-view>
        @endif
    </app-container>
@endsection
