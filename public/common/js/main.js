$(function(){

  $(".sidebar-toggle").click(function(e) {
    e.preventDefault();
    $("body").toggleClass("ss__body-toggled");
    $(".ss__wrapper").toggleClass("toggled");
  });

  // https://qiita.com/syobochim/items/120109315f671918f28d
  $("form").submit(function() {
    var self = this;
    $(":submit", self).prop("disabled", true);
    setTimeout(function() {
      $(":submit", self).prop("disabled", false);
    }, 10000);
  });

});
