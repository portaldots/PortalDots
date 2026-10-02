<?php

namespace Tests\Feature\Http\Controllers\Staff\Places;

use App\Eloquents\Circle;
use App\Eloquents\Permission;
use App\Eloquents\Place;
use App\Eloquents\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use PDOException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ImportActionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    public function setUp(): void
    {
        parent::setUp();

        $this->staff = factory(User::class)->state('staff')->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function CSVで場所を新規作成および更新して関連と操作ログを保持する()
    {
        $this->grantPermissions($this->staff, ['staff.places.read,import']);
        $place = factory(Place::class)->create([
            'name' => '更新前',
            'type' => 1,
            'notes' => '変更前メモ',
        ]);
        $circle = factory(Circle::class)->create();
        $place->circles()->attach($circle);
        Activity::query()->delete();

        $response = $this->actingAsStaff($this->staff)->post(route('staff.places.import.store'), [
            'importFile' => $this->csv([
                [$place->id, '更新後', '屋外', " 変更後メモ\n2行目 "],
                ['', '新規場所', '特殊場所', '新規メモ'],
            ]),
        ]);

        $response
            ->assertRedirect(route('staff.places.index'))
            ->assertSessionHas('topAlert.title', '場所情報をインポートしました');
        $this->assertDatabaseHas('places', [
            'id' => $place->id,
            'name' => '更新後',
            'type' => 2,
            'notes' => " 変更後メモ\n2行目 ",
        ]);
        $created = Place::query()->where('name', '新規場所')->sole();
        $this->assertSame(3, $created->type);
        $this->assertSame('新規メモ', $created->notes);
        $this->assertEquals([$circle->id], $place->fresh()->circles()->pluck('circles.id')->all());
        $this->assertCount(0, $created->circles);
        $this->assertDatabaseCount('activity_log', 2);
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Place::class,
            'subject_id' => $place->id,
            'event' => 'updated',
        ]);
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Place::class,
            'subject_id' => $created->id,
            'event' => 'created',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 既存場所を含む4列テンプレートをそのまま再インポートできる()
    {
        $this->grantPermissions($this->staff, [
            'staff.places.read,import',
            'staff.places.read,export',
        ]);
        factory(Place::class)->create([
            'name' => '001号室',
            'type' => 1,
            'notes' => '=SUM(A1:A2)',
        ]);

        $template = $this->actingAsStaff($this->staff)
            ->get(route('staff.places.import.template'));

        $template
            ->assertOk()
            ->assertDownload();
        $contents = $template->streamedContent();
        $this->assertStringContainsString('"場所ID","場所名","タイプ","スタッフ用メモ"', $contents);
        $this->assertStringContainsString('001号室', $contents);
        $this->assertStringContainsString("\"'=SUM(A1:A2)\"", $contents);
        $this->assertStringNotContainsString('企画ID', $contents);

        $response = $this->actingAsStaff($this->staff)->post(route('staff.places.import.store'), [
            'importFile' => UploadedFile::fake()->createWithContent('template.csv', $contents),
        ]);

        $response->assertRedirect(route('staff.places.index'));
        $this->assertDatabaseHas('places', [
            'name' => '001号室',
            'notes' => '=SUM(A1:A2)',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 全行を検証してエラーを行と項目と理由で表示し変更をロールバックする()
    {
        $this->grantPermissions($this->staff, ['staff.places.read,import']);
        $place = factory(Place::class)->create(['name' => '既存場所']);
        Activity::query()->delete();

        $response = $this->actingAsStaff($this->staff)->post(route('staff.places.import.store'), [
            'importFile' => $this->csv([
                ['', '作成されない場所', '屋内', ''],
                [999999, '重複名', '不明', ''],
                [$place->id, '重複名', '屋外', ''],
                [$place->id, '別名', '特殊場所', ''],
            ]),
        ]);

        $response
            ->assertRedirect(route('staff.places.import.index'))
            ->assertSessionHasErrors('importFile')
            ->assertSessionHas('importErrors');
        $this->assertDatabaseMissing('places', ['name' => '作成されない場所']);
        $this->assertSame('既存場所', $place->fresh()->name);
        $this->assertDatabaseCount('activity_log', 0);

        $this->actingAsStaff($this->staff)
            ->get(route('staff.places.import.index'))
            ->assertOk()
            ->assertSee('2')
            ->assertSee('場所ID')
            ->assertSee('指定された場所IDは存在しません。')
            ->assertSee('同じ場所IDがCSV内で重複しています。')
            ->assertSee('タイプ')
            ->assertSee('タイプは「屋内」「屋外」「特殊場所」のいずれかを入力してください。');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function CSV内の重複場所名をDB照合順序に従って拒否する()
    {
        $this->grantPermissions($this->staff, ['staff.places.read,import']);
        Activity::query()->delete();

        $this->actingAsStaff($this->staff)->post(route('staff.places.import.store'), [
            'importFile' => $this->csv([
                ['', 'DuplicateName', '屋内', ''],
                ['', 'duplicatename', '屋外', ''],
            ]),
        ])->assertSessionHasErrors('importFile');

        $this->assertDatabaseMissing('places', ['name' => 'DuplicateName']);
        $this->assertDatabaseMissing('places', ['name' => 'duplicatename']);
        $this->assertDatabaseCount('activity_log', 0);
        $this->actingAsStaff($this->staff)
            ->get(route('staff.places.import.index'))
            ->assertSee('3')
            ->assertSee('場所名')
            ->assertSee('同名の場所が既に存在します。');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 必須ヘッダー欠落を画面に表示する()
    {
        $this->grantPermissions($this->staff, ['staff.places.read,import']);
        $file = $this->csv(
            [['', '場所A', '屋内']],
            ['場所ID', '場所名', 'タイプ']
        );

        $this->actingAsStaff($this->staff)
            ->post(route('staff.places.import.store'), ['importFile' => $file])
            ->assertRedirect(route('staff.places.import.index'));

        $this->actingAsStaff($this->staff)
            ->get(route('staff.places.import.index'))
            ->assertSee('1')
            ->assertSee('スタッフ用メモ')
            ->assertSee('必須ヘッダーがありません。');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function BOM付き引用ヘッダーを読み込み列数不一致は拒否する()
    {
        $this->grantPermissions($this->staff, ['staff.places.read,import']);
        $valid = UploadedFile::fake()->createWithContent(
            'quoted.csv',
            "\xEF\xBB\xBF\"場所ID\",\"場所名\",\"タイプ\",\"スタッフ用メモ\"\n,\"引用ヘッダー\",\"屋内\",\"\"\n"
        );
        $this->actingAsStaff($this->staff)
            ->post(route('staff.places.import.store'), ['importFile' => $valid])
            ->assertRedirect(route('staff.places.index'));
        $this->assertDatabaseHas('places', ['name' => '引用ヘッダー']);

        $invalid = UploadedFile::fake()->createWithContent(
            'invalid-columns.csv',
            "場所ID,場所名,タイプ,スタッフ用メモ\n,列不足,屋内\n"
        );
        $this->actingAsStaff($this->staff)
            ->post(route('staff.places.import.store'), ['importFile' => $invalid])
            ->assertSessionHasErrors('importFile');
        $this->actingAsStaff($this->staff)
            ->get(route('staff.places.import.index'))
            ->assertSee('列数')
            ->assertSee('ヘッダーと同じ列数で入力してください。');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 途中のDB障害では場所と操作ログを全てロールバックし例外の詳細を表示しない()
    {
        $this->grantPermissions($this->staff, ['staff.places.read,import']);
        Activity::query()->delete();
        $exception = new QueryException(
            'testing',
            'insert into places',
            [],
            new PDOException('injected database failure')
        );
        Place::created(function (Place $place) use ($exception) {
            if ($place->name === '障害行') {
                throw $exception;
            }
        });
        $handler = Mockery::mock(ExceptionHandler::class);
        $handler->shouldReceive('report')->once()->with($exception);
        $this->app->instance(ExceptionHandler::class, $handler);

        $response = $this->actingAsStaff($this->staff)->post(route('staff.places.import.store'), [
            'importFile' => $this->csv([
                ['', '先行行', '屋内', ''],
                ['', '障害行', '屋外', ''],
            ]),
        ]);

        $response
            ->assertRedirect(route('staff.places.import.index'))
            ->assertSessionHasErrors([
                'importFile' => 'インポートに失敗しました。もう一度お試しください。',
            ]);
        $this->assertDatabaseMissing('places', ['name' => '先行行']);
        $this->assertDatabaseMissing('places', ['name' => '障害行']);
        $this->assertDatabaseCount('activity_log', 0);

        $this->actingAsStaff($this->staff)
            ->get(route('staff.places.import.index'))
            ->assertDontSee('injected database failure');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('permissionMatrix')]
    public function インポート3経路に権限行列を適用する(
        string $userKind,
        array $permissions,
        array $expectedStatuses
    ) {
        $user = match ($userKind) {
            'guest' => null,
            'nonstaff' => factory(User::class)->create(),
            'admin' => factory(User::class)->state('admin')->create(),
            default => $this->staff,
        };

        if ($user !== null && $permissions !== []) {
            $this->grantPermissions($user, $permissions);
        }
        if ($user !== null) {
            $this->actingAsStaff($user);
        }

        $this->get(route('staff.places.import.index'))->assertStatus($expectedStatuses[0]);
        $this->post(route('staff.places.import.store'))->assertStatus($expectedStatuses[1]);
        $this->get(route('staff.places.import.template'))->assertStatus($expectedStatuses[2]);
    }

    public static function permissionMatrix(): array
    {
        return [
            'guest' => ['guest', [], [302, 302, 302]],
            'nonstaff' => ['nonstaff', [], [403, 403, 403]],
            'read' => ['staff', ['staff.places.read'], [403, 403, 403]],
            'edit' => ['staff', ['staff.places.read,edit'], [403, 403, 403]],
            'import' => ['staff', ['staff.places.read,import'], [200, 302, 403]],
            'import and export' => [
                'staff',
                ['staff.places.read,import', 'staff.places.read,export'],
                [200, 302, 200],
            ],
            'full' => ['staff', ['staff.places'], [200, 302, 200]],
            'admin' => ['admin', [], [200, 302, 200]],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function インポートとテンプレートのリンクを経路と同じ権限で表示する()
    {
        $this->grantPermissions($this->staff, ['staff.places.read']);
        $this->actingAsStaff($this->staff)
            ->get(route('staff.places.index'))
            ->assertOk()
            ->assertDontSee(route('staff.places.import.index'));

        $this->staff->syncPermissions([]);
        $this->grantPermissions($this->staff, ['staff.places.read,import']);
        $this->actingAsStaff($this->staff)
            ->get(route('staff.places.index'))
            ->assertSee(route('staff.places.import.index'));
        $this->actingAsStaff($this->staff)
            ->get(route('staff.places.import.index'))
            ->assertDontSee(route('staff.places.import.template'));

        $this->grantPermissions($this->staff, ['staff.places.read,export']);
        $this->actingAsStaff($this->staff)
            ->get(route('staff.places.import.index'))
            ->assertSee(route('staff.places.import.template'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 空白の複数行レコードの後も物理行番号を表示する()
    {
        $this->grantPermissions($this->staff, ['staff.places.read,import']);

        $response = $this->actingAsStaff($this->staff)->post(route('staff.places.import.store'), [
            'importFile' => $this->csv([
                ['', '', '', "\n\n"],
                ['', 'エラーの場所', '不明', ''],
            ]),
        ]);

        $response->assertSessionHas('importErrors', fn (array $errors) =>
            $errors[0]['line'] === 5 && $errors[0]['attribute'] === 'タイプ');
        $this->assertDatabaseMissing('places', ['name' => 'エラーの場所']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function テンプレートは数式として読まれずCSV再保存後も保護文字を解除して取り込める()
    {
        $this->grantPermissions($this->staff, ['staff.places.read,import', 'staff.places.read,export']);
        $values = [
            '=1+1', '+1+1', '-1+1', '@SUM(1,1)', '＝1＋1', '＋1', '－1', '＠SUM(1,1)',
            "'literal", "'=1+1", "''=1+1", '001号室',
            "\t=1+1", "\r=1+1", "\n=1+1", ' =1+1', " メモ\n末尾 ",
            '=HYPERLINK("https://example.test/", "text")',
        ];
        $places = [];
        foreach ($values as $index => $value) {
            $places[] = factory(Place::class)->create([
                'name' => trim($value) . $index,
                'type' => 1,
                'notes' => $value,
            ]);
        }

        $download = $this->actingAsStaff($this->staff)->get(route('staff.places.import.template'));
        $download->assertOk();
        $file = UploadedFile::fake()->createWithContent('template.csv', $download->streamedContent());
        $sheet = (new CsvReader())->load($file->getRealPath());
        foreach ($places as $index => $place) {
            foreach (['B', 'D'] as $column) {
                $cell = $sheet->getActiveSheet()->getCell($column . ($index + 2));
                $this->assertNotSame(DataType::TYPE_FORMULA, $cell->getDataType());
            }
        }

        (new CsvWriter($sheet))->save($file->getRealPath());
        $this->actingAsStaff($this->staff)
            ->post(route('staff.places.import.store'), ['importFile' => $file])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('staff.places.index'));

        foreach ($places as $place) {
            $this->assertSame($place->name, $place->fresh()->name);
            // PhpSpreadsheet normalizes CR to LF in spreadsheet cell strings.
            $this->assertSame(str_replace("\r", "\n", $place->notes), $place->fresh()->notes);
        }
    }

    private function actingAsStaff(User $user): self
    {
        return $this->actingAs($user)->withSession(['staff_authorized' => true]);
    }

    private function grantPermissions(User $user, array $permissionNames): void
    {
        foreach ($permissionNames as $permissionName) {
            Permission::query()->firstOrCreate(['name' => $permissionName]);
        }
        $user->givePermissionTo($permissionNames);
    }

    private function csv(array $rows, ?array $headers = null): UploadedFile
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, $headers ?? ['場所ID', '場所名', 'タイプ', 'スタッフ用メモ'], escape: '');
        foreach ($rows as $row) {
            fputcsv($stream, $row, escape: '');
        }
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        return UploadedFile::fake()->createWithContent('places.csv', $contents);
    }
}
