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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'tmproductsslider_settings` (
		`id_slider` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`id_shop` int(10) unsigned NOT NULL,
		`slider_width` int(10) unsigned DEFAULT \'1170\',
		`slider_type` varchar(255) DEFAULT \'fade\',
		`slider_speed` int(10) unsigned DEFAULT \'500\',
		`slider_pause` int(10) unsigned DEFAULT \'3000\',
		`slider_loop` tinyint(1) unsigned NOT NULL DEFAULT \'1\',
		`slider_pause_h` tinyint(1) unsigned NOT NULL DEFAULT \'1\',
		`slider_pager` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
		`slider_controls` tinyint(1) unsigned NOT NULL DEFAULT \'1\',
		`slider_auto_controls` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
		PRIMARY KEY (`id_slider`, `id_shop`)
		) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'tmproductsslider_item` (
    	`id_slide` int(11) NOT NULL AUTO_INCREMENT,
		`id_shop` int(11) NOT NULL,
		`id_product` int(10) NOT NULL,
		`slide_order` int(11) NOT NULL,
		`slide_status` tinyint(1) unsigned NOT NULL DEFAULT \'1\',
    	PRIMARY KEY  (`id_slide`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
