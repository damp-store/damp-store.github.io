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

if (!defined('_PS_VERSION_')) {
    exit;
}

class TMMegaLayoutLayouts extends ObjectModel
{
    public $id_layout;
    public $id_hook;
    public $id_shop;
    public $status;
    public $layout_name;

    public static $definition = array(
        'table' => 'tmmegalayout',
        'primary' => 'id_layout',
        'multilang' => false,
        'fields' => array(
            'id_hook' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isunsignedInt'),
            'id_shop' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isunsignedInt'),
            'layout_name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128),
            'status' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );

    /**
     * Get active layout id
     * 
     * @param int $id_hook
     * @param int $id_shop
     * @return (int) layout id or false
     */
    public static function getActiveLayoutId($id_hook, $id_shop)
    {
        $sql = 'SELECT *
                FROM ' . _DB_PREFIX_ . 'tmmegalayout
                WHERE `id_hook` =' . $id_hook .' 
                AND `id_shop` ='. $id_shop.' 
                AND `status` = 1';

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
            return false;
        }

        return $result;
    }

    /**
     * Get all hook's layouts
     * 
     * @param int $id_hook
     * @param int $id_shop
     * @return array layouts id or false
     */
    public static function getLayoutsForHook($id_hook, $id_shop)
    {
        $sql = 'SELECT *
                FROM ' . _DB_PREFIX_ . 'tmmegalayout
                WHERE `id_hook` =' . $id_hook . ' 
                AND `id_shop` ='. $id_shop;

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return false;
        }

        return $result;
    }

    /**
     * Get all layouts for shop
     * 
     * @return array shop layouts id or false
     */
    public static function getShopLayoutsIds()
    {
        $sql = 'SELECT `id_layout`
                FROM '._DB_PREFIX_.'tmmegalayout
                WHERE `id_shop` = '.Context::getContext()->shop->id;

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return false;
        }

        return $result;
    }
}
