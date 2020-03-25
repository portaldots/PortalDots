@extends('layouts.app')

@section('title', (empty($circle) ? '企画情報新規作成' : '企画情報編集') . ' - ' . config('app.name') )
    
@section('content')
    <div class="container">
        @if (session('toast'))
            <div class="alert alert-success" role="alert">
                {{ session('toast') }}
            </div>
        @endif
        <form method="post"
            action="{{ empty($circle) ? route('staff.circles.new') : route('staff.circles.update', $circle) }}">
            @method(empty($circle) ? 'post' : 'patch' )
            @csrf
            <div class="card">
                <div class="card-body">
                    @isset ($circle)
                        <div class="form-group row">
                            <label for="circleIdInput" class="col-sm-2 col-form-label">企画ID</label>
                            <div class="col-sm-10">
                                <input type="text" readonly class="form-control-plaintext" id="circleIdInput"
                                    value="{{ $circle->id }}">
                            </div>
                        </div>
                    @endisset
    
                    @foreach ([
                        'name' => '企画の名前',
                        'name_yomi' => '企画の名前(よみ)',
                        'group_name' => '企画団体の名前',
                        'group_name_yomi' => '企画団体の名前(よみ)'
                        ] as $field_name => $display_name)
                        <div class="form-group row">
                            <label for="{{ $field_name }}Input" class="col-sm-2 col-form-label">
                                {{ $display_name }}
                            </label>
                            <div class="col-sm-10">
                                <input id="{{ $field_name }}Input"
                                    class="form-control {{ $errors->has($field_name) ? 'is-invalid' : '' }}" type="text"
                                    name="{{ $field_name }}"
                                    value="{{ old($field_name, empty($circle) ? '' : $circle->$field_name) }}">
                                @if ($errors->has($field_name))
                                    <div class="invalid-feedback">
                                        @foreach ($errors->get($field_name) as $message)
                                            {{ $message }}
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
    
                    <hr>
    
                    <div class="form-group row">
                        <label for="leaderInput" class="col-sm-2 col-form-label">責任者の学籍番号</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control {{ $errors->has('leader') ? 'is-invalid' : '' }}"
                                id="leaderInput" name="leader"
                                value="{{ old('leader', empty($leader) ? '' : $leader->student_id) }}">
                            @foreach ($errors->get('leader') as $message)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endforeach
                        </div>
                    </div>
    
                    <div class="form-group row">
                        <label for="membersInput" class="col-sm-2 col-form-label">学園祭係(副責任者)の学籍番号</label>
                        <div class="col-sm-4">
                            <textarea id="membersInput"
                                class="form-control {{ $errors->has('members') ? 'is-invalid' : '' }}" name="members"
                                rows="3">{{ old('members', empty($members) ? '' : $members) }}</textarea>
                            @foreach ($errors->get('members') as $message)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endforeach
                            <small>学籍番号を改行して入力することで複数の学園祭係を追加できます</small>
                        </div>
                    </div>
                    <br>
                    <div class="form-group">
                        <label for="StaffNote">スタッフ用メモ</label>
                        <textarea id="StaffNote" class="form-control" name="notes"
                            rows="5">{{ old('notes', empty($circle) ? '' : $circle->notes) }}</textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="{{ url('/home_staff/circles') }}" class="btn btn-light" role="button">企画リストに戻る</a>
                </div>
            </div>
        </form>
    </div>
@endsection
