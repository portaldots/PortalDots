@extends('layouts.app')

@section('title', term('contact'))

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.threads.index') }}">
        {{ term('contact') }}
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container>
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
            <list-view-form-group label-for="assignee_id">
                <template v-slot:label>担当者</template>
                <form method="post"
                    action="{{ route('staff.threads.assignee.update', ['thread' => $thread]) }}">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="lock_version" value="{{ $thread->lock_version }}">
                    <select id="assignee_id" name="assignee_id" class="form-control">
                        <option value="">未設定</option>
                        @foreach ($staffUsers as $staffUser)
                            <option value="{{ $staffUser->id }}"
                                {{ (int)$thread->assignee_id === $staffUser->id ? 'selected' : '' }}>
                                {{ $staffUser->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn is-secondary is-sm mt-spacing-sm">更新</button>
                </form>
            </list-view-form-group>
        </list-view>
    </app-container>

    <app-container>
        <list-view>
            <template v-slot:title>やり取り</template>
            @foreach ($entries as $entry)
                @if ($entry->kind === \App\Eloquents\ThreadEntry::KIND_INTERNAL_NOTE)
                    <div style="background: var(--color-warning-light); border-radius: 4px; padding: 0 1rem;">
                        <list-view-item no-border>
                            <template v-slot:title>
                                内部メモ ・ {{ optional($entry->author)->name }}
                            </template>
                            <template v-slot:meta>
                                @datetime($entry->created_at)
                            </template>
                            <div style="white-space: pre-wrap">{{ $entry->body }}</div>
                            @unless ($entry->attachments->isEmpty())
                                <ul class="mb-0">
                                    @foreach ($entry->attachments as $attachment)
                                        <li>
                                            <a href="{{ route('staff.threads.attachments.show', ['attachment' => $attachment]) }}"
                                                target="_blank" rel="noopener noreferrer">
                                                <i class="fas fa-paperclip fa-fw"></i>
                                                {{ $attachment->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endunless
                        </list-view-item>
                    </div>
                @elseif ($entry->kind === \App\Eloquents\ThreadEntry::KIND_EVENT)
                    @php($eventLink = \App\Services\Threads\ThreadEventLinkService::staffLink($entry))
                    <list-view-item no-border>
                        <template v-slot:title>
                            @if ($eventLink)
                                <a href="{{ route($eventLink['route'], $eventLink['params']) }}">{{ $entry->eventText() }}</a>
                            @else
                                {{ $entry->eventText() }}
                            @endif
                        </template>
                        <template v-slot:meta>
                            @datetime($entry->created_at)
                        </template>
                    </list-view-item>
                @else
                    <list-view-item no-border>
                        <template v-slot:title>
                            @if ($entry->author_side === \App\Eloquents\ThreadEntry::AUTHOR_SIDE_STAFF)
                                スタッフ ・ {{ optional($entry->author)->name }}
                            @else
                                {{ optional($entry->author)->name }}
                            @endif
                            @if ($entry->contactCategory)
                                <app-badge muted small>{{ $entry->contactCategory->name }}</app-badge>
                            @endif
                        </template>
                        <template v-slot:meta>
                            @datetime($entry->created_at)
                        </template>
                        <div style="white-space: pre-wrap">{{ $entry->body }}</div>
                        @unless ($entry->attachments->isEmpty())
                            <ul class="mb-0">
                                @foreach ($entry->attachments as $attachment)
                                    <li>
                                        <a href="{{ route('staff.threads.attachments.show', ['attachment' => $attachment]) }}"
                                            target="_blank" rel="noopener noreferrer">
                                            <i class="fas fa-paperclip fa-fw"></i>
                                            {{ $attachment->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endunless
                    </list-view-item>
                @endif
            @endforeach
        </list-view>
    </app-container>

    <app-container>
        <list-view>
            <template v-slot:title>返信・内部メモ</template>
            <thread-composer
                messages-url="{{ route('staff.threads.messages.store', ['thread' => $thread]) }}"
                notes-url="{{ route('staff.threads.notes.store', ['thread' => $thread]) }}"
                csrf-token="{{ csrf_token() }}"
                client-token="{{ $clientToken }}"
                v-bind:max-files="{{ \App\Eloquents\ThreadEntryAttachment::MAX_FILES }}">
            </thread-composer>
        </list-view>
    </app-container>
@endsection
