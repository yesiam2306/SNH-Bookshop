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

    fileRadio.classList.add('checked');

    /*-------------- TOGGLE SHORT / FILE --------------*/

    function toggleType() {
        console.log('toggle');
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
        })
    );
    ['dragleave', 'drop'].forEach((evt) =>
        dropArea.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.remove('dragover');
        })
    );
    dropArea.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        console.log(files);
        if (files.length > 0) {
            fileInput.files = files;
            dropArea.querySelector('p').textContent = files[0].name;
        }
    });
    dropArea.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => {
        console.log(fileInput.files[0].name);
        if (fileInput.files.length > 0) {
            dropArea.querySelector('p').textContent = fileInput.files[0].name;
        }
    });

    /*-------------- CONTATORI --------------*/

    if (textarea && counter) {
        textarea.addEventListener('input', () => {
            const len = textarea.value.length;
            counter.textContent = `${len} / ${maxChars}`;
        });
    }
    if (title && counterTitle) {
        title.addEventListener('input', () => {
            const len = title.value.length;
            counterTitle.textContent = `${len} / ${maxCharsTitle}`;
        });
    }
});
