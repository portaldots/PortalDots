@extends('layouts.app')

@section('title', empty($page) ? '新規作成 — お知らせ' : "{$page->title} — お知らせ")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.pages.index') }}">
        お知らせ管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ empty($page) ? route('staff.pages.store') : route('staff.pages.update', $page) }}">
        @method(empty($page) ? 'post' : 'patch' )
        @csrf

        <app-header>
            @if (empty($page))
                <template v-slot:title>お知らせを新規作成</template>
            @endif
            @isset ($page)
                <template v-slot:title>お知らせを編集</template>
                <div>お知らせID : {{ $page->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
                <list-view-form-group label-for="title">
                    <template v-slot:label>タイトル</template>
                    <input id="title" class="form-control @error('title') is-invalid @enderror" type="text" name="title"
                        value="{{ old('title', empty($page) ? '' : $page->title) }}" required>
                    @if ($errors->has('title'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('title') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group>
                    <template v-slot:label>
                        本文&nbsp;
                        <app-badge outline muted>Markdown</app-badge>
                    </template>
                    <markdown-editor input-name="body" default-value="{{ old('body', empty($page) ? '' : $page->body) }}"></markdown-editor>
                    @if ($errors->has('body'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('body') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <list-view>
                <list-view-form-group>
                    <template v-slot:label>お知らせを閲覧可能なユーザー</template>
                    <template v-slot:description>
                        空欄の場合、未ログインユーザーを含む全員に公開されます。
                        タグを指定した場合、指定したタグのうち、1つ以上該当する企画に公開されます。
                    </template>
                    <tags-input
                        input-name="viewable_tags"
                        placeholder="企画タグを指定"
                        placeholder-empty="企画タグを指定 (空欄の場合すべてのユーザーに公開)"
                        v-bind:default-tags="{{ $default_tags }}"
                        v-bind:autocomplete-items="{{ $tags_autocomplete_items }}"
                        add-only-from-autocomplete
                    ></tags-input>
                    @if ($errors->has('viewable_tags'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('viewable_tags') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <list-view>
                <list-view-form-group>
                    <div class="form-checkbox">
                        <label class="form-checkbox__label">
                            <input class="form-checkbox__input" type="checkbox" name="send_emails" value="1">
                            <strong>保存後にこのお知らせを「閲覧可能なユーザー」で指定したユーザー全員にメール配信</strong><br>
                            <span class="text-muted">このお知らせを保存したタイミングでの内容が配信されます。お知らせを編集しても、メール配信が完了するまで編集内容は反映されません。</span>
                        </label>
                    </div>
                    @if ($errors->has('send_emails'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('send_emails') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-card>
                    <i class="fas fa-exclamation-circle"></i>
                    メール配信機能を利用するには、予めサーバー側での設定(CRON)が必要です。
                </list-view-card>
            </list-view>

            <list-view>
                <list-view-form-group label-for="notes">
                    <template v-slot:label>スタッフ用メモ</template>
                    <template v-slot:description>ここに入力された内容はスタッフのみ閲覧できます。スタッフ内で共有したい事項を残しておくメモとしてご活用ください。</template>
                    <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes"
                        rows="5">{{ old('notes', empty($page) ? '' : $page->notes) }}</textarea>
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
