Dms.table.initializeCallbacks.push(function (element) {

    element.find('.dms-table-control').each(function () {
        var control = $(this);
        var tableContainer = control.find('.dms-table-container');
        var table = tableContainer.find('table.dms-table');
        var filterForm = control.find('.dms-table-quick-filter-form');
        var rowsPerPageSelect = control.find('.dms-table-rows-per-page-form select');
        var paginationPreviousButton = control.find('.dms-table-pagination .dms-pagination-previous');
        var paginationNextButton = control.find('.dms-table-pagination .dms-pagination-next');
        var loadRowsUrl = control.attr('data-load-rows-url');
        var reorderRowsUrl = control.attr('data-reorder-row-action-url');
        var stringFilterableComponentIds = JSON.parse(control.attr('data-string-filterable-component-ids')) || [];

        var currentPage = 0;

        var criteria = {
            orderings: [],
            condition_mode: 'or',
            conditions: [],
            offset: 0,
            max_rows: rowsPerPageSelect.val()
        };

        var currentAjaxRequest;

        var loadCurrentPage = function () {
            tableContainer.addClass('loading');

            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            criteria.offset = currentPage * criteria.max_rows;

            currentAjaxRequest = $.ajax({
                url: loadRowsUrl,
                type: 'post',
                dataType: 'html',
                data: criteria
            });

            currentAjaxRequest.done(function (tableData) {
                table.html(tableData);
                Dms.table.initialize(tableContainer);

                if (table.find('tbody tr').length < criteria.max_rows) {
                    paginationNextButton.attr('disabled', true);
                }
            });

            currentAjaxRequest.fail(function () {
                if (currentAjaxRequest.statusText === 'abort') {
                    return;
                }

                tableContainer.addClass('error');

                swal({
                    title: "Could not load table data",
                    text: "An unexpected error occurred",
                    type: "error"
                });
            });

            currentAjaxRequest.always(function () {
                tableContainer.removeClass('loading');
            });
        };

        filterForm.find('button').click(function () {
            criteria.orderings = [
                {
                    component: filterForm.find('[name=component]').val(),
                    direction: filterForm.find('[name=direction]').val()
                }
            ];

            criteria.conditions = [];

            var filterByString = filterForm.find('[name=filter]').val();

            if (filterByString) {
                $.each(stringFilterableComponentIds, function (index, componentId) {
                    criteria.conditions.push({
                        component: componentId,
                        operator: 'string-contains-case-insensitive',
                        value: filterByString
                    });
                });
            }

            loadCurrentPage();
        });

        filterForm.find('input[name=filter]').on('keyup', function (event) {
            var enterKey = 13;

            if (event.keyCode === enterKey) {
                filterForm.find('button').click();
            }
        });

        rowsPerPageSelect.on('change', function () {
            criteria.max_rows = $(this).val();

            loadCurrentPage();
        });

        paginationPreviousButton.click(function () {
            paginationNextButton.attr('disabled', false);
            currentPage--;
            loadCurrentPage();
        });

        paginationNextButton.click(function () {
            paginationPreviousButton.attr('disabled', false);
            currentPage++;
            loadCurrentPage();
        });

        paginationPreviousButton.click(function () {
            currentPage--;
            loadCurrentPage();
        });

        paginationPreviousButton.attr('disabled', true);

        loadCurrentPage();
    });
});