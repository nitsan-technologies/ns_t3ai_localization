
if(TYPO3.settings.ExtensionFiles){

    const fileOptions = TYPO3.settings.ExtensionFiles.fileOptions;
    // Get references to the select elements
    const extKeySelect = document.querySelector('select[id="extKey"]');
    const fileNameSelect = document.getElementById('fileName');

    // Function to update the filename select options
    function updateFileNameOptions(selectedExtension) {
        // Clear the current options
        fileNameSelect.innerHTML = '';

        // Check if the selected extension has corresponding file options
        if (fileOptions[selectedExtension]) {
            // Add the new options
            fileOptions[selectedExtension].forEach(function(file) {
                const option = document.createElement('option');
                option.value = file;
                option.text = file;
                fileNameSelect.appendChild(option);
            });
        }
    }

    // Listen for changes on the extensionKey select
    if(extKeySelect){
        extKeySelect.addEventListener('change', function() {
            const selectedExtension = extKeySelect.value;
            updateFileNameOptions(selectedExtension);
        });
    }
}


document.querySelectorAll('.localizationSaveBtn').forEach(function (button) {
    button.addEventListener("click", async function (ev) {
        ev.preventDefault();

        let form = ev.target.closest('form');
        let url = form.getAttribute('action');

        // let xlfValidateForm = document.querySelector('#localization-validate');
      
        let formData = new FormData(form);
        let loaderIcon = form.querySelector('#ns-t3ai__loader');
        loaderIcon.classList.add('ns-show-overlay');  // Show the loader

        try {
            let status = await submitAjaxForm(formData, url);
            // Show notifications based on TYPO3 version
            await loadNotificationModule(TYPO3.settings.t3version, status);
        } catch (error) {
            await showErrorNotification();
        } finally {
            loaderIcon.classList.remove('ns-show-overlay');  // Hide the loader
        }
    });
});

// Extracted Notification Logic
async function loadNotificationModule(t3version, status) {
    let Notification;

    if (t3version > 12) {
        Notification = await import("@typo3/backend/notification.js");
    } else {
        Notification = await new Promise((resolve) => {
            require(['TYPO3/CMS/Backend/Notification'], resolve);
        });
    }

    if (status) {
        Notification.success(TYPO3.lang['traslation.write.success.title'], TYPO3.lang['traslation.write.success.message']);
    } else {
        Notification.error(TYPO3.lang['traslation.generation.error'], TYPO3.lang['traslation.errorWritingTranslationFile.message']);
    }
}

// Error notification in case of failure
async function showErrorNotification() {
    if (TYPO3.settings.t3version > 12) {
        const Notification = await import("@typo3/backend/notification.js");
        Notification.error(TYPO3.lang['NsT3Ai.error'], 'Something went wrong during the request.');
    } else {
        require(['TYPO3/CMS/Backend/Notification'], function (Notification) {
            Notification.error(TYPO3.lang['NsT3Ai.error'], 'Something went wrong during the request.');
        });
    }
}

function submitAjaxForm(formData, route) {
    return new Promise((resolve, reject) => {
        var request = new XMLHttpRequest();
        var ajaxUrl = TYPO3.settings.ajaxUrls[route];

        request.open('POST', ajaxUrl, true);

        request.onload = function () {
            console.log(request);
            let responseBody = JSON.parse(request.responseText);
            resolve(!!responseBody.status);  // Resolve with boolean status
        };

        // request.onerror = function () {
        //     reject(false);  // Reject with a false in case of an error
        // };

        request.send(formData);
    });
}
