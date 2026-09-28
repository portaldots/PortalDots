@extends('layouts.legacy')

@section('editor', true)

@section('title', config('portal.form_editor.title') . ' - ' . config('app.name'))

@prepend('js')
    @vite(['resources/js/forms_editor/index.js'])
@endprepend

@section('content')
    <div class="d-none" id="forms-editor-config"
        data-api-base-url='{{ json_encode(route('staff.forms.editor.api', [$form->id])) }}'
        data-editor-title="{{ config('portal.form_editor.title') }}"
        data-publish-confirmation="{{ config('portal.form_editor.publish_confirmation') }}">
    </div>
    <div id="forms-editor-container"></div>
@endsection
