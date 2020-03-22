@extends('v2.layouts.no_drawer')

@section('title', 'メール認証完了')
    
@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>メール認証完了</template>
            <list-view-card>
                <p class="text-center text-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>手続きが完了しました！</strong>
                </p>
                <p class="text-center">
                    <a href="{{ route('home') }}" class="btn is-primary">
                        ホームにアクセス
                    </a>
                </p>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
