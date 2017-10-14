<?php
/**
 * 2002-2016 TemplateMonster
 *
 * TM Media Parallax
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
 *  @author    TemplateMonster
 *  @copyright 2002-2016 TemplateMonster
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class TMMediaParallaxItems extends ObjectModel
{
    public $id_item;
    public $id_shop;
    public $selector;
    public $active;
    public $speed = 0;
    public $fade;
    public $inverse;
    public $offset = 0;
    public $full_width;

    public static $definition = array(
        'table' => 'tmmediaparallax',
        'primary' => 'id_item',
        'multilang' => false,
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isunsignedInt'),
            'selector' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128),
            'speed' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'size' => 128),
            'offset' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'inverse' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'fade' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'full_width' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );

    /**
     * Get all items by shop
     * 
     * @param int $id_shop
     * @param bool $only_active
     * @return array items
     */
    public static function getItems($id_shop, $only_active = false)
    {
        $sql = 'SELECT *
                FROM ' . _DB_PREFIX_ . 'tmmediaparallax
                WHERE `id_shop` ='. $id_shop;
        if ($only_active) {
            $sql .= ' AND `active` = 1';
        }

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return array();
        }

        return $result;
    }

    public function delete()
    {
        if (!TMMediaParallaxLayouts::deleteByParent($this->id_item)) {
            return false;
        }
        return parent::delete();
    }
}
