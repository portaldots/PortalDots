@extends('v2.layouts.no_drawer')

@section('title', '企画情報 エクスポート')

@section('navbar')
    <app-nav-bar-back inverse href="{{ url('home_staff/circles') }}" data-turbolinks="false">
        企画情報
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>
                企画情報のエクスポート
            </template>
            <list-view-card>
                件数によっては時間がかかる場合があります
            </list-view-card>
            <form action="{{ route('staff.circles.export') }}" method="post">
                @method('post')
                @csrf
                <list-view-action-btn button submit>
                    エクスポート
                </list-view-action-btn>
            </form>
        </list-view>
    </app-container>
@endsection
