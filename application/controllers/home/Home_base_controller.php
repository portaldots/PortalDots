<?php

/**
 * home ベースコントローラ
 */
class Home_base_controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_require_login();
    }

    /**
     * 404 ページ
     */
    public function error_404()
    {
        $this->output->set_status_header('404');
        $this->_render('error_404', [
            '_error' => true,
            'xs_navbar_title' => "ページが見つかりません",
            'xs_navbar_toggle' => false, // ナビバーにサイドバートグルボタンを表示しない
            'xs_navbar_back' => true,  // 戻るボタンを表示
        ]);
    }

    public function _render($template_filename, $vars = [], $file_type = 'html')
    {
        $vars["_home_type"] = "default"; // staff or default
        if (!isset($vars["_error"]) || $vars["_error"] !== true) {
            $vars["xs_navbar_toggle"] = true; // ナビバーにサイドバートグルボタンを表示
        }
        if (!isset($vars["_sidebar_menu"])) {
            $vars["_sidebar_menu"] = [
                "index" => [
                    "icon" => "tachometer",
                    "name" => "ダッシュボード",
                    "url" => "home",
                ],
                "pages" => [
                    "icon" => "newspaper-o",
                    "name" => "お知らせ",
                    "url" => "home/pages",
                ],
                "applications" => [
                    "icon" => "pencil-square-o",
                    "name" => "申請",
                    "url" => "home/applications",
                ],
                "documents" => [
                    "icon" => "file-text-o",
                    "name" => "配布資料",
                    "url" => "home/documents",
                ],
                "schedules" => [
                    "icon" => "calendar",
                    "name" => "スケジュール",
                    "url" => "home/schedules",
                ],
                "contacts" => [
                  "icon" => "envelope-o",
                  "name" => "お問い合わせ",
                  "url" => "home/contacts",
                ],
                // "help" => [
                //   "icon" => "question-circle",
                //   "name" => "ヘルプ",
                //   "url" => "home/help",
                // ],
            ];
        }
        parent::_render($template_filename, $vars, $file_type);
    }
}
