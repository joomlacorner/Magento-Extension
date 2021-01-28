require([
  "jquery",
  "Magento_Ui/js/modal/alert",
  "mage/template",
  "Magento_Ui/js/modal/modal",
  "Specm_utility",
], function ($, alert, mageTemplate, modal, Specm_utility) {
  $("form[name='settings']").on("submit", function (e) {
    e.preventDefault();
    var frm_elm = $(this).closest("form");
    var address_address = $(frm_elm).find("textarea#address_address").val();
    var billing_address = $(frm_elm).find("textarea#billing_address").val();
    var data = {
      form_key: FORM_KEY,
      address_address: address_address,
      billing_address: billing_address,
    };

    jQuery
      .ajax({
        url: window.specm_ajax_addresscorrector,
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
          if (
            resp.address_address_suggestion.status === false ||
            resp.billing_address_suggestion.status === false
          ) {
            Specm_utility.showAlert(
              "Error",
              resp.address_address_suggestion.message,
              "F",
              false,
              "close"
            );
            return false;
          }

          if (
            resp.address_address_suggestion.type == "1" &&
            resp.billing_address_suggestion.type == "1"
          ) {
            update_settings(frm_elm, {
              state: resp.address_address_suggestion.suggestion[0].state,
              district: resp.address_address_suggestion.suggestion[0].district,
              province: resp.address_address_suggestion.suggestion[0].province,
              postcode: resp.address_address_suggestion.suggestion[0].postcode,
            });
            return true;
          }

          var progressTmpl = mageTemplate("#address-corrector-template"),
            tmpl;
          tmpl = progressTmpl({
            address_address_suggestion:
              resp.address_address_suggestion.suggestion,
            billing_address_suggestion:
              resp.billing_address_suggestion.suggestion,
          });
          if ($("#shippop-popup-modal").find("div").length > 0) {
            $("#shippop-popup-modal").find("div").remove();
          }
          $("#address-corrector-template").after(tmpl);

          var popup = modal(
            {
              type: "popup",
              responsive: true,
              innerScroll: true,
              title: $.mage.__("Choose and checking address"),
              modalClass: "specm-modal",
              buttons: [
                {
                  text: $.mage.__("Select"),
                  class: "button btn-primary action-link",
                  click: function () {
                    var correct_address = $("#address-corrector-form")
                      .find("input[name='correct_address']:checked")
                      .data("full");
                    var correct_address_billing = $("#address-corrector-form")
                      .find("input[name='correct_address_billing']:checked")
                      .data("full");

                    $(frm_elm)
                      .find("textarea#address_address")
                      .val(correct_address);
                    $(frm_elm)
                      .find("textarea#billing_address")
                      .val(correct_address_billing);
                    update_settings(frm_elm, {
                      state: $("#address-corrector-form")
                        .find("input[name='correct_address']:checked")
                        .data("state"),
                      district: $("#address-corrector-form")
                        .find("input[name='correct_address']:checked")
                        .data("district"),
                      province: $("#address-corrector-form")
                        .find("input[name='correct_address']:checked")
                        .data("province"),
                      postcode: $("#address-corrector-form")
                        .find("input[name='correct_address']:checked")
                        .data("postcode"),
                    });
                    return true;
                  },
                },
              ],
            },
            $("#shippop-popup-modal")
          );
          $("#shippop-popup-modal").modal("openModal", popup);
        } else {
          Specm_utility.showAlert("Error", resp.message, "F", false, "close");
          return false;
        }
      })
      .fail(function (XMLHttpRequest, textStatus, errorThrown) {
        Specm_utility.hidePreload();
        Specm_utility.showAlert("Error", errorThrown, "F", false, "close");
        return false;
      });
  });

  function update_settings(frm_elm, address_corrector) {
    var address_state =
      $(frm_elm).find("input#address_state").length > 0
        ? $(frm_elm).find("input#address_state").val()
        : address_corrector.state;
    var address_district =
      $(frm_elm).find("input#address_district").length > 0
        ? $(frm_elm).find("input#address_district").val()
        : address_corrector.district;
    var address_province =
      $(frm_elm).find("input#address_province").length > 0
        ? $(frm_elm).find("input#address_province").val()
        : address_corrector.province;
    var postcode =
      $(frm_elm).find("input#address_postcode").length > 0
        ? $(frm_elm).find("input#address_postcode").val()
        : address_corrector.postcode;
    var data = {
      form_key: FORM_KEY,
      address_name: $(frm_elm).find("input#address_name").val(),
      address_tel: $(frm_elm).find("input#address_tel").val(),
      address_address: $(frm_elm).find("textarea#address_address").val(),
      address_state: address_state,
      address_district: address_district,
      address_province: address_province,
      address_postcode: postcode,
      billing_name_title: $(frm_elm).find("input#billing_name_title").val(),
      billing_name: $(frm_elm).find("input#billing_name").val(),
      billing_tax_id: $(frm_elm).find("input#billing_tax_id").val(),
      billing_tel: $(frm_elm).find("input#billing_tel").val(),
      billing_address: $(frm_elm).find("textarea#address_address").val(),
    };

    jQuery
      .ajax({
        url: window.specm_ajax_settings,
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
          window.location.reload();
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
