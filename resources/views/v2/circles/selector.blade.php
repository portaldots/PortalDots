@extends('v2.layouts.app')

@section('title', '企画を選択')
    
@section('content')
    <app-container>
        <list-view>
            <template v-slot:title>企画を選択</template>
            @foreach ($circles as $circle)
                <list-view-item href="{{ $url }}?circle={{ $circle->id }}">
                    <template v-slot:title>
                        <i class="fa fa-users mr-2" area-hidden="true"></i>
                        {{ $circle->name }}
                    </template>
                </list-view-item>
            @endforeach
        </list-view>
    </app-container>
@endsection
