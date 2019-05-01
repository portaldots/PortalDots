<?php
require_once __DIR__. '/Home_base_controller.php';

/**
 * home/Schedules コントローラ
 *
 * スケジュールページ
 */
class Schedules extends Home_base_controller
{
    public function index()
    {
        $vars = [];
        $vars["main_page_type"] = "schedules";
        $vars["xs_navbar_title"] = "スケジュール";

        if (empty($this->uri->segment(5))) {
          # 日付が未指定
            $vars["schedules_mode"] = "month";

            $year  = $this->uri->segment(3) ?? date("Y");
            $month = $this->uri->segment(4) ?? date("m");

            $schedules = $this->schedules->get_month($month, $year);
            $data = [];
            for ($i = 0; $i < count($schedules); ++$i) {
                $datetime_array = date_parse_from_format("Y-m-d H:i:s", $schedules[$i]->start_at);
                $day = $datetime_array['day'];
                $day_zerofill = str_pad($day, 2, "0", STR_PAD_LEFT);
                $url = base_url("home/schedules/{$year}/{$month}/{$day_zerofill}");
                if (empty($data[$day])) {
                    $data[$day] = '<p class="lead"><a href="'. $url. '"><strong>'. $day. '</strong></a></p><ul>';
                }
                $data[$day] .= '<li>'. $schedules[$i]->name. ' ・ '. $schedules[$i]->place. '</li>';
                if (!empty($schedules[$i]->id)) {
                    $schedules[$i]->documents = $this->documents->get_public_documents_by_schedule_id($schedules[$i]->id);
                }
            }

            $prefs = [];
            $prefs['show_next_prev'] = true;
            $prefs['template'] = [
            'table_open'                => '<table class="calendar table table-bordered hidden-xs">',

            'heading_row_start'         => null,
            'heading_previous_cell'     => null,
            'heading_title_cell'        => null,
            'heading_next_cell'         => null,
            'heading_row_end'           => null,

            'week_row_start'            => '<thead><tr>',
            'week_day_cell'             => '<th>{week_day}</th>',
            'week_row_end'              => '</tr></thead><tbody>',

            'cal_cell_start_today'      => '<td class="today">',

            'cal_cell_content'          => '<div>{content}</ul></div>',
            'cal_cell_content_today'    => '<div>{content}</ul></div>',
            'cal_cell_no_content_today' => '<div><p class="lead">{day}</p></div>',
            'cal_cell_no_content'       => '<div><p class="lead">{day}</p></div>',

            'table_close'               => '</tbody></table>'
            ];
            $this->load->library('calendar', $prefs);

            $vars["calender_html"] = $this->calendar->generate($year, $month, $data);
            $vars["schedules"] = $schedules;
            $vars["year"] = $year;
            $vars["month"] = $month;

            $vars["next_month_array"] = $this->calendar->adjust_date($month + 1, $year);
            $vars["prev_month_array"] = $this->calendar->adjust_date($month - 1, $year);

            $this->_render('home/schedules', $vars);
        } else {
          # 日付の指定あり
            $vars["schedules_mode"] = "day";

            $year  = $this->uri->segment(3);
            $month = $this->uri->segment(4);
            $day   = $this->uri->segment(5);

            $schedules = $this->schedules->get_day($day, $month, $year);
            for ($i = 0; $i < count($schedules); ++$i) {
                if (!empty($schedules[$i]->id)) {
                    $schedules[$i]->documents = $this->documents->get_public_documents_by_schedule_id($schedules[$i]->id);
                }
            }
            $vars["schedules"] = $schedules;
            $vars["year"] = $year;
            $vars["month"] = $month;
            $vars["day"] = $day;
            $this->_render('home/schedules', $vars);
        }
    }
}
