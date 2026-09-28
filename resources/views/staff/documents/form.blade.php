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
        @method(empty($document) ? 'post' : 'patch')
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
                        @empty($document)
                            ファイル
                            <app-badge danger>必須</app-badge>
                        @else
                            新しい版のファイル
                        @endempty
                    </template>
                    @isset($document)
                        <template v-slot:description>
                            <a href="{{ route('staff.documents.show', ['document' => $document]) }}" target="_blank"
                                rel="noopener noreferrer">アップロード済ファイルを表示</a> {{ strtoupper($document->extension) }}ファイル •
                            @filesize($document->size)
                            ファイルを選択すると新しい版として追加され、既存の版は残ります。
                        </template>
                    @endisset
                    <input id="file" class="form-control @error('file') is-invalid @enderror" type="file"
                        name="file" @empty($document) required @endempty>
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
                    <input id="name" class="form-control @error('name') is-invalid @enderror" type="text"
                        name="name" value="{{ old('name', empty($document) ? '' : $document->name) }}" required>
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
                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description">{{ old('description', empty($document) ? '' : $document->description) }}</textarea>
                    @if ($errors->has('description'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('description') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>
            @isset($document)
                @if ($document->versions->isNotEmpty())
                    <list-view>
                        <template v-slot:title>版の履歴</template>
                        @foreach ($document->versions as $version)
                            <list-view-item no-border>
                                <template v-slot:title>第{{ $version->version }}版</template>
                                <template v-slot:meta>
                                    @datetime($version->created_at) アップロード
                                    @if ($version->uploadedBy)
                                        ・{{ $version->uploadedBy->name }}
                                    @endif
                                    ・<a
                                        href="{{ route('staff.documents.versions.show', ['document' => $document, 'version' => $version]) }}"
                                        target="_blank" rel="noopener noreferrer">ダウンロード</a>
                                </template>
                            </list-view-item>
                        @endforeach
                    </list-view>
                @endif
            @endisset
            <list-view>
                <list-view-form-group>
                    <template v-slot:label>公開設定</template>
                    <div class="form-radio">
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="is_public" id="isPublicRadios1"
                                value="1"
                                {{ (bool) old('is_public', isset($document) ? $document->is_public : true) === true ? 'checked' : '' }}>
                            <strong>公開</strong>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="is_public" id="isPublicRadios2"
                                value="0"
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
                    <template v-slot:label>公開範囲</template>
                    <div class="form-radio">
                        @if (in_array('everyone', $allowed_audiences))
                            <label class="form-radio__label">
                                <input class="form-radio__input" type="radio" name="audience"
                                    id="audienceRadiosEveryone" value="everyone"
                                    {{ old('audience', empty($document) ? 'everyone' : $document->audience) === 'everyone' ? 'checked' : '' }}>
                                <strong>誰でも（ログイン不要）</strong>
                            </label>
                        @endif
                        @if (in_array('signed_in', $allowed_audiences))
                            <label class="form-radio__label">
                                <input class="form-radio__input" type="radio" name="audience"
                                    id="audienceRadiosSignedIn" value="signed_in"
                                    {{ old('audience', empty($document) ? 'everyone' : $document->audience) === 'signed_in' ? 'checked' : '' }}>
                                <strong>ログインしているユーザー全員</strong>
                            </label>
                        @endif
                        @if (in_array('selected', $allowed_audiences))
                            <label class="form-radio__label">
                                <input class="form-radio__input" type="radio" name="audience"
                                    id="audienceRadiosSelected" value="selected"
                                    {{ old('audience', empty($document) ? 'everyone' : $document->audience) === 'selected' ? 'checked' : '' }}>
                                <strong>選んだタグ・企画のみ</strong>
                            </label>
                        @endif
                    </div>
                    @if ($errors->has('audience'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('audience') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group>
                    <template v-slot:label>閲覧可能なタグ</template>
                    <template v-slot:description>
                        公開範囲が「選んだタグ・企画のみ」の場合のみ有効です。
                        指定したタグのうち、1つ以上該当する企画に公開されます。
                    </template>
                    <tags-input input-name="viewable_tags" placeholder="企画タグを指定"
                        v-bind:default-tags="{{ $default_tags }}"
                        v-bind:autocomplete-items="{{ $tags_autocomplete_items }}" add-only-from-autocomplete>
                    </tags-input>
                    @if ($errors->has('viewable_tags'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('viewable_tags') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group>
                    <template v-slot:label>閲覧可能な企画</template>
                    <template v-slot:description>
                        公開範囲が「選んだタグ・企画のみ」の場合のみ有効です。
                    </template>
                    <tags-input input-name="viewable_circles" placeholder="企画を指定"
                        v-bind:default-tags="{{ $default_circles }}"
                        v-bind:autocomplete-items="{{ $circles_autocomplete_items }}" add-only-from-autocomplete>
                    </tags-input>
                    @if ($errors->has('viewable_circles'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('viewable_circles') as $message)
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
                    <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes" rows="5">{{ old('notes', empty($document) ? '' : $document->notes) }}</textarea>
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

    @isset($document)
        <app-container>
            <list-view>
                <template v-slot:title>確認依頼</template>
                <template v-slot:description>
                    指定した企画に、現在の版を確認するよう依頼します。新しい版を追加すると、依頼済みの企画はすべて新しい版の確認待ちに戻ります。
                </template>
                <list-view-form-group>
                    <template v-slot:label>企画を追加</template>
                    <form method="post"
                        action="{{ route('staff.documents.approvals.store', ['document' => $document]) }}">
                        @csrf
                        <tags-input input-name="circles" placeholder="企画を指定" v-bind:default-tags="[]"
                            v-bind:autocomplete-items="{{ $circles_autocomplete_items }}" add-only-from-autocomplete>
                        </tags-input>
                        @if ($errors->has('circles'))
                            <template v-slot:invalid>
                                @foreach ($errors->get('circles') as $message)
                                    <div>{{ $message }}</div>
                                @endforeach
                            </template>
                        @endif
                        <button type="submit" class="btn is-primary is-sm">依頼</button>
                    </form>
                </list-view-form-group>
            </list-view>

            <list-view>
                <template v-slot:title>依頼済みの企画（{{ count($document_approvals) }}企画）</template>
                @if (count($document_approvals) === 0)
                    <list-view-empty icon-class="fas fa-users" text="確認を依頼した企画はありません"></list-view-empty>
                @else
                    @foreach ($document_approvals as $approval)
                        <list-view-item>
                            <template v-slot:title>
                                {{ $approval->circle->name }}
                                @if ($approval->status === \App\Eloquents\DocumentApproval::STATUS_APPROVED)
                                    <app-badge success>{{ $approval->staffStatusLabel() }}</app-badge>
                                @elseif ($approval->status === \App\Eloquents\DocumentApproval::STATUS_CHANGES_REQUESTED)
                                    <app-badge danger>{{ $approval->staffStatusLabel() }}</app-badge>
                                @else
                                    <app-badge primary>{{ $approval->staffStatusLabel() }}</app-badge>
                                @endif
                            </template>
                            <template v-slot:meta>
                                対象 : 第{{ $approval->documentVersion->version }}版
                                @if ($approval->status === \App\Eloquents\DocumentApproval::STATUS_CHANGES_REQUESTED && !empty(optional($approval->decisions->first())->comment))
                                    <br>
                                    修正依頼の内容 : {{ $approval->decisions->first()->comment }}
                                @endif
                            </template>
                            <form-with-confirm
                                action="{{ route('staff.documents.approvals.destroy', ['document' => $document, 'circle' => $approval->circle]) }}"
                                method="post" confirm-message="「{{ $approval->circle->name }}」への確認依頼を取り消しますか？">
                                @method('delete')
                                @csrf
                                <button type="submit" class="btn is-danger is-sm">依頼を取り消す</button>
                            </form-with-confirm>
                            <app-accordion>
                                <template v-slot:summary>履歴を見る</template>
                                @foreach ($approval->decisions as $decision)
                                    <p>
                                        第{{ $decision->documentVersion->version }}版 ・ {{ $decision->staffStatusLabel() }}
                                        ・ @datetime($decision->created_at)
                                        @if ($decision->decidedBy)
                                            ・{{ $decision->decidedBy->name }}
                                        @endif
                                        @if (!empty($decision->comment))
                                            <br>{{ $decision->comment }}
                                        @endif
                                    </p>
                                @endforeach
                            </app-accordion>
                        </list-view-item>
                    @endforeach
                @endif
            </list-view>
        </app-container>
    @endisset
@endsection
