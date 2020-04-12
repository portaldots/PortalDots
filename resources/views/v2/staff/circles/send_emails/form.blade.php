@extends('v2.layouts.no_drawer')

@section('title', 'メール送信フォーム')

@section('navbar')
    <app-nav-bar-back inverse href="{{ url("home_staff/circles/read/{$circle->id}") }}" data-turbolinks="false">
        {{ $circle->name }}
    </app-nav-bar-back>
@endsection
    
@section('content')
<form method="post" action="{{ route('staff.circles.email', ['circle' => $circle]) }}">
    @csrf
    <app-container>
        <list-view>
            <list-view-form-group label-for="circle">
                <template v-slot:label>企画名</template>
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

            <list-view-form-group label-for="subject">
                <template v-slot:label>件名</template>
                <input
                    type="text"
                    id="subject"
                    name="subject"
                    class="form-control @error('subject') is-invalid @enderror"
                    value="{{ old('subject') }}"
                    required>

                @error('subject')
                    <template v-slot:invalid>{{ $message }}</template>
                @enderror
            </list-view-form-group>

            <list-view-form-group label-for="body">
                <template v-slot:label>本文</template>
                <template v-slot:description>Markdown利用可能</template>
                <textarea
                    rows="10"
                    id="body"
                    name="body"
                    class="form-control @error('body') is-invalid @enderror"
                    required>{{ old('body', "{$circle->name} 様") }}</textarea>
                @error('body')
                    <template v-slot:invalid>{{ $message }}</template>
                @enderror
            </list-view-form-group>
        </list-view>
        <div class="text-center pt-spacing-md pb-spacing">
            <button type="submit" class="btn is-primary is-wide">送信</button>
        </div>
    </app-container>
</form>
@endsection
