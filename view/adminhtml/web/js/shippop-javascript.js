require([
  "jquery",
  "mage/template",
  "Magento_Ui/js/modal/modal",
  "Specm_utility"
], function ($, mageTemplate, modal, Specm_utility) {

  $(document).on("click", "a.shippop-tracking-history", function (event) {
    event.preventDefault();
    var tracking_code = $(this).data("tracking-code");
    var order_id = $(this).data("order-id");
    if (tracking_code != "" && tracking_code != undefined) {
      data = {
        form_key: FORM_KEY,
        tracking_code: tracking_code,
      };

      jQuery
        .ajax({
          url: window.specm_ajax_trackinghistory,
          method: "POST",
          data: data,
          dataType: "json",
          showLoader: true,
          beforeSend: function (xhr) {
            Specm_utility.showPreload();
          },
        })
        .done(function (resp) {
          Specm_utility.hidePreload();
          if (resp.status) {
            var progressTmpl = mageTemplate("#tracking-history-template"),
              tmpl;
            tmpl = progressTmpl({
              TrackingOrder: resp,
              order_id: order_id,
            });
            if ($("#shippop-popup-modal").find("div").length > 0) {
              $("#shippop-popup-modal").find("div").remove();
            }
            $("#tracking-history-template").after(tmpl);

            var popup = modal(
              {
                type: "popup",
                responsive: true,
                innerScroll: true,
                modalClass: "specm-modal",
                buttons: [],
                title: $.mage.__("Shipping Status"),
              },
              $("#shippop-popup-modal")
            );
            $("#shippop-popup-modal").modal("openModal", popup);
          } else {
            Specm_utility.showAlert("Error", resp.message, "F", false, "close");
          }
        })
        .fail(function (XMLHttpRequest, textStatus, errorThrown) {
          Specm_utility.hidePreload();
          Specm_utility.showAlert("Error", errorThrown, "F", false, "close");
        });
    }
  });

});
