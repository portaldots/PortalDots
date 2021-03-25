@extends('layouts.app')

@section('title', 'フォームの複製')

@section('navbar')
    <app-nav-bar-back href="{{ url('home_staff/applications') }}" data-turbolinks="false">
        申請管理
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>
                「{{ $form->name }}」の複製
            </template>

            <list-view-card>
                <p>「{{ $form->name }}」の設問も全て複製されます</p>
                <p>「{{ $form->name }}のコピー」という名前で複製されます</p>
                <p>「{{ $form->name }}のコピー」は<b>非公開</b>で作成されます</p>
            </list-view-card>
            <form action="{{ route('staff.forms.copy', ['form' => $form]) }}" method="post">
                @csrf
                <list-view-action-btn button submit>
                    複製する
                </list-view-action-btn>
            </form>
        </list-view>
    </app-container>
@endsection
