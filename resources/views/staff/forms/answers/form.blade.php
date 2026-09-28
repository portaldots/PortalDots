@extends('layouts.app')

@section('title', $form->name . ' — 申請')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.forms.answers.index', ['form' => $form]) }}">
        {{ $form->name }}
    </app-nav-bar-back>
@endsection

@section('content')
    @if ($form->requires_review && !empty($answer))
        <app-container>
            <list-view>
                <list-view-card>
                    <p>
                        回答の状況 :
                        @if ($answer->review_status === \App\Eloquents\Answer::REVIEW_STATUS_ACCEPTED)
                            <app-badge success>完了</app-badge>
                        @elseif ($answer->review_status === \App\Eloquents\Answer::REVIEW_STATUS_RETURNED)
                            <app-badge danger>差し戻し中</app-badge>
                        @elseif ($answer->review_status === \App\Eloquents\Answer::REVIEW_STATUS_SUBMITTED)
                            <app-badge primary>要確認</app-badge>
                        @else
                            <app-badge muted>未提出</app-badge>
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
                <template v-slot:title>確認操作</template>
                @if ($answer->review_status !== \App\Eloquents\Answer::REVIEW_STATUS_ACCEPTED)
                <list-view-card>
                    <form method="post"
                        action="{{ route('staff.forms.answers.accept', ['form' => $form, 'answer' => $answer]) }}">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="lock_version" value="{{ $answer->lock_version }}">
                        <button type="submit" class="btn is-primary">完了にする</button>
                    </form>
                </list-view-card>
                @endif
                <list-view-card>
                    <form method="post"
                        action="{{ route('staff.forms.answers.return', ['form' => $form, 'answer' => $answer]) }}">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="lock_version" value="{{ $answer->lock_version }}">
                        <list-view-form-group label-for="review_note">
                            <template v-slot:label>差し戻し理由</template>
                            <textarea id="review_note" name="review_note"
                                class="form-control @error('review_note') is-invalid @enderror" required>{{ old('review_note') }}</textarea>
                            @error('review_note')
                                <template v-slot:invalid>{{ $message }}</template>
                            @enderror
                        </list-view-form-group>
                        <button type="submit" class="btn is-primary-inverse">差し戻す</button>
                    </form>
                </list-view-card>
            </list-view>

            <list-view>
                <template v-slot:title>過去の提出</template>
                @php $staff_revisions = $answer->revisions()->orderByDesc('revision')->get(); @endphp
                @if ($staff_revisions->isEmpty())
                    <list-view-item
                        href="{{ route('staff.forms.answers.edit', ['form' => $form, 'answer' => $answer]) }}">
                        <template v-slot:title>第1版</template>
                        <template v-slot:meta>@datetime($answer->submitted_at ?? $answer->created_at)</template>
                    </list-view-item>
                @else
                    @foreach ($staff_revisions as $_revision)
                        <list-view-item
                            href="{{ route('staff.forms.answers.revisions.show', ['form' => $form, 'answer' => $answer, 'revision' => $_revision]) }}">
                            <template v-slot:title>第{{ $_revision->revision }}版</template>
                            <template v-slot:meta>@datetime($_revision->submitted_at)</template>
                        </list-view-item>
                    @endforeach
                @endif
            </list-view>
        </app-container>
    @endif

    <form method="post"
        action="{{ empty($answer) ? route('staff.forms.answers.store', [$form]) : route('staff.forms.answers.update', [$form, $answer]) }}"
        enctype="multipart/form-data">
        @csrf

        @method(empty($answer) ? 'post' : 'patch')

        <input type="hidden" name="circle_id" value="{{ $circle->id }}">

        <app-header>
            <template v-slot:title>{{ $form->name }}</template>
            <div data-turbolinks="false" class="markdown">
                @markdown($form->description)
            </div>
        </app-header>

        <app-container>
            <list-view>
                <list-view-item>
                    <template v-slot:title>申請企画名</template>
                    {{ $circle->name }}
                    —
                    {{-- TODO: あとでもうちょっといい感じのコードに書き直す --}}
                    <a href="{{ route('staff.forms.answers.create', ['form' => $form]) }}">変更</a>
                </list-view-item>
                <list-view-card>
                    @if ($form->is_public && empty($form->participationType))
                        <div class="text-danger">
                            <i class="fas fa-info-circle"></i>
                            スタッフとして回答します。この画面で回答を作成・編集すると、企画のメンバーには「スタッフによって回答が作成・編集された」という旨のメールが送信されます。
                        </div>
                    @else
                        <div class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            @if (isset($form->participationType))
                                この画面で回答を作成・編集しても、その旨は企画のメンバーに通知されません。
                            @else
                                非公開フォームのため、この画面で回答を作成・編集しても、その旨は企画のメンバーに通知されません。
                            @endif
                        </div>
                    @endif
                </list-view-card>
            </list-view>

            {{-- $answers ← 企画 $circle が回答した全回答（回答新規作成画面で使用。変更画面でも使用可能） --}}
            {{-- $answer ← 編集対象の回答（回答変更画面で使用） --}}

            @if (empty($answer) && count($answers) > 0)
                <list-view>
                    <template v-slot:title>以前の回答を閲覧・変更</template>
                    @foreach ($answers as $_)
                        <list-view-item href="{{ route('staff.forms.answers.edit', ['form' => $form, 'answer' => $_]) }}">
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

            <list-view>

                @if (empty($answer))
                    <template v-slot:title>回答を新規作成</template>
                @endif
                @isset($answer)
                    <template v-slot:title>回答を編集 — 回答ID : {{ $answer->id }}</template>
                    <template v-slot:description>回答の最終更新日時 : @datetime($form->updated_at)</template>
                @endisset

                @foreach ($questions as $question)
                    @include('includes.question', [
                        'show_upload_route' => 'staff.forms.answers.uploads.show',
                    ])
                @endforeach
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">送信</button>
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
