<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms\Answers\Uploads;

use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use App\Eloquents\Form;
use App\Eloquents\User;
use App\Services\Forms\DownloadZipService;
use App\Services\Forms\Exceptions\NoDownloadFileExistException;
use App\Services\Forms\Exceptions\ZipArchiveNotSupportedException;

class DownloadZipActionTest extends TestCase
{
    use RefreshDatabase;

    private $form;
    private $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->form = factory(Form::class)->create();
        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function ダウンロードできる()
    {
        // ダウンロードされるZIPファイルの代わり
        $example_file = new File(base_path('tests/TestFile.png'));
        Storage::putFileAs('answer_details_zip', $example_file, 'TestFile.png');

        $this->mock(DownloadZipService::class, function ($mock) {
            $mock->shouldReceive('makeZip')
                ->once()
                ->andReturn(storage_path("app/answer_details_zip/TestFile.png"));
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.forms.answers.uploads.download_zip', ['form' => $this->form]));

        $response->assertOk();
    }

    /**
     * @test
     */
    public function ダウンロードできるファイルがない時に適切にエラー表示される()
    {
        $this->mock(DownloadZipService::class, function ($mock) {
            $mock->shouldReceive('makeZip')
            ->once()
            ->andThrow(new NoDownloadFileExistException());
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.forms.answers.uploads.download_zip', ['form' => $this->form]));

        $response->assertSessionHas('topAlert.title');
    }

    /**
     * @test
     */
    public function ZipArchive非対応時に適切にエラー表示される()
    {
        $this->mock(DownloadZipService::class, function ($mock) {
            $mock->shouldReceive('makeZip')
            ->once()
            ->andThrow(new ZipArchiveNotSupportedException());
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.forms.answers.uploads.download_zip', ['form' => $this->form]));

        $response->assertSessionHas('topAlert.title');
    }

    /**
     * @test
     */
    public function スタッフ以外はダウンロードできない()
    {
        $response = $this->actingAs(factory(User::class)->create())
            ->post(route('staff.forms.answers.uploads.download_zip', ['form' => $this->form]));

        $response->assertStatus(403);
    }
}
