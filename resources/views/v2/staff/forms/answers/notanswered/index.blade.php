@extends('v2.layouts.no_drawer')

@section('title', '未提出団体一覧')

@section('content')
<app-container>
    <list-view>
        <template v-slot:title>{{ $form->name }} - 未提出団体（{{ count($circles) }}団体）</template>
        @if(empty($circles))
            <list-view-empty
                text="未提出団体はありません"
            >
        @else
        @foreach ($circles as $circle)
            <list-view-item
                href="{{ url('/home_staff/circles/read/' . $circle->id) }}"
                newtab
            >
                <template v-slot:title>団体ID：{{ $circle->id }}　{{ $circle->name }}</template>
            </list-view-item>
        @endforeach
        @endif
    </list-view>
</app-container>
<app-container class="text-center pt-spacing-md">
    <a href="{{ url('home_staff/applications/read/'. $form->id) }}" class="btn is-primary is-wide" data-turbolinks="false">回答一覧へもどる</a>
</app-container>
@endsection
