<?php
class MY_Controller extends CI_Controller
{

    /**
     * Twig_Environment のインスタンスを格納する
     * @var object
     */
    protected $twig;

    /**
     * Twig テンプレートの拡張子
     * @var array
     */
    const TWIG_FILETYPE = [
        'html' => '.html.twig',
        'css'  => '.css.twig',
        'js'   => '.js.twig',
        'json' => '.json.twig',
        'xml'  => '.xml.twig',
        'yaml' => '.yaml.twig',
        'txt'  => '.txt.twig',
        ''     => ''
    ];

    /**
     * ログイン中のユーザーの ID を格納するセッションのキー名
     */
    private const SESSION_KEY_USER_ID = 'user_id';

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        parent::__construct();
        $this->_init_twig();
        $this->_init_crud();
    }

    /**
     * Twig の初期化をする
     */
    private function _init_twig()
    {
        //テンプレートを配置しているフォルダを指定
        //今回はapplication/views
        $loader = new Twig_Loader_Chain([
            new Twig_Loader_Filesystem('../application/views'),
            new Twig_Loader_Filesystem('../application/views/layouts')
        ]);

        //オプションを指定して、twigインスタンス生成
        $option = [
        'cache' => APPPATH.'/cache/twig',
        'debug' => ( ENVIRONMENT === 'development' ) ? true : false
        ];
        $this->twig = new Twig_Environment($loader, $option);

        // Twig に独自関数を追加する
        $functions = [
            // php
            'empty', 'var_dump',
            // code igniter
            'base_url', 'site_url', 'current_url', 'uri_string',
            // form functions
            'form_open', 'form_hidden', 'form_input', 'form_password', 'form_upload', 'form_textarea', 'form_dropdown',
            'form_multiselect', 'form_fieldset', 'form_fieldset_close', 'form_checkbox', 'form_radio', 'form_submit',
            'form_label', 'form_reset', 'form_button', 'form_close', 'form_prep', 'set_value',
            'set_select', 'set_checkbox', 'set_radio', 'form_open_multipart',
            // varidation function
            'validation_errors', 'form_error'
        ];
        foreach ($functions as $function) {
            $this->twig->addFunction(new Twig_SimpleFunction($function, $function));
        }

        // 独自フィルターを作成する( オブジェクトを配列にキャストする )
        $filter_cast_to_array = new Twig_SimpleFilter('cast_to_array', function ($object) {
            return (array)$object;
        });
        $this->twig->addFilter($filter_cast_to_array);
    }

    /**
     * Grocry CRUD の初期化をする
     */
    private function _init_crud()
    {
      // カラム名の日本語化
        $this->grocery_crud->display_as('student_id', '学籍番号');
        $this->grocery_crud->display_as('title', 'タイトル');
        $this->grocery_crud->display_as('category_id', 'カテゴリーID');
        $this->grocery_crud->display_as('body', '本文');
        $this->grocery_crud->display_as('booth_id', 'ブースID');
        $this->grocery_crud->display_as('place_id', '場所');
        $this->grocery_crud->display_as('circle_id', '団体');
        $this->grocery_crud->display_as('genre', 'ジャンル');
        $this->grocery_crud->display_as('image_filename', '画像');
        $this->grocery_crud->display_as('description', '紹介');
        $this->grocery_crud->display_as('url', 'URL');
        $this->grocery_crud->display_as('twitter_id', 'Twitter ID');
        $this->grocery_crud->display_as('facebook_url', 'Facebook URL');
        $this->grocery_crud->display_as('google_plus_url', 'Google+ URL');
        $this->grocery_crud->display_as('is_sell_foods', '飲食物販売');
        $this->grocery_crud->display_as('is_sell_things', '成果物販売');
        $this->grocery_crud->display_as('is_exhibition', '展示');
        $this->grocery_crud->display_as('is_performance', '実演');
        $this->grocery_crud->display_as('is_public', '公開');
        $this->grocery_crud->display_as('is_important', '重要');
        $this->grocery_crud->display_as('is_locked', '編集禁止');
        $this->grocery_crud->display_as('created_at', '作成日時');
        $this->grocery_crud->display_as('modified_at', '最終変更日時');
        $this->grocery_crud->display_as('created_by', '作成者');
        $this->grocery_crud->display_as('modified_by', '最終変更者');
        $this->grocery_crud->display_as('name_family', '姓');
        $this->grocery_crud->display_as('name_family_yomi', '姓(よみ)');
        $this->grocery_crud->display_as('name_given', '名');
        $this->grocery_crud->display_as('name_given_yomi', '名(よみ)');
        $this->grocery_crud->display_as('email', '連絡先メールアドレス');
        $this->grocery_crud->display_as('tel', '電話番号');
        $this->grocery_crud->display_as('verified_univemail', '大学ﾒｱﾄﾞ認証済');
        $this->grocery_crud->display_as('verified_email', '連絡先ﾒｱﾄﾞ認証済');
        $this->grocery_crud->display_as('is_staff', 'スタッフ');
        $this->grocery_crud->display_as('notes', 'ｽﾀｯﾌ用ﾒﾓ');

        // id順に表示する
        $this->grocery_crud->order_by('id', 'asc');

        // textフィールドを表示する際の改行自動追加
        $this->grocery_crud->callback_column('name', array($this, '_crud_full_text'));
        $this->grocery_crud->callback_column('genre', array($this, '_crud_full_text'));
        $this->grocery_crud->callback_column('description', array($this, '_crud_full_text'));
        $this->grocery_crud->callback_column('notes', array($this, '_crud_full_text'));

        // 更新日時や更新者を自動設定する
        $this->grocery_crud->callback_before_insert(array($this,'_crud_before_insert'));
        $this->grocery_crud->callback_before_update(array($this,'_crud_before_update'));
    }

    /**
     * description を適切に改行して表示させるための Grocery CRUD コールバック関数
     */
    public function _crud_full_text($value, $row)
    {
        return $value = $this->_mb_wordwrap($value, 30, "<br>");
    }

    /**
     * Update 前に実行する処理のための Grocery CRUD コールバック関数
     */
    public function _crud_before_update($post_array)
    {
       /*
        * Grocery CRUD の fields または edit_fields に指定されていないカラムの情報をここで設定
        * することはできません。つまり、以下で設定しているカラムが存在しない場合は、単に無視されるだけなので
        * 難しく考える必要がありません。その代わり、change_field_type で、第１引数に、フォームで
        * 非表示にしたいカラム名を指定し、第２引数に invisible を指定すれば OK です。
        * See Also : https://www.grocerycrud.com/documentation/options_functions/callback_before_insert
        */
        $post_array['modified_at'] = date('Y-m-d H:i:s');
        $post_array['modified_by'] = $this->_get_login_user()->id;

        return $post_array;
    }

    /**
     * Insert 前に実行する処理のための Grocery CRUD コールバック関数
     */
    public function _crud_before_insert($post_array)
    {
        $post_array = $this->_crud_before_update($post_array);

        $post_array['created_at'] = date('Y-m-d H:i:s');
        $post_array['created_by'] = $this->_get_login_user()->id;

        return $post_array;
    }


    /**
     * ログインがされていない場合、ログインページにリダイレクトする
     */
    public function _require_login()
    {
        if (empty($this->_get_login_user())) {
            # ログインされていない場合
            // 現在アクセスされているURIをセッションに保存
            $_SESSION["login_return_uri"] = $this->uri->uri_string();
            // ログインページへリダイレクト
            redirect('users/login');
        } else {
            # ログインされている場合
            // login_return_uri がセットされている場合，そのページにアクセス
            if (isset($_SESSION["login_return_uri"])) {
                $uri = $_SESSION["login_return_uri"];
                unset($_SESSION["login_return_uri"]);
                redirect($uri);
            }
        }
    }

    /**
     * ログイン中のユーザーの、ユーザー情報オブジェクトを返す
     */
    public function _get_login_user()
    {
        static $user;
        if (empty($user)) {
            return $user = $this->users->get_user_by_user_id(
                $this->_get_login_user_id()
            );
        } else {
            return $user;
        }
    }

    private function _get_login_user_id()
    {
        return $_SESSION[self::SESSION_KEY_USER_ID] ?? null;
    }

    /**
     * 指定したユーザーでログインする
     */
    protected function _login($user_id)
    {
        session_regenerate_id(true);
        return $_SESSION[self::SESSION_KEY_USER_ID] = $user_id;
    }

    /**
     * ログアウトする
     */
    protected function _logout()
    {
        session_regenerate_id(true);
        unset($_SESSION[self::SESSION_KEY_USER_ID]);
    }

    /**
     * ログイン中のユーザーがスタッフではない場合、アクセスを禁止する
     * 必ず、_require_login() してから、このメソッドを使用すること
     */
    public function _staff_only()
    {
        if ((int)$this->_get_login_user()->is_staff !== 1) {
            $this->_error("アクセス禁止", "このページにアクセスする権限がありません", 403);
        }
    }

    /**
     * ログイン中のユーザーが管理者ではない場合、アクセスを禁止する
     * 必ず、_require_login() してから、このメソッドを使用すること
     * また、_staff_only() の後にこのメソッドを使用することが望ましい(必須ではない)
     */
    public function _admin_only()
    {
        if ((bool)$this->_get_login_user()->is_admin === false) {
            $this->_error("アクセス禁止", "このページにアクセスする権限がありません", 403);
        }
    }

    /**
     * Twig テンプレートによって作成されたビューを出力する
     * @param  string $template_filename 使用する Twig テンプレート( 拡張子不要 )
     * @param  array  $vars              ビューに渡すパラメータ
     * @param  string $file_type         Twig のファイルタイプ( デフォルトは html )
     */
    public function _render($template_filename, $vars = [], $file_type = 'html')
    {
        $template = $this->twig->loadTemplate(
            dirname($template_filename). "/".
            str_replace("/", ".", $template_filename).
            $this::TWIG_FILETYPE[$file_type]
        );

        // ユーザー情報を$varsに追加
        if (! empty($this->_get_login_user())) {
            $vars['user'] = $this->_get_login_user();
        }

        $this->output->set_output($template->render($vars));
    }

    /**
     * エラーを表示してアプリケーションを強制終了する
     * @param  string $error_title エラータイトル
     * @param  string $error_info  [description]
     */
    public function _error($error_title, $error_info, $status_header = 400)
    {
        $this->output->set_status_header($status_header);
        $this->_render('error', [
        '_error'           => true,
        'xs_navbar_toggle' => false, // ナビバーにサイドバートグルボタンを表示しない
        'xs_navbar_back'   => true,  // 戻るボタンを表示
        'xs_navbar_title'  => $error_title,
        'error_title'      => $error_title,
        'error_info'       => $error_info
        ]);
        $this->output->_display();
        exit();
    }

    /**
     * メールを送信する
     * @param  string $to                送信先(To)メールアドレス
     * @param  string $subject           メールの件名
     * @param  string $template_filename 送信するメールの Twig テンプレート
     * @param  array  $vars              パラメータ( name_to(送信者名) を含むこと )
     * @param  string $reply_to          返信先メールアドレス
     * @param  string $cc                CCメールアドレス
     * @return bool                      送信成功時 true
     */
    public function _send_email($to, $subject, $template_filename, $vars = [], $reply_to = null, $cc = null)
    {
        $template = $this->twig->loadTemplate($template_filename. $this::TWIG_FILETYPE['txt']);

        $this->load->library('email');

        // $reply_to の指定がない場合，運営者の連絡先メールアドレスに設定する
        if (empty($reply_to)) {
            $reply_to = RP_CONTACT_EMAIL;
        }

        $this->email->initialize([
            'protocol' => 'smtp',
            'smtp_host' => RP_SMTP_HOST,
            'smtp_user' => RP_SMTP_USER,
            'smtp_pass' => RP_SMTP_PASS,
            'smtp_port' => RP_SMTP_PORT, // default: 587
            'crlf' => "\r\n",
            'newline' => "\r\n",
            'wordwrap' => false,
            'charset' => 'utf-8',
        ]);

        $this->email->from(RP_EMAIL_FROM, RP_EMAIL_FROM_NAME);
        $this->email->to(mb_strtolower($to));
        if (!empty($cc)) {
            $this->email->cc(mb_strtolower($cc));
        }
        $this->email->reply_to(mb_strtolower($reply_to));
        $this->email->subject(RP_EMAIL_SUBJECT_PREFIX. $subject);
        $this->email->message($template->render($vars));
        return $this->email->send();
    }

    /**
     * テーブルデータを操作するためのGUIを出力する
     * @param  string $table_name        表示するデータベースのテーブル名
     * @param  string $title             (省略可)ページタイトル。省略した場合、テーブル名。
     * @param  string $memo              (省略可)ページに表示するメモ。HTML使用不可。
     * @param  string $template_filename (省略可)使用する Twig テンプレート( 拡張子不要 )
     */
    public function _table_render($table_name, $title = null, $memo = null, $template_filename = 'home/database')
    {
        $vars['title'] = empty($title) ? $table_name : $title;
        $vars['table_name'] = $table_name;
        $vars['sql'] = $this->db->get_compiled_select($table_name);
        $vars['table'] = $this->db->get($table_name)->result();
        if (!empty($memo)) {
            $vars['memo'] = $memo;
        }
        $this->_render($template_filename, $vars);
    }

    /**
     * マルチバイト文字対応の wordwrap
     * ( PHP 標準関数の wordwrap は、日本語などのマルチバイト文字に非対応のため )
     * @param  string  $str   入力文字列。
     * @param  integer $width 文字列を分割するときの文字数。
     * @param  string  $break オプションのパラメータ break を用いて行を分割します。
     * @return string         受け取った文字列を指定した長さで分割したものを返します。
     */
    public function _mb_wordwrap($str, $width = 35, $break = PHP_EOL)
    {
        $c = mb_strlen($str);
        $arr = [];
        for ($i=0; $i<=$c; $i+=$width) {
            $arr[] = mb_substr($str, $i, $width);
        }
        return implode($break, $arr);
    }

    /**
     * LINE Notify で同報を送信する
     * @param  string $template_filename 使用する Twig テンプレート( 拡張子不要 )
     * @param  array  $vars              ビューに渡すパラメータ
     * @return bool                      送信に成功したら true
     */
    public function _send_to_line($template_filename, $vars = [])
    {
        $template = $this->twig->loadTemplate($template_filename. $this::TWIG_FILETYPE['txt']);
        $message = $template->render($vars);

        $data = [
            "message" => $message,
        ];
        $data = http_build_query($data, "", "&");

        $options = [
            "http" => [
                "method" => "POST",
                "header" => "Authorization: Bearer " . RP_LINE_NOTIFY_TOKEN . "\r\n"
                        . "Content-Type: application/x-www-form-urlencoded\r\n"
                        . "Content-Length: ". strlen($data). "\r\n",
                "content" => $data,
            ]
        ];
        $context = stream_context_create($options);
        $resultJson = file_get_contents(RP_LINE_NOTIFY_URL, false, $context);
        $resutlArray = json_decode($resultJson, true);
        if ((int)$resutlArray["status"] !== 200) {
            return false;
        }
        return true;
    }
}
