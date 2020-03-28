@extends('v2.layouts.no_drawer')

@section('title', '未提出企画一覧')
    
@section('content')
    <app-container>
        <list-view>
            <template v-slot:title>{{ $form->name }} - 未提出企画（{{ count($circles) }}企画）</template>
            @if(empty($circles))
                <list-view-empty icon-class="fas fa-users" text="未提出企画はありません"></list-view-empty>
            @else
                @foreach ($circles as $circle)
                    <list-view-item href="{{ url('/home_staff/circles/read/' . $circle->id) }}" newtab>
                        <template v-slot:title>企画ID：{{ $circle->id }}　{{ $circle->name }}</template>
                    </list-view-item>
                @endforeach
            @endif
        </list-view>
        <div class="text-center pt-spacing-md pb-spacing">
            <a href="{{ url('home_staff/applications/read/' . $form->id) }}" class="btn is-primary is-wide"
                data-turbolinks="false">回答一覧へもどる</a>
        </div>
    </app-container>
@endsection
