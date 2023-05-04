@extends('layouts.app')

@section('title', "{$participation_type->name} — 参加種別")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.circles.index') }}">
        企画情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    @include('includes.staff_circles_tab_strip')
    <app-header>
        <template v-slot:title>参加種別を編集</template>
        <div>参加種別ID : {{ $participation_type->id }}</div>
        <form-with-confirm
            action="{{ route('staff.circles.participation_types.destroy', ['participation_type' => $participation_type]) }}"
            method="post" confirm-message="本当にこの参加種別を削除しますか？この参加種別に紐づく企画もすべて削除されます。">
            @method('delete')
            @csrf
            <button type="submit" class="btn is-danger is-sm">
                この参加種別を削除
            </button>
        </form-with-confirm>
    </app-header>
    <app-container>
        <form action="{{ route('staff.circles.participation_types.update', ['participation_type' => $participation_type]) }}"
            method="post">
            @csrf
            @method('patch')
            <list-view>
                <list-view-form-group label-for="name">
                    <template v-slot:label>参加種別名</template>
                    <template v-slot:description>
                        一般ユーザーに対して表示されます。例：模擬店、ステージ、など。
                    </template>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                        name="name" value="{{ old('name', $participation_type->name) }}" required>
                    @error('name')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="description">
                    <template v-slot:label>説明</template>
                    <template v-slot:description>一般ユーザーに対して表示されます。</template>
                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description"
                        rows="3">{{ old('description', $participation_type->description) }}</textarea>
                    @if ($errors->has('description'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('description') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group>
                    <template v-slot:label>
                        作成した企画に自動で追加するタグ
                        <small class="text-muted">
                            スペース区切りで複数入力可
                        </small>
                    </template>
                    <template v-slot:description>
                        この設定を保存した後に作成される企画にのみ、これらのタグが自動的に追加されます。
                        @cannot('staff.tags.edit')
                            <br>
                            <i class="fas fa-info-circle fa-fw"></i>
                            <b>企画タグの編集が許可されていないため、ここでは作成済みの企画タグのみを指定できます。企画タグを新しく作成したい場合は、{{ config('app.name') }}の管理者へお問い合わせください。</b>
                        @endcannot
                    </template>
                    <tags-input input-name="tags" v-bind:default-tags="{{ $default_tags }}"
                        v-bind:autocomplete-items="{{ $tags_autocomplete_items }}"
                        {{ Auth::user()->can('staff.tags.edit') ? '' : 'add-only-from-autocomplete' }}></tags-input>
                    @if ($errors->has('tags'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('tags') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>
            <app-fixed-form-footer>
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </app-fixed-form-footer>
        </form>
    </app-container>
@endsection
