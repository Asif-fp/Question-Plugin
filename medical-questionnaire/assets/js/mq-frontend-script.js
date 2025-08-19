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
  var form_page_url_return = mq_ajax_object_frontend.form_page_url;

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

  //======================Set steps============================================================
  // function goToStep(stepIndex) {
  //   history.pushState({ step: stepIndex }, null, "");
  // }

  //   window.addEventListener("popstate", function (event) {
  //   if (event.state && typeof event.state.step !== "undefined") {
  //     const stepIndex = event.state.step;

  //     const currentVisible = jQuery(".form-container-mq:visible");
  //     const targetStep = jQuery(".form-container-mq").eq(stepIndex);

  //     // Fade out current step
  //     currentVisible.fadeOut("slow", function () {
  //       jQuery(this).addClass("hidden-q-container");

  //       // Reset target step before showing
  //       targetStep.find(".option-card").removeClass("selected");
  //       targetStep.find("input[type=radio], input[type=checkbox]").prop("checked", false);

  //       // Show only top-level text inputs (not inside suboptions)
  //       targetStep.find(".text-input-mq").each(function () {
  //         const isInSubsection = jQuery(this).closest(".mq-option-levels, .suboption-level-1, .suboption-level-2").length > 0;
  //         if (!isInSubsection) {
  //           jQuery(this).attr("style", "display:block !important");
  //         }
  //       });

  //       // Fade in new step with animation
  //       targetStep
  //         .removeClass("hidden-q-container")
  //         .hide()
  //         .addClass("fade-in-left")
  //         .fadeIn("slow", function () {
  //           jQuery(this).removeClass("fade-in-left");
  //         });
  //     });

  //         // Hide ONLY main text inputs (not inside suboption levels)
  //       currentStep.find(".text-input-mq").each(function () {
  //         const isInSubsection =
  //           jQuery(this).closest(
  //             ".mq-option-levels, .suboption-level-1, .suboption-level-2"
  //           ).length > 0;
  //         if (!isInSubsection) {

  //           // jQuery(this).val("");
  //           jQuery(this).attr("style", "display:block !important");
  //         }
  //       });
  //   }
  // });

  // history.replaceState({ step: 0 }, null, "");

  //======================Multistep main form next==============================================
  jQuery(".form-container-mq").on(
    "click",
    ".next-btn, .radio-input-mq",
    function () {
      const container = jQuery(this).closest(".form-container-mq");
      const radioInput = jQuery(this);
      const radioLabel = radioInput.next();
      const textInput = radioInput.next().next(".text-input-mq");
      const isMulti = radioInput.hasClass("multi-select-input");
      const hasText = radioInput.attr("attr-has-text-input") === "yes";
      const isAlreadySelected = radioLabel.hasClass("selected");

      // const current = jQuery(".form-container-mq:visible");
      // const currentIndex = jQuery(".form-container-mq").index(current);
      // const nextIndex = currentIndex + 1;

      // goToStep(nextIndex);

      let go_to_next = true;
      let wait_for_go_to_next_step = false;

      // ---- HANDLE SINGLE SELECT ---- //
      if (!isMulti) {
        container.find(".radio-label-mq").removeClass("selected");
        container.find(".text-input-mq").fadeOut("slow");

        if (isAlreadySelected) {
          // Unselect if clicked again
          radioLabel.removeClass("selected");
          if (hasText) textInput.fadeOut("slow");
          // container.find(".next-btn").attr("disabled", true);
          go_to_next = false;
          wait_for_go_to_next_step = true;
        } else {
          // Select new option
          radioLabel.addClass("selected");
          if (hasText) {
            textInput.fadeIn("slow").css("display", "block");
            // container.find(".next-btn").attr("disabled", true);
            go_to_next = false;
            wait_for_go_to_next_step = true;
          } else {
            // container.find(".next-btn").attr("disabled", false);
          }
        }
      }

      // ---- HANDLE MULTI SELECT ---- //
      else {
        if (isAlreadySelected) {
          // Deselect it
          radioLabel.removeClass("selected");
          if (hasText) textInput.fadeOut("slow");
        } else {
          // Select it
          radioLabel.addClass("selected");
          if (hasText) textInput.fadeIn("slow").css("display", "block");
        }

        // Enable/disable next button based on selected items
        const selectedCount = container
          .find(".multi-select-input")
          .next(".radio-label-mq.selected").length;
        // container.find(".next-btn").attr("disabled", selectedCount === 0);

        go_to_next = false;
        wait_for_go_to_next_step = true;
      }

      // ---- SUBOPTION LEVEL HANDLING ---- //
      const clickedOption = radioInput.next().next(".mq-option-levels");
      if (clickedOption.hasClass("suboption-level-1")) {
        container.find(".suboption-level-1").slideToggle("slow");
        go_to_next = false;
        wait_for_go_to_next_step = true;
      }
      if (clickedOption.hasClass("suboption-level-2")) {
        container.find(".suboption-level-2").slideToggle("slow");
        go_to_next = false;
        wait_for_go_to_next_step = true;
      }

      if (
        hasText &&
        textInput.is(":visible") &&
        radioLabel.hasClass("selected")
      ) {
        container.find(".next-btn").attr("disabled", true);
        go_to_next = false;
        wait_for_go_to_next_step = true;
      } else {
        container.find(".next-btn").attr("disabled", false);
      }

      // ---- ✅ Form Submit Check ---- //
      const nextBtn = container.find(".next-btn");

      // 1. Agar click next-btn par hua aur usme submit-btn hai
      // 2. Ya fir radio/text fill hone par container ka next-btn submit-btn hai
      if (
        (jQuery(this).hasClass("next-btn") &&
          jQuery(this).hasClass("submit-btn")) ||
        (!jQuery(this).hasClass("next-btn") && nextBtn.hasClass("submit-btn"))
      ) {
        jQuery("#multiStepForm").trigger("submit");
        return;
      }

      // ---- PROCEED TO NEXT STEP ---- //
      if (go_to_next && !wait_for_go_to_next_step) {
        container.find(".text-input-mq").addClass("d-none");

        container.fadeOut("slow", function () {
          jQuery(this).addClass("hidden-q-container");

          const nextContainer = jQuery(this).next(".form-container-mq");
          nextContainer.removeClass("hidden-q-container").hide();
          nextContainer.addClass("fade-in-right").fadeIn("slow", function () {
            jQuery(this).removeClass("fade-in-right");
          });

          if (nextContainer.attr("attr-is-disable-btn") == "yes") {
            nextContainer.find(".next-btn").attr("disabled", true);
          } else {
            nextContainer.find(".next-btn").attr("disabled", false);
          }
        });
      }
    }
  );

  //======================Multistep main form prev==============================================
  jQuery(".prev-btn").each(function () {
    const qNumber = jQuery(this).attr("data-question-number-in-part");
    const partNumber = jQuery(this).attr("data-current-question-part");

    if (qNumber === "1" && partNumber === "1") {
      jQuery(this).prop("disabled", true);
    }
  });
  jQuery(document).on("click", ".prev-btn", function () {
    const currentContainer = jQuery(this).closest(".form-container-mq");
    const currentIndex = jQuery(".form-container-mq").index(currentContainer);
    const prevIndex = currentIndex - 1;

    if (prevIndex >= 0) {
      // goToStep(prevIndex);

      currentContainer.fadeOut("slow", function () {
        jQuery(this).addClass("hidden-q-container");

        const prevContainer = jQuery(".form-container-mq").eq(prevIndex);
        prevContainer.removeClass("hidden-q-container").hide();

        prevContainer.addClass("fade-in-left").fadeIn("slow", function () {
          jQuery(this).removeClass("fade-in-left");
        });

        // Show only top-level text inputs (not inside suboption levels)
        prevContainer.find(".text-input-mq").each(function () {
          const isInSubsection =
            jQuery(this).closest(
              ".mq-option-levels, .suboption-level-1, .suboption-level-2"
            ).length > 0;

          if (!isInSubsection) {
            jQuery(this).attr("style", "display:block !important");
          }
        });

        prevContainer.find(".option-card").removeClass("selected");
        prevContainer
          .find("input[type=radio], input[type=checkbox]")
          .prop("checked", false);
      });
    }
  });

  //======================Remove next button disable on input====================================
  jQuery(document).on(
    "input",
    ".form-container-mq .text-input-mq",
    function () {
      const $input = jQuery(this);
      const container = jQuery(this).closest(".form-container-mq");
      const label_text = jQuery(this).prev().text().toLowerCase();

      // If label contains cm or kg, allow only numbers
      if (label_text.includes("cm") || label_text.includes("kg")) {
        const val = $input.val();
        if (!/^\d*\.?\d*$/.test(val)) {
          $input.val("");
          mq_show_toaster("Inserisci il valore in numeri", "failure");
          return;
        }
      }

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
                jQuery(".login").find("input[type=text]").eq(0).val(user_mail);
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
                jQuery(".register")
                  .find("input[type=text]")
                  .eq(0)
                  .val(user_mail);
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

          jQuery(".mq-logged-in-user-info").text(
            "Sono " + response.data.user_login
          );
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

  //======================Show selected image====================================================
  let bodyPixNet = null;

  async function loadBodyPix() {
    if (!bodyPixNet) {
      bodyPixNet = await bodyPix.load();
    }
    return bodyPixNet;
  }

  function isImageBlurry(imgElement) {
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");
    canvas.width = imgElement.width;
    canvas.height = imgElement.height;
    ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);

    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const grayData = [];

    for (let i = 0; i < imageData.data.length; i += 4) {
      const avg =
        (imageData.data[i] + imageData.data[i + 1] + imageData.data[i + 2]) / 3;
      grayData.push(avg);
    }

    let sumSq = 0;
    for (let i = 1; i < grayData.length; i++) {
      const diff = grayData[i] - grayData[i - 1];
      sumSq += diff * diff;
    }
    const variance = sumSq / grayData.length;
    return variance < 20;
  }

  async function segmentPerson(net, imageElement) {
    const segmentation = await net.segmentPerson(imageElement);
    const mask = segmentation.data;

    let personPixelCount = 0;
    for (let i = 0; i < mask.length; i++) {
      if (mask[i] === 1) personPixelCount++;
    }

    const coverage = (personPixelCount / mask.length) * 100;

    return coverage > 5;
  }

  // Listen to all inputs
  const validImages = {};
  document.querySelectorAll(".user-images").forEach((input) => {
    validImages[input.id] = false; // initially no valid image
  });

  document.querySelectorAll(".user-images").forEach((input) => {
    input.addEventListener("change", async function (e) {
      mq_show_loader();

      const file = e.target.files[0];
      const previewBox = e.target.previousElementSibling;
      previewBox.innerHTML = "";
      const inputId = e.target.id;

      if (!file) {
        validImages[inputId] = false; // file cleared
        updateNextButton(validImages);
        mq_hide_loader();
        return;
      }

      const reader = new FileReader();
      reader.onload = async function (ev) {
        const img = new Image();
        img.src = ev.target.result;
        img.classList.add("preview-img");
        previewBox.appendChild(img);

        img.onload = async () => {
          // Check blur
          if (isImageBlurry(img)) {
            mq_hide_loader();
            mq_show_toaster(
              "L'immagine è sfocata. Carica un'immagine nitida.",
              "failure"
            );
            validImages[inputId] = false;
            e.target.value = "";
            previewBox.innerHTML = "";
            updateNextButton(validImages);
            return;
          }

          // BodyPix check
          const net = await loadBodyPix();
          const detected = await segmentPerson(net, img);

          mq_hide_loader();

          if (!detected) {
            mq_show_toaster(
              "Nessuna testa/persona rilevata! Carica un'immagine chiara della testa, anteriore o posteriore.",
              "failure"
            );
            validImages[inputId] = false;
            e.target.value = "";
            previewBox.innerHTML = "";
            updateNextButton(validImages);
            return;
          }

          // Mark this input as valid
          validImages[inputId] = true;
          updateNextButton(validImages);
        };
      };
      reader.readAsDataURL(file);
    });
  });

  function updateNextButton(validImages) {
    // Check if ALL inputs are true
    const allValid = Object.values(validImages).every((val) => val === true);

    if (!allValid) {
      jQuery(".form-container-mq").find(".next-btn").attr("disabled", true);
    } else {
      jQuery(".form-container-mq").find(".next-btn").attr("disabled", false);
    }
  }

  //======================Submit patient form====================================================
  function submitPatientForm(e) {
    e.preventDefault();

    let formDataArray = [];

    jQuery("#multiStepForm .form-container-mq").each(function () {
      let container = jQuery(this);
      let questionData = {
        question_part: container.data("current-question-part"),
        question_number_in_part: container.data("question-number-in-part"),
        question_global_number: container.data("question-global-number"),
        question_id: container.data("current-question-id"),
        main_question_input_type: container.data("main-input-type"),
        main_question_type: container.data("main-question-type"),
        answers: [],
      };

      // RADIO inputs
      container.find(".radio-input-mq:checked").each(function () {
        let radio = jQuery(this);
        let answerObj = {
          type: "radio",
          id: radio.attr("id"),
          name: radio.attr("name"),
          value: radio.val(),
          has_text_input: radio.attr("attr-has-text-input") || "no",
          product_related: radio.attr("attr-product-related") || "",
          points: radio.attr("attr-points-for-this") || 0,
          text_value: "",
        };

        // Agar radio ke sath text input hai
        let relatedTextInput = radio
          .closest(".mq-option-levels, .inputBX")
          .find(".text-input-mq:visible");
        if (relatedTextInput.length) {
          answerObj.text_value = relatedTextInput.val() || "";
        }

        questionData.answers.push(answerObj);
      });

      // TEXT inputs not linked to radio
      container.find(".text-input-mq").each(function () {
        let textInput = jQuery(this);
        if (
          textInput.closest(".mq-option-levels").find(".radio-input-mq").length
        )
          return;

        questionData.answers.push({
          type: "text",
          id: textInput.attr("id") || "",
          name: textInput.attr("name"),
          value: textInput.val(),
          product_related: textInput.attr("attr-product-related") || "",
        });
      });

      // IMAGE inputs
      container.find(".user-images").each(function () {
        let imgInput = jQuery(this);
        let file = imgInput[0].files[0] || null;

        questionData.answers.push({
          type: "image",
          id: imgInput.attr("id"),
          name: imgInput.attr("name"),
          file_name: file ? file.name : "",
          valid: file ? true : false,
        });
      });

      formDataArray.push(questionData);
    });

    var is_on_the_last_quetion = jQuery(".last-routine").is(":visible");

    // ✅ AJAX with FormData
    let fd = new FormData();
    fd.append("action", "mq_submit_patient_answers_form_ajax_function");
    fd.append("mq_nonce", security_nonce);
    fd.append("form_data", JSON.stringify(formDataArray));
    fd.append("is_on_last_quetion", is_on_the_last_quetion);

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: fd,
      contentType: false,
      processData: false,
      beforeSend: function () {
        mq_show_loader();
      },
      success: function (response) {
        mq_hide_loader();


        if (response.data.type == "error") {

          mq_show_toaster(response.data.message, "failure");

        } else {
          
        
          let isLast = is_on_the_last_quetion;
          let nextStep2 = response.data.next_step2_info === "yes";
          let nextStep3 = response.data.next_step3_info === "yes";
          let outcome = response.data.outcome;
          let topProducts = response.data.top_products;
          let products_outcome_heading_return =
            response.data.products_outcome_heading;

          if (
            nextStep2 &&
            !nextStep3 &&
            !outcome &&
            (!topProducts || topProducts.length === 0)
          ) {
            // Show next part 2 container (user)
            jQuery(".submit-btn")
              .closest(".form-container-mq")
              .fadeOut("slow", function () {
                jQuery(this).addClass("hidden-q-container");

                const nextContainer = jQuery(".user");
                nextContainer.removeClass("hidden-q-container").hide();
                nextContainer
                  .addClass("fade-in-right")
                  .fadeIn("slow", function () {
                    jQuery(this).removeClass("fade-in-right");
                  });
              });
          } else if (
            nextStep2 &&
            nextStep3 &&
            (!topProducts || topProducts.length === 0)
          ) {
            // Show part 3 container (routine)
            jQuery(".submit-btn")
              .closest(".form-container-mq")
              .fadeOut("slow", function () {
                jQuery(this).addClass("hidden-q-container");

                const nextContainer = jQuery(".form-container-routine").eq(0);
                nextContainer.removeClass("hidden-q-container").hide();
                nextContainer
                  .addClass("fade-in-right")
                  .fadeIn("slow", function () {
                    jQuery(this).removeClass("fade-in-right");
                  });
              });
          } else if (
            isLast &&
            (!topProducts || topProducts.length === 0) &&
            outcome
          ) {
            // Last question, no top products, show outcome
            jQuery(".submit-btn")
              .closest(".form-container-mq")
              .fadeOut("slow", function () {
                jQuery(this).addClass("hidden-q-container");

                jQuery(".outcome-result")
                  .html(
                    `<p>${outcome}<div class="restart-url"><a href="${form_page_url_return}">Ricominciare</a></div></p>`
                  )
                  .hide()
                  .fadeIn("slow");
              });
          } else if (
            isLast &&
            topProducts &&
            topProducts.length > 0 &&
            !outcome
          ) {
            // Last question, top products present, no outcome, show products
            let html =
              '<div class="top-products-wrap"><h2>' +
              products_outcome_heading_return +
              '</h2><ul class="products-ul">';
            topProducts.forEach((p) => {
              html += `
                <li class="product-card">
                    <img src="${p.image}" alt="${p.name}" class="product-img" />
                    <h3><a href="${p.link}">${p.name}</a></h3>
                    <div class="product-price">${p.price}</div>
                    <a href="${p.link}" class="learn-more-btn">Learn More</a>
                </li>
            `;
            });
            html += "</ul></div>";

            jQuery(".submit-btn")
              .closest(".form-container-mq")
              .fadeOut("slow", function () {
                jQuery(this).addClass("hidden-q-container");

                // Width badhane ka code
                jQuery(".main-container-mq").addClass("productResult");

                jQuery(".product-outcome-result")
                  .html(html)
                  .hide()
                  .fadeIn("slow");
              });
          } else {
            // Default fallback: show outcome
            jQuery(".submit-btn")
              .closest(".form-container-mq")
              .fadeOut("slow", function () {
                jQuery(this).addClass("hidden-q-container");

                jQuery(".outcome-result")
                  .html(
                    `<p>${outcome}<div class="restart-url"><a href="${form_page_url_return}">Ricominciare</a></div></p>`
                  )
                  .hide()
                  .fadeIn("slow");
              });
          }


        }

       
      },
      error: function (err) {
        mq_hide_loader();
      },
    });
  }

  // 🔹 Bind both events
  jQuery(document).on("click", ".submit-btn", submitPatientForm);
  jQuery("#multiStepForm").on("submit", submitPatientForm);

  //======================Document ready ends here===============================================
});
