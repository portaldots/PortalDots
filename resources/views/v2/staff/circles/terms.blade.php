@extends('v2.layouts.no_drawer')

@section('title', '企画参加登録の設定')

@section('navbar')
    <app-nav-bar-back inverse href="{{ route('staff.circles.custom_form.index') }}">
        企画参加登録設定
    </app-nav-bar-back>
@endsection

@section('content')
<form action="{{ route('staff.circles.terms.update') }}" method="POST">
    @method('patch')
    @csrf
    <app-header container-medium>
        <template v-slot:title>企画参加登録の設定</template>
        ここで設定した内容が参加登録前に表示されます。なにも入力しなかった場合は表示しません。
    </app-header>
    <app-container medium>
        <list-view>
            <list-view-form-group>
                <template v-slot:label>
                    参加登録前に表示する内容&nbsp;
                    <app-badge outline muted>Markdown</app-badge>
                </template>
                <markdown-editor input-name="description" default-value="{{ old('description', $description) }}"></markdown-editor>
                @if ($errors->has('description'))
                    <template v-slot:invalid>
                        @foreach ($errors->get('description') as $message)
                            <div>{{ $message }}</div>
                        @endforeach
                    </template>
                @endif
            </list-view-form-group>
        </list-view>
        <div class="text-center pt-spacing-md">
            <button type="submit" class="btn is-primary is-wide">
                保存
            </button>
        </div>
    </app-container>
</form>
@endsection
