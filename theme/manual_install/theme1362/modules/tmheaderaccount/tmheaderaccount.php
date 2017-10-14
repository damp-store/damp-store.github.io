<?php
/**
* 2002-2015 TemplateMonster
*
* TemplateMonster Header Account Block
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

class Tmheaderaccount extends Module
{
    public function __construct()
    {
        $this->name = 'tmheaderaccount';
        $this->tab = 'front_office_features';
        $this->version = '1.1.1';
        $this->author = 'TemplateMonster (Alexander Grosul)';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('TemplateMonster Header Account Block');
        $this->description = $this->l('Display customer account information in the site header');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayNav');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addJS(_PS_JS_DIR_.'validate.js');
        $this->context->controller->addJS($this->_path.'views/js/front.js');

        $this->context->controller->addCSS($this->_path.'views/css/front.css');
    }

    public function hookDisplayNav()
    {
        $this->smarty->assign(array(
            'voucherAllowed' => CartRule::isFeatureActive(),
            'returnAllowed' => (int)Configuration::get('PS_ORDER_RETURN'),
            'HOOK_BLOCK_MY_ACCOUNT' => Hook::exec('displayCustomerAccount')
        ));

        return $this->display($this->_path, 'views/templates/hook/tmheaderaccount.tpl');
    }

    public function hookDisplayTop()
    {
        return $this->hookDisplayNav();
    }
}
