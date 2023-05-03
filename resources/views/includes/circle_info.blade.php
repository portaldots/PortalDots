<dl>
    <dt>参加種別</dt>
    <dd>{{ $circle->participationType->name }}</dd>
    @foreach ([
        'name' => '企画名',
        'name_yomi' => '企画名(よみ)',
        'group_name' => '企画を出店する団体の名称',
        'group_name_yomi' => '企画を出店する団体の名称(よみ)',
    ] as $field_name => $display_name)
        <dt>{{ $display_name }}
            @if (Auth::user()->isLeaderInCircle($circle) && Gate::allows('circle.update', $circle))
                — <a href="{{ route('circles.edit', ['circle' => $circle]) }}">変更</a>
            @endif
        </dt>
        <dd>{{ $circle->$field_name }}</dd>
    @endforeach
    <dt>メンバー
        @if (Auth::user()->isLeaderInCircle($circle) && Gate::allows('circle.update', $circle))
            — <a href="{{ route('circles.users.index', ['circle' => $circle]) }}">変更</a>
        @endif
    </dt>
    <dd>
        <ul>
            @foreach ($circle->users as $user)
                <li>
                    {{ $user->name }}
                    ({{ $user->student_id }})
                    @if ($user->pivot->is_leader)
                        <app-badge primary>責任者</app-badge>
                    @else
                        <app-badge muted>学園祭係(副責任者)</app-badge>
                    @endif
                </li>
            @endforeach
        </ul>
    </dd>
    @unless ($circle->places->isEmpty())
        <dt>
            使用場所
        </dt>
        <dd>
            <ul>
                @foreach ($circle->places as $place)
                    <li>
                        {{ $place->name }}
                    </li>
                @endforeach
            </ul>
        </dd>
    @endunless
</dl>
