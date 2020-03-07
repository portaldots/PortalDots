@extends('errors.layout')

@section('title', '403 Forbidden')
@section('top', $exception->getMessage() ?: 'アクセスが拒否されました')
@section('message', '権限がないか、アクセスできないページです')
@section('twitter', false)
