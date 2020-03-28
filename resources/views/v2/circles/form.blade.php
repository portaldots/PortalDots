@extends('v2.layouts.no_drawer')

@section('title', '企画参加登録')
    
@section('content')
    @include('v2.includes.circle_register_header')
    
    <form method="post" action="{{ empty($circle) ? route('circles.store') : route('circles.update', [$circle]) }}"
        enctype="multipart/form-data">
        @csrf
    
        @method(empty($circle) ? 'post' : 'patch' )
    
        <app-container medium>
            <list-view>
                <template v-slot:title>企画情報を入力</template>
                <template v-slot:description>参加を登録される企画の情報を入力してください。</template>
                <list-view-card>
                    <i class="fas fa-exclamation-circle"></i>
                    企画情報の入力は、団体責任者の方が行ってください。団体責任者以外の方は、企画情報の入力は不要です。団体責任者の方の指示に従ってください。
                </list-view-card>
                <list-view-form-group label-for="name">
                    <template v-slot:label>団体責任者</template>
                    <input type="text" id="name" readonly
                        value="{{ isset($circle) ? $circle->leader[0]->name : Auth::user()->name }}"
                        class="form-control is-plaintext">
                </list-view-form-group>
                <list-view-form-group label-for="name">
                    <template v-slot:label>
                        企画の名前
                        <span class="badge is-danger">必須</span>
                    </template>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                        value="{{ old('name', isset($circle) ? $circle->name : '') }}" required>
                    @error('name')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="name_yomi">
                    <template v-slot:label>
                        企画の名前(よみ)
                        <span class="badge is-danger">必須</span>
                    </template>
                    <input id="name_yomi" type="text" class="form-control @error('name_yomi') is-invalid @enderror"
                        name="name_yomi" value="{{ old('name_yomi', isset($circle) ? $circle->name_yomi : '') }}" required>
                    @error('name_yomi')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="group_name">
                    <template v-slot:label>
                        企画団体の名前
                        <span class="badge is-danger">必須</span>
                    </template>
                    <input id="group_name" type="text" class="form-control @error('group_name') is-invalid @enderror"
                        name="group_name" value="{{ old('group_name', isset($circle) ? $circle->group_name : '') }}"
                        required>
                    @error('group_name')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="group_name_yomi">
                    <template v-slot:label>
                        企画団体の名前(よみ)
                        <span class="badge is-danger">必須</span>
                    </template>
                    <input id="group_name_yomi" type="text"
                        class="form-control @error('group_name_yomi') is-invalid @enderror" name="group_name_yomi"
                        value="{{ old('group_name_yomi', isset($circle) ? $circle->group_name_yomi : '') }}" required>
                    @error('group_name_yomi')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                @foreach ($questions as $question)
                    @include('v2.includes.question')
                @endforeach
            </list-view>
    
            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">
                    保存して次へ
                    <i class="fas fa-chevron-right"></i>
                </button>
                @if (config('app.debug'))
                    <button type="submit" class="btn is-primary-inverse" formnovalidate>
                        <strong class="badge is-primary">開発モード</strong>
                        バリデーションせずに送信
                    </button>
                @endif
            </div>
        </app-container>
    </form>
@endsection
