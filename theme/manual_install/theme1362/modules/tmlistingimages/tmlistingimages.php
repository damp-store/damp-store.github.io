<?php
/**
* 2002-2015 TemplateMonster
*
* TemplateMonster Product List Images
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

class Tmlistingimages extends Module
{

    public function __construct()
    {
        $this->name = 'tmlistingimages';
        $this->tab = 'front_office_features';
        $this->version = '1.0.3';
        $this->author = 'TemplateMonster (Alexander Grosul)';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('TemplateMonster Product List Images');
        $this->description = $this->l('Show next image in product list');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('displayProductListImages');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayProductListImages($params)
    {
        $product = new Product($params['product']['id_product']);

        $this->smarty->assign(array(
            'product_images' => $product->getImages($this->context->language->id),
            'product' => $params['product'],
        ));

        return $this->display(__FILE__, 'views/templates/hooks/tmlistingimages.tpl');
    }
}
