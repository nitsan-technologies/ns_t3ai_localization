
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


