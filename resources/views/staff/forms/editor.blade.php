@extends('layouts.app')

@section('no_footer', true)

@section('title', "{$form->name} — フォームエディター")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.forms.index') }}">
        申請管理
    </app-nav-bar-back>
@endsection

@section('content')
    @isset($form)
        @include('includes.staff_answers_tab_strip', ['form_id' => $form->id])
    @endisset
    <iframe src="{{ route('staff.forms.editor.frame', ['form' => $form]) }}" class="content__body__iframe"
        allowtransparency="true">
    </iframe>
@endsection
