@extends('v2.layouts.no_drawer')

@section('title', $form->name . ' — 申請')

@section('navbar')
    <app-nav-bar-back inverse href="{{ url('/home_staff/applications/read/' . $form->id) }}" data-turbolinks="false">
        {{ $form->name }}
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post"
        action="{{ empty($answer) ? route('staff.forms.answers.store', [$form]) : route('staff.forms.answers.update', [$form, $answer]) }}"
        enctype="multipart/form-data">
        @csrf

        @method(empty($answer) ? 'post' : 'patch' )

        <input type="hidden" name="circle_id" value="{{ $circle->id }}">

        <app-header>
            <template v-slot:title>{{ $form->name }}</template>
            <div class="markdown">
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
                    @if ($form->is_public && empty($form->customForm))
                        <div class="text-danger">
                            <i class="fas fa-info-circle"></i>
                            スタッフとして回答します。この画面で回答を作成・編集すると、企画のメンバーには「スタッフによって回答が作成・編集された」という旨のメールが送信されます。
                        </div>
                    @else
                        <div class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            @if (isset($form->customForm))
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
                @isset ($answer)
                    <template v-slot:title>回答を編集 — 回答ID : {{ $answer->id }}</template>
                    <template v-slot:description>回答の最終更新日時 : @datetime($form->updated_at)</template>
                @endisset

                @foreach ($questions as $question)
                    @include('v2.includes.question', ['show_upload_route' => 'staff.forms.answers.uploads.show'])
                @endforeach
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">送信</button>
                @if (config('app.debug'))
                    <button type="submit" class="btn is-primary-inverse" formnovalidate>
                        <strong class="badge is-primary">開発モード</strong>
                        バリデーションせずに送信
                    </button>
                @endif
            </div>
        </app-container>
    </form>
@endsection
