@extends('errors.layout_no_drawer')

@section('title', 'ソフトウェアアップデート中')
@section('top', 'ソフトウェアアップデート中')
@section('message', 'しばらく経ってからもう一度お試しください')
@section('contact')
    <p>解決しない場合は「{{ config('portal.admin_name') }}」までお問い合わせください。</p>
    <p><a href="mailto:{{ config('portal.contact_email') }}">{{ config('portal.contact_email') }}</a></p>
@endsection
@section('twitter', true)
