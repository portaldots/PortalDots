@extends('layouts.app')

@section('title', '編集 — スタッフの権限設定')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.permissions.index') }}">
        スタッフの権限設定
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ route('staff.permissions.update', $user) }}" enctype="multipart/form-data">
        @method('patch')
        @csrf

        <app-header>
            <template v-slot:title>スタッフの権限を編集</template>
            <div>{{ $user->name }} (ID: {{ $user->id }}、学籍番号: {{ $user->student_id }})</div>
        </app-header>

        <app-container>
            <list-view>
                <template v-slot:title>{{ $user->name }}さんに割り当てる権限</template>
                <template v-slot:description>
                    Ctrl + F (macOS の場合 Command + F) で、このページの内容を検索できます
                </template>
                @if (is_array($errors->all()) && count($errors->all()) > 0)
                    <list-view-card>
                        <ul>
                            @foreach ($errors->all() as $message)
                                <li class="text-danger">{{ $message }}</li>
                            @endforeach
                        </ul>
                    </list-view-card>
                @endif
                @if (Auth::id() === $user->id)
                    <list-view-card>
                        <list-view-empty icon-class="fas fa-key" text="自分自身の権限設定は変更できません"></list-view-empty>
                    </list-view-card>
                @elseif ($user->is_admin)
                    <list-view-card>
                        <list-view-empty icon-class="fas fa-key" text="管理者に対して権限を設定することはできません"></list-view-empty>
                    </list-view-card>
                @else
                    @foreach ($defined_permissions as $defined_permission)
                        <list-view-form-group>
                            <div class="form-checkbox">
                                <label class="form-checkbox__label">
                                    <input class="form-checkbox__input" type="checkbox" name="permissions[]" value="{{ $defined_permission->getIdentifier() }}" {{ $user->permissions->containsStrict('name', $defined_permission->getIdentifier()) ? 'checked' : '' }}>
                                    <strong>{{ $defined_permission->getDisplayName() }}</strong><br>
                                    {{ $defined_permission->getDescriptionHtml() }}<br>
                                    <small class="text-muted">識別名 : {{ $defined_permission->getIdentifier() }}、短縮名 : {{ $defined_permission->getDisplayShortName() }}</small>
                                </label>
                            </div>
                        </list-view-form-group>
                    @endforeach
                @endif
            </list-view>

            @if (Auth::id() !== $user->id && !$user->is_admin)
                <div class="text-center pt-spacing-md pb-spacing">
                    <button type="submit" class="btn is-primary is-wide">保存</button>
                </div>
            @endif
        </app-container>
    </form>
@endsection
