@component('mail::message')
# お問い合わせに返信がありました

@isset($thread->circle)
{{ $thread->circle->name }} 様
@else
{{ $thread->user->name }} 様
@endisset

<pre>{{ $entry->body }}</pre>

@component('mail::button', ['url' => route('contacts'), 'color' => 'primary'])
会話を見る
@endcomponent
@endcomponent
