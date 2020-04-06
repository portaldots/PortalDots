@extends('v2.layouts.no_drawer')

@section('title', 'メール送信フォーム')

@section('navbar')
    <app-nav-bar-back inverse href="{{ url("home_staff/circles/{$circle->id}/read") }}" data-turbolinks="false">
        企画情報 - {{ $circle->name }}
    </app-nav-bar-back>
@endsection
    
@section('content')
<form method="post" action="{{ route('staff.circles.email', ['circle' => $circle]) }}">
    @csrf
    <app-container>
        <list-view>
            <list-view-form-group label-for="circle">
                <template v-slot:label>団体名</template>
                <input type="text" id="circle" readonly value="{{ $circle->name }}" class="form-control is-plaintext">
            </list-view-form-group>

            <list-view-form-group label-for="recipient">
                <template v-slot:label>宛先</template>
                <div class="form-radio">
                    <label for="recipient_all" class="form-radio__label">
                        <input
                            type="radio"
                            id="recipient_all"
                            name="recipient"
                            value="all"
                            class="form-radio__input @error('recipient') is-invalid @enderror"
                            {{ old('recipient', 'all') === 'all' ? 'checked' : ''}}>
                        企画責任者・副責任者
                    </label>
                    <label for="recipient_leader" class="form-radio__label">
                        <input
                            type="radio"
                            id="recipient_leader"
                            name="recipient"
                            value="leader"
                            class="form-radio__input @error('recipient') is-invalid @enderror"
                            {{ old('recipient') === 'leader' ? 'checked' : ''}}>
                        企画責任者のみ
                    </label>
                </div>
                @error('recipient')
                    <template v-slot:invalid>{{ $message }}</template>
                @enderror
            </list-view-form-group>

            <list-view-form-group label-for="title">
                <template v-slot:label>件名</template>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title') }}"
                    required>

                @error('title')
                    <template v-slot:invalid>{{ $message }}</template>
                @enderror
            </list-view-form-group>

            <list-view-form-group label-for="message">
                <template v-slot:label>本文</template>
                <template v-slot:description>Markdown も使用できます。</template>
                <textarea
                    rows="10"
                    id="message"
                    name="message"
                    class="form-control @error('message') is-invalid @enderror"
                    required>{{ old('message') }}</textarea>
                @error('message')
                    <template v-slot:invalid>{{ $message }}</template>
                @enderror
            </list-view-form-group>
        </list-view>
        <div class="text-center pt-spacing-md pb-spacing">
            <button type="submit" class="btn is-primary is-wide">送信</button>
            <a href="{{ url('home_staff/circles/read/' . $circle->id) }}" data-turbolinks="false" class="btn is-primary-inverse">団体詳細へ戻る</a>
        </div>
    </app-container>
</form>
@endsection
