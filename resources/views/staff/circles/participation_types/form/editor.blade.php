@extends('layouts.app')

@section('no_footer', true)

@section('title', "{$participation_type->name} — 参加種別")

@push('body-class')
    has-content-fill
@endpush

@section('navbar')
    <app-nav-bar-back
        href="{{ route('staff.circles.participation_types.form.edit', [
            'participation_type' => $participation_type,
        ]) }}">
        参加登録フォームの設定
    </app-nav-bar-back>
@endsection

@section('content')
    <content-iframe src="{{ route('staff.forms.editor.frame', ['form' => $participation_type->form]) }}"></content-iframe>
@endsection
