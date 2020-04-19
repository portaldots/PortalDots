@extends('v2.layouts.no_drawer')

@section('title', '未提出企画一覧')
    
@section('navbar')
    <app-nav-bar-back inverse href="{{ url('home_staff/applications/read/' . $form->id) }}" data-turbolinks="false">
        {{ $form->name }}
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container>
        <list-view>
            <template v-slot:title>未提出企画（{{ count($circles) }}企画）</template>
            @if($circles->isEmpty())
                <list-view-empty icon-class="fas fa-users" text="未提出企画はありません"></list-view-empty>
            @else
                @foreach ($circles as $circle)
                    <list-view-item href="{{ url('/home_staff/circles/read/' . $circle->id) }}" newtab>
                        <template v-slot:title>企画ID：{{ $circle->id }}　{{ $circle->name }}</template>
                    </list-view-item>
                @endforeach
            @endif
        </list-view>
    </app-container>
@endsection
