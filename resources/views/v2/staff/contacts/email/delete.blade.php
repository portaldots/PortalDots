@extends('v2.layouts.no_drawer')

@section('title', "{$contact_email->name} - お問い合わせ")

@section('navbar')
    <app-nav-bar-back inverse href="{{ route('staff.contacts.email.edit', $contact_email) }}">
        {{ $contact_email->name }}
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container medium>
        <list-view>
            <list-view-card class="text-center">
                <p>{{ $contact_email->name }}({{ $contact_email->email }})を削除しますか？</p>

                <form action="{{ route('staff.contacts.email.destroy', $contact_email) }}" method="post">
                    @method('delete')
                    @csrf
                    <button type="submit" class="btn is-danger">削除</button>
                    <a href="{{ route('staff.contacts.email.edit', $contact_email) }}" class="btn is-secondary">戻る</a>
                </form>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
