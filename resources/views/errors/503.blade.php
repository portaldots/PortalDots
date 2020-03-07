@extends('errors.layout_no_drawer')

@section('title', '503 Service Unavailable')
@section('top', $exception->getMessage() ?: '現在「' . config('app.name') . '」を利用することができません')
@section('message', 'しばらく経ってからもう一度お試しください')
@section('contact', "解決しない場合は「".config('portal.admin_name')."」へお問い合わせください。")
@section('twitter', true)
