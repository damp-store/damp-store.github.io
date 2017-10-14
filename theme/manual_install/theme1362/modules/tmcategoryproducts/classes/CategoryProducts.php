<?php
/**
* 2002-2015 TemplateMonster
*
* TM Category Products
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
*  @copyright 2002-2015 TemplateMonster
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class CategoryProducts extends ObjectModel
{
    public $id_shop;
    public $category;
    public $num;
    public $mode;
    public $status;
    public $use_carousel;

    public static $definition = array(
        'table'      => 'tmcategoryproducts',
        'primary'    => 'id_tab',
        'multilang'  => false,
        'fields'     => array(
            'category'     => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isunsignedInt'),
            'num'          => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'mode'         => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'status'       => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'use_carousel' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );

    public function add($autodate = true, $null_values = false)
    {
        $context = Context::getContext();
        $id_shop = $context->shop->id;

        $res = parent::add($autodate, $null_values);
        $res &= Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'tmcategoryproducts_shop` (`id_tab`, `id_shop`)
            VALUES('.(int)$this->id.', '.(int)$id_shop.')'
        );
        return $res;
    }

    public function delete()
    {
        $res = true;

        $res &= Db::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.'tmcategoryproducts_shop`
            WHERE `id_tab` = '.(int)$this->id
        );

        $res &= parent::delete();
        return $res;
    }
}
