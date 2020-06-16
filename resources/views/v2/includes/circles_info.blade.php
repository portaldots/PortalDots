<dl>
    @foreach ([
        'name' => '企画名',
        'name_yomi' => '企画名(よみ)',
        'group_name' => '企画を出店する団体の名称',
        'group_name_yomi' => '企画を出店する団体の名称(よみ)',
        ] as $field_name => $display_name)
        <dt>{{ $display_name }}
            @if (Auth::user()->isLeader($circle))
            — <a href="{{ route('circles.edit', ['circle' => $circle]) }}">変更</a>
            @endif
        </dt>
        <dd>{{ $circle->$field_name }}</dd>
    @endforeach
    <dt>メンバー
        @if (Auth::user()->isLeader($circle))
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
</dl>
