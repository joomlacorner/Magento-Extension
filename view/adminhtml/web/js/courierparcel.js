require([
  "jquery",
  "mage/translate",
  "Specm_utility",
], function ($, $t, Specm_utility) {

  var checkExist = setInterval(function() {
    if ($("select[name='select_print_label']").length) {
          jQuery("select[name='select_print_label']").after('<p style="display: inline;margin: 0px;font-style: italic;"> ' + $t("Please choose your orders first.") + ' </p>');
       clearInterval(checkExist);
    }
 }, 100); // check every 100ms

  $(document).on(
    "change",
    "select[name='select_print_label']",
    function (event) {
      event.preventDefault();
      var elm = $(this);
      var label_size = $(elm).val();
      var order_ids = Specm_utility.getEntityID();
      if (
        label_size != "" &&
        label_size != undefined &&
        order_ids != "" &&
        order_ids != undefined
      ) {
        data = {
          form_key: FORM_KEY,
          label_size: label_size,
          order_ids: order_ids,
        };
        jQuery
          .ajax({
            url: window.specm_ajax_preprintlabel,
            method: "POST",
            data: data,
            dataType: "json",
            showLoader: true,
            beforeSend: function (xhr) {
              Specm_utility.showPreload();
            },
          })
          .done(function (resp) {
            $(elm).val("");
            Specm_utility.hidePreload();
            if (resp.status) {
              var dtn = Date.now();
              var html = '<a href="'+ resp.file_url +'" target="_blank">';
              html += '<button style="display: none;" id="' + dtn + '"  type="button">x</button>';
              html += '</a>';
              $("body").append(html);
              $("#" + dtn).click();
            } else {
              Specm_utility.showAlert("Error", resp.message, "F", false, "close");
            }
          })
          .fail(function (XMLHttpRequest, textStatus, errorThrown) {
            Specm_utility.hidePreload();
            Specm_utility.showAlert("Error", errorThrown, "F", false, "close");
          });
      } else {
        $(elm).val("");
      }
    }
  );
});
