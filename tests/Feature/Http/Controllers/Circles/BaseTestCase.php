<?php

namespace Tests\Feature\Http\Controllers\Circles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;

abstract class BaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected $form;
    protected $customForm;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = factory(Form::class)->create([
            'open_at' => new CarbonImmutable('2020-01-26 11:42:51'),
            'close_at' => new CarbonImmutable('2020-03-26 15:23:31'),
        ]);
        $this->customForm = factory(CustomForm::class)->create([
            'type' => 'circle',
            'form_id' => $this->form->id,
        ]);

        CustomForm::noCacheForm();
    }
}
