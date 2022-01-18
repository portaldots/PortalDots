@extends('layouts.app')

@section('title', empty($form) ? '新規作成 — 申請' : "{$form->name} — 申請")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.forms.index') }}">
        申請管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ empty($form) ? route('staff.forms.store') : route('staff.forms.update', $form) }}">
        @method(empty($form) ? 'post' : 'patch' )
        @csrf

        <app-header>
            @if (empty($form))
                <template v-slot:title>フォームを新規作成</template>
            @endif
            @isset($form)
                <template v-slot:title>フォームを編集</template>
                <div>フォームID : {{ $form->id }}</div>
            @endisset
        </app-header>

        <app-container>
            @isset($form)
                <list-view>
                    <list-view-action-btn href="{{ route('staff.forms.editor', ['form' => $form]) }}" icon-class="far fa-edit"
                        newtab>
                        フォームエディターを開く
                    </list-view-action-btn>
                </list-view>
            @endisset

            <list-view>
                <list-view-form-group label-for="name">
                    <template v-slot:label>
                        フォーム名
                        <app-badge danger>必須</app-badge>
                    </template>
                    <input id="name" class="form-control @error('name') is-invalid @enderror" type="text" name="name"
                        value="{{ old('name', empty($form) ? '' : $form->name) }}" required>
                    @if ($errors->has('name'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('name') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="max_answers">
                    <template v-slot:label>
                        企画毎に回答可能とする回答数
                        <app-badge danger>必須</app-badge>
                    </template>
                    <template v-slot:description>
                        通常は「1」にします。1企画がこのフォームに対し複数の回答を作成できるようにするには、2以上の値を入力してください。
                    </template>
                    <input id="name" class="form-control @error('max_answers') is-invalid @enderror" type="number"
                        name="max_answers" value="{{ old('max_answers', empty($form) ? 1 : $form->max_answers) }}" min="1"
                        required>
                    @if ($errors->has('max_answers'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('max_answers') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="open_at">
                    <template v-slot:label>
                        受付開始日時
                        <app-badge danger>必須</app-badge>
                    </template>
                    <template v-slot:description>
                        フォームへの回答受付を開始する日時。
                    </template>
                    <input id="open_at" type="datetime-local" class="form-control @error('open_at') is-invalid @enderror"
                        name="open_at"
                        value="{{ old('open_at', isset($form) ? $form->open_at->format('Y-m-d\TH:i') : '') }}" required>
                    @error('open_at')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="close_at">
                    <template v-slot:label>
                        受付終了日時
                        <app-badge danger>必須</app-badge>
                    </template>
                    <template v-slot:description>
                        フォームへの回答受付を終了する日時。
                    </template>
                    <input id="close_at" type="datetime-local" class="form-control @error('close_at') is-invalid @enderror"
                        name="close_at"
                        value="{{ old('close_at', isset($form) ? $form->close_at->format('Y-m-d\TH:i') : '') }}"
                        required>
                    @error('close_at')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group>
                    <template v-slot:label>公開設定</template>
                    <template v-slot:description>
                        フォームの内容を公開した場合でも、上記の受付期間内ではない場合、ユーザーはフォームに回答したり、回答内容を編集したりできません。
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
                    <template v-slot:label>フォームへ回答可能なユーザー</template>
                    <template v-slot:description>
                        空欄の場合、企画に所属するユーザー全員がフォームに回答できます。
                        タグを指定した場合、指定したタグのうち、1つ以上該当する企画がフォームに回答できます。
                    </template>
                    <tags-input input-name="answerable_tags" placeholder="企画タグを指定"
                        placeholder-empty="企画タグを指定 (空欄の場合、企画に所属するユーザー全員が回答可能)" v-bind:default-tags="{{ $default_tags }}"
                        v-bind:autocomplete-items="{{ $tags_autocomplete_items }}" add-only-from-autocomplete>
                    </tags-input>
                    @if ($errors->has('answerable_tags'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('answerable_tags') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group>
                    <template v-slot:label>
                        フォームの説明&nbsp;
                        <app-badge outline muted>Markdown</app-badge>
                    </template>
                    <markdown-editor input-name="description"
                        default-value="{{ old('description', empty($form) ? '' : $form->description) }}">
                    </markdown-editor>
                    @if ($errors->has('description'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('description') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <app-fixed-form-footer>
                @isset($form)
                    <button type="submit" class="btn is-primary is-wide">保存</button>
                @else
                    <button type="submit" class="btn is-primary is-wide">次に、フォームエディターで設問を作成する</button>
                @endisset
            </app-fixed-form-footer>
        </app-container>
    </form>
@endsection
