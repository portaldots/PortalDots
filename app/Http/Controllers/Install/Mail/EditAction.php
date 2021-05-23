<?php

namespace App\Http\Controllers\Install\Mail;

use App\Http\Controllers\Controller;
use App\Services\Install\MailService;

class EditAction extends Controller
{
    /**
     * @var MailService
     */
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function __invoke()
    {
        return view('install.mail.form')
            ->with('labels', $this->mailService->getFormLabels())
            ->with('mail', $this->mailService->getInfo());
    }
}
