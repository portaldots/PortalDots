<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Pages\PageRequest;
use App\Services\Pages\PagesService;
use App\Eloquents\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateAction extends Controller
{
    /**
     * @var PagesService
     */
    private $pagesService;

    public function __construct(PagesService $pagesService)
    {
        $this->pagesService = $pagesService;
    }

    public function __invoke(PageRequest $request, Page $page)
    {
        $values = $request->validated();

        DB::transaction(function () use ($values, $page) {
            $this->pagesService->updatePage(
                $page,
                $values['title'],
                $values['body'],
                Auth::user(),
                $values['notes'] ?? '',
                $values['viewable_tags'] ?? []
            );

            if (($values['send_emails'] ?? false) && Auth::user()->can('staff.pages.send_emails')) {
                // 一斉送信をオンにした場合
                $this->pagesService->sendEmailsByPage($page);
            }
        });

        return redirect()
            ->route('staff.pages.edit', ['page' => $page])
            ->with('topAlert.title', 'お知らせを更新しました')
            ->with('topAlert.body', ($values['send_emails'] ?? false)
                ? 'また、このお知らせの一斉送信を予約しました'
                : null);
    }
}
