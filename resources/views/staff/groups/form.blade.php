@extends('layouts.app')

@section('title', empty($group) ? '新規作成 — 団体' : "{$group->name} — 団体")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.groups.index') }}">
        団体管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ empty($group) ? route('staff.groups.store') : route('staff.groups.update', $group) }}"
        enctype="multipart/form-data">
        @method(empty($group) ? 'post' : 'patch')
        @csrf

        <app-header>
            @if (empty($group))
                <template v-slot:title>団体を新規作成</template>
            @endif
            @isset($group)
                <template v-slot:title>団体を編集</template>
                <div>団体ID : {{ $group->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
                <list-view-form-group label-for="name">
                    <template v-slot:label>
                        団体名
                        <app-badge danger>必須</app-badge>
                    </template>
                    <input id="name" class="form-control @error('name') is-invalid @enderror" type="text" name="name"
                        value="{{ old('name', empty($group) ? '' : $group->name) }}" required>
                    @if ($errors->has('name'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('name') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="name_yomi">
                    <template v-slot:label>
                        団体名(よみ)
                        <app-badge danger>必須</app-badge>
                    </template>
                    <input id="name_yomi" class="form-control @error('name') is-invalid @enderror" type="text"
                        name="name_yomi" value="{{ old('name_yomi', empty($group) ? '' : $group->name_yomi) }}" required>
                    @if ($errors->has('name_yomi'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('name_yomi') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <list-view>
                <template v-slot:title>団体のメンバー</template>
                <list-view-form-group label-for="owner">
                    <template v-slot:label>責任者の{{ config('portal.student_id_name') }}</template>
                    <input type="text" class="form-control @error('owner') is-invalid @enderror" id="owner"
                        name="owner" value="{{ old('owner', empty($owner) ? '' : $owner->student_id) }}">
                    @if ($errors->has('owner'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('owner') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="members">
                    <template v-slot:label>学園祭係(副責任者)の{{ config('portal.student_id_name') }}</template>
                    <template
                        v-slot:description>{{ config('portal.student_id_name') }}を改行して入力することで複数の学園祭係を追加できます。{{ config('portal.users_number_to_submit_circle') - 1 }}人を下回っていても構いません。</template>
                    <textarea id="members" class="form-control @error('members') is-invalid @enderror" name="members" rows="3">{{ old('members', empty($members) ? '' : $members) }}</textarea>
                    @if ($errors->has('members'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('members') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <list-view>
                <list-view-form-group label-for="notes">
                    <template v-slot:label>スタッフ用メモ</template>
                    <template v-slot:description>ここに入力された内容はスタッフのみ閲覧できます。スタッフ内で共有したい事項を残しておくメモとしてご活用ください。</template>
                    <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes" rows="5">{{ old('notes', empty($group) ? '' : $group->notes) }}</textarea>
                    @if ($errors->has('notes'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('notes') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <app-fixed-form-footer>
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </app-fixed-form-footer>
        </app-container>
    </form>
@endsection
