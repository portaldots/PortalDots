@php
$is_install = Jackiedo\DotenvEditor\Facades\DotenvEditor::getValue('APP_NOT_INSTALLED') === 'true';
@endphp

@extends($is_install ? 'errors.layout_no_drawer' : 'errors.layout')

@section('title', '500 Internal Server Error')
@section('top', 'サーバーエラーが発生しました')
@section('message', '恐れ入りますが、もう一度同じ操作をお試しください')
@section('contact')
    @if ($is_install)
        <p>
            何度も発生する場合は PortalDots 開発チームまでお問い合わせください。
        </p>
    @else
        <p>何度も発生する場合は「{{ config('portal.admin_name') }}」までお問い合わせください。</p>
        <p><a href="mailto:{{ config('portal.contact_email') }}">{{ config('portal.contact_email') }}</a></p>
    @endif
@endsection
@section('twitter', true)
