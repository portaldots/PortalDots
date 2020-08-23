@extends('layouts.no_drawer')

@section('title', empty($form) ? '新規作成 — フォーム' : "{$form->name} — フォーム")

@section('navbar')
    <app-nav-bar-back inverse href="{{ url('/home_staff/applications') }}" data-turbolinks="false">
        申請管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ empty($form) ? route('staff.documents.store') : route('staff.documents.update', $form) }}" enctype="multipart/form-data">
        @method(empty($form) ? 'post' : 'patch' )
        @csrf

        <app-header>
            @if (empty($form))
                <template v-slot:title>フォームを新規作成</template>
            @endif
            @isset ($form)
                <template v-slot:title>フォームを編集</template>
                <div>フォームID : {{ $form->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
                <list-view-form-group label-for="name">
                    <template v-slot:label>
                        フォーム名
                        <app-badge danger>必須</app-badge>
                    </template>
                    <input id="name" class="form-control @error('name') is-invalid @enderror" type="text"
                        name="name" value="{{ old('name', empty($form) ? '' : $form->name) }}"
                        required>
                    @if ($errors->has('name'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('name') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group>
                    <template v-slot:label>
                        説明
                        <app-badge outline muted>Markdown</app-badge>
                    </template>
                    <markdown-editor input-name="description" default-value="{{ old('description', empty($form) ? '' : $form->description) }}"></markdown-editor>
                    @if ($errors->has('description'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('description') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>
            <list-view>
                <list-view-form-group>
                    <template v-slot:label>公開設定</template>
                    <div class="form-radio">
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="is_public" id="isPublicRadios1" value="1"
                                {{ (bool)old('is_public', isset($form) ? $form->is_public : true) === true ? 'checked' : '' }}>
                            <strong>公開</strong>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="is_public" id="isPublicRadios2" value="0"
                                {{ (bool)old('is_public', isset($form) ? $form->is_public : true) === false ? 'checked' : '' }}>
                            <strong>非公開</strong>
                        </label>
                    </div>
                    @if ($errors->has('is_public'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('is_public') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>
            <list-view>
                <list-view-form-group>
                    <template v-slot:label>このフォームは重要かどうか</template>
                    <div class="form-radio">
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="is_important" id="isImportantRadios1" value="1"
                                {{ (bool)old('is_important', isset($form) ? $form->is_important : false) === true ? 'checked' : '' }}>
                            <strong>重要</strong><br>
                            <span class="text-muted">ユーザーにはフォームが強調されて表示されます</span>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="is_important" id="isImportantRadios2" value="0"
                                {{ (bool)old('is_important', isset($form) ? $form->is_important : false) === false ? 'checked' : '' }}>
                            <strong>重要ではない</strong>
                        </label>
                    </div>
                    @if ($errors->has('is_important'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('is_important') as $message)
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
                        rows="5">{{ old('notes', empty($form) ? '' : $form->notes) }}</textarea>
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
