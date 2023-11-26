
define([], function(jQuery) {

const FormTool = {

    init: function() {
        const self = this;
        // Alle reload Elemente suchen
        const elems = document.querySelectorAll('[data-action-submit="$form"][data-global-event]');
        elems.forEach(function(elem) {
            var event = elem.getAttribute('data-global-event');
            elem.addEventListener(event, function() {
                // Alle hidden Submit-Buttons suchen und entfernen
                // Das ist hier kein Problem, weil sofort der Reload erfolgt
                const hiddens = document.querySelectorAll('input[type="hidden"].rnbase-modal-submit-btn')
                hiddens.forEach(function(hidden){
                    if (hidden.form) {
                        hidden.form.removeChild(hidden);
                    }
                })
            });
        });
        const editForm = document.forms['editform'];

        // const btns = document.querySelectorAll('button[type="submit"].rnbase-btn.t3js-modal-trigger, input[type="submit"].rnbase-btn.t3js-modal-trigger');
        const btns = document.querySelectorAll('button[type="submit"].rnbase-btn, input[type="submit"].rnbase-btn');
        btns.forEach(function(btn) {
            btn.addEventListener('click', function(evt) {
                const hiddenField = document.querySelector('input[type="hidden"].rnbase-modal-submit-btn');
                if (btn.classList.contains('t3js-modal-trigger')) {
                    // Hole den Namen des Submit-Buttons
                    hiddenField.name = btn.getAttribute('name');
                    hiddenField.value = '1';
                } else {
                    hiddenField.name = '_none';
                }
            })
        });
        if (editForm) {
            // Das hidden-Feld wird den Namen des Submit-Buttons bei Modal-Dialogen schicken
            var hiddenField = document.querySelector('input[type="hidden"].rnbase-modal-submit-btn');
            if (!hiddenField) {
                hiddenField = document.createElement('input');
                hiddenField.type = 'hidden';
                hiddenField.className = 'rnbase-modal-submit-btn';
                hiddenField.name = '_none';

                // Füge das hidden-Feld zum Formular hinzu
                editForm.appendChild(hiddenField);
            }
        }

        console.info('Toolbox loaded');
    }
}

FormTool.init();
});


