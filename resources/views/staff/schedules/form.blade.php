@extends('layouts.app')

@section('title', empty($schedule) ? '新規作成 — スケジュール' : "{$schedule->name} — スケジュール")

@section('navbar')
    <app-nav-bar-back inverse href="{{ route('staff.schedules.index') }}">
        スケジュール管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ empty($schedule) ? route('staff.schedules.store') : route('staff.schedules.update', $schedule) }}" enctype="multipart/form-data">
        @method(empty($schedule) ? 'post' : 'patch' )
        @csrf

        <app-header>
            @if (empty($schedule))
                <template v-slot:title>予定を新規作成</template>
            @endif
            @isset ($schedule)
                <template v-slot:title>予定を編集</template>
                <div>予定ID : {{ $schedule->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
                <list-view-form-group label-for="name">
                    <template v-slot:label>
                        予定名
                        <app-badge danger>必須</app-badge>
                    </template>
                    <input id="name" class="form-control @error('name') is-invalid @enderror" type="text"
                        name="name" value="{{ old('name', empty($schedule) ? '' : $schedule->name) }}"
                        required>
                    @if ($errors->has('name'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('name') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="start_at">
                    <template v-slot:label>
                        開始日時
                        <app-badge danger>必須</app-badge>　
                    </template>
                    <template v-slot:description>
                        この予定が始まる日時
                    </template>
                    <input id="start_at" type="datetime-local" class="form-control @error('start_at') is-invalid @enderror"
                        name="start_at"
                        value="{{ old('start_at', isset($schedule) ? $schedule->start_at->format('Y-m-d\TH:i') : '') }}" required>
                    @if ($errors->has('start_at'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('start_at') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="place">
                    <template v-slot:label>
                        場所
                        <app-badge danger>必須</app-badge>
                    </template>
                    <input id="place" class="form-control @error('place') is-invalid @enderror" type="text"
                        name="place" value="{{ old('place', empty($schedule) ? '' : $schedule->place) }}"
                        required>
                    @if ($errors->has('place'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('place') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group>
                    <template v-slot:label>
                        説明&nbsp;
                        <app-badge outline muted>Markdown</app-badge>
                    </template>
                    <markdown-editor input-name="description" default-value="{{ old('description', empty($schedule) ? '' : $schedule->description) }}"></markdown-editor>
                    @if ($errors->has('description'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('description') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="notes">
                    <template v-slot:label>スタッフ用メモ</template>
                    <template v-slot:description>ここに入力された内容はスタッフのみ閲覧できます。スタッフ内で共有したい事項を残しておくメモとしてご活用ください。</template>
                    <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes"
                        rows="5">{{ old('notes', empty($schedule) ? '' : $schedule->notes) }}</textarea>
                    @if ($errors->has('notes'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('notes') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </div>
        </app-container>
    </form>
@endsection
