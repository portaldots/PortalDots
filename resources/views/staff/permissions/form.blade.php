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
            <div>{{ $user->name }} (ID: {{ $user->id }}、{{ config('portal.student_id_name') }}:
                {{ $user->student_id }})</div>
        </app-header>

        <app-container>
            <list-view>
                <template v-slot:title>{{ $user->name }}さんに割り当てる権限</template>
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
                    <permissions-selector v-bind:defined-permissions="{{ json_encode($defined_permissions) }}"
                        v-bind:default-permissions="{{ $user->permissions->pluck('name')->toJson() }}"
                        input-name="permissions"></permissions-selector>
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
