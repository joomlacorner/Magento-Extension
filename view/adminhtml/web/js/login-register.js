require([
  "jquery",
  "mage/translate",
  "Specm_utility"
], function ($, $t, Specm_utility) {
  $(document).on("click", "button.specm-register-btn", function (e) {
    $("div.specm-wrapper-register").show();
    $("div.specm-wrapper-login").hide();
  });

  $(document).on("click", "span.specm-register-back", function (e) {
    $("div.specm-wrapper-login").show();
    $("div.specm-wrapper-register").hide();
  });

  $("form.specm-form-login").on("submit", function (e) {
    e.preventDefault();
    var frm_elm = $(this);

    $("p.notics-error").hide();

    var data = {
      form_key: FORM_KEY,
      shippop_email: $(frm_elm).find("input[name='shippop_email']").val(),
      shippop_password: $(frm_elm).find("input[name='shippop_password']").val(),
      shippop_server: $(frm_elm).find("select[name='shippop_server']").val(),
      shippop_method: "LOGIN",
    };

    jQuery
      .ajax({
        url: window.specm_ajax_loginregister,
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
          window.location.href = resp.redirect_url;
        } else {
          // Specm_utility.showAlert("Error", resp.message, "F", false, "close");
          $("p.notics-error").text( resp.message );
          $("p.notics-error").show();
        }
      })
      .fail(function (XMLHttpRequest, textStatus, errorThrown) {
        Specm_utility.hidePreload();
        Specm_utility.showAlert("Error", errorThrown, "F", false, "close");
      });
  });

  $("form.specm-form-register").on("submit", function (e) {
    e.preventDefault();
    var frm_elm = $(this);

    var data = {
      form_key: FORM_KEY,
      shippop_company: $(frm_elm).find("input[name='shippop_company']").val(),
      shippop_name: $(frm_elm).find("input[name='shippop_name']").val(),
      shippop_tel: $(frm_elm).find("input[name='shippop_tel']").val(),
      shippop_email: $(frm_elm).find("input[name='shippop_email']").val(),
      shippop_courier: $(frm_elm).find("input[name='shippop_courier']").val(),
      shippop_server: $(frm_elm).find("select[name='shippop_server']").val(),
      shippop_method: "REGISTER",
    };

    jQuery
      .ajax({
        url: window.specm_ajax_loginregister,
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
          Specm_utility.showAlert( $t("Success"), resp.message + resp.message2, "F", false, "reload");
        } else {
          Specm_utility.showAlert("Error", resp.message, "F", false, "close");
        }
      })
      .fail(function (XMLHttpRequest, textStatus, errorThrown) {
        Specm_utility.hidePreload();
        Specm_utility.showAlert("Error", errorThrown, "F", false, "close");
      });
  });
});
