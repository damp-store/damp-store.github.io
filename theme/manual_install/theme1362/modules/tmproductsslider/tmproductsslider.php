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
* @author    TemplateMonster (Alexander Grosul)
* @copyright 2002-2016 TemplateMonster
* @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once (dirname(__FILE__).'/classes/TMProductSlider.php');

class TMProductsSlider extends Module
{
    public function __construct()
    {
        $this->name = 'tmproductsslider';
        $this->tab = 'front_office_features';
        $this->version = '1.2.3';
        $this->bootstrap = true;
        $this->author = 'TemplateMonster (Alexander Grosul)';
        $this->default_language = Language::getLanguage(Configuration::get('PS_LANG_DEFAULT'));
        $this->languages = Language::getLanguages();
        $this->secure_key = Tools::encrypt($this->name);
        $this->module_key = '4d13770dd3ec44a69f4ab2ca34e14fdc';
        parent::__construct();
        $this->displayName = $this->l('TM Products Slider');
        $this->description = $this->l('Module for displaying products in slider.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function createAjaxController()
    {
        $tab = new Tab();
        $tab->active = 1;
        $languages = Language::getLanguages(false);
        if (is_array($languages)) {
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = 'tmproductsslider';
            }
        }
        $tab->class_name = 'AdminTMProductsSlider';
        $tab->module = $this->name;
        $tab->id_parent = - 1;
        return (bool)$tab->add();
    }

    private function removeAjaxContoller()
    {
        if ($tab_id = (int)Tab::getIdFromClassName('AdminTMProductsSlider')) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }
        return true;
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        if (!parent::install()
            || !$this->registerHook('displayBackOfficeHeader')
            || !$this->registerHook('displayAdminProductsExtra')
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('displayTopColumn')
            || !$this->createAjaxController()) {
            return false;
        } else {
            //set default setting each shop
            $shops = Shop::getContextListShopID();

            foreach ($shops as $shop_id) {
                $this->setDefaultSettings($shop_id);
            }
        }

        return true;
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        if (!$this->removeAjaxContoller()
            || !parent::uninstall()) {
            return false;
        }

        return true;
    }

    public function setDefaultSettings($id_shop)
    {
        //set default settings to shop
        Db::getInstance()->insert('tmproductsslider_settings', array(
            'id_shop'                =>    (int)$id_shop,
            'slider_width'           =>    1170,
            'slider_type'            =>    'fade',
            'slider_speed'           =>    500,
            'slider_pause'           =>    3000,
            'slider_loop'            =>    1,
            'slider_pause_h'         =>    1,
            'slider_pager'           =>    0,
            'slider_controls'        =>    0,
            'slider_auto_controls'   =>    0
        ));
    }

    public function prepareNewTab()
    {
        $higher_ver = Tools::version_compare(_PS_VERSION_, '1.6.0.9', '>');
        $this->context->smarty->assign(
            'is_slide',
            TMProductSlider::checkSlideExist((int)Tools::getValue('id_product'), $this->context->shop->id)
        );
        $this->context->smarty->assign('higher_ver', $higher_ver);
    }

    public function hookDisplayAdminProductsExtra()
    {
        if (Validate::isLoadedObject(new Product((int)Tools::getValue('id_product')))) {
            if (Shop::isFeatureActive()) {
                if (Shop::getContext() != Shop::CONTEXT_SHOP) {
                    $this->context->smarty->assign(array(
                        'display_multishop_checkboxes' => true
                    ));
                }

                if (Shop::getContext() != Shop::CONTEXT_ALL) {
                    $this->context->smarty->assign('bullet_common_field', '<i class="icon-circle text-orange"></i>');
                    $this->context->smarty->assign('display_common_field', true);
                }
            }

            $this->prepareNewTab();
            return $this->display(__FILE__, 'views/templates/admin/tmproductsslider_tab.tpl');
        } else {
            return $this->displayError($this->l('You must save this product before add it to slider.'));
        }
    }

    public function hookActionProductUpdate()
    {
        $id_product = (int)Tools::getValue('id_product');
        $add_slide = (int)Tools::getValue('is_slide');

        if (!$add_slide) {
            $this->removeSlide($id_product);
        } else {
            $this->addSlide($id_product);
        }
    }

    protected function addSlide($id_product)
    {
        $shops = Shop::getContextListShopID();

        if (empty($shops)) {
            return false;
        }
        foreach ($shops as $id_shop) {
            if (!TMProductSlider::checkSlideExist($id_product, $id_shop)) {
                $product_slide = new TMProductSlider();
                $product_slide->id_product = $id_product;
                $product_slide->id_shop = $id_shop;
                $product_slide->slide_order = $product_slide->setSortOrder(
                    $product_slide->id_shop,
                    $product_slide->id_product,
                    true
                );
                $product_slide->slide_status = true;
                if (!$product_slide->add()) {
                    $this->context->controller->_errors[] = Tools::displayError('Error: ').mysql_error();
                }
            }
        }
    }

    protected function removeSlide($id_product)
    {
        $shops = Shop::getContextListShopID();
        if (empty($shops)) {
            return false;
        }
        foreach ($shops as $id_shop) {
            if ($slide_id = TMProductSlider::checkSlideExist($id_product, $id_shop)) {
                $product_slide = new TMProductSlider($slide_id['id_slide']);

                if (!$product_slide->delete()) {
                    $this->context->controller->_errors[] = Tools::displayError('Error: ').mysql_error();
                }
            }
        }
    }

    public function setSliderSettings($id_shop)
    {
        $sql = 'SELECT *
                FROM '._DB_PREFIX_.'tmproductsslider_settings
                WHERE id_shop ='.(int)$id_shop;

        if (!Db::getInstance()->executeS($sql)) {
            Db::getInstance()->insert(
                'tmproductsslider_settings',
                array(
                    'id_shop'=> (int)$id_shop,
                    'slider_width'=> (int)Tools::getValue('slider_width'),
                    'slider_type'=> pSQL(Tools::getValue('slider_effect')),
                    'slider_speed'=> (int)Tools::getValue('slider_speed'),
                    'slider_pause'=> (int)Tools::getValue('slider_pause'),
                    'slider_loop'=> (int)Tools::getValue('slider_loop'),
                    'slider_pause_h'=> (int)Tools::getValue('slider_pause_h'),
                    'slider_pager'=> (int)Tools::getValue('slider_pager'),
                    'slider_controls'=> (int)Tools::getValue('slider_controls'),
                    'slider_auto_controls'=> (int)Tools::getValue('slider_auto_controls')
                )
            );

            $this->_html = '<div class="alert alert-succes">'.$this->l('Settings updated successful.').'</div>';
        } else {
            Db::getInstance()->update(
                'tmproductsslider_settings',
                array(
                    'slider_width'=> (int)Tools::getValue('slider_width'),
                    'slider_type'=> pSQL(Tools::getValue('slider_effect')),
                    'slider_speed'=> (int)Tools::getValue('slider_speed'),
                    'slider_pause'=> (int)Tools::getValue('slider_pause'),
                    'slider_loop'=> (int)Tools::getValue('slider_loop'),
                    'slider_pause_h'=> (int)Tools::getValue('slider_pause_h'),
                    'slider_pager'=> (int)Tools::getValue('slider_pager'),
                    'slider_controls'=> (int)Tools::getValue('slider_controls'),
                    'slider_auto_controls'=> (int)Tools::getValue('slider_auto_controls')
                ),
                'id_shop = '.(int)$id_shop
            );

            $this->_html = '<div class="alert alert-success">'.$this->l('Settings updated successful.').'</div>';
        }

        return $this->_html;
    }

    public function getSliderSettings()
    {
        $settings = array();
        $results = Db::getInstance()->executeS(
            'SELECT * 
            FROM '._DB_PREFIX_.'tmproductsslider_settings
            WHERE id_shop = '.(int)$this->context->shop->id
        );

        if (!$results) {
            return false;
        }

        foreach ($results as $res) {
            $settings['slider_width'] = $res['slider_width'];
            $settings['slider_type'] = $res['slider_type'];
            $settings['slider_speed'] = $res['slider_speed'];
            $settings['slider_pause'] = $res['slider_pause'];
            $settings['slider_loop'] = $res['slider_loop'];
            $settings['slider_pause_h'] = $res['slider_pause_h'];
            $settings['slider_pager'] = $res['slider_pager'];
            $settings['slider_controls'] = $res['slider_controls'];
            $settings['slider_auto_controls'] = $res['slider_auto_controls'];
        }

        return $settings;
    }

    public function getSlides()
    {
        $result = Db::getInstance()->ExecuteS(
            'SELECT `id_product`
            FROM '._DB_PREFIX_.'tmproductsslider_item
            WHERE `id_shop` ='.(int)$this->context->shop->id.'
            AND `slide_status` = 1
            ORDER BY `slide_order` ASC'
        );

        if (!$result) {
            return false;
        }

        $slides = array();
        $i = 0;

        foreach ($result as $slide) {
            $reduction_type = Db::getInstance()->getValue(
                'SELECT `reduction_type`
                FROM '._DB_PREFIX_.'specific_price
                WHERE `id_product` = '.(int)$slide['id_product']
            );

            $reduction_amount = Db::getInstance()->getValue(
                'SELECT `reduction`
                FROM '._DB_PREFIX_.'specific_price
                WHERE `id_product` = '.(int)$slide['id_product']
            );

            $slide_info = new Product($slide['id_product'], false, (int)$this->context->language->id);
            $image = new Image();

            $slides[$i]['id_product'] = $slide['id_product'];
            $slides[$i]['link'] = $slide_info->getLink();
            $slides[$i]['name'] = $slide_info->name;
            $slides[$i]['description'] = $slide_info->description_short;
            $slides[$i]['price'] = $slide_info->getPrice();
            $slides[$i]['price_without_reduction'] = $slide_info->getPriceWithoutReduct();
            $slides[$i]['reduction_type'] = $reduction_type;
            $slides[$i]['reduction_amount'] = $reduction_amount;
            $slides[$i]['features'] = $slide_info->getFrontFeatures((int)$this->context->language->id);
            $slides[$i]['on_sale'] = $slide_info->on_sale;
            $slides[$i]['image'] = $image->getCover($slide['id_product']);
            $slides[$i]['show_price'] = $slide_info->show_price;

            $i++;
        }

        return $slides;
    }

    public function getContent()
    {
        $this->_html = '';

        if (Tools::isSubmit('submitSlider')) {
            if (count(Shop::getContextListShopID()) > 1) {
                $shops = Shop::getContextListShopID();
                if (empty($shops)) {
                    return false;
                }
                foreach ($shops as $id_shop) {
                    $this->setSliderSettings($id_shop);
                }
            } else {
                $this->setSliderSettings((int)$this->context->shop->id);
            }
        }

        return '<div class="bootstrap">'.$this->_html.'</div>'.$this->displayForm();
    }

    public function getShopName($id_shop)
    {
        $shop_name = new Shop($id_shop);
        return $shop_name->name;
    }

    public function displayForm()
    {
        $items_list = array();
        $shop_name = array();

        $shops = Shop::getContextListShopID();
        if (empty($shops)) {
            return false;
        }
        foreach ($shops as $id_shop) {
            $items_list[] = TMProductSlider::generateAdminList($id_shop);
            $shop_name[] = $this->getShopName($id_shop);
        }

        $this->context->smarty->assign(
            'admin_list',
            array(
                'lists' => $items_list,
                'shop_name' => $shop_name,
                'theme_url' => $this->context->link->getAdminLink('AdminTMProductsSlider')
            )
        );

        $this->context->smarty->assign(
            'settings',
            $this->getSliderSettings()
        );

        $this->context->controller->addJqueryUI('ui.sortable');
        $this->context->controller->addJS($this->_path.'views/js/admin.js');
        return $this->display(__FILE__, 'views/templates/admin/tmproductsslider.tpl');
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/admin.css');
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addJqueryPlugin(array('bxslider'));
        $this->context->controller->addJS($this->_path.'views/js/tmproductsslider.js');
        $this->context->controller->addCSS($this->_path.'views/css/tmproductsslider.css');
    }

    public function hookDisplayTopColumn()
    {
        $this->context->smarty->assign('slides', $this->getSlides());
        $this->context->smarty->assign('settings', $this->getSliderSettings());

        return $this->display(__FILE__, 'tmproductsslider.tpl');
    }

    public function hookDisplayTop()
    {
        return $this->hookDisplayTopColumn();
    }
}
