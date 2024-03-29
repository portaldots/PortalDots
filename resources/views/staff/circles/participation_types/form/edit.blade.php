@extends('layouts.app')

@section('title', "{$participation_type->name} — 参加種別")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.circles.index') }}">
        企画情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    @include('includes.staff_circles_tab_strip')
    <app-container>
        <form
            action="{{ route('staff.circles.participation_types.form.update', ['participation_type' => $participation_type]) }}"
            method="post">
            @csrf
            @method('patch')
            <list-view>
                <template v-slot:title>企画参加登録のカスタムフォーム</template>
                <template v-slot:description>企画参加登録フォームに独自の設問やテキストを追加できます</template>
                <list-view-action-btn
                    href="{{ route('staff.circles.participation_types.form.editor', ['participation_type' => $participation_type]) }}"
                    icon-class="far fa-edit">
                    フォームエディターを開く
                </list-view-action-btn>
            </list-view>
            <list-view>
                <template v-slot:title>企画参加登録の受付</template>
                <list-view-form-group>
                    <template v-slot:label>参加登録画面の公開設定</template>
                    <template v-slot:description>
                        この設定がオンの場合、かつ受付期間内のとき、ユーザーに企画参加登録画面が表示されます。
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
            <app-accordion>
                <template v-slot:summary>
                    参加登録前に表示する内容
                </template>
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
            </app-accordion>
            <app-accordion>
                <template v-slot:summary>
                    参加登録の提出後に表示する内容
                </template>
                <list-view>
                    <list-view-form-group>
                        <template v-slot:label>
                            参加登録の提出後に表示する内容&nbsp;
                            <app-badge outline muted>Markdown</app-badge>
                        </template>
                        <template v-slot:description>
                            参加登録を完了した方に向けて表示するメッセージを設定できます。この内容は、参加登録の提出ユーザーに自動で送信されるメールにも表示されます。
                        </template>
                        <markdown-editor input-name="form_confirmation_message"
                            default-value="{{ old('form_confirmation_message', $participation_type->form->confirmation_message) }}">
                        </markdown-editor>
                        @error('form_confirmation_message')
                            <template v-slot:invalid>{{ $message }}</template>
                        @enderror
                    </list-view-form-group>
                </list-view>
            </app-accordion>
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
