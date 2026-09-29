@extends('layouts.app')

@section('title', term('contact'))

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.threads.index') }}">
        {{ term('contact') }}
    </app-nav-bar-back>
@endsection

@section('no_footer', '1')

@push('body-class')
    has-thread-workspace {{ config('portal.navigation.staff_bottom_tabs', false) ? 'has-staff-bottom-tabs' : '' }}
@endpush

@section('content')
    <app-container fluid class="staff-thread-workspace-container">
        <div class="staff-thread-workspace">
            <aside class="staff-thread-workspace__details" aria-label="問い合わせの状況">
                <div class="staff-thread-workspace__details-desktop">
                    <list-view>
                        <template v-slot:title>
                            @if ($thread->circle)
                                {{ $thread->circle->name }}（{{ $thread->circle->group_name }}）
                            @else
                                {{ $thread->user->name }}
                            @endif
                        </template>
                        <list-view-form-group>
                            <template v-slot:label>状況</template>
                            @if ($thread->status === \App\Eloquents\Thread::STATUS_NEEDS_STAFF)
                                <app-badge danger>{{ $thread->staffStatusLabel() }}</app-badge>
                            @elseif ($thread->status === \App\Eloquents\Thread::STATUS_AWAITING_REPLY)
                                <app-badge primary>{{ $thread->staffStatusLabel() }}</app-badge>
                            @else
                                <app-badge muted>{{ $thread->staffStatusLabel() }}</app-badge>
                            @endif
                        </list-view-form-group>
                        @include('staff.threads.assignee_form', ['inputId' => 'assignee_id'])
                    </list-view>
                </div>
                <details class="staff-thread-workspace__details-mobile">
                    <summary>
                        @if ($thread->circle)
                            {{ $thread->circle->name }}（{{ $thread->circle->group_name }}）
                        @else
                            {{ $thread->user->name }}
                        @endif
                        <span class="staff-thread-workspace__details-status">
                            @if ($thread->status === \App\Eloquents\Thread::STATUS_NEEDS_STAFF)
                                <app-badge danger>{{ $thread->staffStatusLabel() }}</app-badge>
                            @elseif ($thread->status === \App\Eloquents\Thread::STATUS_AWAITING_REPLY)
                                <app-badge primary>{{ $thread->staffStatusLabel() }}</app-badge>
                            @else
                                <app-badge muted>{{ $thread->staffStatusLabel() }}</app-badge>
                            @endif
                        </span>
                        <span class="staff-thread-workspace__details-assignee">担当者: {{ $thread->assignee?->name ?? '未設定' }}</span>
                    </summary>
                    <list-view>
                        @include('staff.threads.assignee_form', ['inputId' => 'assignee_id_mobile'])
                    </list-view>
                </details>
            </aside>

            <section class="staff-thread-workspace__conversation" aria-label="やり取りと返信">
                <div class="staff-thread-workspace__history">
                    <list-view no-card-style>
                        <template v-slot:title>やり取り</template>
                        @include('threads.timeline', [
                            'entries' => $entries,
                            'viewerSide' => \App\Eloquents\ThreadEntry::AUTHOR_SIDE_STAFF,
                            'eventUrlFor' => function ($entry) {
                                $link = \App\Services\Threads\ThreadEventLinkService::staffLink($entry);
                                return $link ? route($link['route'], $link['params']) : null;
                            },
                            'attachmentUrlFor' => fn ($attachment) => route('staff.threads.attachments.show', ['attachment' => $attachment]),
                        ])
                    </list-view>
                </div>
                <div class="staff-thread-workspace__reply">
                    <list-view no-card-style>
                        <thread-composer class="thread-composer-panel"
                            messages-url="{{ route('staff.threads.messages.store', ['thread' => $thread]) }}"
                            notes-url="{{ route('staff.threads.notes.store', ['thread' => $thread]) }}"
                            csrf-token="{{ csrf_token() }}"
                            client-token="{{ $clientToken }}"
                            v-bind:max-files="{{ \App\Eloquents\ThreadEntryAttachment::MAX_FILES }}">
                        </thread-composer>
                    </list-view>
                </div>
            </section>
        </div>
    </app-container>
@endsection
