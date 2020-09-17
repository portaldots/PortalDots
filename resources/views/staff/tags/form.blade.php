@extends('layouts.no_drawer')

@section('title', empty($tag) ? '新規作成 — 企画タグ' : "{$tag->name} — 企画タグ")

@section('navbar')
    <app-nav-bar-back inverse href="{{ url('/home_staff/tags') }}" data-turbolinks="false">
        企画タグ管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ empty($tag) ? route('staff.tags.store') : route('staff.tags.update', $tag) }}" enctype="multipart/form-data">
        @method(empty($tag) ? 'post' : 'patch' )
        @csrf

        <app-header>
            @if (empty($tag))
                <template v-slot:title>企画タグを新規作成</template>
            @endif
            @isset ($tag)
                <template v-slot:title>企画タグを編集</template>
                <div>タグID : {{ $tag->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
                <list-view-form-group label-for="name">
                    <template v-slot:label>
                        タグ名
                        <app-badge danger>必須</app-badge>
                    </template>
                    <input id="name" class="form-control @error('name') is-invalid @enderror" type="text"
                        name="name" value="{{ old('name', empty($tag) ? '' : $tag->name) }}"
                        required>
                    @if ($errors->has('name'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('name') as $message)
                                {{ $message }}
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
