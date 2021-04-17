<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\Forms;

use App\Http\Controllers\Controller;
use Auth;
use DB;

class StoreAction extends Controller
{
    // /**
    //  * @var FormEditorService
    //  */
    // private $pagesService;

    // public function __construct(FormEditorService $pagesService)
    // {
    //     $this->pagesService = $pagesService;
    // }

    public function __invoke()
    {
        //     $values = $request->validated();

    //     DB::transaction(function () use ($values) {
    //         $page = $this->pagesService->createForm(
    //             $values['title'],
    //             $values['body'],
    //             Auth::user(),
    //             $values['notes'] ?? '',
    //             $values['viewable_tags'] ?? []
    //         );

    //         if ($values['send_emails'] ?? false) {
    //             // 一斉送信をオンにした場合
    //             $this->pagesService->sendEmailsByForm($page);
    //         }
    //     });

    //     return redirect()
    //         ->route('staff.pages.create')
    //         ->with('topAlert.title', 'お知らせを作成しました')
    //         ->with('topAlert.body', ($values['send_emails'] ?? false)
    //             ? 'また、このお知らせの一斉送信を予約しました'
    //             : null);
    }
}
