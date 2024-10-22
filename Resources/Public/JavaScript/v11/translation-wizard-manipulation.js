define([
    "jquery",
    "TYPO3/CMS/Backend/Icons",
    "TYPO3/CMS/Backend/Severity",
    "TYPO3/CMS/Backend/Modal",
  ], function ($, Icons, Severity, modalObject) {
  
    class GlobalButtonBarModal {
      listenEvent(url, title, modalSize, icon, coreIcon, modalConfirmation, comingSoon, modalClass, docUrl) {
        modalObject.advanced({
          type: modalObject.types.iframe,
          title: title,
          staticBackdrop: true,
          size: modalSize,
          additionalCssClasses: [`ns_t3ai--modal ns_t3ai--modal-${modalSize} ${modalClass}`],
          content: url,
          severity: comingSoon ? Severity.info : Severity.notice,
          callback: (currentModal) => {
            if (currentModal.find('.t3js-modal-close').length && modalConfirmation === 'true') {
              localStorage.setItem("flag", 'false');
              currentModal.find('.t3js-modal-close').addClass('d-none');
              var parser = new DOMParser();
              var doc = parser.parseFromString('<div class="custom-close close">' + currentModal.find('.t3js-modal-close').html() + '</div>', 'text/html');
              currentModal.find('.t3js-modal-close').after($(doc).find('.custom-close'));
              handelCloseButton(currentModal.find('.custom-close'), currentModal);
            }
  
            $(currentModal).find("iframe").on("load", function (p) {
              Icons.getIcon(icon, Icons.sizes.small).then(function (markup) {
                var titleWithIcon;
                if (coreIcon) {
                  titleWithIcon = markup + ' ' + title;
                } else {
                  titleWithIcon = icon + ' ' + title;
                }
                $(currentModal).find('.modal-header .t3js-modal-title').html(titleWithIcon);
                if (!coreIcon && $(currentModal).find('.t3js-modal-title .t3js-icon').length) {
                  $(currentModal).find('.t3js-modal-title .t3js-icon').addClass('icon icon-size-small');
                }
              });
  
              let iframeDocument = $(this).contents();
              let closeBtn = iframeDocument.find('[t3ai-modal="close"]');
              if (iframeDocument.find('.t3ai-btn-docurl')) {
                iframeDocument.find('.t3ai-btn-docurl').attr('href', docUrl);
              }
              if (closeBtn.length) {
                closeBtn.each(function () {
                  if (!$(this).is('#seo-save-close') && !$(this).is('#og-save-close')) {
                    let closeConfirmation = $(this).attr('data-confirmation');
                    if (closeConfirmation !== 'false') {
                      handelCloseButton($(this), currentModal);
                    } else {
                      $(this).on('click', function () {
                        modalObject.dismiss();
                      });
                    }
                  }
                });
              }
  
              const formChanged = function (form) {
                const formField = form.find('.form-control, .form-select, .btn-check, .btn');
                if (formField.length) {
                  formField.each(function () {
                    $(this).on('change', function () {
                      if ($(this)[0].defaultValue !== $(this).val()) {
                        localStorage.setItem("flag", 'true');
                      } else if ($(this).is(':checked')) {
                        localStorage.setItem("flag", 'true');
                      }
                    });
                  });
                }
              };
  
              if (iframeDocument.find('.t3ai-modal-content').length) {
                formChanged(iframeDocument.find('.t3ai-modal-content'));
              }
            
            });
  
            currentModal[0].addEventListener('shown.bs.modal', function () {
              if (currentModal[0].querySelector('iframe')) {
                  currentModal[0].querySelector('iframe').focus();
              }
            });
  
            if (currentModal.length && currentModal[0].querySelector('iframe')) {
              handelSpeechToText(currentModal[0]);
            }
  
            currentModal.on('hidden.bs.modal', function (event) {
              localStorage.setItem("flag", 'false');
            });
          }
        });
      }
  
      async aiContentElementModal(url, title, modalSize) {
        const modalData = await modalObject.advanced({
          type: modalObject.types.ajax,
          title: title,
          size: modalSize,
          staticBackdrop: true,
          additionalCssClasses: [`ns_t3ai--modal ns_t3ai--modal-${modalSize}`],
          content: url,
          severity: Severity.notice,
          callback: (currentModal) => {
            if ($(currentModal).find('.t3js-modal-close').length) {
              localStorage.setItem("flag", 'false');
              $(currentModal).find('.t3js-modal-close').addClass('d-none');
              var parser = new DOMParser();
              var doc = parser.parseFromString(`<div class="custom-close close">${$(currentModal).find('.t3js-modal-close').html()}</div>`, 'text/html');
              $(currentModal).find('.t3js-modal-close').after($(doc).find('.custom-close'));
              if ($(currentModal).find('.custom-close').length) {
                handelCloseButton($(currentModal).find('.custom-close'), currentModal);
              }
            }
  
            currentModal.on("shown.bs.modal", () => {
              const formChanged = function (form) {
                const formField = $(form).find('.form-control, .form-select, .btn-check');
                if (formField.length) {
                  formField.each(function () {
                    $(this).on('change', () => {
                      if (this.defaultValue != $(this).val()) {
                        localStorage.setItem("flag", 'true');
                      } else if ($(this).is(':checked')) {
                        localStorage.setItem("flag", 'true');
                      }
                    });
                  });
                }
              };
  
              if ($(currentModal).find('#ai-element-data').length) {
                if (formChanged($(currentModal).find('#ai-element-data'))) {
                  localStorage.setItem("flag", 'true');
                }
              }
  
              let closeBtn = $(currentModal).find('[t3ai-modal="close"]');
              if (closeBtn.length) {
                closeBtn.each(function () {
                let closeConfirmation = $(this).attr('data-confirmation');
                if (closeConfirmation != 'false') {
                    handelCloseButton($(this), $(currentModal));
                } else {
                    $(this).addEventListener('click', () => {
                      $(currentModal).modal('hide');
                    });
                }
                });
              }
            });
  
            currentModal.on("hidden.bs.modal", () => {
              localStorage.setItem("flag", 'false');
            });
  
            Icons.getIcon('content-menu-thumbnail', Icons.sizes.small).then(function (markup) {
              let titleWithIcon = markup + ' ' + title;
              $(currentModal).find('.modal-header .t3js-modal-title').html(titleWithIcon);
            });
  
            return currentModal;
          }
  
        });
        return modalData;
      }
  
      async aiNewsButtonModal(url, title, modalSize, icon, coreIcon, docUrl) {
        return await modalObject.advanced({
          type: modalObject.types.iframe,
          title: title,
          staticBackdrop: true,
          size: modalSize,
          additionalCssClasses: [`ns_t3ai--modal ns_t3ai--modal-${modalSize}`],
          content: url,
          severity: Severity.notice,
          callback: (currentModal) => {
            if (currentModal.find('.t3js-modal-close').length) {
              localStorage.setItem("flag", 'false');
              currentModal.find('.t3js-modal-close').addClass('d-none');
              var parser = new DOMParser();
              var doc = parser.parseFromString('<div class="custom-close close">' + currentModal.find('.t3js-modal-close').html() + '</div>', 'text/html');
              currentModal.find('.t3js-modal-close').after($(doc).find('.custom-close'));
              handelCloseButton(currentModal.find('.custom-close'), currentModal);
            }
  
            $(currentModal).find("iframe").on("load", function (p) {
              Icons.getIcon(icon, Icons.sizes.small).then(function (markup) {
                var titleWithIcon;
                if (coreIcon) {
                  titleWithIcon = markup + ' ' + title;
                } else {
                  titleWithIcon = icon + ' ' + title;
                }
                $(currentModal).find('.modal-header .t3js-modal-title').html(titleWithIcon);
                if (!coreIcon && $(currentModal).find('.t3js-modal-title .t3js-icon').length) {
                  $(currentModal).find('.t3js-modal-title .t3js-icon').addClass('icon icon-size-small');
                }
              });
  
              let iframeDocument = $(this).contents();
              if (iframeDocument.find('.t3ai-btn-docurl')) {
                iframeDocument.find('.t3ai-btn-docurl').attr('href', docUrl);
              }
              let closeBtn = iframeDocument.find('[t3ai-modal="close"]');
              if (closeBtn.length) {
                closeBtn.each(function () {
                  let closeConfirmation = $(this).attr('data-confirmation');
                  if (closeConfirmation !== 'false') {
                    handelCloseButton($(this), currentModal);
                  } else {
                    $(this).on('click', function () {
                      modalObject.dismiss();
                    });
                  }
                });
              }
  
              const formChanged = function (form) {
                const formField = form.find('.form-control, .form-select, .btn-check, .btn');
                if (formField.length) {
                  formField.each(function () {
                    $(this).on('change', function () {
                      if ($(this)[0].defaultValue !== $(this).val()) {
                        localStorage.setItem("flag", 'true');
                      } else if ($(this).is(':checked')) {
                        localStorage.setItem("flag", 'true');
                      }
                    });
                  });
                }
              };
  
              if (iframeDocument.find('.t3ai-modal-content').length) {
                formChanged(iframeDocument.find('.t3ai-modal-content'));
              }
  
              const reportBtn = iframeDocument.find('button[data-button="ai_techies_report_issue"]');
              if (reportBtn.length) {
                reportBtn.attr('data-additionalparams', title.trim());
              }
  
              const featureReqBtn = iframeDocument.find('button[data-button="ai_techies_suggest_features"]');
              if (featureReqBtn.length) {
                featureReqBtn.attr('data-additionalparams', title.trim());
              }
            });
  
            if (currentModal.length && currentModal[0].querySelector('iframe')) {
                handelSpeechToText(currentModal[0]);
            }
  
            currentModal[0].addEventListener('shown.bs.modal', function () {
              if (currentModal[0].querySelector('iframe')) {
                  currentModal[0].querySelector('iframe').focus();
              }
            });
  
            currentModal.on('hidden.bs.modal', function (event) {
              localStorage.setItem("flag", 'false');
            });
            return currentModal
          }
        });
      }
  
      async tcaSuggestionsModal(url, title, modalSize) {
        const modalData = await modalObject.advanced({
          type: modalObject.types.ajax,
          title: title,
          staticBackdrop: true,
          size: modalSize,
          buttons: [
            {
              text: 'Cancel',
              btnClass: 'btn btn-default',
              name: 'cancel',
            },
            {
              text: 'Save',
              btnClass: 'btn btn-primary',
              name: 'save-suggestion',
            },
          ],
          additionalCssClasses: [`ns_t3ai--modal ns_t3ai--modal-${modalSize}`],
          content: url,
          severity: Severity.notice,
          callback: (currentModal) => {
            if (currentModal.find('.t3js-modal-close').length) {
              localStorage.setItem("flag", 'false');
              currentModal.find('.t3js-modal-close').addClass('d-none');
              var parser = new DOMParser();
              var doc = parser.parseFromString('<div class="custom-close close">' + currentModal.find('.t3js-modal-close').html() + '</div>', 'text/html');
              currentModal.find('.t3js-modal-close').after($(doc).find('.custom-close'));
              handelCloseButton(currentModal.find('.custom-close'), currentModal);
            }
            Icons.getIcon('content-menu-thumbnail', Icons.sizes.small).then(function (markup) {
              let titleWithIcon = markup + ' ' + title;
              $(currentModal).find('.modal-header .t3js-modal-title').html(titleWithIcon);
            });
  
            $(currentModal).on('shown.bs.modal', function () {
              const formChanged = function (form) {
                const formField = $(form).find('.form-control, .form-select, .btn-check');
                if (formField.length) {
                  formField.each(function () {
                    $(this).on('change', function () {
                      if (this.defaultValue != this.value || this.checked) {
                        localStorage.setItem("flag", 'true');
                      }
                    });
                  });
                }
              }
  
              if ($(currentModal).find('.t3js-modal-body').length) {
                if (formChanged($(currentModal).find('.t3js-modal-body')[0])) {
                  localStorage.setItem("flag", 'true');
                }
              }
            });
  
            $(currentModal).on('hidden.bs.modal', function (event) {
              localStorage.setItem("flag", 'false');
            });
  
            let closeBtn = $(currentModal).find('button[name="cancel"]');
            if (closeBtn.length) {
              closeBtn.each(function () {
                handelCloseButton($(this)[0], currentModal);
              });
            }
            return currentModal
          }
        });
        return modalData;
      }
    }
  
    const handelCloseButton = function (closeBtn, currentModal) {
      if (closeBtn.length) {
        closeBtn.each(function () {
          $(this).on('click', function (e) {
            if (localStorage.getItem("flag") === 'true') {
              e.preventDefault();
              let modalButtons = [];
              modalButtons.push({
                text: TYPO3.lang['confirmModal.cancel'] || 'No, I will continue editing',
                active: true,
                btnClass: 'btn-default',
                name: 'cancel',
                trigger: function (e, t) {
                  modalObject.dismiss();
                }
              });
              modalButtons.push({
                text: TYPO3.lang['confirmModal.yes'] || 'Yes, discard my changes',
                active: true,
                btnClass: 'btn-warning',
                name: 'confirm',
                trigger: function (e, t) {
                  localStorage.setItem("flag", 'false');
                  modalObject.dismiss();
                  modalObject.dismiss();
                }
              });
              modalObject.show(
                  TYPO3.lang['confirmModal.newPage.modal.header'] || 'Do you want to close without saving?',
                  TYPO3.lang['confirmModal.newPage.modal.message'] || 'Are you sure you want to discard these changes?',
                  Severity.warning,
                  modalButtons
              );
            } else {
              localStorage.setItem("flag", 'false');
              modalObject.dismiss();
            }
          });
        });
      }
    };
  
    const handelSpeechToText = (currentModal) => {
      currentModal.querySelector('iframe').addEventListener('load', (e) => {
          const iframeContent = e.target.contentWindow.document;
          const btnAudio = iframeContent.querySelectorAll('.btn-audio');
          if (btnAudio.length) {
              btnAudio.forEach(element => {
                  element.addEventListener('click', () => {
                      var output = element.closest(".modal-body").querySelector('.output');
                      var action = element;
                      let recognization = new webkitSpeechRecognition();
                      recognization.onstart = () => {
                          element.closest(".modal-body").querySelector('.output').value = "Listening...";
                          output.classList.add('recording');
                          action.innerHTML = `<div class="t3js-icon icon icon-size-small icon-state-default"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="211" height="211" style="shape-rendering: auto; display: block; background: transparent;"><g><rect fill="#ff8700" height="66" width="11" y="17" x="19.5">
                          <animate begin="-0.12422360248447205s" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" values="7.100000000000001;17;17" keyTimes="0;0.5;1" calcMode="spline" dur="0.6211180124223602s" repeatCount="indefinite" attributeName="y"/>
                          <animate begin="-0.12422360248447205s" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" values="85.8;66;66" keyTimes="0;0.5;1" calcMode="spline" dur="0.6211180124223602s" repeatCount="indefinite" attributeName="height"/>
                        </rect>
                        <rect fill="#ff8700" height="66" width="11" y="17" x="44.5">
                          <animate begin="-0.062111801242236024s" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" values="9.574999999999996;17;17" keyTimes="0;0.5;1" calcMode="spline" dur="0.6211180124223602s" repeatCount="indefinite" attributeName="y"/>
                          <animate begin="-0.062111801242236024s" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" values="80.85000000000001;66;66" keyTimes="0;0.5;1" calcMode="spline" dur="0.6211180124223602s" repeatCount="indefinite" attributeName="height"/>
                        </rect>
                        <rect fill="#ff8700" height="66" width="11" y="17" x="69.5">
                          <animate keySplines="0 0.5 0.5 1;0 0.5 0.5 1" values="9.574999999999996;17;17" keyTimes="0;0.5;1" calcMode="spline" dur="0.6211180124223602s" repeatCount="indefinite" attributeName="y"/>
                          <animate keySplines="0 0.5 0.5 1;0 0.5 0.5 1" values="80.85000000000001;66;66" keyTimes="0;0.5;1" calcMode="spline" dur="0.6211180124223602s" repeatCount="indefinite" attributeName="height"/>
                        </rect><g/></g></div>`;
                      }
                      recognization.onend = () => {
                          output.classList.remove('recording');
                          action.innerHTML = `<div class="t3js-icon icon icon-size-small icon-state-default"><svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M361 210V105C361 47.103 313.897 0 256 0S151 47.103 151 105v105H61v30h30v45c0 75.387 50.82 139.126 120 158.762V482h-90v30h270v-30h-90v-38.238C370.18 424.126 421 360.387 421 285v-45h30v-30h-90zm-180-30h60v-30h-60v-30h60V90h-58.491c6.969-34.192 37.272-60 73.491-60 36.22 0 66.522 25.808 73.491 60H271v30h60v30h-60v30h60v30H181v-30zm0 60h150v45c0 41.355-33.645 75-75 75s-75-33.645-75-75v-45zm90 242h-30v-32.689c4.942.447 9.943.689 15 .689s10.058-.242 15-.689V482zm120-197c0 74.439-60.561 135-135 135s-135-60.561-135-135v-45h30v45c0 57.897 47.103 105 105 105s105-47.103 105-105v-45h30v45z"/></svg></div>`;
                      }
                      recognization.onresult = (e) => {
                          var transcript = e.results[0][0].transcript;
                          output.value = transcript;
                          output.classList.remove("hide")
                          action.innerHTML = "";
                      }
                      recognization.start();
                  });
              });
          }
      });
    };
  
    let modal;
    return modal || (modal = new GlobalButtonBarModal, TYPO3.GlobalButtonBarModal = modal), modal;
  
  });
  