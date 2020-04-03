@extends('v2.layouts.no_drawer')

@section('title', '企画参加登録')
    
@section('content')
    @include('v2.includes.circle_register_header')
    
    <app-container medium>
        <list-view>
            <template v-slot:title>参加登録の提出</template>
            <list-view-card>
                以下の情報で参加登録を提出します。<strong>参加登録の提出後は、登録内容の変更ができなくなります。</strong>
                <hr>
                <dl>
                    @foreach ([
                        'name' => '企画の名前',
                        'name_yomi' => '企画の名前(よみ)',
                        'group_name' => '企画団体の名前',
                        'group_name_yomi' => '企画団体の名前(よみ)',
                        ] as $field_name => $display_name)
                        <dt>{{ $display_name }} — <a href="{{ route('circles.edit', ['circle' => $circle]) }}">変更</a></dt>
                        <dd>{{ $circle->$field_name }}</dd>
                    @endforeach
                    <dt>メンバー — <a href="{{ route('circles.users.index', ['circle' => $circle]) }}">変更</a></dt>
                    <dd>
                        <ul>
                            @foreach ($circle->users as $user)
                                <li>
                                    {{ $user->name }}
                                    ({{ $user->student_id }})
                                    @if ($user->pivot->is_leader)
                                        <span class="badge is-primary">責任者</span>
                                    @else
                                        <span class="badge is-muted">学園祭係(副責任者)</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </dd>
                </dl>
            </list-view-card>
        </list-view>
    
        <form action="{{ route('circles.submit', ['circle' => $circle]) }}" method="post">
            @csrf
            <div class="text-center pt-spacing-sm pb-spacing">
                <a class="btn is-secondary" href="{{ route('circles.users.index', ['circle' => $circle]) }}">
                    <i class="fas fa-chevron-left"></i>
                    「メンバーを招待」へもどる
                </a>
                <button type="submit" class="btn is-primary">
                    <strong>参加登録を提出</strong>
                </button>
            </div>
        </form>
    </app-container>
@endsection
