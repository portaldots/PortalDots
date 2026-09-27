<?php

namespace Tests\Feature\Services\Forms;

use App\Eloquents\Form;
use App\Services\Forms\DownloadZipService;
use App\Services\Forms\Exceptions\NoDownloadFileExistException;
use App\Services\Forms\Exceptions\ZipArchiveNotSupportedException;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class DownloadZipServiceTest extends TestCase
{
    private $form;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->form = new Form();
        $this->form->id = 123;
        Storage::put('answer_details/first.txt', 'first upload');
        Storage::put('answer_details/second.txt', 'second upload');
    }

    public function test_archive_contains_only_existing_uploads()
    {
        $path = $this->app->make(DownloadZipService::class)->makeZip($this->form, [
            'answer_details/first.txt',
            'answer_details/missing.txt',
            null,
            '',
            'answer_details/second.txt',
        ]);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));
        $this->assertSame(2, $zip->numFiles);
        $this->assertSame('first upload', $zip->getFromName('first.txt'));
        $this->assertSame('second upload', $zip->getFromName('second.txt'));
        $zip->close();
        $this->assertSame(realpath(Storage::path('answer_details_zip')), dirname(realpath($path)));
    }

    public function test_repeated_exports_are_isolated()
    {
        $this->freezeTime();
        $service = $this->app->make(DownloadZipService::class);
        $first = $service->makeZip($this->form, ['answer_details/first.txt']);
        $second = $service->makeZip($this->form, ['answer_details/second.txt']);

        $this->assertNotSame($first, $second);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($second));
        $this->assertSame(1, $zip->numFiles);
        $this->assertSame('second upload', $zip->getFromName('second.txt'));
        $this->assertFalse($zip->getFromName('first.txt'));
        $zip->close();
        $this->assertTrue($zip->open($first));
        $this->assertSame(1, $zip->numFiles);
        $this->assertSame('first upload', $zip->getFromName('first.txt'));
        $zip->close();
    }

    /** @dataProvider invalidPaths */
    public function test_rejects_invalid_paths(array $paths)
    {
        Storage::put('private.txt', 'private content');
        Storage::put('answer_details_backup/private.txt', 'private content');
        symlink(Storage::path('private.txt'), Storage::path('answer_details/link.txt'));
        $this->expectException(NoDownloadFileExistException::class);

        $this->app->make(DownloadZipService::class)->makeZip($this->form, $paths);
    }

    public static function invalidPaths(): array
    {
        return [
            'no files' => [[]],
            'empty values' => [[null, '']],
            'missing file' => [['answer_details/missing.txt']],
            'another directory' => [['private.txt']],
            'parent directory' => [['answer_details/../private.txt']],
            'similar directory prefix' => [['answer_details/../answer_details_backup/private.txt']],
            'directory' => [['answer_details/']],
            'backslash' => [['answer_details/..\\private.txt']],
            'null byte' => [["answer_details/first.txt\0"]],
            'symlink' => [['answer_details/link.txt']],
        ];
    }

    public function test_reports_an_archive_open_error()
    {
        $this->mock(ZipArchive::class, function ($mock) {
            $mock->shouldReceive('open')->once()->andReturn(ZipArchive::ER_OPEN);
        });
        $this->expectException(ZipArchiveNotSupportedException::class);

        $this->app->make(DownloadZipService::class)->makeZip($this->form, ['answer_details/first.txt']);
    }

    /** @dataProvider writeFailures */
    public function test_removes_incomplete_archives_after_a_write_failure(bool $addResult, bool $closeResult)
    {
        $archivePath = null;
        $this->mock(ZipArchive::class, function ($mock) use (&$archivePath, $addResult, $closeResult) {
            $mock->shouldReceive('open')->once()->andReturnUsing(function ($path) use (&$archivePath) {
                $archivePath = $path;
                file_put_contents($path, 'incomplete archive');
                return true;
            });
            $mock->shouldReceive('addFile')->once()->andReturn($addResult);
            $mock->shouldReceive('close')->once()->andReturn($closeResult);
        });

        try {
            $this->app->make(DownloadZipService::class)->makeZip($this->form, ['answer_details/first.txt']);
            $this->fail('An incomplete archive must not be returned.');
        } catch (ZipArchiveNotSupportedException $e) {
            $this->assertFileDoesNotExist($archivePath);
        }
    }

    public static function writeFailures(): array
    {
        return [
            'addFile failure' => [false, true],
            'close failure' => [true, false],
        ];
    }
}
