<?php

namespace Tests\Feature\GridMakers;

use App\Eloquents\User;
use App\GridMakers\UsersGridMaker;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class UsersGridMakerTest extends TestCase
{
    /**
     * @var UsersGridMaker
     */
    private $usersGridMaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->usersGridMaker = App::make(UsersGridMaker::class);
        Carbon::setTestNow(new CarbonImmutable('2020-02-08 00:00:00'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2020-02-08 00:00:00'));
    }

    /**
     * @test
     */
    public function map()
    {
        $user = factory(User::class)->make([
            'last_accessed_at' => '2020-02-02 02:02:02',
            'created_at' => '2020-02-02 02:02:02',
            'updated_at' => '2020-02-02 02:02:02',
        ]);

        $result = $this->usersGridMaker->map($user);

        $this->assertSame('5日前', $result['last_accessed_at']); // 02:02:02 を迎えていないので 5日前 と返ってくる
        $this->assertSame('2020/02/02 02:02:02', $result['created_at']);
        $this->assertSame('2020/02/02 02:02:02', $result['updated_at']);
    }
}
