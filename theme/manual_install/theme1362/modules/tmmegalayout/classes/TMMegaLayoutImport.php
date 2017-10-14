<?php
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

if (!defined('_PS_VERSION_')) {
    exit;
}

class TMMegaLayoutImport
{
    protected $id_layout;

    public static function checkTempFolder($path)
    {
        $temp_folder = $path . 'temp/';

        if (!file_exists($temp_folder)) {
            mkdir($temp_folder, 0777);
        } else {
            Tmmegalayout::cleanFolder($temp_folder);
        }

        return $temp_folder;
    }

    public static function isZip($file)
    {
        $file_extension = pathinfo($file, PATHINFO_EXTENSION);

        if ($file_extension != 'zip') {
            return false;
        }

        return true;
    }

    public function readSettings($path, $file_name = 'settings.json')
    {
        $file = $path . $file_name;

        if (!file_exists($file)) {
            return false;
        }

        $settings_json = Tools::file_get_contents($file);

        return Tools::jsonDecode($settings_json, true);
    }

    protected function createLayout($settings, $def = false)
    {
        $obj = new Tmmegalayout();
        $id_shop = $obj->getIdShop();
        $id_hook = Hook::getIdByName($settings['hook']);
        $status = 0;

        if ($def && (int)$settings['status'] == 1) {
            $status = 1;
        }

        $layout = new TMMegaLayoutLayouts();
        $layout->id_shop = $id_shop;
        $layout->id_hook = $id_hook;
        $layout->layout_name = $settings['layout_name'];
        $layout->status = $status;
        $layout->save();

        return $layout->id;
    }

    protected function createLayoutItems($layout_items, $id_parent = null, $styles = array())
    {
        foreach ($layout_items as $item) {
            if (!isset($id_parent)) {
                $id_parent = 0;
            }

            $layout = new TMMegaLayoutItems();
            $layout->id_parent = $id_parent;
            $layout->id_layout = $this->id_layout;

            $id_unique = 'it_' . Tools::passwdGen(12, 'NO_NUMERIC');
            switch ($item['type']) {
                case 'module':
                    $layout->type = $item['type'];
                    $layout->module_name = $item['module_name'];
                    $layout->sort_order = $item['sort_order'];
                    $layout->id_unique = $id_unique;
                    break;
                case 'wrapper':
                    $layout->type = $item['type'];
                    $layout->sort_order = $item['sort_order'];
                    $layout->specific_class = $item['specific_class'];
                    $layout->id_unique = $id_unique;
                    break;
                case 'row':
                    $layout->type = $item['type'];
                    $layout->sort_order = $item['sort_order'];
                    $layout->specific_class = $item['specific_class'];
                    $layout->id_unique = $id_unique;
                    break;
                case 'col':
                    $layout->type = $item['type'];
                    $layout->sort_order = $item['sort_order'];
                    $layout->specific_class = $item['specific_class'];
                    $layout->col_xs = $item['col_xs'];
                    $layout->col_sm = $item['col_sm'];
                    $layout->col_md = $item['col_md'];
                    $layout->col_lg = $item['col_lg'];
                    $layout->id_unique = $id_unique;
                    break;
            }
            $styles[$id_unique] = $item['id_unique'];
            $layout->add();

            if ($item['child']) {
                $styles = $this->createLayoutItems($item['child'], $layout->id, $styles);
            }
        }
        return $styles;
    }

    protected function restoreLayoutMap($import_layouts, $map = array(), $id_parent = 0, $level = 0)
    {
        if (!$import_layouts) {
            return $map;
        }

        foreach ($import_layouts as $layout) {
            $layout['id_item'] = rand(1, 100000);
            $child_layouts = $layout['child'];
            $map[$level][$id_parent][] = $layout;
            $map = $this->restoreLayoutMap($child_layouts, $map, $layout['id_item'], $level + 1);
        }

        return $map;
    }

    protected function getLayoutStyles($path, $styles)
    {
        if (count($styles) > 0) {
            $style_obj = new Tmmegalayout();

            $style_folder = $path . 'styles/';
            $img_folder = $path . 'images/';

            if (file_exists($style_folder)) {
                foreach ($styles as $new_id => $old_id) {
                    if ($style_obj->checkUniqueStylesExists($old_id, $style_folder)) {
                        $style_content = $style_obj->getStylesContent($old_id, $style_folder);
                        $styles_enc = $style_obj->encodeStyles($style_content);
                        $style_obj->saveItemStyles($new_id, $styles_enc, $style_folder, true);
                        copy($style_folder . $new_id . '.css', $style_obj->style_path . $new_id . '.css');
                    }
                }
            }
            $style_obj->combineAllItemsStyles();
            if (file_exists($img_folder)) {
                Tmmegalayout::recurseCopy($img_folder, _PS_IMG_DIR_);
            }
        }
    }

    public function layoutPreview($path, $file_name)
    {
        $lang = new Tmmegalayout();
        $errors = null;
        if (TMMegaLayoutImport::isZip($path . $file_name)) {
            $temp_folder = $this->checkTempFolder($path);
            $zip = new ZipArchive();
            $zip->open($path . $file_name);
            $zip->extractTo($temp_folder);
            if (!$layout_items = $this->readSettings($temp_folder, 'grid.json')) {
                $errors = $lang->displayError($lang->l('Grid file is missing'));
            }
            $map = $this->restoreLayoutMap($layout_items);
            $render = new Tmmegalayout();
            if (!$layout_settings = $this->readSettings($temp_folder)) {
                $errors .= $lang->displayError($lang->l('Settings file is missing'));
            }

            if ($errors != null) {
                Context::getContext()->smarty->assign(array(
                    'error' => $errors
                ));
            } else {
                Context::getContext()->smarty->assign(array(
                    'layout_preview' => $render->renderLayoutAdmin($map, true),
                    'layout_name' => $layout_settings['layout_name'],
                    'hook_name' => $layout_settings['hook']
                ));
            }
            return $lang->display($lang->getLocalPath(), 'views/templates/admin/tools/import-preview.tpl');
        } else {
            Context::getContext()->smarty->assign(array(
                'error' => $lang->displayError($lang->l('Layout archive must have zip format'))
            ));
            return $lang->display($lang->getLocalPath(), 'views/templates/admin/tools/import-preview.tpl');
        }
    }

    public function importLayout($path, $file_name, $def = false)
    {
        $temp_folder = TMMegaLayoutImport::checkTempFolder($path);
        $lang = new Tmmegalayout();

        if (TMMegaLayoutImport::isZip($path . $file_name)) {
            $zip = new ZipArchive();
            $zip->open($path . $file_name);
            $zip->extractTo($temp_folder);
            $settings = $this->readSettings($temp_folder);

            if (!$this->id_layout = $this->createLayout($settings, $def)) {
                return false;
            }

            $layout_items = $this->readSettings($temp_folder, 'grid.json');
            $styles = $this->createLayoutItems($layout_items);
            $this->getLayoutStyles($temp_folder, $styles);
            Tmmegalayout::cleanFolder($temp_folder);

            return true;
        } elseif ($def) {
            return true;
        } else {
            return $lang->displayError($lang->l('Layout archive must have zip format'));
        }
    }
}
