@extends('v2.layouts.no_drawer')

@section('no_circle_selector', true)

@section('title', '企画参加登録')

@section('content')
<app-header container-medium>
    <template v-slot:title>企画参加登録を始める前に</template>
</app-header>
<app-container medium>
    <list-view>
        <list-view-card class="markdown">
            @markdown($description)
        </list-view-card>
    </list-view>
    <div class="text-center pt-spacing-md">
        <a class="btn is-primary is-wide" href="{{ route('circles.create') }}">
            企画参加登録を始める
        </a>
    </div>
</app-container>
@endsection
