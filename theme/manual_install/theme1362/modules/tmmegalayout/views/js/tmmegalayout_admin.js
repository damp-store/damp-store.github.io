/**
 * 2002-2016 TemplateMonster
 *
 * TM Mega Layout
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 2.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-2.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the module to newer
 * versions in the future.
 *
 *  @author    TemplateMonster (Alexander Grosul & Alexander Pervakov)
 *  @copyright 2002-2016 TemplateMonster
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */
$(document).ready(function(e) {
    tmml.init();

    $(document).on('change', '.tmmegalayout-styles input:not([type="radio"])', function(e) {
        tmml.validate.styleInput($(this));
        return false;
    });

    $('#selectLayoutArchive').live('click', function(e) {
        $('#layoutArchive').trigger('click');
    });

    $('#layoutArchiveName').live('click', function(e) {
        $('#layoutArchive').trigger('click');
    });

    $('#layoutArchiveName').live('dragenter', function(e) {
        e.preventDefault();
    });

    $('#layoutArchiveName').live('dragover', function(e) {
        e.preventDefault();
    });

    $('#layoutArchiveName').live('drop', function(e) {
        e.preventDefault();
        var files = e.originalEvent.dataTransfer.files;
        $('#layoutArchive')[0].files[0] = files;
        $(this).val(files[0].name);
    });

    $('#layoutArchive').live('change', function(e) {
        if ($(this)[0].files !== undefined) {
            var files = $(this)[0].files;
            var name = '';

            $.each(files, function(index, value) {
                name += value.name + ', ';
            });

            $('#layoutArchiveName').val(name.slice(0, -2));
        } else {
            var name = $(this).val().split(/[\\/]/);
            $('#layoutArchiveName').val(name[name.length - 1]);
        }

        e.preventDefault();
        var file = $('#layoutArchive')[0].files[0];
        var form_data = new FormData();
        form_data.append('file', file);
        send_file(form_data, file.name, file.size, 'preview');
    });

    $('#importLayoutArchive').live('click', function(e) {
        e.preventDefault();
        var file = $('#layoutArchive')[0].files[0];
        var form_data = new FormData();
        form_data.append('file', file);
        send_file(form_data, file.name, file.size, 'import');
    });

    function send_file(file_to_send, fileName, fileSize, type) {
        var xhr = new XMLHttpRequest();

        if (type == 'preview') {
            xhr.open('POST', tmml_theme_url + '&ajax&action=getImportInfo', false);
        } else {
            xhr.open('POST', tmml_theme_url + '&ajax&action=importLayout', false);
        }

        xhr.setRequestHeader("Cache-Control", "no-cache");
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader('X-FILE-NAME', fileName);
        xhr.setRequestHeader('X-FILE-SIZE', fileSize);
        xhr.send(file_to_send);

        if (xhr.status == 200) {
            if (type == 'preview') {
                $('.layout-preview-wrapper').removeClass('hidden');
                $('.layout-preview-wrapper').html(JSON.parse(xhr.responseText)['preview']);
            } else {
                if (JSON.parse(xhr.responseText)['status']) {
                    showSuccessMessage(JSON.parse(xhr.responseText)['response_msg']);
                    $('.layout-preview-wrapper').html('');
                    $('#layoutArchiveName').attr('value', '');
                    tmml.ajax.afterImport();
                }
            }
        }
    }

    $('.iframe-btn').fancybox({
        'width': 900,
        'height': 600,
        'type': 'iframe',
        'autoScale': false,
        'autoDimensions': false,
        'fitToView': false,
        'autoSize': false,
        onUpdate: function() {
            $('.fancybox-iframe').contents().find('a.link').data('field_id', $(this.element).data("input-name"));
            $('.fancybox-iframe').contents().find('a.link').attr('data-field_id', $(this.element).data("input-name"));
        },
        afterShow: function() {
            $('.fancybox-iframe').contents().find('a.link').data('field_id', $(this.element).data("input-name"));
            $('.fancybox-iframe').contents().find('a.link').attr('data-field_id', $(this.element).data("input-name"));
        },
        afterClose: function() {
            setTimeout(function() {
                $('.edit-styles.active').trigger('click')
            }, 50);
        }
    });
});

function fancyBoxOpen(type, data, action, content) {
    if (type == 'wrapper') {
        tmmegalayout_content = getWrapperSettings(data);
    } else if (type == 'row') {
        tmmegalayout_content = getRowSettings(data);
    } else if (type == 'col') {
        tmmegalayout_content = getColSettings(data, action);
    } else if (type == 'module') {
        tmmegalayout_content = getModulesList(data);
    } else if (type == 'message') {
        tmmegalayout_content = content
    }

    $.fancybox.open({
        type: 'inline',
        autoScale: true,
        minHeight: 30,
        minWidth: 320,
        maxWidth: 815,
        padding: 0,
        content: '<div class="bootstrap tmml-popup">' + tmmegalayout_content + '</div>',
        helpers: {
            overlay: {
                locked: false
            },
        },
        afterClose: function() {
            $('.button-container a:not(.edit-styles)').removeClass('active');
        }
    });
}

function getWrapperSettings(data) {
    if (!data) {
        data = '';
    }

    tmmegalayout_wrapper_content = '';
    tmmegalayout_wrapper_content += '<h2 class="popup-heading">' + tmml_wrapper_heading + '</h2>';
    tmmegalayout_wrapper_content += '<div class="form-group popup-content">';
    tmmegalayout_wrapper_content += '<label for="wrapper-classes">' + tmml_row_classese_text + '</label>';
    tmmegalayout_wrapper_content += '<input name="wrapper-classes" value="' + data + '" class="form-control" />';
    tmmegalayout_wrapper_content += '</div>';
    tmmegalayout_wrapper_content += '<div class="popup-btns">';
    tmmegalayout_wrapper_content += '<a href="#" class="edit-wrapper-confirm btn btn-success">' + tmml_confirm_text + '</a>';
    tmmegalayout_wrapper_content += '</div>';

    return tmmegalayout_wrapper_content;
}

function getRowSettings(data) {
    if (!data) {
        data = '';
    }

    tmmegalayout_row_content = '';
    tmmegalayout_row_content += '<h2 class="popup-heading">' + tmml_row_heading + '</h2>';
    tmmegalayout_row_content += '<div class="form-group popup-content">';
    tmmegalayout_row_content += '<label for="row-classes">' + tmml_row_classese_text + '</label>';
    tmmegalayout_row_content += '<input name="row-classes" value="' + data + '" class="form-control" />';
    tmmegalayout_row_content += '</div>';
    tmmegalayout_row_content += '<div class="popup-btns">';
    tmmegalayout_row_content += '<a href="#" class="edit-row-confirm btn btn-success">' + tmml_confirm_text + '</a>';
    tmmegalayout_row_content += '</div>';

    return tmmegalayout_row_content;
}

function getModulesList(data) {
    tmml_modules_select = '';

    tmml_modules_select += '<h2 class="popup-heading">' + tmml_module_heading + '</h2>';
    tmml_modules_select += '<div class="form-group popup-content">';
    tmml_modules_select += '<label>' + tmml_sp_class_text + '</label>';
    tmml_modules_select += '<input class="form-control" name="module-classes" value="' + data + '" />';
    tmml_modules_select += '</div>';
    tmml_modules_select += '<div class="popup-btns">';
    tmml_modules_select += '<a href="#" class="edit-module-confirm btn btn-success">' + tmml_confirm_text + '</a>';
    tmml_modules_select += '</div>';

    return tmml_modules_select;
}

function getColSettings(data, action) {
    specific_class = data.attr('data-specific-class');

    if (typeof (specific_class) == 'undefined') {
        specific_class = '';
    }

    tmml_cols_dimensions = [data.attr('data-col-xs'), data.attr('data-col-sm'), data.attr('data-col-md'), data.attr('data-col-lg')];

    tmml_cols_select = '';
    tmml_cols_sizes = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    tmml_cols_types = ['col-xs', 'col-sm', 'col-md', 'col-lg'];
    tmml_cols_select += '<h2 class="popup-heading">' + tmml_col_heading+ '</h2>';
    tmml_cols_select += '<div class="form-group popup-content">';
    tmml_cols_select += '<div class="form-wrapper form-group">';
    tmml_cols_select += '<label>' + tmml_sp_class_text + '</label>';
    tmml_cols_select += '<input class="form-control" name="col-specific-class" value="' + specific_class + '" />';
    tmml_cols_select += '</div>';
    tmml_cols_select += '<div class="form-wrapper row">';
    selected_item = '';
    for (i = 0; i < tmml_cols_types.length; i++) {
        tmml_cols_select += '<div class="col-md-3">';
        tmml_cols_select += '<label>' + tmml_cols_types[i] + '*</label>';
        tmml_cols_select += '<select class="form-group" name="tmml-cols-' + tmml_cols_types[i] + '">';
        tmml_cols_select += '<option value=""></option>';
        for (k = 0; k < tmml_cols_sizes.length; k++) {
            if ($.inArray(tmml_cols_types[i] + '-' + tmml_cols_sizes[k], tmml_cols_dimensions) != -1) {
                selected_item = 'selected=selected';
            }
            tmml_cols_select += '<option ' + selected_item + ' value="' + tmml_cols_types[i] + '-' + tmml_cols_sizes[k] + '">' + tmml_cols_types[i] + '-' + tmml_cols_sizes[k] + '</option>';
            selected_item = '';
        }
        tmml_cols_select += '</select>';
        tmml_cols_select += '</div>';
    }
    tmml_cols_select += '</div>';
    tmml_cols_select += '</div>';
    tmml_cols_select += '<div class="popup-btns">';

    if (action == 'edit') {
        tmml_cols_select += '<a href="#" class="edit-column-confirm btn btn-success">' + tmml_confirm_text + '</a>';
    } else {
        tmml_cols_select += '<a href="#" class="add-column-confirm btn btn-success">' + tmml_confirm_text + '</a>';
    }

    tmml_cols_select += '</div>';

    return tmml_cols_select;
}

//tmml obj
tmml = {
    ajax: {
        request: function(sendData, successFunction, elem, errorFunction) {
            elem = elem || null;
            errorFunction = errorFunction || function (response) {
                };
            successFunction = successFunction || function(response) {
                };
            $.ajax({
                type: 'POST',
                url: tmml_theme_url + '&ajax',
                headers: {"cache-control": "no-cache"},
                dataType: 'json',
                async: false,
                data: sendData,
                error: function(response) {
                    errorFunction(response, sendData, elem);
                },
                success: function(response) {
                    successFunction(response, sendData, elem);
                }
            });
        },
        reloadTab: function(elem) {
            id_tab = elem.attr('data-tab-id');
            data = {
                action: 'reloadTab',
                id_tab: id_tab
            };
            this.request(data, this.reloadTabSuccess);
        },
        reloadTabSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                $('.nav.nav-tabs li.active .layout-list-info').attr('value', response.layouts_list);
                $('.layout-tab-content.active .tmpanel-content').html(response.tab_content);
                $('ul.nav li.active .layouts-tab').removeClass('afterReset');
                $('.ajax_running').remove();
                tmml.sortInit();
                tmml.tooltipInit();
            }
        },
        resetToDefault: function() {
            data = {
                action: 'resetToDefault'
            }
            this.request(data, this.resetToDefaultSuccess);
        },
        resetToDefaultSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                $('ul.nav .layouts-tab').addClass('afterReset');
                showSuccessMessage(response.message);
            } else {
                showErrorMessage(response.message);
            }
        },
        afterExport: function() {
            data = {
                action: 'afterExport'
            };
            this.request(data);
        },
        afterImport: function() {
            data = {
                action: 'afterImport'
            };
            this.request(data);
        },
        addModuleConfirmation: function(elem) {
            id_hook = elem.find('input[name="tmml_id_hook"]').val();
            id_layout = elem.find('input[name="tmml_id_layout"]').val();
            data = {
                action: 'addModuleConfirmation',
                id_hook: id_hook,
                id_layout: id_layout
            };
            this.request(data, this.addModuleConfirmationSuccess);
        },
        addModuleConfirmationSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                fancyBoxOpen('message', '', '', response.message);
            } else {
                showErrorMessage(response.message);
            }
        },
        enableLayout: function(id_hook, id_layout) {
            data = {
                action: 'enableLayout',
                id_layout: id_layout,
                id_hook: id_hook
            };
            this.request(data, this.enableLayoutSuccess);
        },
        enableLayoutSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                $('.tmlist-layout-btns[data-layout-id="' + data.id_layout + '"]').find('.disable-layout').removeClass('hidden');
                $('.tmlist-layout-btns[data-layout-id="' + data.id_layout + '"]').find('.use-layout').addClass('hidden');
                $('.tmml-layouts-list[data-list-id="' + data.id_hook + '"]').find('i.visible').addClass('hidden');
                $('.tmlist-group-item[data-layout-id="' + data.id_layout + '"]').find('i').removeClass('hidden').addClass('visible');
                showSuccessMessage(response.message);
            } else {
                showErrorMessage(response.message);
            }
        },
        disableLayout: function(id_layout) {
            data = {
                action: 'disableLayout',
                id_layout: id_layout
            };
            this.request(data, this.disableLayoutSuccess);
        },
        disableLayoutSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                $('.tmlist-layout-btns[data-layout-id="' + data.id_layout + '"]').find('.disable-layout').addClass('hidden');
                $('.tmlist-layout-btns[data-layout-id="' + data.id_layout + '"]').find('.use-layout').removeClass('hidden');
                $('.tmlist-group-item[data-layout-id="' + data.id_layout + '"]').find('i').addClass('hidden');
                showSuccessMessage(response.message);
            } else {
                showErrorMessage(response.message);
            }
        },
        renameLayout: function(id_layout, layout_name) {
            data = {
                action: 'renameLayout',
                id_layout: id_layout,
                layout_name: layout_name
            };
            this.request(data, this.renameLayoutSuccess);
        },
        renameLayoutSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                $('.tmlist-group li[data-layout-id="' + data.id_layout + '"]').parent().prev('button').text(data.layout_name);
                $('.tmmegalayout-admin[data-layout-id="' + data.id_layout + '"]').find('.tmmlmegalayout-layout-name').text(data.layout_name);
                tmml_active_icon_class = $('.tmlist-group li[data-layout-id="' + data.id_layout + '"]').find('i').attr('class');
                $('.tmlist-group li[data-layout-id="' + data.id_layout + '"]').children('a').html(data.layout_name + '<i class="' + tmml_active_icon_class + '"></i>');
                showSuccessMessage(response.message);
            } else {
                showErrorMessage(response.message);
            }
        },
        getLayoutRenameConfirmation: function(id_layout) {
            data = {
                action: 'getLayoutRenameConfirmation',
                id_layout: id_layout
            };
            this.request(data, this.getLayoutRenameConfirmationSuccess);
        },
        getLayoutRenameConfirmationSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                fancyBoxOpen('message', '', '', response.message);
            } else {
                showErrorMessage(response.message);
            }
        },
        removeLayout: function(id_layout, id_hook) {
            data = {
                action: 'removeLayout',
                id_layout: id_layout,
                id_hook: id_hook
            };
            this.request(data, this.removeLayoutSuccess);
        },
        removeLayoutSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                tmml_hook_layout_list = $('.tmlist-group[data-list-id="' + data.id_hook + '"]');
                $('.nav.nav-tabs li.active .layout-list-info').attr('value', response.new_layouts);
                $('.tmlist-group li[data-layout-id="' + data.id_layout + '"]').remove();
                $('.tmmegalayout-admin[data-layout-id="' + data.id_layout + '"]').remove();
                $('.tmlist-group[data-list-id="' + data.id_hook + '"] li').eq(0).trigger('click');
                if (tmml_hook_layout_list.find('li').length < 1) {
                    tmml_hook_layout_list.prev('button').remove();
                    tmml_hook_layout_list.closest('.tmmegalayout-lsettins').find('.tmlist-layout-btns').remove();
                }
                showSuccessMessage(response.message);
            } else {
                showErrorMessage(response.message);
            }
        },
        getLayoutRemoveConfirmation: function(id_layout) {
            data = {
                action: 'getLayoutRemoveConfirmation',
                id_layout: id_layout
            };
            this.request(data, this.getLayoutRemoveConfirmationSuccess);
        },
        getLayoutRemoveConfirmationSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                fancyBoxOpen('message', '', '', response.message)
            } else {
                showErrorMessage(response.message);
            }
        },
        loadLayoutContent: function(elem) {
            tmml_layout_container = elem.closest('.tab-pane').find('.layout-container');
            tmml_layout_container.html('');
            tmml_layout_container.append('<p class="loading col-xs-12">' + tmml_loading_text + '</p>');
            data = {
                action: 'loadLayoutContent',
                id_layout: elem.attr('data-layout-id')
            };
            this.request(data, this.loadLayoutContentSuccess);
        },
        loadLayoutContentSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                tmml_layout_container.append(response.layout);
                tmml_layout_container.prev('.tmmegalayout-lsettins').find('.tmlist-layout-buttons').html(response.layout_buttons);
                tmml_layout_container.find('p.loading').remove();
                $('.tmml-layouts-list').removeClass('loading');
                tmml.sortInit();
                tmml.tooltipInit();
            } else {
                showErrorMessage(response.message);
            }
        },
        addLayout: function(elem, layout_name) {
            id_hook = elem.attr('data-hook-id');
            data = {
                action: 'addLayout',
                id_hook: id_hook,
                layout_name: layout_name
            };
            this.request(data, this.addLayoutSuccess);
        },
        addLayoutSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                $('.nav.nav-tabs li.active .layout-list-info').attr('value', response.new_layouts);
                if ($('.tmlist-group[data-list-id="' + data.id_hook + '"] li').length < 1) {
                    $('.tmlist-group[data-list-id="' + data.id_hook + '"]').before('<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">' + data.layout_name + '</button>');
                }
                $('.tmlist-group[data-list-id="' + data.id_hook + '"]').append('<li data-layout-id="' + response.id_layout + '" class="tmlist-group-item"><a href="#">' + data.layout_name + '<i class="icon-check hidden pull-right"></i></a></li>');
                $('.tmlist-group li[data-layout-id="' + response.id_layout + '"]').trigger('click');
                showSuccessMessage(response.message);
            } else {
                showErrorMessage(response.message);
            }
        },
        addLayoutForm: function(elem) {
            id_hook = elem.attr('data-hook-id');
            data = {
                action: 'addLayoutForm',
                id_hook: id_hook
            };
            this.request(data, this.addLayoutFormSuccess);
        },
        addLayoutFormSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                fancyBoxOpen('message', '', '', response.response_msg);
            }
        },
        loadToolTab: function(elem) {
            tool_name = elem.attr('data-tool-name');
            data = {
                action: 'loadTool',
                tool_name: tool_name
            };
            this.request(data, this.loadToolTabSuccess);
        },
        loadToolTabSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                $('#tmml-tools .active .tmpanel').html(response.content);
                $('.ajax_running').remove();
            }
        },
        loadLayoutTab: function(elem) {
            tab_id = elem.attr('data-tab-id');
            old_layouts = elem.parent('li').find('.layout-list-info').attr('value');
            data = {
                action: 'loadLayoutTab',
                tab_id: tab_id,
                old_layouts: old_layouts
            };
            this.request(data, this.loadLayoutTabSuccess);
        },
        loadLayoutTabSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                $('.nav.nav-tabs li.active .layout-list-info').attr('value', response.old_layouts);
                new_layouts = JSON.parse(response.new_layouts);
                new_layouts.forEach(function(item, i, arr) {
                    if ($('.tab-content .tab-pane.active .tmml-layouts-list li').length < 1) {
                        $('.tab-content .tab-pane.active .tmml-layouts-list').before('<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">--</button>')
                    }
                    list_item_html = '<li data-layout-id="' + item['id_layout'] + '" class="tmlist-group-item">'
                    list_item_html += '<a href="#">' + item['layout_name'] + '<i class="icon-check pull-right hidden"></i></a></li>'
                    html = $('.tab-content .tab-pane.active .tmml-layouts-list').html();
                    $('.tab-content .tab-pane.active .tmml-layouts-list').html(html + list_item_html);
                });
                $('.ajax_running').remove();
            }
        },
        layoutExport: function(elem) {
            id_layout = elem.attr('data-id-layout');
            data = {
                id_layout: id_layout,
                action: 'layoutExport'
            };
            this.request(data, this.layoutExportSuccess);
        },
        layoutExportSuccess: function(response, data, elem) {
            location.href = response.href;
            tmml.ajax.afterExport();
        },
        layoutPreview: function(elem) {
            id_layout = elem.attr('data-id-layout');
            data = {
                action: 'layoutPreview',
                id_layout: id_layout
            };
            this.request(data, this.layoutPreviewSuccess);
        },
        layoutPreviewSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                fancyBoxOpen('message', '', '', '<div class="tmmegalayout-admin container"><div class="preview-popup-content">' + response.msg + '</div></div>')
            }
        },
        deleteLayoutItem: function(elem) {
            id_item = elem.attr('data-id');
            tmml_itemsdel_ids = [id_item];
            $(elem).find('div').each(function() {
                tmml_itemsdel_ids.push($(this).attr('data-id'));
            });
            data = {
                action: 'deleteLayoutItem',
                id_item: tmml_itemsdel_ids
            };
            this.request(data, this.deleteLayoutItemSuccess);
        },
        deleteLayoutItemSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                $('div[data-id="' + data.id_item[0] + '"]').remove();
                showSuccessMessage(response.response_msg);
                return;
            }

            showErrorMessage(response.response_msg);
        },
        clearItemStyles: function(elem, id_unique) {
            elem.find('select, input:not([type="hidden"])').val('').attr('style', '');
            data = {
                action: 'clearItemStyles',
                id_unique: id_unique
            };
            this.request(data, this.clearItemStylesSuccess, elem, this.clearItemStylesError);
        },
        clearItemStylesSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                elem.find('.alert').remove();
                elem.prepend('<p class="alert alert-success">' + response.message + '<button class="close" aria-label="close" data-dismiss="alert" type="button">×</button></p>');
                return;
            }
            showErrorMessage(response.response_msg);
        },
        clearItemStylesError: function(response, data, elem) {
            elem.find('.alert').remove();
        },
        saveItemStyles: function(elem, id_unique, data) {
            data = {
                action: 'saveItemStyles',
                id_unique: id_unique,
                data: data
            };
            this.request(data, this.saveItemStylesSuccess, elem, this.saveItemStylesError);
        },
        saveItemStylesSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                elem.find('.alert').remove();
                elem.prepend('<p class="alert alert-success">' + response.message + '<button class="close" aria-label="close" data-dismiss="alert" type="button">×</button></p>');
                return;
            }

            showErrorMessage(response.response_msg);
        },
        saveItemStylesError: function(response, data, elem) {
            elem.find('.alert').remove();
        },
        getItemStyles: function(id_unique) {
            data = {
                action: 'getItemStyles',
                id_unique: id_unique
            };
            this.request(data, this.getItemStylesSuccess);
        },
        getItemStylesSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                fancyBoxOpen('message', '', '', response.content);
                $('.tmml_color_input').mColorPicker();
                tmml_tmp_img = $('.tmpanel-content').find('input[id="flmbgimg"]').val();

                if (tmml_tmp_img.length) {
                    tmml_tmp_img = tmml_tmp_img.substring(tmml_tmp_img.indexOf('/img/cms') + 1);
                    $('input[name="background-image"]').val('url(../../../../../' + tmml_tmp_img + ')');
                    $('.tmpanel-content').find('input[id="flmbgimg"]').val('');
                }

                return;
            }
        },
        updateSortOrders: function(elem) {
            tmml_itemsorder_ids = {};
            elem.find('> div.sortable').each(function() {
                tmml_itemsorder_ids[$(this).attr('data-id')] = $(this).attr('data-sort-order');
            });
            data = {
                action: 'updateLayoutItemsOrder',
                data: tmml_itemsorder_ids
            };
            this.request(data, this.updateSortOrdersSuccess);
        },
        updateSortOrdersSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                showSuccessMessage(response.response_msg);
                return;
            }
            showErrorMessage(response.response_msg);
        },
        saveLayoutItem: function(id_item, elem, type, type_class, specific_class, col_xs, col_sm, col_md, col_lg, module_name, public_module_name) {
            type_class = type_class || '';
            public_module_name = public_module_name || null;
            tmml_edit_item = id_item;
            id_parent = tmml.get.parentId(elem);
            id_layout = elem.closest('.layout-container').find('input[name="tmml_id_layout"]').val();
            sort_order = tmml.get.sortOrder(elem);
            if (tmml_edit_item) {
                sort_order = elem.attr('data-sort-order');
                id_parent = elem.attr('data-parent-id');
            }
            if (typeof (specific_class) == 'undefined') {
                specific_class = '';
            }
            itemData = {
                'id_layout': id_layout,
                'id_parent': id_parent,
                'type': type,
                'sort_order': sort_order,
                'specific_class': specific_class,
                'col_xs': col_xs,
                'col_sm': col_sm,
                'col_md': col_md,
                'col_lg': col_lg,
                'module_name': module_name
            };
            data = {
                action: 'updateLayoutItem',
                id_item: id_item,
                data: itemData
            };
            this.request(data, this.saveLayoutItemSuccess, elem);
        },
        saveLayoutItemSuccess: function(response, data, elem) {
            if (response.status == 'true') {
                if (!tmml_edit_item) {
                    if (!elem.hasClass('min-level')) {
                        elem.closest('article').append(response.content);
                    } else {
                        elem.closest('article').find('.add-buttons').before(response.content);
                    }
                    tmml.sortInit();
                    tmml.tooltipInit();
                }
                showSuccessMessage(response.response_msg);
                return;
            }
            showErrorMessage(response.response_msg);
        }
    },
    events: {
        docClick: function(elemSelector, innerFunc) {
            $(document).on('click', elemSelector, function(e) {
                innerFunc($(this));
                e.preventDefault();
            });
        },
        layoutExport: function() {
            this.docClick('.layout-export', function(elem) {
                tmml.ajax.layoutExport(elem);
            });
        },
        loadToolTab: function() {
            this.docClick('#tmml-tools ul.nav.tmmegalayout-nav a, #tmml-tools ul.nav.nav-pills a, #tmml-tools_tab', function(elem) {
                elem.append('<span class="ajax_running"><i class="icon-refresh icon-spin icon-fw"></i></span>');
                setTimeout(function() {
                    tmml.ajax.loadToolTab($('#tmml-tools ul.nav li.active a'))
                }, 100);
            });
        },
        addLayoutForm: function() {
            this.docClick('.add_layout', function(elem) {
                tmml.ajax.addLayoutForm(elem);
            });
        },
        addLayout: function() {
            this.docClick('.save-layout', function(elem) {
                layout_name = $('input[name="layout_name"]').attr('value');
                if (tmml.validate.layoutName(layout_name)) {
                    tmml.ajax.addLayout(elem, layout_name);
                    $.fancybox.close();
                } else {
                    error_container = elem.parent('div');
                    error_container.find('p.alert').remove();
                    error_container.prepend('<p class="alert alert-warning">' + tmml_layout_validate_error_text + '</p>');
                }
            });
        },
        loadLayoutContent: function() {
            this.docClick('.tmml-layouts-list .tmlist-group-item:not(.active)', function(elem) {
                parent_element = elem.parent();
                parent_element.find('li').removeClass('active');
                parent_element.addClass('loading');
                elem.addClass('active');
                parent_element.prev('button').text(elem.find('a').text());
                tmml.ajax.loadLayoutContent(elem);
            });
        },
        resetToDefault: function() {
            this.docClick('.reset-layouts', function(elem) {
                tmml.ajax.resetToDefault();
            });
        },
        addModuleConfirmation: function() {
            this.docClick('.add-module', function(elem) {
                parentElem = elem.parents('.tmpanel-content');
                $('.add-module').removeClass('active');
                elem.addClass('active');
                tmml.ajax.addModuleConfirmation(parentElem);
            });
        },
        loadLayoutTab: function() {
            this.docClick('ul.nav.tmmegalayout-nav .layouts-tab', function(elem) {
                elem.append('<span class="ajax_running"><i class="icon-refresh icon-spin icon-fw"></i></span>');
                if (elem.hasClass('afterReset')) {
                    setTimeout(function() {
                        tmml.ajax.reloadTab($('ul.nav li.active .layouts-tab'));
                    }, 100);
                } else {
                    setTimeout(function() {
                        tmml.ajax.loadLayoutTab($('ul.nav li.active .layouts-tab'));
                    }, 100);
                }
            });
        },
        layoutPreview: function() {
            this.docClick('#export_layout .layout-preview', function(elem) {
                tmml.ajax.layoutPreview(elem);
            });
        },
        deleteLayoutItem: function() {
            this.docClick('.remove-item', function(elem) {
                tmml.ajax.deleteLayoutItem(elem.closest('div:not(.button-container)'));
            });
        },
        disableLayout: function() {
            this.docClick('.disable-layout', function(elem) {
                var id_layout = elem.attr('data-layout-id');
                tmml.ajax.disableLayout(id_layout);
            });
        },
        enableLayout: function() {
            this.docClick('.use-layout', function(elem) {
                var id_hook = elem.parents('.tmpanel-content').find('input[name="tmml_id_hook"]').val();
                var id_layout = elem.attr('data-layout-id');
                tmml.ajax.enableLayout(id_hook, id_layout);
            });
        },
        cleanImage: function() {
            this.docClick('a.clear-image', function(elem) {
                elem.parents('.input-group').find('input').val('');
            });
        },
        cleanImageNone: function() {
            this.docClick('a.clear-image-none', function(elem) {
                elem.parents('.input-group').find('input').val('none');
            });
        },
        cleanItemStyles: function() {
            this.docClick('.clear-styles', function(elem) {
                elem = elem.parents('.form-wrapper');
                element_id_unique = elem.find('input[name="id_unique"]').val();
                tmml.ajax.clearItemStyles(elem, element_id_unique);
            });
        },
        saveItemStyles: function() {
            this.docClick('.save-styles', function(elem) {
                tmml_item_styles = {};
                elem = elem.parents('.form-wrapper');
                element_id_unique = elem.find('input[name="id_unique"]').val();

                elem.find('select, input:not([name="id_unique"])').each(function(e) {
                    if (tmml_style_value = $(this).val()) {
                        if ($(this).attr('type') == 'radio') {
                            if (typeof($(this).attr('checked')) != 'undefined') {
                                tmml_item_styles[$(this).attr('name')] = tmml_style_value;
                            }
                        } else {
                            tmml_item_styles[$(this).attr('name')] = tmml_style_value;
                        }
                    }
                });
                tmml.ajax.saveItemStyles(elem, element_id_unique, tmml_item_styles);
            });
        },
        editItemStyles: function() {
            this.docClick('.edit-styles', function(elem) {
                $('.edit-styles').removeClass('active');
                elem.addClass('active');
                element_id_unique = elem.closest('div:not(.button-container)').attr('data-id-unique');
                tmml.ajax.getItemStyles(element_id_unique);
            });
        },
        getLayoutRemoveConfirmation: function() {
            this.docClick('.remove-layout', function(elem) {
                tmml.ajax.getLayoutRemoveConfirmation(elem.attr('data-layout-id'));
            });
        },
        removeLayout: function() {
            this.docClick('.remove-layout-confirm', function(elem) {
                id_hook = $('.nav.nav-tabs li.active a.layouts-tab').attr('data-tab-id');
                tmml.ajax.removeLayout(elem.attr('data-layout-id'), id_hook);
                $.fancybox.close();
            });
        },
        getLayoutRenameConfirmation: function() {
            this.docClick('.edit-layout', function(elem) {
                tmml.ajax.getLayoutRenameConfirmation(elem.attr('data-layout-id'));
            });
        },
        renameLayout: function() {
            this.docClick('.edit-layout-confirm', function(elem) {
                layout_name = elem.closest('.tmml-popup').find('input[name="layout_name"]').attr('value');
                if (tmml.validate.layoutName(layout_name)) {
                    tmml.ajax.renameLayout(elem.attr('data-layout-id'), layout_name);
                    $.fancybox.close();
                } else {
                    error_container = elem.parent('div');
                    error_container.find('p.alert').remove();
                    error_container.prepend('<p class="alert alert-warning">' + tmml_layout_validate_error_text + '</p>');
                }
            });
        },
        addWrapper: function() {
            this.docClick('.add-wrapper', function(elem) {
                tmml.layout.add.wrapper(elem);
            });
        },
        editWrapper: function() {
            this.docClick('.edit-wrapper-confirm', function(elem) {
                parentElem = elem.closest('.tmml-popup');
                parentElem.find('.alert').remove();
                specific_class = parentElem.find('input[name="wrapper-classes"]').val().trim();

                if (error = tmml.validate.spClasses(specific_class)) {
                    parentElem.prepend(error);
                    return;
                }

                tmml.layout.edit.wrapper($('.edit-wrapper.active').closest('div:not(.button-container)'), specific_class);
                $.fancybox.close($('.edit-wrapper').removeClass('active'));
            });
        },
        editWrapperConfirmation: function() {
            this.docClick('.edit-wrapper', function(elem) {
                $('.edit-wrapper').removeClass('active');
                elem.addClass('active');
                fancyBoxOpen('wrapper', elem.closest('div:not(.button-container)').attr('data-specific-class'));
            });
        },
        addRow: function() {
            this.docClick('.add-row', function(elem) {
                tmml.layout.add.row(elem);
            });
        },
        editRowConfirmation: function() {
            this.docClick('.edit-row', function(elem) {
                $('.edit-row').removeClass('active');
                elem.addClass('active');
                fancyBoxOpen('row', elem.closest('div:not(.button-container)').attr('data-specific-class'));
            });
        },
        editRow: function() {
            this.docClick('.edit-row-confirm', function(elem) {
                parentElem = elem.closest('.tmml-popup');
                parentElem.find('.alert').remove();
                specific_class = parentElem.find('input[name="row-classes"]').val().trim();

                if (error = tmml.validate.spClasses(specific_class)) {
                    parentElem.prepend(error);
                    return;
                }

                tmml.layout.edit.row($('.edit-row.active').closest('div:not(.button-container)'), specific_class);
                $.fancybox.close($('.edit-row').removeClass('active'));
            });
        },
        addColumnConfirmation: function() {
            this.docClick('.add-column', function(elem) {
                $('.add-column').removeClass('active');
                elem.addClass('active');
                fancyBoxOpen('col', elem);
            });
        },
        editColumnConfirmation: function() {
            this.docClick('.edit-column', function(elem) {
                $('.edit-column').removeClass('active');
                elem.addClass('active');
                fancyBoxOpen('col', elem.closest('div:not(.button-container)'), 'edit');
            });
        },
        addColumn: function() {
            this.docClick('.add-column-confirm', function(elem) {
                parentElem = elem.closest('.tmml-popup');
                specific_class = parentElem.find('input[name="col-specific-class"]').val().trim();
                xs_ = parentElem.find('select[name="tmml-cols-col-xs"]').val();
                sm_ = parentElem.find('select[name="tmml-cols-col-sm"]').val();
                md_ = parentElem.find('select[name="tmml-cols-col-md"]').val();
                lg_ = parentElem.find('select[name="tmml-cols-col-lg"]').val();

                if (error = tmml.validate.spClasses(specific_class)) {
                    parentElem.prepend(error);
                    return;
                } else if (xs_ == '' && sm_ == '' && md_ == '' && lg_ == '') {
                    parentElem.prepend('<p class="alert alert-danger">' + tmml_cols_validate_error + '</p>');
                    return;
                }

                tmml.layout.add.col($('.add-column.active'), specific_class, xs_, sm_, md_, lg_);
                $.fancybox.close($('.add-column').removeClass('active'));
            });
        },
        editColumn: function() {
            this.docClick('.edit-column-confirm', function(elem) {
                parentElem = elem.closest('.tmml-popup');
                specific_class = parentElem.find('input[name="col-specific-class"]').val().trim();
                xs_ = parentElem.find('select[name="tmml-cols-col-xs"]').val();
                sm_ = parentElem.find('select[name="tmml-cols-col-sm"]').val();
                md_ = parentElem.find('select[name="tmml-cols-col-md"]').val();
                lg_ = parentElem.find('select[name="tmml-cols-col-lg"]').val();

                if (error = tmml.validate.spClasses(specific_class)) {
                    parentElem.prepend(error);
                    return;
                } else if (xs_ == '' && sm_ == '' && md_ == '' && lg_ == '') {
                    parentElem.prepend('<p class="alert alert-danger">' + tmml_cols_validate_error + '</p>');
                    return;
                }

                tmml.layout.edit.col($('.edit-column.active').closest('div:not(.button-container)'), specific_class, xs_, sm_, md_, lg_);
                $.fancybox.close($('.edit-column').removeClass('active'));
            });
        },
        addModule: function() {
            this.docClick('.add-module-confirm', function(elem) {
                parentElem = elem.closest('.tmml-popup');
                specific_class = parentElem.find('input[name="module-classes"]').val().trim();
                data_select_id = parentElem.find('select').attr('data-select-id');
                module_name = parentElem.find('select[name="tmml_module_' + data_select_id + '"]').val();
                public_module_name = parentElem.find('select[name="tmml_module_' + data_select_id + '"] option[value="' + module_name + '"]').text();

                if (error = tmml.validate.spClasses(specific_class)) {
                    parentElem.prepend(error);
                    return;
                }

                tmml.layout.add.module($('.add-module.active'), specific_class, module_name, public_module_name);
                $.fancybox.close($('.add-module').removeClass('active'));
            });
        },
        editModuleConfirmation: function() {
            this.docClick('.edit-module', function(elem) {
                $('.edit-module').removeClass('active');
                elem.addClass('active');
                fancyBoxOpen('module', elem.closest('div:not(.button-container)').attr('data-specific-class'), 'edit');
            });
        },
        editModule: function() {
            this.docClick('.edit-module-confirm', function(elem) {
                parentElem = elem.closest('.tmml-popup');
                specific_class = parentElem.find('input[name="module-classes"]').val().trim();

                if (error = tmml.validate.spClasses(specific_class)) {
                    parentElem.prepend(error);
                    return;
                }

                tmml.layout.edit.module($('.edit-module.active').closest('div:not(.button-container)'), specific_class);
                $.fancybox.close($('.edit-module').removeClass('active'));
            });
        },
        init: function() {
            this.layoutExport();
            this.loadToolTab();
            this.addLayoutForm();
            this.addLayout();
            this.loadLayoutContent();
            this.resetToDefault();
            this.loadLayoutTab();
            this.layoutPreview();
            this.deleteLayoutItem();
            this.disableLayout();
            this.enableLayout();
            this.cleanImage();
            this.cleanImageNone();
            this.cleanItemStyles();
            this.saveItemStyles();
            this.editItemStyles();
            this.getLayoutRemoveConfirmation();
            this.removeLayout();
            this.getLayoutRenameConfirmation();
            this.renameLayout();
            this.addWrapper();
            this.editWrapper();
            this.editWrapperConfirmation();
            this.addRow();
            this.editRowConfirmation();
            this.editRow();
            this.addColumn();
            this.editColumn();
            this.addColumnConfirmation();
            this.editColumnConfirmation();
            this.addModuleConfirmation();
            this.editModuleConfirmation();
            this.addModule();
            this.editModule();
        }
    },
    validate: {
        layoutName: function(name) {
            if ($.trim(name) == '') {
                return false;
            }

            for (i = 0; i < name.length; i++) {
                if (i == 0 && name[i] == '-') {
                    return false;
                }

                if (/^[a-zA-Z0-9-]*$/.test(name[i]) == false) {
                    return false;
                }
            }

            return true;
        },
        styleClr: function(content) {
            colorProhibitedString = "~!@$%^&*_+=`{}[]|\:;'<>/?-";

            for (i = 0; i < colorProhibitedString.length; i++) {
                if (content.indexOf(colorProhibitedString[i]) != -1) {
                    return false;
                }
            }

            return true;
        },
        styleShdw: function(content) {
            prohibitedString = "~!@$%^&*_+=`{}[]|\:;'<>/?";

            for (i = 0; i < prohibitedString.length; i++) {
                if (content.indexOf(prohibitedString[i]) != -1) {
                    return false;
                }
            }

            return true;
        },
        styleDmns: function(content) {
            if (content == 0) {
                return true;
            }

            dimension = content.substr(content.length - 2);
            value = content.replace(dimension, '');

            if (!dimension || (dimension != 'px' && dimension != 'em') || !$.isNumeric(value) || !value) {
                return false;
            }

            return true;
        },
        styleCheckErrors: function() {
            stylesBtn = $('.save-styles');
            tmmlStyleErrors = false;

            $('.tmmegalayout-styles').find('input').each(function() {
                if ($(this).hasClass('error')) {
                    tmmlStyleErrors = true;
                }
            });

            if (tmmlStyleErrors) {
                stylesBtn.addClass('disabled');
            } else {
                stylesBtn.removeClass('disabled');
            }
        },
        styleInput: function(elem) {
            tmml_input_content = elem.val();
            tmml_input_type = elem.attr('data-type');

            if ($.trim(tmml_input_content) == '') {
                elem.val('');
                result = true;
            } else if (tmml_input_type == 'dmns') {
                result = this.styleDmns(tmml_input_content);
            } else if (tmml_input_type == 'shdw') {
                result = this.styleShdw(tmml_input_content);
            } else if (tmml_input_type == 'clr') {
                result = this.styleClr(tmml_input_content);
            }

            if (!result) {
                elem.addClass('error');
            } else {
                elem.removeClass('error');
            }

            this.styleCheckErrors();
        },
        spClasses: function(clasess) {
            tmml_prohibited_chars = "<>@!#$%^&*()+[]{}?:;|'\"\\,./~`=";
            tmml_classes_to_validate = clasess.trim().split(' ');

            if (tmml_classes_to_validate.length && tmml_classes_to_validate != '') {
                for (i = 0; i < tmml_classes_to_validate.length; i++) {
                    if (!tmml_classes_to_validate[i][0].match(/^([a-z\(\)]+)$/i)) {
                        return '<p class="alert alert-danger">' + tmml_class_validate_error + '</p>';
                    }
                    for (k = 0; k < tmml_prohibited_chars.length; k++) {
                        if (tmml_classes_to_validate[i].indexOf(tmml_prohibited_chars[k]) > -1) {
                            return '<p class="alert alert-danger">' + tmml_class_validate_error + '<button class="close" aria-label="close" data-dismiss="alert" type="button">×</button></p>';
                        }
                    }
                }
            }
            return false;
        }
    },
    layout: {
        add: {
            wrapper: function(elem) {
                tmml.ajax.saveLayoutItem(false, elem, 'wrapper', '', '', '', '', '', '', '', '');
            },
            row: function(elem) {
                tmml.ajax.saveLayoutItem(false, elem, 'row', '', '', '', '', '', '', '', '');
            },
            col: function(elem, specific_class, col_xs, col_sm, col_md, col_lg) {
                classes = 'col ' + col_xs + ' ' + col_sm + ' ' + col_md + ' ' + col_lg + ' ' + specific_class;
                tmml.ajax.saveLayoutItem(false, elem, 'col', classes, specific_class, col_xs, col_sm, col_md, col_lg, '', '');
            },
            module: function(elem, specific_class, module_name, public_module_name) {
                classes = 'module ' + specific_class;
                tmml.ajax.saveLayoutItem(false, elem, 'module', classes, specific_class, '', '', '', '', module_name, public_module_name);
            }
        },
        edit: {
            wrapper: function(elem) {
                id_element = elem.attr('data-id');
                id_unique = elem.attr('data-id-unique');
                classes = 'wrapper sortable ' + id_unique + ' ' + specific_class;
                $('.tmmegalayout-admin .wrapper[data-id="' + id_element + '"]').attr('data-specific-class', specific_class).attr('class', classes);
                $('.tmmegalayout-admin .wrapper[data-id="' + id_element + '"] > article > .button-container .element-name').find('.identificator').text('(' + specific_class.replace(' ',' | ') + ')');
                tmml.ajax.saveLayoutItem(id_element, elem, 'wrapper', classes, specific_class, '', '', '', '', '', '');
            },
            row: function(elem, specific_class) {
                id_element = elem.attr('data-id');
                id_unique = elem.attr('data-id-unique');
                classes = 'row sortable ' + id_unique + ' ' + specific_class;
                $('.tmmegalayout-admin .row[data-id="' + id_element + '"]').attr('data-specific-class', specific_class).attr('class', classes);
                $('.tmmegalayout-admin .row[data-id="' + id_element + '"] > article > .button-container .element-name').find('.identificator').text('(' + specific_class.replace(' ',' | ') + ')');
                tmml.ajax.saveLayoutItem(id_element, elem, 'row', classes, specific_class, '', '', '', '', '', '');
            },
            col: function(elem, specific_class, col_xs, col_sm, col_md, col_lg) {
                id_element = elem.attr('data-id');
                id_unique = elem.attr('data-id-unique');
                classes = 'col sortable ' + id_unique + ' ' + col_xs + ' ' + col_sm + ' ' + col_md + ' ' + col_lg + ' ' + specific_class;
                $('.tmmegalayout-admin .col[data-id="' + id_element + '"]').attr('data-specific-class', specific_class).attr('data-col-xs', col_xs).attr('data-col-sm', col_sm).attr('data-col-md', col_md).attr('data-col-lg', col_lg).attr('class', classes);
                $('.tmmegalayout-admin .col[data-id="' + id_element + '"] > article > .button-container .element-name').find('.identificator').text('(' + specific_class.replace(' ',' | ') + ')');
                tmml.ajax.saveLayoutItem(id_element, elem, 'col', classes, specific_class, col_xs, col_sm, col_md, col_lg, '', '');
            },
            module: function(elem, specific_class, module_name, public_module_name) {
                id_element = elem.attr('data-id');
                id_unique = elem.attr('data-id-unique');
                module_name = elem.attr('data-module');
                classes = 'module sortable ' + id_unique + ' ' + specific_class;
                $('.tmmegalayout-admin .module[data-id="' + id_element + '"]').attr('data-specific-class', specific_class).attr('class', classes);
                tmml.ajax.saveLayoutItem(id_element, elem, 'module', classes, specific_class, '', '', '', '', module_name, '');
            }
        }
    },
    sortInit: function() {
        $('.tmmegalayout-admin, .tmmegalayout-admin article, .tmmegalayout-admin article article').sortable({
            cursor: 'move',
            items: '> div.col, > div.row, > div.wrapper, > div.module',
            update: function(event, ui) {
                $(this).find('> div.sortable').each(function(index) {
                    index = index + 1;
                    $(this).attr('data-sort-order', index);
                    $(this).find('.sort-order').text(index);
                });

                tmml.ajax.updateSortOrders($(this));
            }
        });
    },
    tooltipInit: function() {
        $('span.module-name').tooltip()
    },
    get: {
        sortOrder: function(elem) {
            sort_order = [];
            elem.closest('article').find('> div.sortable').each(function() {
                sort_order.push($(this).attr('data-sort-order'));
            });

            if ($.isEmptyObject(sort_order)) {
                sort_order = 1;
            } else {
                sort_order = Math.max.apply(Math, sort_order) + 1;
            }

            return sort_order;
        },
        parentId: function(elem) {
            parent_id = elem.closest('div:not(.button-container)').attr('data-id');

            if (typeof (parent_id) == 'undefined' && !parent_id) {
                parent_id = 0;
            }

            return parent_id;
        },
        elementId: function() {
            ids_items = [];
            $('.container').find('div[data-id]').each(function() {
                ids_items.push($(this).attr('data-id'));
            });

            if ($.isEmptyObject(ids_items)) {
                id_item = 1;
            } else {
                id_item = Math.max.apply(Math, ids_items) + 1;
            }

            return id_item;
        }
    },
    init: function() {
        this.sortInit();
        this.tooltipInit();
        this.events.init();
    }
};