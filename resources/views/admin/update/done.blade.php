@extends('layouts.no_drawer')

@section('title', 'PortalDots のアップデート')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>アップデート完了</template>
            <list-view-card>
                <p class="text-center text-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>アップデートが完了しました</strong>
                </p>
                <p class="text-center">
                    <a href="{{ route('staff.index') }}" class="btn is-primary">
                        スタッフモード ホームにアクセス
                    </a>
                </p>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
