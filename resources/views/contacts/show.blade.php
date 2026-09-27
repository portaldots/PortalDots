@extends('layouts.app')

@section('title', 'お問い合わせ')

@section('content')
    <app-container>
        <list-view>
            <template v-slot:title>お問い合わせ</template>
            <template v-slot:description>
                お問い合わせへの返信は <strong>{{ Auth::user()->email }}</strong> に送信されます。メールアドレスは<a
                    href="{{ route('user.edit') }}">ユーザー設定</a>で変更できます。
            </template>
            @if (isset($thread))
                <list-view-form-group>
                    <template v-slot:label>状況</template>
                    @if ($thread->status === \App\Eloquents\Thread::STATUS_AWAITING_REPLY)
                        <app-badge success>{{ $thread->circleStatusLabel() }}</app-badge>
                    @elseif ($thread->status === \App\Eloquents\Thread::STATUS_RESOLVED)
                        <app-badge muted>{{ $thread->circleStatusLabel() }}</app-badge>
                    @else
                        <app-badge primary>{{ $thread->circleStatusLabel() }}</app-badge>
                    @endif
                </list-view-form-group>
            @endif
        </list-view>
    </app-container>

    @unless ($entries->isEmpty())
        <app-container>
            <list-view>
                <template v-slot:title>やり取り</template>
                @foreach ($entries as $entry)
                    <list-view-item no-border>
                        <template v-slot:title>
                            @if ($entry->kind === \App\Eloquents\ThreadEntry::KIND_EVENT)
                                {{ $entry->event_type }}
                            @elseif ($entry->author_side === \App\Eloquents\ThreadEntry::AUTHOR_SIDE_STAFF)
                                スタッフ
                            @else
                                {{ optional($entry->author)->name }}
                            @endif
                            @if (isset($entry->contactCategory))
                                <app-badge muted small>{{ $entry->contactCategory->name }}</app-badge>
                            @endif
                        </template>
                        <template v-slot:meta>
                            @datetime($entry->created_at)
                        </template>
                        @if ($entry->kind !== \App\Eloquents\ThreadEntry::KIND_EVENT)
                            <div style="white-space: pre-wrap">{{ $entry->body }}</div>
                            @unless ($entry->attachments->isEmpty())
                                <ul class="mb-0">
                                    @foreach ($entry->attachments as $attachment)
                                        <li>
                                            <a href="{{ route('contacts.attachments.show', ['attachment' => $attachment]) }}"
                                                target="_blank" rel="noopener noreferrer">
                                                <i class="fas fa-paperclip fa-fw"></i>
                                                {{ $attachment->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endunless
                        @endif
                    </list-view-item>
                @endforeach
            </list-view>
        </app-container>
    @endunless

    <app-container>
        <form method="post" action="{{ route('contacts.post') }}" enctype="multipart/form-data">
            @csrf
            @if (isset($circle))
                <input type="hidden" name="circle_id" value="{{ $circle->id }}">
            @endif
            <input type="hidden" name="client_token" value="{{ $clientToken }}">

            <list-view>
                <template v-slot:title>メッセージを送る</template>
                @if (isset($circle))
                    <list-view-form-group>
                        <template v-slot:label>企画名</template>
                        <input type="text" readonly value="{{ $circle->name }}({{ $circle->group_name }})"
                            class="form-control">
                        @if (Auth::user()->circles()->approved()->count() > 1)
                            <template v-slot:append>
                                <a href="{{ route('circles.selector.show', ['redirect_to' => Request::path()]) }}">変更</a>
                            </template>
                        @endif
                    </list-view-form-group>
                @else
                    <list-view-form-group label-for="name">
                        <template v-slot:label>名前</template>
                        <input type="text" id="name" readonly
                            value="{{ Auth::user()->name }}({{ Auth::user()->student_id }})"
                            class="form-control is-plaintext">
                    </list-view-form-group>
                @endif
                @unless ($categories->isEmpty())
                    <list-view-form-group label-for="category">
                        <template v-slot:label>お問い合わせ項目</template>
                        <template v-slot:description>以下のリストから項目を選択してください</template>
                        <select id="category" name="category" class="form-control @error('category') is-invalid @enderror ">
                            <option hidden>選択してください</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category', 0) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                            <option value="0">その他</option>
                        </select>
                        @error('category')
                            <template v-slot:invalid>
                                {{ $message }}
                            </template>
                        @enderror
                    </list-view-form-group>
                @else
                    <input type="hidden" id="category" name="category" value="0">
                @endunless
                <list-view-form-group label-for="contact_body">
                    <template v-slot:label>お問い合わせ内容</template>
                    <template v-slot:description>確認のため、お問い合わせ内容をメールで送信いたします。</template>
                    <textarea name="contact_body" id="contact_body"
                        class="form-control {{ $errors->has('contact_body') ? 'is-invalid' : '' }}" rows="10"
                        required>{{ old('contact_body') }}</textarea>

                    @if ($errors->has('contact_body'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('contact_body') as $message)
                                {{ $message }}<br>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-form-group label-for="attachments">
                    <template v-slot:label>添付ファイル</template>
                    <template v-slot:description>
                        {{ \App\Eloquents\ThreadEntryAttachment::MAX_FILES }}個まで、1つにつき10MBまで添付できます。
                    </template>
                    <input type="file" id="attachments" name="attachments[]" multiple
                        class="form-control {{ $errors->has('attachments') ? 'is-invalid' : '' }}">
                    @if ($errors->has('attachments'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('attachments') as $message)
                                {{ $message }}<br>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>
            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">送信</button>
            </div>
        </form>
    </app-container>
@endsection
