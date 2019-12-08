@extends('layouts.app')

@push('css')
    <link href="{{ mix('css/forms_editor.css') }}" rel="stylesheet">
@endpush

@push('js')
    <script src="{{ mix('js/forms_editor/index.js') }}" defer></script>
@endpush

@section('content')
    <div class="d-none" id="forms-editor-config" data-api-base-url='@json(route('staff.forms.editor.api', [$form->id]))'></div>
    <div id="forms-editor-container"></div>
@endsection
