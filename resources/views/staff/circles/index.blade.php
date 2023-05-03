@extends('layouts.app')

@section('title', '企画情報管理')

@section('content')
    <app-container>
        <list-view>
            @if (empty($participation_types) || count($participation_types) === 0)
                <list-view-card>
                    <list-view-empty text="まずは参加種別を作成しましょう" icon-class="fas fa-star">
                        <p>
                            「模擬店」、「ステージ出演」などの種類ごとに企画参加登録フォームを作成できます。
                        </p>
                        <p>
                            受付期間や参加登録フォームの内容は参加種別ごとに設定できます。
                        </p>
                    </list-view-empty>
                </list-view-card>
            @endif
            @foreach ($participation_types as $participation_type)
                <list-view-item
                    href="{{ route('staff.circles.participation_types.index', ['participation_type' => $participation_type]) }}">
                    <template v-slot:title>
                        {{ $participation_type->name }}
                        @if (!$participation_type->form->is_public)
                            <app-badge muted>非公開</app-badge>
                        @elseif(!$participation_type->form->isOpen())
                            <app-badge muted>受付期間外</app-badge>
                        @else
                            <app-badge primary strong>受付期間内</app-badge>
                        @endif
                    </template>
                    <template v-slot:meta>
                        @if ($participation_type->form->is_public)
                            受付期間 : @datetime($participation_type->form->open_at)〜@datetime($participation_type->form->close_at)
                        @endif
                    </template>
                    {{ $participation_type->description }}
                </list-view-item>
            @endforeach
            <list-view-action-btn href="{{ route('staff.circles.participation_types.create') }}" icon-class="fas fa-plus">
                参加種別を作成
            </list-view-action-btn>
        </list-view>
    </app-container>
@endsection
