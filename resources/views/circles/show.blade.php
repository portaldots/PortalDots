@extends('layouts.no_drawer')

@section('no_circle_selector', true)

@section('title', $circle->hasApproved() ? '企画情報詳細' : '企画参加登録')

@section('content')
    <app-header container-medium>
        <template v-slot:title>
            {{ $circle->hasApproved() ? '企画情報詳細' : '企画参加登録' }}
        </template>
        <span class="text-muted">
            {{ $circle->name }}
            @if (!$circle->hasSubmitted())
                <app-badge muted outline>未提出</app-badge>
            @elseif ($circle->isPending())
                <app-badge success outline>提出済</app-badge>
            @elseif ($circle->hasApproved())
                <app-badge success>受理</app-badge>
            @elseif ($circle->hasRejected())
                <app-badge danger>不受理</app-badge>
            @endif
        </span>
        @if (!Auth::user()->isLeaderInCircle($circle) && Gate::allows('circle.update', $circle))
            <form-with-confirm action="{{ route('circles.users.destroy', ['circle' => $circle, 'user' => Auth::user()]) }}"
                method="post" confirm-message="本当にこの企画を抜けますか？">
                @method('delete')
                @csrf
                <button type="submit" class="btn is-danger is-sm" style="display:inline-block;">
                    この企画から抜ける
                </button>
            </form-with-confirm>
        @endif
    </app-header>
    <app-container medium>
        @if ($circle->hasSubmitted() && $circle->participationType->form->confirmation_message)
            <list-view>
                <list-view-card data-turbolinks="false" class="markdown">
                    @markdown($circle->participationType->form->confirmation_message)
                </list-view-card>
            </list-view>
        @endif
        <list-view>
            @if (!$circle->hasSubmitted())
                <list-view-card>
                    <app-info-box primary>
                        <strong>企画情報の修正や、企画参加登録を提出することができるのは、企画責任者のみです。</strong>
                    </app-info-box>
                </list-view-card>
            @endif
            <list-view-card>
                @include('includes.circle_info')
            </list-view-card>
        </list-view>
        @if ($circle->hasRejected() && isset($circle->status_reason))
            <list-view>
                <template v-slot:title>不受理となった理由</template>
                <list-view-card data-turbolinks="false" class="markdown">
                    @markdown($circle->status_reason)
                </list-view-card>
            </list-view>
        @endif
        @if (!empty($questions) && count($questions) > 0)
            <list-view>
                @if (Auth::user()->isLeaderInCircle($circle) && Gate::allows('circle.update', $circle))
                    <list-view-card>
                        <a href="{{ route('circles.edit', ['circle' => $circle]) }}" class="btn is-secondary">下記回答の変更</a>
                    </list-view-card>
                @endif
                @foreach ($questions as $question)
                    @include('includes.question', ['is_disabled' => true])
                @endforeach
            </list-view>
        @endif
    </app-container>
@endsection
