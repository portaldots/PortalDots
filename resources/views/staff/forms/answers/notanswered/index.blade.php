@extends('layouts.app')

@section('title', '未提出企画一覧')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.forms.answers.index', ['form' => $form]) }}">
        {{ $form->name }}
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container>
        <list-view>
            <template v-slot:title>未提出企画（{{ count($circles) }}企画）</template>
            @if(empty($circles))
                <list-view-empty icon-class="fas fa-users" text="未提出企画はありません"></list-view-empty>
            @else
                @foreach ($circles as $circle)
                    {{-- TODO: 将来的には企画編集ページではなく企画詳細ページへリンクしたい --}}
                    <list-view-item href="{{ route('staff.circles.edit', ['circle' => $circle->id]) }}" newtab>
                        <template v-slot:title>企画ID：{{ $circle->id }}　{{ $circle->name }}</template>
                    </list-view-item>
                @endforeach
            @endif
        </list-view>
    </app-container>
@endsection
