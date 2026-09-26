<?php

namespace Tests\Feature\Http\Controllers\Admin\Portal;

use App\Eloquents\User;
use App\Services\Install\PortalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EditActionTest extends TestCase
{
    use RefreshDatabase;

    public static function portalUrls(): array
    {
        return [
            '未設定' => [null],
            '空文字' => [''],
            '設定済み' => ['https://portal.example.test/festival'],
        ];
    }

    #[DataProvider('portalUrls')]
    public function test_settings_page_displays_a_url_even_when_it_is_not_configured(?string $configuredUrl): void
    {
        $this->mock(PortalService::class, function ($mock) use ($configuredUrl) {
            $mock->shouldReceive('getInfo')->once()->andReturn(['APP_URL' => $configuredUrl]);
        });

        $admin = factory(User::class)->states('admin')->create();
        $expectedUrl = $configuredUrl ?: url('/');

        $response = $this->actingAs($admin)
            ->withSession(['staff_authorized' => true])
            ->get(route('admin.portal.edit'));

        $response->assertOk();
        $response->assertSee('value="' . e($expectedUrl) . '"', false);
    }
}
