@extends('v2.layouts.app')

@section('title', __('配布資料'))

@section('content')
<app-container>
    @if ($documents->isEmpty())
    <list-view-empty
        icon-class="far fa-file-alt"
        text="{{ __('配布資料はまだありません') }}"
    />
    @else
    <list-view>
        @foreach ($documents as $document)
        <list-view-item
            href="{{ url("uploads/documents/{$document->id}") }}"
            newtab
        >
            <template v-slot:title>
                @if ($document->is_important)
                <i class="fas fa-exclamation-circle fa-fw text-danger"></i>
                @else
                <i class="far fa-file-alt fa-fw"></i>
                @endif
                {{ $document->name }}
            </template>
            <template v-slot:meta>
                {{ __('更新 :') }}
                @datetime($document->updated_at)
                @isset($document->schedule)
                •
                {{ __(':schedule_name で配布', ['schedule_name' => $document->schedule->name]) }}
                @endisset
            </template>
            @summary($document->description)
        </list-view-item>
        @endforeach
    </list-view>
    @endif
</app-container>
@endsection
