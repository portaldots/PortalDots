@extends('v2.layouts.no_drawer')

@section('title', '企画参加登録')
    
@section('content')
    <app-header container-medium text-center>
        <template v-slot:title>企画参加登録</template>
    </app-header>
    
    <app-container medium>
        <list-view>
            <template v-slot:title>参加登録の提出</template>
            <list-view-card>
                以下の情報で参加登録を提出します。<strong>参加登録の提出後は、登録内容の変更ができなくなります。</strong>
                <hr>
                <dl>
                    <dt>企画の名前</dt>
                    <dd>{{ $circle->name }}</dd>
                    <dt>企画の名前(よみ)</dt>
                    <dd>{{ $circle->name_yomi }}</dd>
                    <dt>企画団体の名前</dt>
                    <dd>{{ $circle->group_name }}</dd>
                    <dt>企画団体の名前(よみ)</dt>
                    <dd>{{ $circle->group_name_yomi }}</dd>
                    <dt>メンバー</dt>
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
                    「メンバーを招待」へもどる
                </a>
                <button type="submit" class="btn is-primary">
                    <strong>参加登録を提出</strong>
                </button>
            </div>
        </form>
    </app-container>
@endsection
