require([
  "jquery",
  "mage/translate",
  "mage/template",
  "Magento_Ui/js/modal/modal",
  "Specm_utility"
], function ($, $t, mageTemplate, modal, Specm_utility) {

  $("body").on("click", "button.choose_courier", function (event) {
    event.preventDefault();

    var order_ids = Specm_utility.getEntityID();
    if (order_ids != "" && order_ids != undefined) {
      data = {
        form_key: FORM_KEY,
        order_ids: order_ids,
      };

      jQuery
        .ajax({
          url: window.specm_ajax_choosecourier,
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
            var progressTmpl = mageTemplate("#couriers-template"),
              tmpl;
            tmpl = progressTmpl({
              couriers: resp.data,
              order_ids: order_ids,
            });
            if ($("#shippop-popup-modal").find("div").length > 0) {
              $("#shippop-popup-modal").find("div").remove();
            }
            $("#couriers-template").after(tmpl);

            var popup = modal(
              {
                type: "popup",
                responsive: true,
                innerScroll: true,
                title: $t("Choose Courier"),
                modalClass: "specm-modal",
                buttons: [
                  {
                    text: $t("Cancel"),
                    class: "button button alert-modal-button-close",
                    click: function () {
                      this.closeModal(true);
                    },
                  },
                  {
                    text: $t("Confirm"),
                    class: "button btn-primary button-shippop-submit",
                    click: function () {
                      this.closeModal(true);
                      btnBooking();
                    },
                  },
                ],
              },
              $("#shippop-popup-modal")
            );
            $("#shippop-popup-modal").modal("openModal", popup);
          } else {
            Specm_utility.showAlert( "Error", resp.message, "F", false, "close");
          }
        })
        .fail(function (XMLHttpRequest, textStatus, errorThrown) {
          Specm_utility.hidePreload();
          Specm_utility.showAlert( "Error", errorThrown, "F", false, "close");
        });
    } else {
      Specm_utility.showAlert( "Alert", "Please select order first", "F", false, "close");
    }
  });

  $("body").on("click", "button.button-select-courier", function (e) {
    var btn_elm = $(this);
    var txt = $(btn_elm).text();

    var active = $(this).hasClass("active-select-courier");
    if (active) {
      $(btn_elm).closest("table").find(".active-select-courier").html(txt);
      $(btn_elm).removeClass("active-select-courier");
      $(btn_elm).closest("div").find("input#select_courier").val("");

      $("footer.modal-footer").find("button.button-shippop-submit").hide();
      $("footer.modal-footer").find("button.alert-modal-button-close").hide();
    } else {
      $(btn_elm).closest("table").find(".active-select-courier").html(txt);
      $(btn_elm)
        .closest("table")
        .find(".active-select-courier")
        .removeClass("active-select-courier");
      $(btn_elm).closest("div").find("input#select_courier").val("");

      $(btn_elm).addClass("active-select-courier");
      $(btn_elm)
        .closest("div")
        .find("input#select_courier")
        .val($(btn_elm).data("courier-code"));
      $(btn_elm).html(
        '<i class="fa fa-check" aria-hidden="true"></i>' + " " + txt
      );

      $("footer.modal-footer").find("button.button-shippop-submit").show();
      $("footer.modal-footer").find("button.alert-modal-button-close").show();
    }
  });

  $(document).ready(function ($) {
    if ( $("body.shippop-ecommerce-choosecourier").length > 0 ) {
      var txt = window.specm_translate_sub_choose_parcel;
      if (typeof txt == "undefined") {
        txt = "";
        setTimeout(() => {
          txt = window.specm_translate_sub_choose_parcel;
        }, 3000);
      }
      $("body.shippop-ecommerce-choosecourier").find("h1.page-title").after('<p style="margin: 0px;font-style: italic;"> ' + txt + ' </p>');
    }
  });

  function btnBooking() {
    var frm_elm = $("#wp-shippop-ecommerce-booking");
    var select_courier = frm_elm.find("#select_courier").val();
    var order_ids = frm_elm.find("#order_ids").val();
  
    if (select_courier == "" || order_ids == "") {
      return false;
    } else {
      data = {
        form_key: FORM_KEY,
        order_ids: order_ids,
        select_courier: select_courier,
      };
  
      jQuery
        .ajax({
          url: window.specm_ajax_confirmbooking,
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
            Specm_utility.showAlert( $t("Success"), resp.message + resp.message2, "S", true, "reload", {
              text: $t("Print waybill"),
              class: "action-primary action-link",
              click: function() {
                window.location.href = resp.print_waybill_link;
              },
            });
          } else {
            Specm_utility.showAlert( "Error", resp.message, "F", false, "close");
          }
        })
        .fail(function (XMLHttpRequest, textStatus, errorThrown) {
          Specm_utility.hidePreload();
          Specm_utility.showAlert( "Error", errorThrown, "F", false, "close");
        });
    }
  }

});