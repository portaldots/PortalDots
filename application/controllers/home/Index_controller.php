<?php
require_once __DIR__. '/Home_base_controller.php';

/**
 * home/Index_controller
 */
class Index_controller extends Home_base_controller
{
    public function index()
    {
        $vars = [];
        $vars["main_page_type"] = "index";
        $vars["xs_navbar_title"] = "ダッシュボード";

        $vars["flashdata_success"] = $_SESSION["flashdata_success"] ?? null;
        $vars["flashdata_danger"] = $_SESSION["flashdata_danger"] ?? null;

        $vars["next_event"] = $this->schedules->get_next_event();
        $vars["pages"] = $this->pages->get_list(5);
        $vars["pages_count"] = $this->pages->count_all();
        $vars["documents"] = $this->documents->get_list_public(5);
        $vars["documents_count"] = $this->documents->count_all_public();

        $this->_render('home/index', $vars);
    }
}
