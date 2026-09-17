define([
    'jquery',
    'Magento_Ui/js/grid/columns/actions',
    'Magento_Ui/js/modal/confirm',
    'mage/translate',
    'mage/backend/notification'
], function ($, Column, confirm, $t) {
    'use strict';

    return Column.extend({

        applyAction: function (actionIndex, rowIndex) {
            var rowData = this.rows[rowIndex] || this.getSource().data.items[rowIndex],
                action = rowData[this.index][actionIndex];

            var ajaxUrl = action?.params?.ajax_url;

            if (!ajaxUrl) {
                return this._super(actionIndex, rowIndex);
            }

            var label = action.label;
            var data = action.params;

            confirm({
                title: label,
                content: $t('Are you sure to ' + label + ' for this configuration now?'),
                actions: {
                    confirm: function () {
                        $.ajax({
                            url: ajaxUrl,
                            type: 'POST',
                            dataType: 'json',
                            showLoader: true,
                            data: data
                        }).done(function (response) {
                            if (response.success) {
                                $('body').notification('add', {
                                    error: false,
                                    message: response.message,
                                    insertMethod: function (elem) {
                                        $(elem).addClass('message-success messages-message-success');
                                        $('.page-main-actions').after(elem);
                                    }
                                });
                                var $targetRow = $('tr.data-row').eq(rowIndex);
                                $targetRow.find('.topology_applied_at .data-grid-cell-content').html(response.topology_applied_at);
                            } else {
                                $('body').notification('add', {
                                    error: true,
                                    message: response.message,
                                    insertMethod: function (elem) {
                                        $('.page-main-actions').after(elem);
                                    }
                                });
                            }
                        }).fail(function () {
                            $('body').notification('add', {
                                error: true,
                                message: 'Request failed. Please try again.',
                                insertMethod: function (elem) {
                                    $('.page-main-actions').after(elem);
                                }
                            });
                        });
                    }
                }
            });
        }
    });
});
