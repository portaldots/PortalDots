@extends('layouts.app')

@section('title', empty($document) ? '新規作成 — 配布資料' : "{$document->name} — 配布資料")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.documents.index') }}">
        配布資料情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post"
        action="{{ empty($document) ? route('staff.documents.store') : route('staff.documents.update', $document) }}"
        enctype="multipart/form-data">
        @method(empty($document) ? 'post' : 'patch' )
        @csrf

        <app-header>
            @if (empty($document))
                <template v-slot:title>配布資料を新規作成</template>
            @endif
            @isset($document)
                <template v-slot:title>配布資料を編集</template>
                <div>配布資料ID : {{ $document->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
                <list-view-form-group label-for="file">
                    <template v-slot:label>
                        ファイル
                        @empty($document)
                            <app-badge danger>必須</app-badge>
                        @endempty
                    </template>
                    @isset($document)
                        <template v-slot:description>
                            <a href="{{ route('staff.documents.show', ['document' => $document]) }}" target="_blank"
                                rel="noopener">アップロード済ファイルを表示</a> {{ strtoupper($document->extension) }}ファイル •
                            @filesize($document->size)
                        </template>
                    @endisset
                    <input id="file" class="form-control @error('file') is-invalid @enderror" type="file" name="file"
                        @empty($document) required @endempty>
                    @if ($errors->has('file'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('file') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="name">
                    <template v-slot:label>
                        配布資料名
                        <app-badge danger>必須</app-badge>
                    </template>
                    <input id="name" class="form-control @error('name') is-invalid @enderror" type="text" name="name"
                        value="{{ old('name', empty($document) ? '' : $document->name) }}" required>
                    @if ($errors->has('name'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('name') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="description">
                    <template v-slot:label>説明</template>
                    <textarea id="description" class="form-control @error('description') is-invalid @enderror"
                        name="description">{{ old('description', empty($document) ? '' : $document->description) }}</textarea>
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
                                {{ (bool) old('is_public', isset($document) ? $document->is_public : true) === true ? 'checked' : '' }}>
                            <strong>公開</strong>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="is_public" id="isPublicRadios2" value="0"
                                {{ (bool) old('is_public', isset($document) ? $document->is_public : true) === false ? 'checked' : '' }}>
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
                    <template v-slot:label>この配布資料は重要かどうか</template>
                    <div class="form-radio">
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="is_important" id="isImportantRadios1"
                                value="1"
                                {{ (bool) old('is_important', isset($document) ? $document->is_important : false) === true ? 'checked' : '' }}>
                            <strong>重要</strong><br>
                            <span class="text-muted">ユーザーには配布資料が強調されて表示されます</span>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="is_important" id="isImportantRadios2"
                                value="0"
                                {{ (bool) old('is_important', isset($document) ? $document->is_important : false) === false ? 'checked' : '' }}>
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
                        rows="5">{{ old('notes', empty($document) ? '' : $document->notes) }}</textarea>
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
