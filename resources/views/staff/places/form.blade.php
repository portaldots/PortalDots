@extends('layouts.app')

@section('title', empty($place) ? '新規作成 — 場所' : "{$place->name} — 場所")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.places.index') }}">
        場所管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ empty($place) ? route('staff.places.store') : route('staff.places.update', $place) }}" enctype="multipart/form-data">
        @method(empty($place) ? 'post' : 'patch' )
        @csrf

        <app-header>
            @if (empty($place))
                <template v-slot:title>場所を新規作成</template>
            @endif
            @isset ($place)
                <template v-slot:title>場所を編集</template>
                <div>場所ID : {{ $place->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
                <list-view-form-group label-for="name">
                    <template v-slot:label>
                        場所名
                        <app-badge danger>必須</app-badge>
                    </template>
                    <input id="name" class="form-control @error('name') is-invalid @enderror" type="text"
                        name="name" value="{{ old('name', empty($place) ? '' : $place->name) }}"
                        required>
                    @if ($errors->has('name'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('name') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="type">
                    <template v-slot:label>
                        タイプ
                        <app-badge danger>必須</app-badge>
                    </template>
                    <select id="type" class="form-control @error('type') is-invalid @enderror"
                        name="type" required>
                        <option value="1" {{ old('type', empty($place) ? 1 : $place->type) === 1 ? 'selected' : '' }}>屋内</option>
                        <option value="2" {{ old('type', empty($place) ? 1 : $place->type) === 2 ? 'selected' : '' }}>屋外</option>
                        <option value="3" {{ old('type', empty($place) ? 1 : $place->type) === 3 ? 'selected' : '' }}>特殊場所</option>
                    </select>
                    @if ($errors->has('type'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('type') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="notes">
                    <template v-slot:label>スタッフ用メモ</template>
                    <template v-slot:description>ここに入力された内容はスタッフのみ閲覧できます。スタッフ内で共有したい事項を残しておくメモとしてご活用ください。</template>
                    <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes"
                        rows="5">{{ old('notes', empty($place) ? '' : $place->notes) }}</textarea>
                    @if ($errors->has('notes'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('notes') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </div>
        </app-container>
    </form>
@endsection
