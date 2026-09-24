(function () {

    function getRoot(element) {

        return element.closest('[data-bulk-root]');

    }



    function getForm(root) {

        return root ? root.querySelector('form[data-bulk-form]') : null;

    }



    function getDateFilter(root) {

        return root ? root.querySelector('.console-date-filter') : null;

    }



    function rowSelectors(form) {

        if (!form) {

            return [];

        }



        return Array.from(form.querySelectorAll('tbody [data-row-selector]'));

    }



    function checkedRows(form) {

        return rowSelectors(form).filter((checkbox) => checkbox.checked);

    }



    function csrfToken(form) {

        const input = form.querySelector('input[name="_token"]');



        return input ? input.value : '';

    }



    function filterFormValues(root) {

        const filter = getDateFilter(root);

        if (!filter) {

            return {};

        }



        const values = {};



        filter.querySelectorAll('input, select, textarea').forEach((field) => {

            if (!field.name || field.disabled) {

                return;

            }



            if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) {

                return;

            }



            if (field.value !== '') {

                values[field.name] = field.value;

            }

        });



        return values;

    }



    function hasDateFilter(values) {

        return Boolean(values.from_date || values.to_date);

    }



    function appendField(postForm, name, value) {

        if (!value) {

            return;

        }



        const input = document.createElement('input');

        input.type = 'hidden';

        input.name = name;

        input.value = value;

        postForm.appendChild(input);

    }



    function submitBulkPost(action, form, rows, extraFields) {

        const postForm = document.createElement('form');

        postForm.method = 'POST';

        postForm.action = action;

        postForm.style.display = 'none';



        appendField(postForm, '_token', csrfToken(form));



        Object.entries(extraFields || {}).forEach(([name, value]) => {

            appendField(postForm, name, value);

        });



        rows.forEach((checkbox) => {

            appendField(postForm, 'selected_ids[]', checkbox.value);

        });



        document.body.appendChild(postForm);

        postForm.submit();

    }



    function updateState(root) {

        const form = getForm(root);

        if (!form) {

            return;

        }



        const selectAll = form.querySelector('[data-select-all]');

        const exportBtn = root.querySelector('.bulk-export-btn');

        const deleteBtn = root.querySelector('.bulk-delete-btn');

        const deleteRangeBtn = root.querySelector('.bulk-delete-range-btn');

        const rows = rowSelectors(form);

        const checkedCount = rows.filter((checkbox) => checkbox.checked).length;

        const filters = filterFormValues(root);

        const dateFilterActive = hasDateFilter(filters);



        if (exportBtn) {

            exportBtn.disabled = checkedCount === 0;

        }



        if (deleteBtn) {

            deleteBtn.disabled = checkedCount === 0;

        }



        if (deleteRangeBtn) {

            deleteRangeBtn.disabled = !dateFilterActive;

        }



        if (!selectAll) {

            return;

        }



        if (rows.length === 0) {

            selectAll.checked = false;

            selectAll.indeterminate = false;

            return;

        }



        if (checkedCount === rows.length) {

            selectAll.checked = true;

            selectAll.indeterminate = false;

        } else if (checkedCount === 0) {

            selectAll.checked = false;

            selectAll.indeterminate = false;

        } else {

            selectAll.checked = false;

            selectAll.indeterminate = true;

        }

    }



    function initAll() {

        document.querySelectorAll('[data-bulk-root]').forEach(updateState);

    }



    document.addEventListener('change', function (event) {

        const root = getRoot(event.target);

        if (!root) {

            return;

        }



        const form = getForm(root);



        if (event.target.matches('[data-select-all]')) {

            const checked = event.target.checked;

            rowSelectors(form).forEach((checkbox) => {

                checkbox.checked = checked;

            });

            updateState(root);

            return;

        }



        if (event.target.matches('[data-row-selector]')) {

            updateState(root);

            return;

        }



        if (event.target.closest('.console-date-filter')) {

            updateState(root);

        }

    });



    document.addEventListener('click', function (event) {

        const exportBtn = event.target.closest('.bulk-export-btn');

        const deleteBtn = event.target.closest('.bulk-delete-btn');

        const deleteRangeBtn = event.target.closest('.bulk-delete-range-btn');



        if (deleteRangeBtn) {

            if (deleteRangeBtn.disabled) {

                return;

            }



            const root = getRoot(deleteRangeBtn);

            const form = getForm(root);

            if (!form) {

                return;

            }



            event.preventDefault();



            const filters = filterFormValues(root);

            if (!hasDateFilter(filters)) {

                return;

            }



            const rangeLabel = [filters.from_date || 'start', filters.to_date || 'end'].join(' to ');

            if (!window.confirm('Delete ALL records in date range ' + rangeLabel + '? This cannot be undone.')) {

                return;

            }



            const deleteAction = deleteRangeBtn.getAttribute('data-delete-action');

            if (!deleteAction) {

                return;

            }



            submitBulkPost(deleteAction, form, [], filters);

            return;

        }



        const actionBtn = exportBtn || deleteBtn;

        if (!actionBtn || actionBtn.disabled) {

            return;

        }



        const root = getRoot(actionBtn);

        const form = getForm(root);

        if (!form) {

            return;

        }



        const rows = checkedRows(form);

        if (rows.length === 0) {

            event.preventDefault();

            return;

        }



        event.preventDefault();



        if (deleteBtn) {

            if (!window.confirm('Delete selected records permanently?')) {

                return;

            }



            const deleteAction = deleteBtn.getAttribute('data-delete-action');

            if (!deleteAction) {

                return;

            }



            submitBulkPost(deleteAction, form, rows, filterFormValues(root));

            return;

        }



        submitBulkPost(form.getAttribute('action'), form, rows, filterFormValues(root));

    });



    if (document.readyState === 'loading') {

        document.addEventListener('DOMContentLoaded', initAll);

    } else {

        initAll();

    }

})();


