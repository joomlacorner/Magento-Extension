require([
  "jquery",
  "mage/translate",
  "mage/template",
  "Magento_Ui/js/modal/modal",
  "Specm_utility",
], function ($, $t, mageTemplate, modal, Specm_utility) {
  // for menu
  $(document).ready(function () {
    check_menu_available();
  });

  var existCondition = setInterval(function () {
    var vs = jQuery("#menu-shippop-ecommerce-main-menu").find("ul > li:hidden")
      .length;
    // console.log(vs);
    if (vs <= 1) {
      console.log("Exists!");
      clearInterval(existCondition);
      check_menu_available();
    }
  }, 100); // check every 100ms

  function check_menu_available() {
    data = {
      form_key: FORM_KEY,
    };

    jQuery
      .ajax({
        url: window.specm_ajax_getstatus,
        method: "POST",
        data: data,
        dataType: "json",
        showLoader: true,
        beforeSend: function (xhr) {
        },
      })
      .done(function (resp) {
        if (resp.status) {
          if (resp.login) {
            if (resp.address_pickup === false) {
              $("#menu-shippop-ecommerce-main-menu")
                .find("li.item-settings")
                .show();
              return false;
            }
            $("#menu-shippop-ecommerce-main-menu")
              .find("li.item-choose-courier")
              .show();
            $("#menu-shippop-ecommerce-main-menu")
              .find("li.item-courier-parcel")
              .show();
            if (resp.cod) {
              $("#menu-shippop-ecommerce-main-menu")
                .find("li.item-report-cod")
                .show();
            }
            $("#menu-shippop-ecommerce-main-menu")
              .find("li.item-settings")
              .show();
          } else {
            $("#menu-shippop-ecommerce-main-menu")
              .find("li.item-login-register")
              .show();
          }
        } else {
          $("#menu-shippop-ecommerce-main-menu")
            .find("li.item-login-register")
            .show();
        }
      })
      .fail(function (XMLHttpRequest, textStatus, errorThrown) {
      });
  }

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
                title: $t("Shipping Status"),
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
