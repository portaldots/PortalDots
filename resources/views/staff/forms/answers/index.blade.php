@extends('layouts.app')

@section('title', '申請管理')

@section('top_alert_props', 'container-fluid')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.forms.index') }}">
        申請管理
    </app-nav-bar-back>
@endsection

@section('content')
    @include('includes.staff_answers_tab_strip', ['form_id' => $form->id])
    <app-header container-fluid>
        <template v-slot:title>
            {{ $form->name }}
            @if ($form->is_public)
                <app-badge success strong>公開</app-badge>
            @else
                <app-badge danger strong>非公開</app-badge>
            @endif
        </template>
        <div data-turbolinks="false" class="markdown">
            <p class="text-muted">
                受付期間 : @datetime($form->open_at)〜@datetime($form->close_at)
                @if (!$form->isOpen())
                    —
                    <strong class="text-danger">
                        <i class="fas fa-info-circle"></i>
                        受付期間外
                    </strong>
                @endif
                <br />
                @if (!$form->answerableTags->isEmpty())
                    回答可能なタグ :
                    {{ $form->answerableTags->implode('name', ',') }}
                @else
                    全体に公開 — 企画に所属しているユーザー全員が回答可能
                @endif
            </p>
            @markdown($form->description)
        </div>
    </app-header>
    <data-grid api-url="{{ route('staff.forms.answers.api', ['form' => $form]) }}"
        v-bind:key-translations="{
            id: '回答ID',
            circle_id: '提出した企画',
            'circle_id.id': '企画ID',
            'circle_id.name': '企画名',
            'circle_id.name_yomi': '企画名(よみ)',
            'circle_id.group_name': '企画を出店する団体の名称',
            'circle_id.group_name_yomi': '企画を出店する団体の名称(よみ)',
            'circle_id.submitted_at': '参加登録提出日時',
            'circle_id.status_set_at': '登録受理状況設定日時',
            'circle_id.created_at': '作成日時',
            'circle_id.updated_at': '更新日時',
            'circle_id.notes': 'スタッフ用メモ',
            created_at: '作成日時',
            updated_at: '更新日時',
            @if (isset($form)) @foreach ($form->questions as $question)
                    {{ App\GridMakers\AnswersGridMaker::FORM_QUESTIONS_KEY_PREFIX . $question->id }}: '{{ $question->name }}',
                @endforeach @endif
        }">
        <template v-slot:toolbar>
            <a class="btn is-primary" href="{{ route('staff.forms.answers.create', ['form' => $form]) }}">
                <i class="fas fa-plus fa-fw"></i>
                新規回答
            </a>
            <a class="btn is-primary-inverse is-no-border"
                href="{{ route('staff.forms.answers.export', ['form' => $form]) }}" download>
                <i class="fas fa-file-csv fa-fw"></i>
                CSVで出力
            </a>
            <a class="btn is-primary-inverse is-no-border"
                href="{{ route('staff.forms.answers.uploads.index', ['form' => $form]) }}">
                <i class="far fa-file-archive fa-fw"></i>
                ファイルを一括ダウンロード
            </a>
            @if (empty($form->participationType))
                <a class="btn is-primary-inverse is-no-border"
                    href="{{ route('staff.forms.not_answered', ['form' => $form]) }}">
                    未提出企画を表示
                </a>
            @endif
        </template>
        <template v-slot:activities="{ row }">
            <icon-button
                v-bind:href="`{{ route('staff.forms.answers.edit', ['form' => $form, 'answer' => '%%ANSWER%%']) }}`.replace('%%ANSWER%%', row['id'])"
                title="編集">
                <i class="fas fa-pencil-alt fa-fw"></i>
            </icon-button>
            <form-with-confirm
                v-bind:action="`{{ route('staff.forms.answers.destroy', ['form' => $form, 'answer' => '%%ANSWER%%']) }}`.replace('%%ANSWER%%', row['id'])"
                method="post"
                v-bind:confirm-message="`この回答を削除しますか？

• 回答が削除されたという通知は${row['circle_id'].group_name}には送信されません。`" inline>
                @method('delete')
                @csrf
                <icon-button submit title="削除">
                    <i class="fas fa-trash fa-fw"></i>
                </icon-button>
            </form-with-confirm>
        </template>
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'circle_id'">
                {{-- 企画 --}}
                <b><ruby>@{{ row[keyName].name }}<rt>@{{ row[keyName].name_yomi }}</rt></ruby></b> —
                <ruby class="text-muted">@{{ row[keyName].group_name }}<rt>@{{ row[keyName].group_name_yomi }}</rt></ruby> (企画ID :
                @{{ row[keyName].id }})
            </template>
            <template v-else-if="keyName.includes('{{ App\GridMakers\AnswersGridMaker::FORM_QUESTIONS_KEY_PREFIX }}')">
                {{-- フォームへの回答 --}}
                <template v-if="row[keyName] && row[keyName].file_url">
                    <a v-bind:href="row[keyName].file_url" target="_blank" rel="noopener noreferrer">表示</a>
                </template>
                <template v-if="row[keyName] && row[keyName].join">
                    @{{ row[keyName].join(', ') }}
                </template>
            </template>
            <template v-else>
                @{{ row[keyName] }}
            </template>
        </template>
    </data-grid>
@endsection
