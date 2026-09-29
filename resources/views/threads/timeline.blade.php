@php
    $viewerSide = $viewerSide ?? \App\Eloquents\ThreadEntry::AUTHOR_SIDE_CIRCLE;
    $visibleEntries = $viewerSide === \App\Eloquents\ThreadEntry::AUTHOR_SIDE_STAFF
        ? $entries
        : $entries->where('kind', '!=', \App\Eloquents\ThreadEntry::KIND_INTERNAL_NOTE);
@endphp

<div class="thread-timeline" role="log" aria-label="やり取り">
    @forelse ($visibleEntries as $entry)
        @if ($entry->kind === \App\Eloquents\ThreadEntry::KIND_EVENT)
            @php
                $eventUrl = isset($eventUrlFor) ? $eventUrlFor($entry) : null;
                $eventText = isset($eventTextFor) ? $eventTextFor($entry) : $entry->eventText();
            @endphp
            <div class="thread-timeline__event">
                <span class="thread-timeline__event-mark" aria-hidden="true"></span>
                <span class="thread-timeline__event-content">
                    @if ($eventUrl)
                        <a href="{{ $eventUrl }}">{{ $eventText }}</a>
                    @else
                        {{ $eventText }}
                    @endif
                    <time datetime="{{ $entry->created_at?->toIso8601String() }}">{{ $entry->created_at?->format('Y/m/d H:i') }}</time>
                </span>
            </div>
        @else
            @php
                $isNote = $entry->kind === \App\Eloquents\ThreadEntry::KIND_INTERNAL_NOTE;
                $isOwn = $entry->author_side === $viewerSide;
            @endphp
            <article class="thread-timeline__message {{ $isNote ? 'is-note' : ($isOwn ? 'is-own' : 'is-other') }}">
                <div class="thread-timeline__meta">
                    <span>
                        @if ($isNote)<span class="thread-timeline__note-label">内部メモ</span>@endif
                        @if ($entry->author_side === \App\Eloquents\ThreadEntry::AUTHOR_SIDE_STAFF && !$isNote)
                            スタッフ@if ($viewerSide === \App\Eloquents\ThreadEntry::AUTHOR_SIDE_STAFF) ・ {{ optional($entry->author)->name }}@endif
                        @else
                            {{ optional($entry->author)->name }}
                        @endif
                        @if ($entry->contactCategory)<span class="thread-timeline__category">{{ $entry->contactCategory->name }}</span>@endif
                    </span>
                    <time datetime="{{ $entry->created_at?->toIso8601String() }}">{{ $entry->created_at?->format('Y/m/d H:i') }}</time>
                </div>
                <p class="thread-timeline__body">{{ $entry->body }}</p>
                @if (isset($attachmentUrlFor) && $entry->attachments->isNotEmpty())
                    <ul class="thread-timeline__attachments">
                        @foreach ($entry->attachments as $attachment)
                            <li><a href="{{ $attachmentUrlFor($attachment) }}" target="_blank" rel="noopener noreferrer">{{ $attachment->name }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </article>
        @endif
    @empty
        <p class="thread-timeline__empty">メッセージはまだありません</p>
    @endforelse
</div>
