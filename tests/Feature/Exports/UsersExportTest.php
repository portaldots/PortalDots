<?php

namespace Tests\Feature\Exports;

use App\Eloquents\User;
use App\Exports\UsersExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class UsersExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var UsersExport
     */
    private $usersExport;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->usersExport = App::make(UsersExport::class);
        $this->user = factory(User::class)->create([
            'student_id' => '0123456',
            'univemail_local_part' => 'this-is-local-part-12345',
            'univemail_domain_part' => 's.example.ac.jp',
            'tel' => '09012345678',
            'notes' => '"こんにちは,'
        ]);
    }

    /**
     * @test
     */
    public function map_ユーザー情報のフォーマットが正常に行われる()
    {
        $this->assertEquals(
            [
                $this->user->id,
                '"0123456"',
                $this->user->name,
                $this->user->name_yomi,
                $this->user->email,
                'this-is-local-part-12345@s.example.ac.jp',
                '"09012345678"',
                'いいえ',
                'いいえ',
                '認証済み',
                '認証済み',
                '1時間以内',
                '"こんにちは,',
                $this->user->created_at,
                $this->user->updated_at,
            ],
            $this->usersExport->map($this->user)
        );
    }
}
