<?php
/**
 * 2002-2015 TemplateMonster
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
 *  @copyright 2002-2015 TemplateMonster
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

class AdminTMMegaLayoutController extends ModuleAdminController
{

    public function ajaxProcessUpdateLayoutItem()
    {
        $errors = array();
        $item_data = Tools::getValue('data');
        $id_item = Tools::getValue('id_item');

        if ($id_item != 'false') {
            $item = new TMMegaLayoutItems($id_item);
        } else {
            $item = new TMMegaLayoutItems();
            $item->id_unique = 'it_' . Tools::passwdGen(12, 'NO_NUMERIC');
        }

        $item->id_layout = $item_data['id_layout'];
        $item->id_parent = $item_data['id_parent'];
        $item->sort_order = $item_data['sort_order'];
        $item->col_xs = $item_data['col_xs'];
        $item->col_sm = $item_data['col_sm'];
        $item->col_md = $item_data['col_md'];
        $item->col_lg = $item_data['col_lg'];
        $item->module_name = $item_data['module_name'];
        $item->specific_class = $item_data['specific_class'];
        $item->type = $item_data['type'];

        if ($id_item == 'false') {
            if (!$item->add()) {
                $errors[] = $this->l('Error occurred while adding an item!');
            }
        } else {
            if (!$item->update()) {
                $errors[] = $this->l('Error occurred while saving an item!');
            }
        }

        if (count($errors)) {
            die(Tools::jsonEncode(array('status' => 'false', 'response_msg' => $this->l('Oops...something went wrong!'))));
        }

        $item->id_item = $item->id;
        $tmmegalayout = new Tmmegalayout();
        $item_content = null;
        switch ($item_data['type']) {
            case 'module':
                $this->context->smarty->assign(array(
                    'elem' => get_object_vars($item),
                    'preview' => false,
                    'position' => ''
                ));
                $item_content = $tmmegalayout->display($tmmegalayout->getLocalPath(), '/views/templates/admin/layouts/module.tpl');
                break;
            case 'wrapper':
                $this->context->smarty->assign(array(
                    'elem' => get_object_vars($item),
                    'preview' => false,
                    'position' => ''
                ));
                $item_content = $tmmegalayout->display($tmmegalayout->getLocalPath(), '/views/templates/admin/layouts/wrapper.tpl');
                break;
            case 'row':
                $this->context->smarty->assign(array(
                    'elem' => get_object_vars($item),
                    'preview' => false,
                    'position' => ''
                ));
                $item_content = $tmmegalayout->display($tmmegalayout->getLocalPath(), '/views/templates/admin/layouts/row.tpl');
                break;
            case 'col':
                $class = $item_data['col_xs'] . ' ' . $item_data['col_sm'] . ' ' . $item_data['col_md'] . ' ' . $item_data['col_lg'] . ' ';
                $this->context->smarty->assign(array(
                    'elem' => get_object_vars($item),
                    'preview' => false,
                    'position' => '',
                    'class' => $class
                ));
                $item_content = $tmmegalayout->display($tmmegalayout->getLocalPath(), '/views/templates/admin/layouts/col.tpl');
                break;
        }
        die(Tools::jsonEncode(array('status' => 'true', 'id_item' => $item->id, 'id_unique' => $item->id_unique, 'response_msg' => $this->l('Changes were saved successfully'), 'content' => $item_content)));
    }

    public function ajaxProcessDeleteLayoutItem()
    {
        $id_items = Tools::getValue('id_item');

        if (count($id_items) < 1) {
            die(Tools::jsonEncode(array('status' => 'error', 'response_msg' => $this->l('Bad ID value'))));
        }

        foreach ($id_items as $id_item) {
            $item = new TMMegaLayoutItems($id_item);

            if (!$item->delete()) {
                die(Tools::jsonEncode(array('status' => 'error', 'response_msg' => $this->l('Can\'t delete item(s)'))));
            }
        }

        die(Tools::jsonEncode(array('status' => 'true', 'response_msg' => $this->l('Item(s) was/were deleted successfully'))));
    }

    public function ajaxProcessUpdateLayoutItemsOrder()
    {
        $data = Tools::getValue('data');

        if (count($data) > 1) {
            foreach ($data as $id_item => $sort_order) {
                $item = new TMMegaLayoutItems($id_item);

                if (!Validate::isLoadedObject($item)) {
                    die(Tools::jsonEncode(array('status' => 'error', 'response_msg' => $this->l('Bad ID value'))));
                }

                $item->sort_order = $sort_order;

                if (!$item->update()) {
                    die(Tools::jsonEncode(array('status' => 'error', 'response_msg' => $this->l('Sort order changes were not saved successfully'))));
                }
            }

            die(Tools::jsonEncode(array('status' => 'true', 'response_msg' => $this->l('Changes were saved successfully'))));
        }
    }

    public function ajaxProcessLayoutPreview()
    {
        $id_layout = Tools::getValue('id_layout');

        $item = new Tmmegalayout();

        die(Tools::jsonEncode(array('status' => 'true', 'msg' => $item->getLayoutAdmin($id_layout, true))));
    }

    public function ajaxProcessLayoutExport()
    {
        $id_layout = Tools::getValue('id_layout');
        $obj = new TMMegalayoutExport();
        $href = $obj->init($id_layout);
        die(Tools::jsonEncode(array('status' => true, 'href' => $href)));
    }

    public function ajaxProcessLoadTool()
    {
        $tool_name = Tools::getValue('tool_name');
        $tools = new Tmmegalayout();
        $tool_content = $tools->renderToolContent($tool_name);
        die(Tools::jsonEncode(array('status' => 'true', 'content' => $tool_content)));
    }

    public function ajaxProcessGetItemStyles()
    {
        $id_unique = Tools::getValue('id_unique');
        $tools = new Tmmegalayout();
        $styles = $tools->getItemStyles($id_unique);
        die(Tools::jsonEncode(array('status' => 'true', 'content' => $styles)));
    }

    public function ajaxProcessSaveItemStyles()
    {
        $id_unique = Tools::getValue('id_unique');
        $data = Tools::getValue('data');

        if (!$data || Tools::isEmpty($data)) {
            die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Nothing to save'))));
        }

        $tools = new Tmmegalayout();

        if ($tools->saveItemStyles($id_unique, $data)) {
            die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Saved'))));
        }

        die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
    }

    public function ajaxProcessClearItemStyles()
    {
        $id_unique = Tools::getValue('id_unique');
        $tools = new Tmmegalayout();

        if ($tools->deleteItemStyles($id_unique)) {
            die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Item styles are removed'))));
        }

        die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred while removing styles'))));
    }

    public function ajaxProcessAddLayoutForm()
    {
        $id_hook = Tools::getValue('id_hook');
        $layout = new Tmmegalayout();

        if (!$result = $layout->showMessage($id_hook, 'addLayout')) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
        }

        die(Tools::jsonEncode(array('status' => 'true', 'response_msg' => $result)));
    }

    public function ajaxProcessAddLayout()
    {
        $id_hook = Tools::getValue('id_hook');
        $layout_name = Tools::getValue('layout_name');
        $layout = new Tmmegalayout();

        if (!$id_layout = $layout->addLayout($id_hook, $layout_name)) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
        }
        $new_layouts = Tools::jsonEncode(TMMegaLayoutLayouts::getLayoutsForHook($id_hook, $this->context->shop->id));
        die(Tools::jsonEncode(array('status' => 'true', 'id_layout' => $id_layout, 'message' => $this->l('The layout is successfully added.'), 'new_layouts' => $new_layouts)));
    }

    public function ajaxProcessAddModuleConfirmation()
    {
        $id_hook = (int) Tools::getValue('id_hook');
        $id_layout = (int) Tools::getValue('id_layout');

        $tmmegalayout = new Tmmegalayout();

        if (!$form = $tmmegalayout->addModuleForm($id_hook, $id_layout)) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some error occurred'))));
        }

        die(Tools::jsonEncode(array('status' => 'true', 'message' => $form)));
    }

    public function ajaxProcessLoadLayoutContent()
    {
        $id_layout = Tools::getValue('id_layout');
        $layout = new Tmmegalayout();

        if (!$result = $layout->renderLayoutContent($id_layout)) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
        }

        die(Tools::jsonEncode(array('status' => 'true', 'layout' => $result[0], 'layout_buttons' => $result[1])));
    }

    public function ajaxProcessGetLayoutRemoveConfirmation()
    {
        $id_layout = Tools::getValue('id_layout');
        $layout = new Tmmegalayout();

        if (!$result = $layout->showMessage($id_layout, 'layoutRemoveConf')) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
        }

        die(Tools::jsonEncode(array('status' => 'true', 'message' => $result)));
    }

    public function ajaxProcessRemoveLayout()
    {
        $id_layout = (int) Tools::getValue('id_layout');
        $layouts = TMMegaLayoutItems::getItems($id_layout);

        if ($layouts && count($layouts) > 0) {
            foreach ($layouts as $layout) {
                $item = new TMMegaLayoutItems($layout['id_item']);

                if (!$item->delete()) {
                    die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some error occurred. Can\'t delete layout item') . $item->id)));
                }
            }
        }

        $tab = new TMMegaLayoutLayouts($id_layout);

        if (!$tab->delete()) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => 'Can\'t delete layout')));
        }
        $id_hook = Tools::getValue('id_hook');
        $new_layouts = Tools::jsonEncode(TMMegaLayoutLayouts::getLayoutsForHook($id_hook, $this->context->shop->id));
        die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Layout is successfully removed'), 'new_layouts' => $new_layouts)));
    }

    public function ajaxProcessGetLayoutRenameConfirmation()
    {
        $id_layout = Tools::getValue('id_layout');
        $layout = new Tmmegalayout();
        $tab = new TMMegaLayoutLayouts($id_layout);

        if (!$result = $layout->showMessage($id_layout, 'layoutRenameConf', $tab->layout_name)) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
        }

        die(Tools::jsonEncode(array('status' => 'true', 'message' => $result)));
    }

    public function ajaxProcessRenameLayout()
    {
        $id_layout = (int) Tools::getValue('id_layout');
        $layout_name = Tools::getValue('layout_name');

        $tab = new TMMegaLayoutLayouts($id_layout);
        $tab->layout_name = $layout_name;

        if (!$tab->update()) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Can\'t update a layout name'))));
        }

        die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Layout name is successfully changed'))));
    }

    public function ajaxProcessDisableLayout()
    {
        $id_layout = (int) Tools::getValue('id_layout');
        $tab = new TMMegaLayoutLayouts($id_layout);
        $tab->status = 0;

        if (!$tab->update()) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Can\'t disable layout'))));
        }

        die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Layout is disabled'))));
    }

    public function ajaxProcessEnableLayout()
    {
        $id_layout = (int) Tools::getValue('id_layout');
        $id_hook = (int) Tools::getValue('id_hook');
        $tmmegalayout = new Tmmegalayout();
        $tabs = TMMegaLayoutLayouts::getLayoutsForHook($id_hook, $this->context->shop->id);

        if ($tabs) {
            foreach ($tabs as $layout) {
                $tab = new TMMegaLayoutLayouts($layout['id_layout']);
                $tab->status = 0;

                if (!$tab->update()) {
                    die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Can\'t disable previous layout'))));
                }
            }
        }

        $item = new TMMegaLayoutLayouts($id_layout);
        $item->status = 1;

        if (!$item->update()) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Can\'t enable this layout'))));
        }

        if (!$tmmegalayout->combineAllItemsStyles()) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Can\'t regenerate layout styles'))));
        }

        die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Layout is enabled'))));
    }

    public function ajaxProcessGetImportInfo()
    {
        $import = new TMMegaLayoutImport();
        $import_path = new Tmmegalayout();
        Tmmegalayout::cleanFolder($import_path->getLocalPath() . 'import/');
        $file_name = basename($_FILES['file']['name']);
        $upload_file = $import_path->getLocalPath() . 'import/' . $file_name;
        move_uploaded_file($_FILES['file']['tmp_name'], $upload_file);
        $preview = $import->layoutPreview($import_path->getLocalPath() . 'import/', $file_name);
        die(Tools::jsonEncode(array(
                    'status' => 'true',
                    'preview' => $preview,
        )));
    }

    public function ajaxProcessImportLayout()
    {
        $import = new TMMegaLayoutImport();
        $import_path = new Tmmegalayout();
        Tmmegalayout::cleanFolder($import_path->getLocalPath() . 'import/');
        $file_name = basename($_FILES['file']['name']);
        $upload_file = $import_path->getLocalPath() . 'import/' . $file_name;
        move_uploaded_file($_FILES['file']['tmp_name'], $upload_file);
        $import->importLayout($import_path->getLocalPath() . 'import/', $file_name);
        die(Tools::jsonEncode(array('status' => 'true', 'response_msg' => $this->l('Successful import'))));
    }

    public function ajaxProcessLoadLayoutTab()
    {
        $layout_list = new Tmmegalayout();
        $id_hook = Tools::getValue('tab_id');
        $old_layouts = Tools::jsonDecode(Tools::getValue('old_layouts'), true);
        $new_layouts = Tools::jsonEncode($layout_list->checkNewLayouts($id_hook, $old_layouts));
        die(Tools::jsonEncode(array('status' => 'true', 'new_layouts' => $new_layouts, 'old_layouts' => Tools::jsonEncode(TMMegaLayoutLayouts::getLayoutsForHook($id_hook, $this->context->shop->id)))));
    }

    public function ajaxProcessAfterImport()
    {
        $import_path = new Tmmegalayout();
        $path = $import_path->getLocalPath() . 'import/';
        Tmmegalayout::cleanFolder($path);
        die(Tools::jsonEncode(array('status' => 'true')));
    }

    public function ajaxProcessAfterExport()
    {
        die(Tools::jsonEncode(array('status' => 'true')));
    }

    public function ajaxProcessResetToDefault()
    {
        // get all tabs for this store
        $layouts = TMMegaLayoutLayouts::getShopLayoutsIds();

        if ($layouts) {
            foreach ($layouts as $layout) {
                // if no layouts for this tab delete it immediately
                if (!$items = TMMegaLayoutItems::getItems($layout['id_layout'])) {
                    $current_layout = new TMMegaLayoutLayouts($layout['id_layout']);

                    if (!$current_layout->delete()) {
                        die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some error occurred. Can\'t delete layout') . $current_layout->id)));
                    }
                    // if there is layouts for this tab delete all and delete tab after
                } else {
                    foreach ($items as $item) {
                        $current_item = new TMMegaLayoutItems($item['id_item']);
                        if (!$current_item->delete()) {
                            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some error occurred. Can\'t delete layout item') . $current_item->id)));
                        }
                    }

                    $current_layout = new TMMegaLayoutLayouts($layout['id_layout']);

                    if (!$current_layout->delete()) {
                        die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some error occurred. Can\'t delete layout item') . $current_layout->id)));
                    }
                }
            }
        }

        $tmmegalayout = new Tmmegalayout();
        // install default layouts from "default" folder
        if (!$tmmegalayout->installDefLayouts()) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Can\'t load default layouts'))));
        }

        die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('All data is successfully removed'))));
    }

    public function ajaxProcessReloadTab()
    {
        $tab_id = (int) Tools::getValue('id_tab');
        $tab = new Tmmegalayout();
        $layouts_list = Tools::jsonEncode(TMMegaLayoutLayouts::getLayoutsForHook($tab_id, $this->context->shop->id));
        if (!$tab_content = $tab->renderLayoutTab($tab_id)) {
            die(Tools::jsonEncode(array('status' => 'false')));
        }
        die(Tools::jsonEncode(array('status' => 'true', 'tab_content' => $tab_content, 'layouts_list' => $layouts_list)));
    }
}
