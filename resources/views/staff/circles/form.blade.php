@extends('layouts.app')

@section('title', (empty($circle) ? '団体情報新規作成' : '団体情報編集') . ' - ' . config('app.name') )

@section('content')
<div class="container">
    @if (session('toast'))
        <div class="alert alert-success" role="alert">
            {{ session('toast') }}
        </div>
    @endif
    <form method="post" action="{{ empty($circle) ? route('staff.circles.new') : route('staff.circles.update', $circle) }}">
        @method(empty($circle) ? 'post' : 'patch' )
        @csrf
        <div class="card">
            <div class="card-body">
                @isset ($circle)
                <div class="form-group row">
                    <label for="circleIdInput" class="col-sm-2 col-form-label">団体ID</label>
                    <div class="col-sm-10">
                        <input type="text" readonly class="form-control-plaintext" id="circleIdInput" value="{{ $circle->id }}">
                    </div>
                </div>
                @endisset
                <div class="form-group row">
                    <label for="nameInput" class="col-sm-2 col-form-label">団体名</label>
                    <div class="col-sm-10">
                        <input
                            id="nameInput"
                            class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            type="text"
                            name="name"
                            value="{{ old('name', empty($circle) ? '' : $circle->name) }}"
                        >
                        @if ($errors->has('name'))
                            <div class="invalid-feedback">
                                @foreach ($errors->get('name') as $message)
                                    {{ $message }}
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label for="leaderInput" class="col-sm-2 col-form-label">責任者の学籍番号</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control {{ $errors->has('leader') ? 'is-invalid' : '' }}" id="leaderInput" name="leader" value="{{ old('leader', empty($leader) ? '' : $leader->student_id) }}">
                        @error('leader')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>


                <div class="form-group row">
                    <label for="membersInput" class="col-sm-2 col-form-label">学園祭係(副責任者)の学籍番号</label>
                    <div class="col-sm-4">
                        <textarea id="membersInput" class="form-control {{ $errors->has('members') ? 'is-invalid' : '' }}" name="members" rows="3">{{ old('members', empty($members) ? '' : $members) }}</textarea>
                        @error('members')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small>学籍番号を改行して入力することで複数の学園祭係を追加できます</small>
                    </div>
                </div>
                <br>
                <div class="form-group">
                    <label for="StaffNote">スタッフ用メモ</label>
                    <textarea id="StaffNote" class="form-control" name="notes" rows="5">{{ old('notes', empty($circle) ? '' : $circle->notes) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="{{ url('/home_staff/circles') }}" class="btn btn-light" role="button">団体リストに戻る</a>
            </div>
        </div>
    </form>
</div>
@endsection
