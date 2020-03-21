@extends('layouts.app')

@section('editor', true)

@section('title', '申請フォームエディター - ' . config('app.name'))

@section('content')
    <div class="d-none" id="forms-editor-config" data-api-base-url='@json(route('staff.forms.editor.api', [$form->id]))'></div>
    <div id="forms-editor-container"></div>
@endsection
