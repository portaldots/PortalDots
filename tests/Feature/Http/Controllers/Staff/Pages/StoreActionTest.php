<?php

namespace Tests\Feature\Http\Controllers\Staff\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Eloquents\Tag;
use App\Eloquents\Circle;
use App\Eloquents\User;
use App\Eloquents\Page;
use App\Eloquents\Email;
use DB;

class StoreActionTest extends TestCase
{
    // リクエストの内容が createPage メソッドに渡されるか

    // バリデーションエラーがちゃんと発生するか

    // 一斉配信予約した場合は sendEmailsByPage が呼ばれるか
}
