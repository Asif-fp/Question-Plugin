/**
 * Custom JavaScript for Medical Questionnaire
 * Description: This file contains the JavaScript and jQuery code for your medical-questionnaire plugin.
 * Author: Future Profilez
 * Version: 1.0.0
 */

//======================Document ready========================================================
jQuery(document).ready(function () {
  //======================Objects=============================================================
  var ajaxurl = mq_ajax_object_frontend.ajaxurl;
  var security_nonce = mq_ajax_object_frontend.nonce;

  //=====================Show loader==========================================================
  function mq_show_loader() {
    jQuery(".loader-holder").show();
  }

  //=====================Hide loader==========================================================
  function mq_hide_loader() {
    jQuery(".loader-holder").hide();
  }

  //=====================Reload page after seconds============================================
  function mq_reload_page(seconds) {
    setTimeout(function () {
      location.reload();
    }, seconds);
  }

  //====================Show the toaster notification with a message==========================
  function mq_show_toaster(message, type = "success") {
    document.getElementById("toaster-message").textContent = message;

    const toaster = document.getElementById("toaster-notification");
    toaster.classList.remove("success", "failure");
    toaster.classList.add(type);
    toaster.classList.remove("fade-out");

    toaster.classList.add("active");

    setTimeout(function () {
      mq_close_toaster();
    }, 4000);
  }

  //===================Close the toaster manually==============================================
  function mq_close_toaster() {
    const toaster = document.getElementById("toaster-notification");
    toaster.classList.remove("active");

    setTimeout(function () {
      toaster.classList.add("fade-out");
    }, 400);
  }

  //======================Hide suboptions=======================================================
  jQuery(".suboption-level-2").each(function () {
    jQuery(this).hide();
    jQuery(this).find('input[type="text"]').hide();
  });

  jQuery(".suboption-level-1").each(function () {
    jQuery(this).hide();
    jQuery(this).find('input[type="text"]').hide();
  });

  //======================Multistep main form next==============================================
  jQuery(".form-container-mq").on(
    "click",
    ".next-btn, .radio-input-mq",
    function () {
      var go_to_next = true;
      var wait_for_go_to_next_step = false;
      const container = jQuery(this).closest(".form-container-mq");

      if (jQuery(this).hasClass("radio-input-mq")) {
        jQuery(this).next().toggleClass("selected");

        const clickedOption = jQuery(this).next().next(".mq-option-levels");

        if (clickedOption.hasClass("suboption-level-1")) {
          console.log("clicked on suboption");
          go_to_next = false;
          wait_for_go_to_next_step = true;
          const parents = jQuery(this).parentsUntil(container);
          parents.find(".suboption-level-1").slideToggle("slow");
        }

        if (clickedOption.hasClass("suboption-level-2")) {
          console.log("clicked on suboption");
          go_to_next = false;
          wait_for_go_to_next_step = true;
          const parents = jQuery(this).parentsUntil(container);
          parents.find(".suboption-level-2").slideToggle("slow");
        }

        // MULTI-SELECT INPUT LOGIC
        if (jQuery(this).hasClass("multi-select-input")) {
          go_to_next = false;
          wait_for_go_to_next_step = true;

          if (jQuery(this).attr("attr-has-text-input") == "yes") {
            const input = jQuery(this).next().next(".text-input-mq");
            const isSelected = jQuery(this).next().hasClass("selected");

            // Slide input based on selection state
            if (isSelected) {
              input.slideDown("slow");
              container.find(".next-btn").attr("disabled", true);
            } else {
              input.slideUp("slow");
              container.find(".next-btn").attr("disabled", false);
            }
          } else {
            container.find(".next-btn").attr("disabled", false);
          }
        }
      }

      // FOR NON-MULTI SELECT (SINGLE CHOICE) — fallback check
      if (!jQuery(this).hasClass("multi-select-input")) {
        if (jQuery(this).attr("attr-has-text-input") == "yes") {
          const input = jQuery(this).next().next(".text-input-mq");
          if (input.length && !input.is(":visible")) {
            input.slideDown("slow");
            go_to_next = false;
            wait_for_go_to_next_step = true;
            container.find(".next-btn").attr("disabled", true);
          } else {
            container.find(".next-btn").attr("disabled", false);
          }
        } else {
          container.find(".next-btn").attr("disabled", false);
        }
      }

      if (go_to_next && !wait_for_go_to_next_step) {
        // ==== PROCEED TO NEXT STEP ====
        container.find(".text-input-mq").addClass("d-none").val("");
        // container.find(".next-btn").attr("disabled", true);

        jQuery(this).next().addClass("selected");

        jQuery(this)
          .closest(".form-container-mq")
          .fadeOut("slow", function () {
            jQuery(this).addClass("hidden-q-container");

            const nextContainer = jQuery(this).next(".form-container-mq");
            nextContainer.removeClass("hidden-q-container").hide();
            nextContainer.addClass("fade-in-right").fadeIn("slow", function () {
              jQuery(this).removeClass("fade-in-right");
            });
          });

        container.find(".next-btn").attr("disabled", false);
      }
    }
  );

  //======================Remove next button disable=============================================
  jQuery(document).on(
    "input",
    ".form-container-mq .text-input-mq",
    function () {
      const container = jQuery(this).closest(".form-container-mq");

      // Check if ALL visible text fields have values
      const allFilled = container
        .find(".text-input-mq:visible")
        .toArray()
        .every(function (input) {
          return jQuery(input).val().trim() !== "";
        });

      const shouldDisable = !allFilled;

      container.find(".next-btn").attr("disabled", shouldDisable);
      container
        .find(".mq-register-form-submit")
        .attr("disabled", shouldDisable);
      container.find(".login-patient").attr("disabled", shouldDisable);
    }
  );

  //======================Search patient or user object by email=================================
  let mq_patient_search_timer;

  jQuery(document).on(
    "input",
    ".user-register-field-user-mail-data",
    function (search_user_event) {
      search_user_event.preventDefault();

      clearTimeout(mq_patient_search_timer);

      const $this = jQuery(this);
      const user_mail = $this.val();
      jQuery(".next-btn").attr("disabled", true);

      mq_patient_search_timer = setTimeout(function () {
        if (user_mail.length < 9) return;

        $.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            action: "mq_search_patient_object_by_email",
            mq_nonce: security_nonce,
            user_mail: user_mail,
          },
          beforeSend: function () {
            mq_show_loader();
          },
          success: function (response) {
            mq_hide_loader();
            if (response.data.type === "error") {
              mq_show_toaster(response.data.message, "failure");
            } else {
              if (response.data.is_user_exists == "true") {
                jQuery($this)
                  .closest(".form-container-mq")
                  .fadeOut("slow", function () {
                    jQuery($this).addClass("hidden-q-container");

                    const nextContainer = jQuery(".login");
                    nextContainer.removeClass("hidden-q-container").hide(); // reset first
                    nextContainer
                      .addClass("fade-in-right")
                      .fadeIn("slow", function () {
                        jQuery($this).removeClass("fade-in-right");
                      });
                  });
      jQuery(".next-btn").attr("disabled", false);

              } else {
                jQuery($this)
                  .parent()
                  .parent()
                  .fadeOut("slow", function () {
                    jQuery($this).addClass("hidden-q-container");

                    const nextContainer = jQuery(".register");
                    nextContainer.removeClass("hidden-q-container").hide(); // reset first
                    nextContainer
                      .addClass("fade-in-right")
                      .fadeIn("slow", function () {
                        jQuery($this).removeClass("fade-in-right");
                      });
                  });
      jQuery(".next-btn").attr("disabled", false);

              }
            }
          },
          error: function () {
            mq_hide_loader();
            mq_show_toaster(
              "Qualcosa è andato storto, riprova più tardi.",
              "failure"
            );
          },
        });
      }, 900);
    }
  );

  //======================Register the user=======================================================
  jQuery(document).on(
    "click",
    ".mq-register-form-submit",
    function (user_register_event) {
      user_register_event.preventDefault();

      const $this_btn = jQuery(this);
      const register_name = jQuery(".user-register-field-register-name").val();
      const register_email = jQuery(".user-register-field-register-mail")
        .val()
        .trim();
      const register_password = jQuery(".user-register-field-register-password")
        .val()
        .trim();
      const register_phone = jQuery(
        ".user-register-field-register-mobile-number"
      )
        .val()
        .replace(/\s+/g, "")
        .trim();

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "mq_register_patient_as_user",
          mq_nonce: security_nonce,
          register_name: register_name,
          register_email: register_email,
          register_phone: register_phone,
          register_password: register_password,
        },
        beforeSend: function () {
          mq_show_loader();
        },
        success: function (response) {
          mq_hide_loader();

          if (response.data.type === "error") {
            mq_show_toaster(response.data.message, "failure");
          } else {
            mq_show_toaster(response.data.message, "success");

            jQuery($this_btn)
              .parent()
              .parent()
              .fadeOut("slow", function () {
                jQuery($this_btn).addClass("hidden-q-container");
                const nextContainer = jQuery(".form-container-points").eq(0);
                nextContainer.removeClass("hidden-q-container").hide(); // reset first
                nextContainer
                  .addClass("fade-in-right")
                  .fadeIn("slow", function () {
                    jQuery($this_btn).removeClass("fade-in-right");
                  });
              });
          }
        },
        error: function () {
          mq_hide_loader();
          mq_show_toaster(
            "Qualcosa è andato storto, riprova più tardi.",
            "failure"
          );
        },
      });
    }
  );

  //======================Login user============================================================
  jQuery(document).on("click", ".login-patient", function (user_login_event) {
    user_login_event.preventDefault();

    const $login_this_btn = jQuery(this);

    const login_email = jQuery(".login-field-login-email").val().trim();
    const login_pass = jQuery(".login-field-login-pass").val().trim();

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "mq_login_patient_user",
        mq_nonce: security_nonce,
        login_email: login_email,
        login_pass: login_pass,
      },
      beforeSend: function () {
        mq_show_loader();
      },
      success: function (response) {
        mq_hide_loader();

        if (response.data.type === "error") {
          mq_show_toaster(response.data.message, "failure");
        } else {
          mq_show_toaster(response.data.message, "success");
      jQuery(".next-btn").attr("disabled", false);

          jQuery($login_this_btn)
            .parent()
            .parent()
            .fadeOut("slow", function () {
              jQuery(this).addClass("hidden-q-container");
              const nextContainer = jQuery(".form-container-points").eq(0);

              nextContainer.removeClass("hidden-q-container").hide(); // reset first
              nextContainer
                .addClass("fade-in-right")
                .fadeIn("slow", function () {
                  jQuery($login_this_btn).removeClass("fade-in-right");
                });
            });
        }
      },
      error: function () {
        mq_hide_loader();
        mq_show_toaster(
          "Qualcosa è andato storto, riprova più tardi.",
          "failure"
        );
      },
    });
  });

  //======================Document ready ends here===============================================
});
