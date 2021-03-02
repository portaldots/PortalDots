@extends('layouts.no_drawer')

@section('title', '企画参加登録の設定')

@section('navbar')
    <app-nav-bar-back href="{{ url('home_staff/circles') }}" data-turbolinks="false">
        企画情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>企画参加登録のカスタムフォーム</template>
            <template v-slot:description>企画参加登録フォームに独自の設問やテキストを追加できます</template>
            <list-view-action-btn href="{{ route('staff.forms.editor', ['form' => $form]) }}" icon-class="far fa-edit"
                newtab>
                フォームエディターを開く
            </list-view-action-btn>
        </list-view>
        <form action="{{ route('staff.circles.custom_form.update') }}" method="post">
            @csrf
            @method('patch')
            <list-view>
                <template v-slot:title>企画参加登録の設定</template>
                <list-view-form-group label-for="users_number_to_submit_circle">
                    <template v-slot:label>企画参加登録を提出するために必要な企画担当者の最低人数</template>
                    <template v-slot:description>
                        企画責任者と学園祭係(副責任者)の合計人数です。企画責任者に加え、学園祭係(副責任者)の登録を最低2人必要とする場合、<code>3</code>と入力してください
                    </template>
                    <input id="users_number_to_submit_circle" type="number"
                        class="form-control @error('users_number_to_submit_circle') is-invalid @enderror"
                        name="users_number_to_submit_circle" min="1" step="1"
                        value="{{ old('users_number_to_submit_circle', $users_number_to_submit_circle) }}" required>
                    @error('users_number_to_submit_circle')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="open_at">
                    <template v-slot:label>受付開始日時</template>
                    <template v-slot:description>
                        企画参加登録を受付開始する日時。この日時以前は、企画参加登録画面はユーザーから非表示になります
                    </template>
                    <input id="open_at" type="datetime-local" class="form-control @error('open_at') is-invalid @enderror"
                        name="open_at"
                        value="{{ old('open_at', isset($form) ? $form->open_at->format('Y-m-d\TH:i') : '') }}" required>
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
                        value="{{ old('close_at', isset($form) ? $form->close_at->format('Y-m-d\TH:i') : '') }}" required>
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
                                value="1"
                                {{ old('is_public', (isset($form) ? $form->is_public : false) === true) ? 'checked' : '' }}>
                            公開する
                        </label>
                    </div>

                    @error('is_public')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group>
                    <template v-slot:label>
                        参加登録前に表示する内容&nbsp;
                        <app-badge outline muted>Markdown</app-badge>
                    </template>
                    <template v-slot:description>
                        参加登録のページにここで設定した内容を表示できます。規約の表示などにご利用ください。
                    </template>
                    <markdown-editor input-name="description" default-value="{{ old('description', isset($form) ? $form->description : '') }}"></markdown-editor>
                    @error('description')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
            </list-view>
            <div class="text-center py-spacing-md">
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </div>
        </form>
    </app-container>
@endsection
