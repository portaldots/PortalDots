<?php
require_once __DIR__. '/Home_base_controller.php';

/**
 * home/Documents コントローラ
 *
 * 配布資料ページ
 */
class Documents extends Home_base_controller
{
    public function index()
    {
        $vars = [];
        $vars["main_page_type"] = "documents";
        $vars["xs_navbar_title"] = "配布資料";

        if (empty($this->input->get("q"))) {
            $vars['q'] = null;
            $vars['is_search'] = false;
            $vars['documents'] = $this->documents->get_all_public();
        } else {
            $vars['q'] = $this->input->get("q");
            $vars['is_search'] = true;
            $vars['documents'] = $this->documents->search_public($this->input->get("q"));
        }

        $this->_render('home/documents', $vars);
    }
}
