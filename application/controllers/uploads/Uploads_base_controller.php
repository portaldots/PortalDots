<?php

/**
 * uploads ベースコントローラ
 */
class Uploads_base_controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (empty($this->_get_login_user())) {
            $this->_uploads_forbidden();
        }
    }

  /**
   * 指定したパスのファイルを表示
   */
    protected function _render_file($filepath)
    {
        if (file_exists($filepath)) {
          //MIMEタイプの取得
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($filepath);

            header('Content-Type: '.$mime_type);
            readfile($filepath);
        } else {
            $this->_uploads_forbidden();
        }
    }

    protected function _uploads_forbidden()
    {
        $this->_error("エラー", "このページにアクセスする権限がありません。", 403);
    }
}
