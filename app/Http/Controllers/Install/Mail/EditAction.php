<?php

namespace App\Http\Controllers\Install\Mail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Install\MailService;

class EditAction extends Controller
{
    /**
     * @var MailService
     */
    private $editor;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function __invoke(Request $request)
    {
        return view('install.mail.form')
            ->with('labels', $this->mailService->getFormLabels())
            ->with('mail', $this->mailService->getInfo());
    }
}
