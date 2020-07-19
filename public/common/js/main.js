/* eslint-disable */

$(function() {
  $('.sidebar-toggle').click(function(e) {
    e.preventDefault()
    $('body').toggleClass('ss__body-toggled')
    $('.ss__wrapper').toggleClass('toggled')
  })

  // https://qiita.com/syobochim/items/120109315f671918f28d
  $('form').submit(function() {
    const self = this
    $(':submit', self).prop('disabled', true)
    setTimeout(function() {
      $(':submit', self).prop('disabled', false)
    }, 10000)
  })

  // スタッフモードにおける Project v2 の告知
  if (
    window.document.cookie
      .split(';')
      .some(item => item.trim().startsWith('hide-v2-alert='))
  ) {
    $('.js-v2-alert-small').show();
  } else {
    $('.js-v2-alert').show();
  }

  $('.js-v2-alert-close').click(function (e) {
    e.preventDefault();
    document.cookie = 'hide-v2-alert=1'
  });
})
