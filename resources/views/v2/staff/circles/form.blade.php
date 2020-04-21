@extends('v2.layouts.no_drawer')

@section('title', empty($circle) ? '新規作成 — 企画' : "{$circle->name} — 企画")

@section('navbar')
    <app-nav-bar-back inverse href="{{ url('/home_staff/circles') }}" data-turbolinks="false">
        企画情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ empty($circle) ? route('staff.circles.new') : route('staff.circles.update', $circle) }}">
        @method(empty($circle) ? 'post' : 'patch' )
        @csrf

        <app-header>
            @if (empty($circle))
                <template v-slot:title>企画を新規作成</template>
            @endif
            @isset ($circle)
                <template v-slot:title>企画を編集</template>
                <div>企画ID : {{ $circle->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
                <template v-slot:title>企画基本情報</template>
                @foreach ([
                    'name' => '企画の名前',
                    'name_yomi' => '企画の名前(よみ)',
                    'group_name' => '企画団体の名前',
                    'group_name_yomi' => '企画団体の名前(よみ)'
                    ] as $field_name => $display_name)
                    <list-view-form-group label-for="{{ $field_name }}">
                        <template v-slot:label>{{ $display_name }}</template>
                        <input id="{{ $field_name }}" class="form-control @error($field_name) is-invalid @enderror" type="text"
                            name="{{ $field_name }}" value="{{ old($field_name, empty($circle) ? '' : $circle->$field_name) }}"
                            required>
                        @if ($errors->has($field_name))
                            <template v-slot:invalid>
                                @foreach ($errors->get($field_name) as $message)
                                    {{ $message }}
                                @endforeach
                            </template>
                        @endif
                    </list-view-form-group>
                @endforeach
                @if (isset($custom_form))
                    <list-view-form-group>
                        <template v-slot:label>カスタムフォームへの回答</template>
                        @empty ($circle)
                            <div class="text-muted">
                                <i class="fa fa-info-circle fa-fw" aria-hidden="true"></i>
                                カスタムフォーム回答の内容を編集するには、まず企画情報を保存してください
                            </div>
                        @else
                            <a href="{{ route('staff.forms.answers.create', ['form' => $custom_form, 'circle' => $circle]) }}"
                                class="btn is-primary">
                                回答の内容を表示・編集
                            </a>
                        @endempty
                    </list-view-form-group>
                @endif
                <list-view-form-group>
                    <template v-slot:label>
                        タグ
                        <small class="text-muted">
                            スペース区切りで複数入力可
                        </small>
                    </template>
                    <template v-slot:description>
                        企画をタグで分類できます(例 : <span class="badge is-primary">ステージ企画</span>、<span
                            class="badge is-primary">模擬店</span>、<span class="badge is-primary">講義棟教室</span>、<span
                            class="badge is-primary">食品販売</span>)。<br>
                        タグに応じて、閲覧可能なお知らせ、ダウンロード可能な配布資料、回答可能な申請フォームを制限することもできます。</template>
                    <tags-input input-name="tags" :default-tags="{{ $default_tags }}"
                        :autocomplete-items="{{ $tags_autocomplete_items }}"></tags-input>
                </list-view-form-group>
            </list-view>

            <list-view>
                <template v-slot:title>企画のメンバー</template>
                <list-view-form-group label-for="leader">
                    <template v-slot:label>責任者の学籍番号</template>
                    <input type="text" class="form-control @error('leader') is-invalid @enderror" id="leader" name="leader"
                        value="{{ old('leader', empty($leader) ? '' : $leader->student_id) }}">
                    @if ($errors->has('leader'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('leader') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="members">
                    <template v-slot:label>学園祭係(副責任者)の学籍番号</template>
                    <template
                        v-slot:description>学籍番号を改行して入力することで複数の学園祭係を追加できます。{{ config('portal.users_number_to_submit_circle') - 1 }}人を下回っていても構いません。</template>
                    <textarea id="members" class="form-control @error('members') is-invalid @enderror" name="members"
                        rows="3">{{ old('members', empty($members) ? '' : $members) }}</textarea>
                    @if ($errors->has('members'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('members') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <list-view>
                <list-view-form-group>
                    <template v-slot:label>参加登録の受理設定</template>
                    <div class="form-radio">
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="status" id="statusRadios1" value="pending"
                                {{ old('status', isset($circle) ? $circle->status : null) === null ? 'checked' : '' }}>
                            <strong>確認中</strong><br>
                            <span class="text-muted">ユーザーには参加登録が確認中である旨が表示されます</span>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="status" id="statusRadios2" value="approved"
                                {{ old('status', isset($circle) ? $circle->status : null) === 'approved' ? 'checked' : '' }}>
                            <strong>受理</strong><br>
                            <span class="text-muted">参加登録を受理します。当該企画は申請機能を利用できるようになります</span>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="status" id="statusRadios3" value="rejected"
                                {{ old('status', isset($circle) ? $circle->status : null) === 'rejected' ? 'checked' : '' }}>
                            <strong>不受理</strong><br>
                            <span class="text-muted">参加登録を不受理とします。ユーザーには参加登録が受理されなかった旨が表示されます</span>
                        </label>
                    </div>
                    @if ($errors->has('status'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('status') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="status_reason">
                    <template v-slot:label>不受理に関する詳細(ユーザーに通知)</template>
                    <template
                        v-slot:description>この内容はユーザーに通知されます。参加登録を不受理とする際、ユーザーに伝達したい事項があれば入力してください(Markdown利用可能)</template>
                    <textarea id="status_reason" class="form-control @error('status_reason') is-invalid @enderror"
                        name="status_reason"
                        rows="5">{{ old('status_reason', empty($circle) ? '' : $circle->status_reason) }}</textarea>
                    @if ($errors->has('status_reason'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('status_reason') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <list-view>
                <list-view-form-group label-for="notes">
                    <template v-slot:label>スタッフ用メモ</template>
                    <template v-slot:description>ここに入力された内容はスタッフのみ閲覧できます。スタッフ内で共有したい事項を残しておくメモとしてご活用ください。</template>
                    <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes"
                        rows="5">{{ old('notes', empty($circle) ? '' : $circle->notes) }}</textarea>
                    @if ($errors->has('notes'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('notes') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </div>
        </app-container>
    </form>
@endsection
