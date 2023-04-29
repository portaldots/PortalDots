@extends('layouts.app')

@section('title', '団体へメール送信')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.groups.index') }}">
        団体管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ route('staff.groups.email', ['group' => $group]) }}">
        @csrf

        <app-header>
            <template v-slot:title>団体へメール送信</template>
            <div>団体ID : {{ $group->id }}</div>
        </app-header>

        <app-container>
            <list-view>
                <list-view-form-group label-for="group">
                    <template v-slot:label>団体名</template>
                    <input type="text" id="group" readonly value="{{ $group->name }}" class="form-control is-plaintext">
                </list-view-form-group>

                @if ($group->users->isEmpty())
                    <list-view-card>
                        <list-view-empty icon-class="far fa-envelope" text="団体に所属しているメンバーがいないため、この団体に対しメールを送ることはできません。">
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
                                団体責任者・副責任者
                            </label>
                            <label for="recipient_owner" class="form-radio__label">
                                <input type="radio" id="recipient_owner" name="recipient" value="owner"
                                    class="form-radio__input @error('recipient') is-invalid @enderror"
                                    {{ old('recipient') === 'owner' ? 'checked' : '' }}>
                                団体責任者のみ
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
                        <markdown-editor input-name="body" default-value="{{ old('body', "{$group->name} 様\n\n") }}">
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
            @if (!$group->users->isEmpty())
                <div class="text-center pt-spacing-md pb-spacing">
                    <button type="submit" class="btn is-primary is-wide">送信</button>
                </div>
            @endif
        </app-container>
    </form>
@endsection
