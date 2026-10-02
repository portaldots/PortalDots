@extends('layouts.app')

@section('title', $form->name . ' — ' . term('form'))

@section('no_circle_selector', true)

@section('navbar')
    @if (!empty($answer) && count($answers) > 0 && $form->max_answers > 1)
        <app-nav-bar-back href="{{ route('forms.answers.create', ['form' => $form]) }}">
            回答の新規作成
        </app-nav-bar-back>
    @else
        <app-nav-bar-back href="{{ route('forms.index') }}">
            {{ term('form') }}
        </app-nav-bar-back>
    @endif
@endsection

@section('content')
    <form method="post"
        action="{{ empty($answer) ? route('forms.answers.store', [$form]) : route('forms.answers.update', [$form, $answer]) }}"
        enctype="multipart/form-data">
        @csrf

        @method(empty($answer) ? 'post' : 'patch')

        <input type="hidden" name="circle_id" value="{{ $circle->id }}">
        @if ($form->requires_review && !empty($answer))
            <input type="hidden" name="lock_version" value="{{ $answer->lock_version }}">
        @endif

        @php
            $is_review_locked = $form->requires_review && !empty($answer) &&
                $answer->review_status === \App\Eloquents\Answer::REVIEW_STATUS_ACCEPTED;
        @endphp

        <app-header>
            <template v-slot:title>{{ $form->name }}</template>
            <div data-turbolinks="false" class="markdown">
                <p class="text-muted">
                    受付期間 : @datetime($form->open_at)〜@datetime($form->close_at)
                    @if (!$form->isOpen())
                        —
                        <strong class="text-danger">
                            <i class="fas fa-info-circle"></i>
                            受付期間外です
                        </strong>
                    @endif
                </p>
                @php $dueAt = $form->effectiveDueDateFor($circle); @endphp
                @if (!$dueAt->equalTo($form->close_at) || $form->isOverdueFor($circle))
                    <p class="text-muted">
                        期限 : @datetime($dueAt)
                        @if ($form->isOverdueFor($circle))
                            <app-badge danger>期限切れ</app-badge>
                        @endif
                    </p>
                @endif
                @if ($form->audience === \App\Contracts\AudiencePolicy::SELECTED)
                    <p class="text-muted">
                        <app-badge primary outline>限定公開</app-badge>
                        このフォームは、限られた企画のみ回答可能です。
                    </p>
                @endif
                @markdown($form->description)
            </div>
        </app-header>

        <app-container>
            <list-view>
                <list-view-form-group>
                    <template v-slot:label>{{ term('form') }}{{ term('circle') }}名</template>
                    <input type="text" readonly value="{{ $circle->name }}({{ $circle->group_name }})"
                        class="form-control">
                    @if (empty($answer) &&
                            Auth::user()->circles()->approved()->count() > 1)
                        <template v-slot:append>
                            <a href="{{ route('circles.selector.show', ['redirect_to' => Request::path()]) }}">変更</a>
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            @if ($form->requires_review && !empty($answer))
                <list-view>
                    <list-view-card>
                        <p>
                            回答の状況 :
                            @if ($answer->review_status === \App\Eloquents\Answer::REVIEW_STATUS_ACCEPTED)
                                <app-badge success>完了</app-badge>
                            @elseif ($answer->review_status === \App\Eloquents\Answer::REVIEW_STATUS_RETURNED)
                                <app-badge danger>修正してください</app-badge>
                            @else
                                <app-badge primary>確認中</app-badge>
                            @endif
                        </p>
                        @if ($answer->review_status === \App\Eloquents\Answer::REVIEW_STATUS_RETURNED && !empty($answer->review_note))
                            <p class="text-danger">
                                <i class="fas fa-info-circle"></i>
                                差し戻し理由 : {{ $answer->review_note }}
                            </p>
                        @endif
                    </list-view-card>
                </list-view>

                <list-view>
                    <template v-slot:title>過去の提出</template>
                    @php $answer_revisions = $answer->revisions()->orderByDesc('revision')->get(); @endphp
                    @if ($answer_revisions->isEmpty())
                        <list-view-item href="{{ route('forms.answers.edit', ['form' => $form, 'answer' => $answer]) }}">
                            <template v-slot:title>第1版</template>
                            <template v-slot:meta>@datetime($answer->submitted_at ?? $answer->created_at)</template>
                        </list-view-item>
                    @else
                        @foreach ($answer_revisions as $_revision)
                            <list-view-item
                                href="{{ route('forms.answers.revisions.show', ['form' => $form, 'answer' => $answer, 'revision' => $_revision]) }}">
                                <template v-slot:title>第{{ $_revision->revision }}版</template>
                                <template v-slot:meta>@datetime($_revision->submitted_at)</template>
                            </list-view-item>
                        @endforeach
                    @endif
                </list-view>
            @endif

            {{-- $answers ← 企画 $circle が回答した全回答（回答新規作成画面で使用。変更画面でも使用可能） --}}
            {{-- $answer ← 編集対象の回答（回答変更画面で使用） --}}

            @if (empty($answer) && count($answers) > 0)
                <list-view>
                    <template v-slot:title>以前の回答を閲覧・変更</template>
                    <template v-slot:description>受付期間内に限り、回答の変更ができます</template>
                    @foreach ($answers as $_)
                        <list-view-item href="{{ route('forms.answers.edit', ['form' => $form, 'answer' => $_]) }}">
                            <template v-slot:title>
                                @datetime($_->created_at) に新規作成した回答 — 回答ID : {{ $_->id }}
                            </template>
                            @unless ($_->created_at->eq($_->updated_at))
                                <template v-slot:meta>回答の最終更新日時 : @datetime($_->updated_at)</template>
                            @endunless
                        </list-view-item>
                    @endforeach
                </list-view>
            @endif

            @if (isset($answer) && isset($form->confirmation_message) && $form->confirmation_message !== '')
                <list-view>
                    <list-view-card data-turbolinks="false" class="markdown">
                        @markdown($form->confirmation_message)
                    </list-view-card>
                </list-view>
            @endif

            <list-view>

                @if (empty($answer) && $form->max_answers > 1)
                    <template v-slot:title>回答を新規作成</template>
                    @if ($form->max_answers - count($answers) > 0)
                        <template
                            v-slot:description>貴企画はこの申請を、あと{{ $form->max_answers - count($answers) }}つ新規作成できます</template>
                    @else
                        <template
                            v-slot:description>回答数上限({{ $form->max_answers }}つ)に達したため、これ以上新規作成できません。以前の回答の編集は上記より可能です。</template>
                    @endif
                @endif
                @isset($answer)
                    <template v-slot:title>{{ ($form->isOpen() && !$is_review_locked) ? '回答を編集' : '回答を閲覧' }} — 回答ID : {{ $answer->id }}</template>
                    <template v-slot:description>回答の最終更新日時 : @datetime($form->updated_at)</template>
                @endisset

                @foreach ($questions as $question)
                    @include('includes.question', [
                        'is_disabled' =>
                            !$form->isOpen() || $is_review_locked || (empty($answer) && $form->max_answers <= count($answers)),
                    ])
                @endforeach
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide"
                    {{ !$form->isOpen() || $is_review_locked || (empty($answer) && $form->max_answers <= count($answers)) ? ' disabled' : '' }}>送信</button>
                @if (config('app.debug'))
                    <button type="submit" class="btn is-primary-inverse" formnovalidate>
                        <app-badge primary strong>開発モード</app-badge>
                        バリデーションせずに送信
                    </button>
                @endif
            </div>
        </app-container>
    </form>
@endsection
