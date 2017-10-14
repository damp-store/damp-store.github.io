<?php
/**
* 2002-2016 TemplateMonster
*
* TM Products Slider
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
*  @author    TemplateMonster (Alexander Grosul)
*  @copyright 2002-2016 TemplateMonster
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

class AdminTMProductsSliderController extends ModuleAdminController
{
    public function ajaxProcessUpdatePosition()
    {
        $items = Tools::getValue('item');
        $total = count($items);
        $id_shop = Tools::getValue('id_shop');
        $success = true;
        for ($i = 1; $i <= $total; $i++) {
            $success &= Db::getInstance()->update(
                'tmproductsslider_item',
                array('slide_order' => (int)$i),
                '`id_product` = '.preg_replace('/(item-)([0-9]+)/', '${2}', $items[$i - 1]).'
                AND `id_shop` ='.(int)$id_shop
            );
        }
        if (!$success) {
            die(Tools::jsonEncode(array('error' => 'Update Fail')));
        }
        die(Tools::jsonEncode(array('success' => 'Update Success !', 'error' => false)));
    }

    public function ajaxProcessUpdateStatus()
    {
        $id_product = Tools::getValue('id_product');
        $id_shop = Tools::getValue('id_shop');
        $slide_status = Tools::getValue('slide_status');
        $success = true;
        if ($slide_status == 1) {
            $slide_status = 0;
        } else {
            $slide_status = 1;
        }

        $success &= Db::getInstance()->update(
            'tmproductsslider_item',
            array('slide_status'=> (int)$slide_status),
            ' id_shop = '.(int)$id_shop.'
            AND id_product = '.(int)$id_product
        );

        if (!$success) {
            die(Tools::jsonEncode(array('error_status' => 'Status Update Fail')));
        }
        die(Tools::jsonEncode(array('success_status' => 'Status Update Success !', 'error' => false)));
    }
}
