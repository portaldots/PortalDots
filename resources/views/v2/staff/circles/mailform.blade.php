@extends('v2.layouts.no_drawer')

@section('title', 'メール送信フォーム')
    
@section('content')
<form method="post" action="#">
    @csrf
    <app-container>
        <list-view>
            <list-view-form-group label-for="recipient">
                <template v-slot:label>宛先</template>
                <template v-slot:description>この団体に登録されている全ユーザーに送信されます。</template>
                <input type="text" id="recipient" readonly value="{{ $circle->name }}" class="form-control is-plaintext">
            </list-view-form-group>
            <list-view-form-group label-for="title">
                <template v-slot:label>件名</template>
                <input type="text" id="title" name="title" class="form-control" required>
            </list-view-form-group>
            <list-view-form-group label-for="message">
                <template v-slot:label>本文</template>
                <template v-slot:description>Markdown も使用できます。</template>
                <textarea type="text" rows="10" id="message" name="message" class="form-control" required></textarea>
            </list-view-form-group>
        </list-view>
        <div class="text-center pt-spacing-md pb-spacing">
            <button type="submit" class="btn is-primary is-wide">送信</button>
            <a href="{{ url('home_staff/circles/read/' . $circle->id) }}" data-turbolinks="false" class="btn is-primary-inverse">団体詳細へ戻る</a>
        </div>
    </app-container>
</form>
@endsection
