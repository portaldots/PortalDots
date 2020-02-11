@extends('v2.layouts.app')

@section('title', '団体を選択')

@section('content')
    <app-container>
        <list-view header-title="団体を選択してください">
            @foreach ($circles as $circle)
                <list-view-item href="{{ route($redirect, ['circle' => $circle->id]) }}">
                    <template v-slot:title>
                        <i class="fa fa-users mr-2" area-hidden="true"></i>
                        　{{ $circle->name }}
                    </template>
                </list-view-item>
            @endforeach
        </list-view>
    </app-container>
@endsection