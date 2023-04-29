@extends('layouts.no_drawer')

@section('title', 'ユーザー登録')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @method('post')
        @csrf

        <app-container medium>
            <list-view>
                <template v-slot:title>ユーザー登録</template>
                <template v-slot:description>「{{ config('app.name') }}」にユーザー登録します。</template>

                <list-view-group-info-input-for-register is-individual-input-name="is_individual"
                    group-name-input-name="group_name" group-name-yomi-input-name="group_name_yomi"
                    v-bind:default-is-individual-value="{{ intval(old('is_individual', 0)) === 1 ? 'true' : 'false' }}"
                    default-group-name-value="{{ old('group_name') }}"
                    default-group-name-yomi-value="{{ old('group_name_yomi') }}">
                </list-view-group-info-input-for-register>
                @include('includes.user_register_form')
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">
                    登録
                </button>
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
