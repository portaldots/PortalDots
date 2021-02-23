<?php
defined('BASEPATH') or exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">
<title>404 Page Not Found</title>
<style type="text/css">

::selection { background-color: #E13300; color: white; }
::-moz-selection { background-color: #E13300; color: white; }

body {
    background-color: #fff;
    margin: 40px;
    font: 13px/20px normal Helvetica, Arial, sans-serif;
    color: #4F5155;
}

a {
    color: #003399;
    background-color: transparent;
    font-weight: normal;
}

h1 {
    color: #444;
    background-color: transparent;
    border-bottom: 1px solid #D0D0D0;
    font-size: 19px;
    font-weight: normal;
    margin: 0 0 14px 0;
    padding: 14px 15px 10px 15px;
}

code {
    font-family: Consolas, Monaco, Courier New, Courier, monospace;
    font-size: 12px;
    background-color: #f9f9f9;
    border: 1px solid #D0D0D0;
    color: #002166;
    display: block;
    margin: 14px 0 14px 0;
    padding: 12px 10px 12px 10px;
}

#container {
    margin: 10px;
    border: 1px solid #D0D0D0;
    box-shadow: 0 0 8px #D0D0D0;
}

p {
    margin: 12px 15px 12px 15px;
}

.button {
    text-decoration: none;
    display: inline-block;
    padding: 6px 12px;
    margin-bottom: 0;
    font-size: 16px;
    font-weight: 400;
    line-height: 1.5;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    -ms-touch-action: manipulation;
    touch-action: manipulation;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-image: none;
    border: 1px solid transparent;
    border-radius: 2px;
    font-size: 14px;
    color: #fff;
    background-color: #1879F4;
    border-color: #1791f2;
    -webkit-transition-timing-function: ease;
    -o-transition-timing-function: ease;
    transition-timing-function: ease;
    -webkit-transition-duration: .3s;
    -o-transition-duration: .3s;
    transition-duration: .3s;
    -webkit-transition-property: all;
    -o-transition-property: all;
    transition-property: all;
}

.button:hover {
  color: #fff;
  background-color: #0c7cd5;
  border-color: #0a71c2;
}

.button:focus,
.button:active {
    color: #fff;
    background-color: #0c7cd5;
    border-color: #074c83;
    outline: 5px auto -webkit-focus-ring-color;
    outline-offset: -2px;
}
</style>
</head>
<body>
    <div id="container">
        <h1><?php echo $heading; ?></h1>
        <?php echo $message; ?>
        <p lang="ja"><a href="javascript:history.back()" class="button">≫ 前の画面に戻る</a></p>
    </div>
</body>
</html>
