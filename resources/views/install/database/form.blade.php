@extends('layouts.no_drawer')

@section('title', 'PortalDots のインストール')

@section('content')
    @include('includes.install_header')

    <form method="post" action="{{ route('install.database.update') }}">
        @csrf

        @method('patch')

        <app-container medium>
            <list-view>
                <template v-slot:title>MySQLデータベース設定</template>
                <template v-slot:description>
                    PortalDots のデータを保存するためのデータベースの設定をします。<br>
                    利用しているサーバーサービスのマニュアルを参考に入力してください。
                </template>
                @foreach ($database as $key => $value)
                    <list-view-form-group label-for="name">
                        <template v-slot:label>
                            {{ $labels[$key] }}
                        </template>
                        <template v-slot:description>
                            {{ [
                                        'DB_HOST' => '',
                                        'DB_PORT' => '通常は 3306 です',
                                        'DB_DATABASE' => '',
                                        'DB_USERNAME' => '',
                                        'DB_PASSWORD' => ''
                                    ][$key] }}
                        </template>
                        <input id="{{ $key }}" type="text" class="form-control @error($key) is-invalid @enderror"
                            name="{{ $key }}" value="{{ old($key, $value) }}" required>
                        @error($key)
                        <template v-slot:invalid>{{ $message }}</template>
                        @enderror
                    </list-view-form-group>
                @endforeach
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <a href="{{ route('install.portal.edit') }}" class="btn is-secondary">
                    <i class="fas fa-chevron-left"></i>
                    戻る
                </a>
                <button type="submit" class="btn is-primary is-wide">
                    保存して次へ
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </app-container>
    </form>
@endsection
