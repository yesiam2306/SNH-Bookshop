/**
 * questo file viene usato in upload.php per gestire il form di upload. principalmente è per avere una visualizzazione carina, con pulsante
 * di selezione file e drag&drop, e per contare i caratteri inseriti nei campi titolo e contenuto.
 */

document.addEventListener('DOMContentLoaded', () => {
    const shortRadio = document.querySelector('input[value="short"]');
    const fileRadio = document.querySelector('input[value="file"]');
    const shortBox = document.getElementById('shortStoryBox');
    const fileBox = document.getElementById('fileUploadBox');
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('file');

    const textarea = document.getElementById('content');
    const counter = document.getElementById('charCounter');
    const maxChars = 200;

    const title = document.getElementById('title');
    const counterTitle = document.getElementById('charCounterTitle');
    const maxCharsTitle = 50;

    /*-------------- TOGGLE SHORT / FILE --------------*/

    function toggleType() {
        if (fileRadio.checked) {
            shortBox.classList.add('hidden');
            fileBox.classList.remove('hidden');
            textarea.removeAttribute('required');
        } else {
            fileBox.classList.add('hidden');
            shortBox.classList.remove('hidden');
            textarea.setAttribute('required', '');
        }
    }

    shortRadio.addEventListener('change', toggleType);
    fileRadio.addEventListener('change', toggleType);
    toggleType();

    /*-------------- FINESTRA FILE --------------*/

    ['dragenter', 'dragover'].forEach((evt) =>
        dropArea.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.add('dragover');
        }),
    );
    ['dragleave', 'drop'].forEach((evt) =>
        dropArea.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.remove('dragover');
        }),
    );
    dropArea.addEventListener('drop', (e) => {
        if (files.length > 0) {
            fileInput.files = files;
            dropArea.querySelector('p').textContent = files[0].name;
        }
    });
    dropArea.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => {
        if (fileInput.files && fileInput.files.length > 0) {
            dropArea.querySelector('p').textContent = fileInput.files[0].name;
        }
    });

    /*-------------- CONTATORI --------------*/
    if (textarea && counter) {
        textarea.addEventListener('input', () => {
            let len = textarea.value.length;
            if (len > maxChars) {
                textarea.value = textarea.value.slice(0, maxChars);
                len = maxChars;
            }
            counter.textContent = `${len} / ${maxChars}`;
        });
    }

    if (title && counterTitle) {
        title.addEventListener('input', () => {
            let len = title.value.length;
            if (len > maxCharsTitle) {
                title.value = title.value.slice(0, maxCharsTitle);
                len = maxCharsTitle;
            }
            counterTitle.textContent = `${len} / ${maxCharsTitle}`;
        });
    }
});
