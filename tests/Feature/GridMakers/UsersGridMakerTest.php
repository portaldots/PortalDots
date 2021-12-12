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
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-08 00:00:00'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-08 00:00:00'));
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

    public function formatLastAccessedAt_provider()
    {
        return [
            [new CarbonImmutable('2020-02-07 23:23:23'), '1時間以内'],
            [new CarbonImmutable('2020-02-01 01:23:45'), '6日前'],
            [new CarbonImmutable('2019-10-31 19:10:31'), '3ヶ月前'],
            [new CarbonImmutable('2012-05-22 09:30:00'), '1年以上前']
        ];
    }

    /**
     * @test
     * @dataProvider formatLastAccessedAt_provider
     */
    public function formatLastAccessedAt(CarbonImmutable $last_accessed_at, string $expected)
    {
        $user = factory(User::class)->make([
            'last_accessed_at' => $last_accessed_at,
        ]);
        $this->assertSame($expected, $user->formatLastAccessedAt());
    }
}
