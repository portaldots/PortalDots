@extends('layouts.single_column')

@section('title', '団体を選択してください - '. config('app.name'))

@section('main')

<div class="card">
    <div class="card-header">操作する団体を選択してください。</div>
    <div class="card-body">
        次に進むにはどの団体として操作するのか選択してください。
    </div>
    <div class="list-group list-group-flush">
        @foreach ($circles as $circle)
            <a class="list-group-item list-group-item-action" href="{{ route($redirect, ['circle' => $circle]) }}">
                <i class="fa fa-users mr-2" area-hidden="true"></i>
                {{ $circle->name }}
            </a>
        @endforeach
        <a href="{{ route('home') }}" class="btn btn-light" role="button">キャンセル</a>
    </div>
</div>

@endsection