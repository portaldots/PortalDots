<?php
require_once __DIR__. '/Home_base_controller.php';

/**
 * home/Pages コントローラ
 *
 * お知らせページ
 */
class Pages extends Home_base_controller
{
  /**
   * お知らせ一覧ページ
   */
    public function index()
    {
        $vars = [];
        $vars["main_page_type"] = "pages";
        $vars["xs_navbar_title"] = "お知らせ";

        $vars["mode"] = "all";
        $vars["pages"] = $this->pages->get_all();
        $this->_render('home/pages_index', $vars);
    }

  /**
   * お知らせ個別ページ
   */
    public function detail($id = null)
    {
        $vars = [];
        $vars["main_page_type"] = "pages";
        $vars["xs_navbar_title"] = "お知らせ";

        $page = $this->pages->get_page_by_page_id($id);
        if ($page !== false) {
          // ページ１件表示
            $vars['page'] = $page;
            $this->_render('home/pages_detail', $vars);
        } else {
          // 該当するページIDなし
            show_404();
        }
    }
}
