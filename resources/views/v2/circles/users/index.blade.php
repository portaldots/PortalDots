@extends('v2.layouts.no_drawer')

@section('title', '企画参加登録')
    
@section('content')
    <app-header container-medium text-center>
        <template v-slot:title>企画参加登録</template>
    </app-header>
    
    <app-container medium>
        <list-view>
            <template v-slot:title>メンバーを招待</template>
            <list-view-card>
                あなたの企画「{{ $circle->name }}」の学園祭係(副責任者)に、以下のURLを共有してください。これは、学園祭係(副責任者)の招待URLです。
            </list-view-card>
            <list-view-form-group label-for="invitation_url">
                <template v-slot:label>
                    招待URL
                </template>
                <template v-slot:description>
                    あなたの企画の部外者にこのURLを教えないでください
                </template>
                <input id="invitation_url" type="text" class="form-control" name="invitation_url"
                    value="{{ $invitation_url }}" readonly>
            </list-view-form-group>
        </list-view>
    
        <div class="text-center pt-spacing-sm pb-spacing">
            <button class="btn is-primary-inverse is-wide" v-on:click="share({{ $share_json }})">
                <i class="far fa-share-square"></i>
                URLを共有
            </button>
        </div>
    
        <list-view>
            <template v-slot:title>メンバー一覧</template>
            <template v-slot:description>「{{ $circle->name }}」に所属するメンバーのリスト</template>
    
            @foreach ($circle->users as $user)
                <list-view-item>
                    <template v-slot:title>
                        {{ $user->name }}
                        ({{ $user->student_id }})
                        @if ($user->pivot->is_leader)
                            <span class="badge is-primary">責任者</span>
                        @else
                            <span class="badge is-muted">学園祭係(副責任者)</span>
                        @endif
                    </template>
                    @unless ($user->pivot->is_leader)
                        <template v-slot:meta>
                            <form-with-confirm action="{{ route('circles.users.destroy', ['circle' => $circle, 'user' => $user]) }}"
                                method="post" confirm-message="本当にこのユーザーをメンバーから削除しますか？">
                                @method('delete')
                                @csrf
                                <button type="submit" class="btn is-danger is-sm">
                                    メンバーから削除
                                </button>
                            </form-with-confirm>
                        </template>
                    @endunless
                </list-view-item>
            @endforeach
        </list-view>
    
        <div class="text-center pt-spacing-md pb-spacing">
            @unless ($circle->canSubmit())
                <p class="text-danger">
                    企画参加登録を提出するには、あと{{ config('portal.users_number_to_submit_circle') - count($circle->users) }}人がメンバーになる必要があります。
                </p>
            @else
                <a href="{{ route('circles.confirm', ['circle' => $circle]) }}" class="btn is-primary is-wide">
                    企画参加登録の最終確認へ進む
                </a>
            @endunless
        </div>
    </app-container>
@endsection
