<?php

namespace Tests\Feature\Http\Controllers\Forms\Answers\Uploads;

use App\Eloquents\User;

class ShowActionTest extends UploadTestCase
{
    protected $routeName = 'forms.answers.uploads.show';

    public function test_cannot_download_another_circles_upload()
    {
        $this->actingAs(factory(User::class)->create());

        $this->getUpload()->assertNotFound();
    }
}
