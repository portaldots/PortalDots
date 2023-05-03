@extends('layouts.app')

@section('title', "{$participation_type->name} — 参加種別")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.circles.index') }}">
        企画情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    @include('includes.staff_circles_tab_strip')
    <app-header>
        <template v-slot:title>参加種別を編集</template>
        <div>参加種別ID : {{ $participation_type->id }}</div>
        <form-with-confirm
            action="{{ route('staff.circles.participation_types.destroy', ['participation_type' => $participation_type]) }}"
            method="post" confirm-message="本当にこの参加種別を削除しますか？この参加種別に紐づく企画もすべて削除されます。">
            @method('delete')
            @csrf
            <button type="submit" class="btn is-danger is-sm">
                この参加種別を削除
            </button>
        </form-with-confirm>
    </app-header>
    <app-container>
        <form action="{{ route('staff.circles.participation_types.update', ['participation_type' => $participation_type]) }}"
            method="post">
            @csrf
            @method('patch')
            <list-view>
                <list-view-form-group label-for="name">
                    <template v-slot:label>参加種別名</template>
                    <template v-slot:description>
                        一般ユーザーに対して表示されます。例：模擬店、ステージ、など。
                    </template>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                        name="name" value="{{ old('name', $participation_type->name) }}" required>
                    @error('name')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="description">
                    <template v-slot:label>説明</template>
                    <template v-slot:description>一般ユーザーに対して表示されます。</template>
                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description"
                        rows="3">{{ old('description', $participation_type->description) }}</textarea>
                    @if ($errors->has('description'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('description') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>
            <list-view>
                <template v-slot:title>企画参加登録のカスタムフォーム</template>
                <template v-slot:description>企画参加登録フォームに独自の設問やテキストを追加できます</template>
                <list-view-action-btn href="{{ route('staff.forms.editor', ['form' => $participation_type->form]) }}"
                    icon-class="far fa-edit" newtab>
                    フォームエディターを開く
                </list-view-action-btn>
            </list-view>
            <list-view>
                <template v-slot:title>企画参加登録を提出するために必要な企画担当者の人数</template>
                <template v-slot:description>
                    企画責任者を含むメンバー数の合計人数です。企画責任者に加え、学園祭係(副責任者)の登録を最低2人必要とする場合、<code>3</code>と入力してください。個人での参加を許可する場合は<code>1</code>と入力してください。
                </template>
                <list-view-form-group label-for="users_count_min">
                    <template v-slot:label>最低人数</template>
                    <input id="users_count_min" type="number"
                        class="form-control @error('users_count_min') is-invalid @enderror" name="users_count_min"
                        min="1" step="1"
                        value="{{ old('users_count_min', $participation_type->users_count_min) }}" required>
                    @error('users_count_min')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="users_count_max">
                    <template v-slot:label>最大人数</template>
                    <template v-slot:description>
                        グループではなく個人からの参加登録のみを受け付けるには、<code>1</code>と入力してください。
                    </template>
                    <input id="users_count_max" type="number"
                        class="form-control @error('users_count_max') is-invalid @enderror" name="users_count_max"
                        min="1" step="1"
                        value="{{ old('users_count_max', $participation_type->users_count_max) }}" required>
                    @error('users_count_max')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
            </list-view>
            <list-view>
                <template v-slot:title>企画参加登録の受付</template>
                <list-view-form-group label-for="open_at">
                    <template v-slot:label>受付開始日時</template>
                    <template v-slot:description>
                        企画参加登録を受付開始する日時。この日時以前は、企画参加登録画面はユーザーから非表示になります
                    </template>
                    <input id="open_at" type="datetime-local" class="form-control @error('open_at') is-invalid @enderror"
                        name="open_at"
                        value="{{ old('open_at', $participation_type->form->open_at->format('Y-m-d\TH:i')) }}" required>
                    @error('open_at')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="close_at">
                    <template v-slot:label>受付終了日時</template>
                    <template v-slot:description>
                        企画参加登録を受付終了する日時。この日時以降、企画参加登録画面はユーザーから非表示になります
                    </template>
                    <input id="close_at" type="datetime-local" class="form-control @error('close_at') is-invalid @enderror"
                        name="close_at"
                        value="{{ old('close_at', $participation_type->form->close_at->format('Y-m-d\TH:i')) }}" required>
                    @error('close_at')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group>
                    <template v-slot:label>参加登録画面の公開設定</template>
                    <template v-slot:description>
                        この設定がオンの場合かつ上記の受付期間内のとき、ユーザーに企画参加登録画面が表示されます。
                    </template>

                    <div class="form-checkbox">
                        <label class="form-checkbox__label">
                            <input id="is_public" type="checkbox"
                                class="form-checkbox__input @error('is_public') is-invalid @enderror" name="is_public"
                                value="1" @checked(old('is_public', $participation_type->form->is_public === true))>
                            公開する
                        </label>
                    </div>

                    @error('is_public')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
            </list-view>
            <list-view>
                <list-view-form-group>
                    <template v-slot:label>
                        参加登録前に表示する内容&nbsp;
                        <app-badge outline muted>Markdown</app-badge>
                    </template>
                    <template v-slot:description>
                        参加登録のページにここで設定した内容を表示できます。規約の表示などにご利用ください。
                    </template>
                    <markdown-editor input-name="form_description"
                        default-value="{{ old('form_description', $participation_type->form->description) }}">
                    </markdown-editor>
                    @error('form_description')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
            </list-view>
            <app-fixed-form-footer>
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </app-fixed-form-footer>
        </form>
        <list-view>
            <template v-slot:title>企画参加登録機能について</template>
            <list-view-card class="markdown">
                @include('includes.circles_custom_form_instructions')
            </list-view-card>
        </list-view>
    </app-container>
@endsection
