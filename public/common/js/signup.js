$(function(){

  /**
   * ユーザー登録ページの送信ボタンがクリックされた後の処理
   */
  $('#js_signup__form').on('submit', function () {
    $('#js_signup__form input[type="submit"]').attr( 'disabled', true );
    $('#js_signup__mainpanel').addClass( 'signup__loading' );
  });

  /**
   * 同意チェックボックスの有無により、送信ボタンのdisabledの値を変化させる
   * @return {[type]} [description]
   */
  $('#checkbox_agree').on('change', function(){
    if( $(this).is(':checked') ) {
      $('#button_submit').prop('disabled', false);
    }else{
      $('#button_submit').prop('disabled', true);
    }
  });

});
