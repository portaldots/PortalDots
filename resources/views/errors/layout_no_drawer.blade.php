@extends('v2.layouts.no_drawer')

@hasSection ('twitter')
@push('js')
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
@endpush
@endif

@section('content')
<app-container>
    <div class="error">
        <div class="error-title">
            @yield('top')
        </div>
        <div class="error-message">
            @yield('message')
        </div>
        @hasSection ('contact')
            <div class="error-contact">
                @yield('contact')
                <p><a href="mailto:{{ config('portal.contact_email') }}">{{ config('portal.contact_email') }}</a></p>
            </div>
        @endif
        @hasSection ('back')
            <div class="error-button">
                <button class="btn is-primary" onclick="window.history.back();">前のページに戻る</button>
            </div>
        @endif
        @if (!empty(config('portal.admin_twitter')))
        @hasSection ('twitter')
        <div class="error-twitter">
            <a class="twitter-timeline" data-height="100%" data-chrome="noscrollbar noborders" href="https://twitter.com/{{ config('portal.admin_twitter') }}?ref_src=twsrc%5Etfw">
                Tweets by {{ config('portal.admin_twitter') }}
            </a>
        </div>
        @endif
        @endif
    </div>
</app-container>
@endsection
