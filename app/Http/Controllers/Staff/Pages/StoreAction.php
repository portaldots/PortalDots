<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Pages\PageRequest;
use App\Services\Pages\PagesService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreAction extends Controller
{
    /**
     * @var PagesService
     */
    private $pagesService;

    public function __construct(PagesService $pagesService)
    {
        $this->pagesService = $pagesService;
    }

    public function __invoke(PageRequest $request)
    {
        $values = $request->validated();

        DB::transaction(function () use ($values) {
            $page = $this->pagesService->createPage(
                $values['title'],
                $values['body'],
                Auth::user(),
                $values['notes'] ?? '',
                $values['viewable_tags'] ?? [],
                isset($values['is_public']) && $values['is_public'] === '1',
                isset($values['is_pinned']) && $values['is_pinned'] === '1'
            );

            if (($values['send_emails'] ?? false) && Auth::user()->can('staff.pages.send_emails')) {
                // 一斉送信をオンにした場合
                $this->pagesService->sendEmailsByPage($page);
            }
        });

        return redirect()
            ->route('staff.pages.create')
            ->with('topAlert.title', 'お知らせを作成しました')
            ->with('topAlert.body', ($values['send_emails'] ?? false)
                ? 'また、このお知らせの一斉送信を予約しました'
                : null);
    }
}
