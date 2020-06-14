@extends('v2.layouts.no_drawer')

@section('title', 'お問い合わせ - メールアドレス一覧')

@section('navbar')
    <app-nav-bar-back inverse href="{{ url('/home_staff') }}" data-turbolinks="false">
        戻る
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>
                お問い合わせ - メールアドレス一覧
            </template>

            <list-view-card>
                <p>ここでメールアドレスを設定するとポータルからのお問い合わせを振り分けることができます。</p>
            </list-view-card>
            @foreach ($contact_emails as $contact_email)
                <list-view-item href="{{ route('staff.contacts.emails.edit', ['contact_email' => $contact_email]) }}">
                    <template v-slot:title>
                        {{ $contact_email->name }}
                    </template>
                    {{ $contact_email->email }}
                </list-view-item>
            @endforeach
            <list-view-action-btn href="{{ route('staff.contacts.emails.create') }}">
                <i class="fas fa-plus"></i>
                メールアドレスを追加
            </list-view-action-btn>
        </list-view>
    </app-container>
@endsection
