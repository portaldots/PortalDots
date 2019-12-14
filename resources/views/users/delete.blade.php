@extends('layouts.single_column')

@section('title', 'アカウントを削除しますか？ - ' . config('app.name') )

@section('main')
<div class="card">
    <div class="card-header">
        {{ $belong ? 'アカウントを削除できません' : '本当に削除しますか？'}}
    </div>
    <div class="card-body">
        @if ($belong)
            <p class="card-text">団体に所属しているため削除することができません</p>
            <p class="card-text">詳細については「{{ config('portal.admin_name') }}」までお問い合わせください</p>
            <p><a href="{{ route('home') }}" class="btn btn-primary" role="button">ホームに戻る</a></p>
        @else
            <p class="card-text">アカウントを削除した場合申請の手続きなどができなくなります。</p>
            <form action="{{ route('user.delete') }}" method="post">
                @method('delete')
                @csrf
                <button type="submit" {{ $belong ? 'disabled' : ''}} class="btn btn-danger">アカウントを削除</button>
                <a href="{{ route('home') }}" class="btn btn-primary">キャンセル</a>
            </form>
        @endif
    </div>
</div>
@endsection