@section('no_circle_selector', true)

@extends('layouts.no_drawer')

@section('title', '企画を選択')

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>企画を選択</template>
            <template v-slot:description>ログインする企画を選択してください。</template>
            @foreach ($circles as $circle)
                <list-view-item
                    href="{{ route('circles.selector.set', ['redirect_to' => $redirect_to, 'circle' => $circle]) }}">
                    <template v-slot:title>
                        <i class="fa fa-users mr-2" area-hidden="true"></i>
                        {{ $circle->name }}
                    </template>
                </list-view-item>
            @endforeach
            @if (Gate::allows('circle.create'))
                <list-view-action-btn href="{{ route('circles.create') }}" icon-class="fas fa-plus">
                    別の企画を参加登録する
                </list-view-action-btn>
            @endif
        </list-view>

        <hr>

        @if (Gate::allows('circle.create') && !$not_submitted_circles->isEmpty())
            <list-view class="pt-spacing-sm">
                <template v-slot:description>
                    <div class="text-muted">
                        <i class="fas fa-info-circle fa-fw"></i>
                        以下の企画参加登録は提出されていません。提出期限(<strong>@datetime($circle_custom_form->close_at)</strong>)までに提出してください。
                    </div>
                </template>
                @each('includes.circle_list_view_item_with_status', $not_submitted_circles, 'circle')
            </list-view>
        @endif
    </app-container>
@endsection
