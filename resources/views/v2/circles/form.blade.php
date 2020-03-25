@extends('v2.layouts.no_drawer')

@section('title', '企画参加登録')
    
@section('content')
    <app-header container-medium>
        <template v-slot:title>
            企画参加登録
            <small class="text-muted">(ステップ 1 / 3)</small>
        </template>
        @isset ($circle)
            <div class="text-muted">
                {{ $circle->name }}
            </div>
        @endisset
    </app-header>
    
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
            </list-view>
    
            <div class="text-center pt-spacing-md pb-spacing">
                <div class="pb-spacing-sm">
                    <button type="submit" class="btn is-primary is-wide">
                        つづいて、メンバーの登録
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <p class="text-muted">ボタンをクリックすると、ここまでの入力内容が保存されます</p>
            </div>
        </app-container>
    </form>
@endsection
