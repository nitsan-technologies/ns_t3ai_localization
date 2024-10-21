define([
    "TYPO3/CMS/NsT3aiLocalization/v11/translation-wizard-manipulation",
    "TYPO3/CMS/Backend/Notification",
  ], function (GlobalButtonBarModal, Notification) {
  

    class globalButtonBar {
      constructor() {
        this.init(event);
      }
  
      init(event) {
        if (event.target.hasAttribute('data-button') && event.target.hasAttribute('data-identifier', 'globalWizardBtn')) {
          var comingSoon = false;
          if (event.target.getAttribute('data-button') === 'ai_coming_soon' && event.target.getAttribute('data-modal-size') != 'small') {
            comingSoon = true;
            Notification.warning('Access Restricted: Contact the admin to access this feature.');
          } else {
            let params = new URL(location.href).searchParams;
  
            if (event.target.getAttribute('data-button') === 'ai_coming_soon') {
              comingSoon = true;
            } else {
              comingSoon = false;
            }
            let id = params.get('id');
            let additionalParams = event.target.getAttribute('data-additionalParams');
            let buttonFor = event.target.getAttribute('data-button');
            var coreIcon = true;
            if (event.target.closest('.card') && event.target.closest('.card').querySelector('.card-icon .t3js-icon') && event.target.closest('.card').querySelector('.card-icon .t3js-icon').getAttribute('data-identifier')) {
              var coreIcon = true;
              var cardIcon = event.target.closest('.card').querySelector('.card-icon .t3js-icon').getAttribute('data-identifier');
            } else if ((event.target.className.includes('dropdown-item') || event.target.className.includes('btn-t3ai-main')) && event.target.querySelector('.t3js-icon')) {
              var coreIcon = true;
              var cardIcon = event.target.querySelector('.t3js-icon').getAttribute('data-identifier');
            } else if (event.target.closest('.card') && event.target.closest('.card').querySelector('.card-icon')) {
              var coreIcon = false;
              var cardIcon = event.target.closest('.card').querySelector('.card-icon').innerHTML;
            }
            let icon = cardIcon ? cardIcon : 'actions-localize-nst3ai';
  
            if (event.target.closest('.card') && event.target.closest('.card').querySelector('.card-title')) {
              var cardTitle = event.target.closest('.card').querySelector('.card-title').textContent;
            }
            let buttonTitle = event.target.getAttribute('title');
            let title = cardTitle ? cardTitle : buttonTitle;
  
            let buttonSize = event.target.getAttribute('data-modal-size');
            let modalSize = buttonSize ? buttonSize : 'large';
  
            let buttonClass = event.target.getAttribute('data-model-class');
            let modalClass = buttonClass ? buttonClass : '';
  
            let buttonConfirmation = event.target.getAttribute('data-confirmation');
            let modalConfirmation = buttonConfirmation ? buttonConfirmation : 'true';
  
            let buttonName = event.target.getAttribute('data-model-name');
            buttonName = buttonName ? buttonName : '';
  
            let buttonId = event.target.getAttribute('id');
            buttonId = buttonId ? buttonId : '';
  
            let mode = event.target.getAttribute('data-mode');
            mode = mode ? mode : '';
  
            let pageId = event.target.getAttribute('data-page-id');
            pageId = pageId ? pageId : '';
  
            let elemId = event.target.getAttribute('data-element-id');
            elemId = elemId ? elemId : '';
  
            let docUrl = event.target.getAttribute('data-url');
            docUrl = docUrl ? docUrl : 'https://docs.t3planet.com/en/latest/';
  
            let returnUrl = event.target.getAttribute('data-return-url');
            returnUrl = returnUrl ? returnUrl : '';
  
            let url = TYPO3.settings.ajaxUrls[buttonFor]+'&pageId='+id;
            if (additionalParams) {
              url += '&additionalParams='+additionalParams;
            }
            if (buttonName) {
              url += '&name='+buttonName;
            }
            if (mode) {
              url += '&mode='+mode;
            }
            if (pageId) {
              url += '&pid='+pageId;
            }
            if (elemId) {
              url += '&elemId='+elemId;
            }
            if (returnUrl) {
              url += '&returnUrl='+returnUrl;
            }
            if (buttonId) {
              url += '&buttonId='+buttonId;
            }
            GlobalButtonBarModal.listenEvent(url, title, modalSize, icon, coreIcon, modalConfirmation, comingSoon, modalClass, docUrl);
          }
        }
      }
    }
  
    document.addEventListener('click', (event) => {
      if (event.target.hasAttribute('data-button') && event.target.hasAttribute('data-identifier', 'globalWizardBtn')) {
        new globalButtonBar(event);
      }
    });
  
  
    let modal;
    return modal || (modal = new globalButtonBar, TYPO3.globalButtonBar = modal), modal;
  });
  