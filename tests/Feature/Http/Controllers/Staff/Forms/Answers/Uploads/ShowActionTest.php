<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms\Answers\Uploads;

use App\Eloquents\Permission;
use App\Eloquents\User;
use Tests\Feature\Http\Controllers\Forms\Answers\Uploads\UploadTestCase;

class ShowActionTest extends UploadTestCase
{
    protected $routeName = 'staff.forms.answers.uploads.show';

    public function setUp(): void
    {
        parent::setUp();
        $this->user->is_staff = true;
        $this->user->save();
        $this->user->circles()->detach();
        Permission::create(['name' => 'staff.forms.answers.read']);
        $this->user->syncPermissions(['staff.forms.answers.read']);
        $this->withSession(['staff_authorized' => true]);
    }

    public function test_cannot_download_without_permission()
    {
        $this->user->syncPermissions([]);

        $this->getUpload()->assertForbidden();
    }

    public function test_non_staff_cannot_download()
    {
        $this->actingAs(factory(User::class)->create());

        $this->getUpload()->assertForbidden();
    }
}
