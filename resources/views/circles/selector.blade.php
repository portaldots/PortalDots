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
        </list-view>

        @if (Gate::allows('circle.create'))
            <list-view no-card-style>
                <template v-slot:title>別の企画を参加登録する</template>
                <layout-row grid-template-columns="repeat(auto-fill, minmax(240px, 1fr))">
                    @foreach ($participation_types as $participation_type)
                        <layout-column>
                            <card-link href="{{ route('circles.create', ['participation_type' => $participation_type]) }}">
                                <template v-slot:title>
                                    {{ $participation_type->name }}
                                </template>
                                <template v-slot:description>
                                    <p>{{ $participation_type->description }}</p>
                                    <dl class="text-small">
                                        @datetime($participation_type->form->close_at)まで受付
                                    </dl>
                                </template>
                            </card-link>
                        </layout-column>
                    @endforeach
                </layout-row>
            </list-view>
        @endif

        @if (Gate::allows('circle.create') && !$not_submitted_circles->isEmpty())
            <hr>
            <list-view class="pt-spacing-sm">
                <template v-slot:description>
                    <div class="text-muted">
                        <i class="fas fa-info-circle fa-fw"></i>
                        以下の企画参加登録は提出されていません。提出期限までに提出してください。
                    </div>
                </template>
                @each('includes.circle_list_view_item_with_status', $not_submitted_circles, 'circle')
            </list-view>
        @endif
    </app-container>
@endsection
