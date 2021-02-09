define([
  "jquery",
  "mage/translate",
  "Magento_Ui/js/modal/alert",
  "Magento_Ui/js/modal/modal",
], function ($, $t, alert, modal) {
  "use strict";
  return {
    getEntityID() {
      var val = jQuery(
        "label.data-grid-checkbox-cell-inner input:checkbox:checked.admin__control-checkbox"
      )
        .map(function () {
          return this.value;
        })
        .get()
        .join(",");

      return val;
    },
    showPreload() {
      $("body").trigger("processStart");
    },
    hidePreload() {
      $("body").trigger("processStop");
    },
    showAlert(title, message, type, reload_when_close, ok_callback, buttons) {
      if (typeof message == "undefined") {
        message = $t("Error");
      }
      if (type === "S") {
        var icon =
          '<div class="md-success" style="color: green;font-size: 50px;text-align: center;"><i class="fa fa-check-circle" aria-hidden="true"></i></div><br />';
      } else if (type === "F") {
        var icon =
          '<div class="md-fail" style="color: red;font-size: 50px;text-align: center;"><i class="fa fa-times-circle" aria-hidden="true"></i></div><br />';
      } else {
        var icon = "";
      }
      var msg = icon + " " + message;

      var alertBtn = [];
      alertBtn.push({
        text: $t("Close"),
        class: "action-primary action-accept",
        click: function () {
          if (ok_callback == "reload") {
            window.location.reload();
          } else if (ok_callback == "close") {
            this.closeModal(true);
          }
        },
      });
      if (typeof buttons !== "undefined") {
        alertBtn.push(buttons);
      }

      alert({
        title: title,
        content: msg,
        clickableOverlay: false,
        modalClass: "confirm specm-modal",
        closed: function () {
          if (reload_when_close) {
            window.location.reload();
          }
        },
        buttons: alertBtn,
      });
    },
  };
});
