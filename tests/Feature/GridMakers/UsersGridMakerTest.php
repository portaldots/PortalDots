<?php

namespace Tests\Feature\GridMakers;

use App\Eloquents\User;
use App\GridMakers\UsersGridMaker;
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
    }

    /**
     * @test
     */
    public function map()
    {
        $user = factory(User::class)->make([
            'created_at' => '2020-02-02 02:02:02',
            'updated_at' => '2020-02-02 02:02:02',
        ]);

        $result = $this->usersGridMaker->map($user);

        $this->assertSame('2020/02/02 02:02:02', $result['created_at']);
        $this->assertSame('2020/02/02 02:02:02', $result['updated_at']);
    }
}
