<?php

// このクラスは、CodeIgniter を完全に廃止したら、Laravel にコードを移植することなく
// 削除する
//
// 【理由】
// このクラスは、/home という URL を廃止したことに伴う暫定措置として設置している
// ものであり、その暫定措置は、CodeIgniter を完全に廃止したタイミングで解消する
class Home extends MY_Controller
{
    public function index()
    {
        codeigniter_redirect('/');
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
}
