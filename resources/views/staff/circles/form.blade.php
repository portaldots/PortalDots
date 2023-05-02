@extends('layouts.app')

@section('title', empty($circle) ? '新規作成 — 企画' : "{$circle->name} — 企画")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.circles.index') }}">
        企画情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post"
        action="{{ empty($circle) ? route('staff.circles.store') : route('staff.circles.update', $circle) }}">
        @method(empty($circle) ? 'post' : 'patch')
        @csrf

        <app-header>
            @if (empty($circle))
                <template v-slot:title>企画を新規作成</template>
            @endif
            @isset($circle)
                <template v-slot:title>企画を編集</template>
                <div>企画ID : {{ $circle->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
                <template v-slot:title>企画基本情報</template>
                <list-view-form-group label-for="participation_type_id">
                    <template v-slot:label>参加種別</template>
                    @empty($circle)
                        <template v-slot:description>後から変更することはできません。</template>
                    @endempty
                    <select id="participation_type_id"
                        class="form-control @error('participation_type_id') is-invalid @enderror" name="participation_type_id"
                        value="{{ old('participation_type_id', empty($circle) ? '' : $circle->participation_type_id) }}"
                        {{ empty($circle) ? '' : 'disabled' }} required>
                        <option disabled value="">選択してください</option>
                        @foreach ($participation_types as $participation_type)
                            <option value="{{ $participation_type->id }}">{{ $participation_type->name }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('participation_type_id'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('participation_type_id') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                @foreach ([
            'name' => '企画名',
            'name_yomi' => '企画名(よみ)',
            'group_name' => '企画を出店する団体の名称',
            'group_name_yomi' => '企画を出店する団体の名称(よみ)',
        ] as $field_name => $display_name)
                    <list-view-form-group label-for="{{ $field_name }}">
                        <template v-slot:label>{{ $display_name }}</template>
                        <input id="{{ $field_name }}" class="form-control @error($field_name) is-invalid @enderror"
                            type="text" name="{{ $field_name }}"
                            value="{{ old($field_name, empty($circle) ? '' : $circle->$field_name) }}" required>
                        @if ($errors->has($field_name))
                            <template v-slot:invalid>
                                @foreach ($errors->get($field_name) as $message)
                                    {{ $message }}
                                @endforeach
                            </template>
                        @endif
                    </list-view-form-group>
                @endforeach
                <list-view-form-group>
                    <template v-slot:label>
                        使用場所
                        <small class="text-muted">
                            スペース区切りで複数入力可
                        </small>
                    </template>
                    <template v-slot:description>
                        「場所情報管理」にて登録した場所から選択できます
                        @can('staff.places.edit')
                            — <a href="{{ route('staff.places.create') }}" target="_blank">場所の新規作成</a>
                        @endcan
                    </template>
                    <tags-input input-name="places" v-bind:default-tags="{{ $default_places }}"
                        v-bind:autocomplete-items="{{ $places_autocomplete_items }}" add-only-from-autocomplete
                        placeholder="場所を追加"></tags-input>
                </list-view-form-group>
                @if (isset($circle->participationType->form))
                    <list-view-form-group>
                        <template v-slot:label>カスタムフォームへの回答</template>
                        @if (Auth::user()->cannot('staff.forms.answers.edit'))
                            <div class="text-muted">
                                <i class="fa fa-info-circle fa-fw" aria-hidden="true"></i>
                                <b>
                                    申請フォームへの回答の編集が許可されていないため、カスタムフォームへの回答の編集はできません。
                                    カスタムフォームへの回答を編集したい場合は、{{ config('app.name') }}の管理者へお問い合わせください。
                                </b>
                            </div>
                        @else
                            <a href="{{ route('staff.forms.answers.create', ['form' => $circle->participationType->form, 'circle' => $circle]) }}"
                                class="btn is-primary" target="_blank">
                                回答の内容を表示・編集
                            </a>
                        @endif
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
                        企画をタグで分類できます(例 : <app-badge primary>ステージ企画</app-badge>、<app-badge primary>模擬店</app-badge>、<app-badge
                            primary>講義棟教室</app-badge>、<app-badge primary>食品販売</app-badge>)。<br>
                        タグに応じて、閲覧可能なお知らせ、回答可能な申請フォームを制限することもできます。
                        @cannot('staff.tags.edit')
                            <br>
                            <i class="fas fa-info-circle fa-fw"></i>
                            <b>企画タグの編集が許可されていないため、ここでは作成済みの企画タグのみを指定できます。企画タグを新しく作成したい場合は、{{ config('app.name') }}の管理者へお問い合わせください。</b>
                        @endcannot
                    </template>
                    <tags-input input-name="tags" v-bind:default-tags="{{ $default_tags }}"
                        v-bind:autocomplete-items="{{ $tags_autocomplete_items }}"
                        {{ Auth::user()->can('staff.tags.edit') ? '' : 'add-only-from-autocomplete' }}></tags-input>
                    @if ($errors->has('tags'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('tags') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <list-view>
                <template v-slot:title>企画のメンバー</template>
                <list-view-form-group label-for="leader">
                    <template v-slot:label>責任者の{{ config('portal.student_id_name') }}</template>
                    <input type="text" class="form-control @error('leader') is-invalid @enderror" id="leader"
                        name="leader" value="{{ old('leader', empty($leader) ? '' : $leader->student_id) }}">
                    @if ($errors->has('leader'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('leader') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="members">
                    <template v-slot:label>学園祭係(副責任者)の{{ config('portal.student_id_name') }}</template>
                    <template
                        v-slot:description>{{ config('portal.student_id_name') }}を改行して入力することで複数の学園祭係を追加できます。{{ config('portal.users_number_to_submit_circle') - 1 }}人を下回っていても構いません。</template>
                    <textarea id="members" class="form-control @error('members') is-invalid @enderror" name="members" rows="3">{{ old('members', empty($members) ? '' : $members) }}</textarea>
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
                            <input class="form-radio__input" type="radio" name="status" id="statusRadios1"
                                value="pending"
                                {{ old('status', isset($circle) ? $circle->status : null) === null ? 'checked' : '' }}>
                            <strong>確認中</strong><br>
                            <span class="text-muted">ユーザーには参加登録が確認中である旨が表示されます</span>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="status" id="statusRadios2"
                                value="approved"
                                {{ old('status', isset($circle) ? $circle->status : null) === 'approved' ? 'checked' : '' }}>
                            <strong>受理</strong><br>
                            <span class="text-muted">参加登録を受理します。当該企画は申請機能を利用できるようになります</span>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="status" id="statusRadios3"
                                value="rejected"
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
                <list-view-form-group>
                    <template v-slot:label>
                        不受理に関する詳細(ユーザーに通知)&nbsp;
                        <app-badge outline muted>Markdown</app-badge>
                    </template>
                    <template v-slot:description>
                        この内容はユーザーに通知されます。参加登録を不受理とする際、ユーザーに伝達したい事項があれば入力してください。
                    </template>
                    <markdown-editor input-name="status_reason"
                        default-value="{{ old('status_reason', empty($circle) ? '' : $circle->status_reason) }}">
                    </markdown-editor>
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
                    <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes" rows="5">{{ old('notes', empty($circle) ? '' : $circle->notes) }}</textarea>
                    @if ($errors->has('notes'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('notes') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <app-fixed-form-footer>
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </app-fixed-form-footer>
        </app-container>
    </form>
@endsection
