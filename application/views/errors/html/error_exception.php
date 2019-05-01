<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

<?php // h4>An uncaught Exception was encountered</h4 ?>
<h4>予期しないエラーが発生しました。</h4>

<p>エラータイプ(Type): <?php echo get_class($exception); ?></p>
<p>エラーメッセージ(Message): <?php echo $message; ?></p>
<p>ファイル名(Filename): <?php echo $exception->getFile(); ?></p>
<p>行数(Line Number): <?php echo $exception->getLine(); ?></p>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true) : ?>
    <p>バックトレース(Backtrace):</p>
    <?php foreach ($exception->getTrace() as $error) : ?>
        <?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>
            <p style="margin-left:10px">
            ファイル(File): <?php echo $error['file']; ?><br />
            行数(Line): <?php echo $error['line']; ?><br />
            関数(Function): <?php echo $error['function']; ?>
            </p>
        <?php endif ?>

    <?php endforeach ?>

<?php endif ?>

</div>
