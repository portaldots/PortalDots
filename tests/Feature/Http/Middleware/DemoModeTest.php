<?php

namespace Tests\Feature\Http\Middleware;

use App\Eloquents\User;
use App\Http\Middleware\DemoMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var DemoMode
     */
    private $demoMode;

    public function setUp(): void
    {
        parent::setUp();
        $this->demoMode = App::make(DemoMode::class);
    }

    /**
     * @test
     */
    public function handle_デモモードではない場合はGET以外のリクエストも許可する()
    {
        /** @var User */
        $user = factory(User::class)->create();

        $this->actingAs($user);

        $request = Request::create(route('contacts.post'), 'POST');

        $response = $this->demoMode->handle($request, function () {
            return 'handled!';
        });

        $this->assertSame('handled!', $response);
    }

    /**
     * @test
     */
    public function handle_デモモードの場合はGET以外のリクエストを拒否()
    {
        Config::set('portal.enable_demo_mode', true);

        /** @var User */
        $user = factory(User::class)->create();

        $this->actingAs($user);

        $request = Request::create(route('contacts.post'), 'POST');

        $response = $this->demoMode->handle($request, function () {
        });

        $testResponse = $this->createTestResponse($response);

        $testResponse->assertRedirect();
    }
}
