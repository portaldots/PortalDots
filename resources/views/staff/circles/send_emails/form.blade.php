@extends('layouts.app')

@section('title', '企画へメール送信')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.circles.index') }}">
        企画情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ route('staff.circles.email', ['circle' => $circle]) }}">
        @csrf

        <app-header>
            <template v-slot:title>企画へメール送信</template>
            <div>企画ID : {{ $circle->id }}</div>
        </app-header>

        <app-container>
            <list-view>
                <list-view-form-group label-for="circle">
                    <template v-slot:label>企画名</template>
                    <input type="text" id="circle" readonly value="{{ $circle->name }}" class="form-control is-plaintext">
                </list-view-form-group>

                @if ($circle->users->isEmpty())
                    <list-view-card>
                        <list-view-empty icon-class="far fa-envelope" text="企画に所属しているメンバーがいないため、この企画に対しメールを送ることはできません。">
                        </list-view-empty>
                    </list-view-card>
                @else
                    <list-view-form-group label-for="recipient">
                        <template v-slot:label>宛先</template>
                        <div class="form-radio">
                            <label for="recipient_all" class="form-radio__label">
                                <input type="radio" id="recipient_all" name="recipient" value="all"
                                    class="form-radio__input @error('recipient') is-invalid @enderror"
                                    {{ old('recipient', 'all') === 'all' ? 'checked' : '' }}>
                                企画責任者・副責任者
                            </label>
                            <label for="recipient_leader" class="form-radio__label">
                                <input type="radio" id="recipient_leader" name="recipient" value="leader"
                                    class="form-radio__input @error('recipient') is-invalid @enderror"
                                    {{ old('recipient') === 'leader' ? 'checked' : '' }}>
                                企画責任者のみ
                            </label>
                        </div>
                        @error('recipient')
                            <template v-slot:invalid>{{ $message }}</template>
                        @enderror
                    </list-view-form-group>

                    <list-view-form-group label-for="subject">
                        <template v-slot:label>件名</template>
                        <input type="text" id="subject" name="subject"
                            class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}"
                            required>

                        @error('subject')
                            <template v-slot:invalid>{{ $message }}</template>
                        @enderror
                    </list-view-form-group>

                    <list-view-form-group>
                        <template v-slot:label>
                            本文&nbsp;
                            <app-badge outline muted>Markdown</app-badge>
                        </template>
                        <markdown-editor input-name="body" default-value="{{ old('body', "{$circle->name} 様\n\n") }}">
                        </markdown-editor>
                        @error('body')
                            <template v-slot:invalid>{{ $message }}</template>
                        @enderror
                    </list-view-form-group>
                    <list-view-card>
                        <app-info-box primary>
                            メール配信機能を利用するには、予めサーバー側での設定(CRON)が必要です。
                        </app-info-box>
                    </list-view-card>
                @endif
            </list-view>
            @if (!$circle->users->isEmpty())
                <div class="text-center pt-spacing-md pb-spacing">
                    <button type="submit" class="btn is-primary is-wide">送信</button>
                </div>
            @endif
        </app-container>
    </form>
@endsection
