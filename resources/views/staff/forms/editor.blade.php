@extends('layouts.app')

@section('no_footer', true)

@section('title', $form->name . ' — ' . config('portal.form_editor.title'))

@push('body-class')
    has-content-fill
@endpush

@section('navbar')
    <app-nav-bar-back href="{{ route(config('portal.form_editor.back_route')) }}">
        {{ config('portal.form_editor.back_label') }}
    </app-nav-bar-back>
@endsection

@section('content')
    @isset($form)
        @include('includes.staff_answers_tab_strip', ['form_id' => $form->id])
    @endisset
    <content-iframe src="{{ route('staff.forms.editor.frame', ['form' => $form]) }}"></content-iframe>
@endsection
