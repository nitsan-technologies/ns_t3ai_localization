define([
    "TYPO3/CMS/NsT3aiLocalization/v11/translation-wizard-manipulation",
  ], function (GlobalButtonBarModal) {
  

    class globalButtonBar {
      constructor() {
        this.init(event);
      }
  
      init(event) {
        if (event.target.hasAttribute('data-button') && event.target.hasAttribute('data-identifier', 'globalWizardBtn')) {
         
            let params = new URL(location.href).searchParams;
            let id = params.get('id');
            let additionalParams = event.target.getAttribute('data-additionalParams');
            let buttonFor = event.target.getAttribute('data-button');
            let icon = 'actions-translate';
            let title =  event.target.getAttribute('aria-label');
  
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
  
            let url = TYPO3.settings.ajaxUrls[buttonFor];
            if (additionalParams) {
              url += '&additionalParams='+additionalParams;
            }
            if (buttonName) {
              url += '&name='+buttonName;
            }
            if (buttonId) {
              url += '&buttonId='+buttonId;
            }
            GlobalButtonBarModal.listenEvent(url, title, modalSize, icon, modalConfirmation, modalClass);
        }
      }
    }
  
    document.addEventListener('click', (event) => {
      if (event.target.hasAttribute('data-button') && event.target.hasAttribute('data-identifier', 'localizationWizardBtn')) {
        if(event.target.getAttribute('data-button') != ''){
          new globalButtonBar(event);
        }
      }
    });
  
  
    let modal;
    return modal || (modal = new globalButtonBar, TYPO3.globalButtonBar = modal), modal;
  });
  