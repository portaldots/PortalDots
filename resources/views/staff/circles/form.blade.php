@extends('layouts.app')

@section('title', (empty($circle) ? '企画情報新規作成' : '企画情報編集') . ' - ' . config('app.name') )

@section('content')
    <div class="container">
        @if (session('toast'))
            <div class="alert alert-success" role="alert">
                {{ session('toast') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                エラーがあります。以下をご確認ください。
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
                        'name' => '企画名',
                        'name_yomi' => '企画名(よみ)',
                        'group_name' => '企画を出店する団体の名称',
                        'group_name_yomi' => '企画を出店する団体の名称(よみ)'
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
                            <small>学籍番号を改行して入力することで複数の学園祭係を追加できます。{{ config('portal.users_number_to_submit_circle') - 1 }}人を下回っていても構いません。</small>
                        </div>
                    </div>
                    <hr>
                    @if (isset($custom_form))
                        <div class="form-group row">
                            <span class="col-sm-2 col-form-label">カスタムフォームへの回答</span>
                            <div class="col-sm-10">
                                @empty ($circle)
                                    <p>
                                        <i class="fa fa-info-circle fa-fw" aria-hidden="true"></i>
                                        カスタムフォーム回答の内容を編集するには、まず企画情報を保存してください
                                    </p>
                                @else
                                    <a href="{{ route('staff.forms.answers.create', ['form' => $custom_form, 'circle' => $circle]) }}"
                                        class="btn btn-primary">
                                        カスタムフォーム回答の内容を表示・編集
                                    </a>
                                @endempty
                            </div>
                        </div>
                        <hr>
                    @endif
                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">参加登録受理</div>
                        <div class="col-sm-10">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="status" id="statusRadios1"
                                    value="pending"
                                    {{ old('status', isset($circle) && $circle->status === null ? 'checked' : '') }}>
                                <label class="form-check-label" for="statusRadios1">
                                    <strong>確認中</strong><br>
                                    <span class="text-muted">ユーザーには参加登録が確認中である旨が表示されます</span>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="status" id="statusRadios2"
                                    value="approved"
                                    {{ old('status', isset($circle) && $circle->status === 'approved' ? 'checked' : '') }}>
                                <label class="form-check-label" for="statusRadios2">
                                    <strong>受理</strong><br>
                                    <span class="text-muted">参加登録を受理します。当該企画は申請機能を利用できるようになります</span>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="status" id="statusRadios3"
                                    value="rejected"
                                    {{ old('status', isset($circle) && $circle->status === 'rejected' ? 'checked' : '') }}>
                                <label class="form-check-label" for="statusRadios3">
                                    <strong>不受理</strong><br>
                                    <span class="text-muted">参加登録を不受理とします。ユーザーには参加登録が受理されなかった旨が表示されます</span>
                                </label>
                            </div>
                            @foreach ($errors->get('status') as $message)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="statusReason">不受理に関する詳細(ユーザーに通知)</label>
                        <textarea id="statusReason" class="form-control" name="status_reason"
                            rows="5">{{ old('status_reason', empty($circle) ? '' : $circle->status_reason) }}</textarea>
                        <small>この内容はユーザーに通知されます。参加登録を不受理とする際、ユーザーに伝達したい事項があれば入力してください(Markdown利用可能)</small>
                    </div>
                    <div class="form-group">
                        <label for="StaffNote">スタッフ用メモ</label>
                        <textarea id="StaffNote" class="form-control" name="notes"
                            rows="5">{{ old('notes', empty($circle) ? '' : $circle->notes) }}</textarea>
                        <small>ここに入力された内容はスタッフのみ閲覧できます。スタッフ内で共有したい事項を残しておくメモとしてご活用ください</small>
                    </div>
                    @empty ($circle)
                        <hr>
                        <p>このページで企画を作成すると、企画参加登録提出済という扱いになります。企画の責任者・学園祭係(副責任者)は企画の情報を修正・削除できません。</p>
                    @endempty
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="{{ url('/home_staff/circles') }}" class="btn btn-light" role="button">企画リストに戻る</a>
                </div>
            </div>
        </form>
    </div>
@endsection
