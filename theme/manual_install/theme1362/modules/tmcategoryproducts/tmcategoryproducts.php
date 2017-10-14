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

include_once(_PS_MODULE_DIR_.'tmcategoryproducts/classes/CategoryProducts.php');

class Tmcategoryproducts extends Module
{
    protected $config_form = false;
    protected $html = '';
    private $spacer_size = '5';
    private $list = array();

    public function __construct()
    {
        $this->name = 'tmcategoryproducts';
        $this->tab = 'front_office_features';
        $this->version = '0.1.3';
        $this->author = 'TemplateMonster (Alexander Grosul)';
        $this->need_instance = 0;

        $this->bootstrap = true;
        $this->module_key = '79d1fb476ec1f172e5d27aef1e759021';

        parent::__construct();

        $this->displayName = $this->l('TM Category Products');
        $this->description = $this->l('This module displays category products on homepage');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook('displayHome')
            && $this->registerHook('displayHomeTab')
            && $this->registerHook('displayHomeTabContent')
            && $this->registerHook('displayTopColumn')
            && Configuration::updateValue('TM_CPS_CAROUSEL_NB', 4)
            && Configuration::updateValue('TM_CPS_CAROUSEL_SLIDE_WIDTH', 180)
            && Configuration::updateValue('TM_CPS_CAROUSEL_SLIDE_MARGIN', 20)
            && Configuration::updateValue('TM_CPS_CAROUSEL_AUTO', false)
            && Configuration::updateValue('TM_CPS_CAROUSEL_ITEM_SCROLL', 1)
            && Configuration::updateValue('TM_CPS_CAROUSEL_SPEED', 500)
            && Configuration::updateValue('TM_CPS_CAROUSEL_AUTO_PAUSE', 3000)
            && Configuration::updateValue('TM_CPS_CAROUSEL_RANDOM', false)
            && Configuration::updateValue('TM_CPS_CAROUSEL_LOOP', true)
            && Configuration::updateValue('TM_CPS_CAROUSEL_HIDE_CONTROL', true)
            && Configuration::updateValue('TM_CPS_CAROUSEL_PAGER', false)
            && Configuration::updateValue('TM_CPS_CAROUSEL_CONTROL', false)
            && Configuration::updateValue('TM_CPS_CAROUSEL_AUTO_CONTROL', false)
            && Configuration::updateValue('TM_CPS_CAROUSEL_AUTO_HOVER', true);
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        if (!Configuration::deleteByName('TM_CPS_CAROUSEL_NB')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_SLIDE_WIDTH')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_SLIDE_MARGIN')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_AUTO')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_ITEM_SCROLL')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_SPEED')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_AUTO_PAUSE')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_RANDOM')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_LOOP')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_HIDE_CONTROL')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_PAGER')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_CONTROL')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_AYTO_CONTROL')
            || !Configuration::deleteByName('TM_CPS_CAROUSEL_AUTO_HOVER')
            || !parent::uninstall()) {
                return false;
        }

        return true;
    }

    public function getContent()
    {
        $output = '';

        if (((bool)Tools::isSubmit('submitTmcategoryproductsModule')) == true) {
            $output .= $this->updateTab();
        }

        if (Tools::isSubmit('deletecategoryproducts')) {
            $output .= $this->deleteTab();
        }

        if (Tools::isSubmit('statuscategoryproducts')) {
            $output .= $this->updateStatusTab();
        }

        if (Tools::isSubmit('use_carouselcategoryproducts')) {
            $output .= $this->updateStatusCarousel();
        }

        if (Tools::isSubmit('submitSettingsForm')) {
            $output .= $this->updateSettingsFieldsValues();
        }

        if (Tools::getIsset('updatecategoryproducts') || Tools::getValue('updatecategoryproducts')) {
            $output .= $this->renderForm();
        } elseif (Tools::isSubmit('addcategoryproducts')) {
            $output .= $this->renderForm();
        } elseif (Tools::isSubmit('updateSettings')) {
            $output .= $this->renderSettingsForm();
        } else {
            if (!$this->getWarningMultishopHtmlSlider()) {
                $output .= $this->renderTabList();
                $output .= $this->renderTabList(true);
                $output .= $this->renderConfigButtons();
            } else {
                $output .= $this->getWarningMultishopHtmlSlider();
            }
        }

        return $output;
    }

    protected function renderConfigButtons()
    {
        $fields_form = array(
            'form' => array(
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-plus',
                        'title' => $this->l('Add new item'),
                        'type' => 'submit',
                        'name' => 'addcategoryproducts',
                    ),
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-cogs',
                        'title' => $this->l('Settings'),
                        'type' => 'submit',
                        'name' => 'updateSettings',
                    ),
                ),
            )
        );

        $helper = new HelperForm();

        $helper->show_toolbar = true;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    protected function renderForm()
    {
        $this->getCategoriesList();

        $fields_form = array(
            'form' => array(
                'legend' => array(
                'title' => ((int)Tools::getValue('id_tab')
                                ? $this->l('Update tab')
                                : $this->l('Add tab')),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select category'),
                        'name' => 'category',
                        'options' => array(
                            'query' => $this->list,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'desc' => $this->l('Number of products to display'),

                        'name' => 'num',
                        'label' => $this->l('Number of products to display'),
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'mode',
                        'label' => $this->l('Mode'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => '0',
                                    'name' => $this->l('Tab')),
                                array(
                                    'id' => '1',
                                    'name' => $this->l('Block')),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Use carousel'),
                        'name' => 'use_carousel',
                        'is_bool' => true,
                        'desc' => $this->l('Use carousel for this block'),
                        'class' => 'my-class',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Status'),
                        'name' => 'status',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        if (Tools::getIsset('updatecategoryproducts') && (int)Tools::getValue('id_tab') > 0) {
            $fields_form['form']['input'][] = array(
                'type' => 'hidden',
                'name' => 'id_tab',
                'value' => (int)Tools::getValue('id_tab')
            );
        }

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTmcategoryproductsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    protected function getConfigFormValues()
    {
        if (Tools::getIsset('updatecategoryproducts') && (int)Tools::getValue('id_tab') > 0) {
            $tab = new CategoryProducts((int)Tools::getValue('id_tab'));
        } else {
            $tab = new CategoryProducts();
        }

        $fields_values = array(
            'id_tab' => Tools::getValue('id_tab'),
            'category' => Tools::getValue('category', $tab->category),
            'num' => Tools::getValue('num', $tab->num),
            'mode' => Tools::getValue('mode', $tab->mode),
            'status' => Tools::getValue('status', $tab->status),
            'use_carousel' => Tools::getValue('use_carousel', $tab->use_carousel),
        );

        return $fields_values;
    }

    public function renderTabList($tab = false)
    {
        if (!$tabs = $this->getTabList($tab)) {
            $tabs = array();
        }

        $fields_list = array(
            'name' => array(
                'title' => (!$tab?$this->l('Tab category'):$this->l('Block category')),
                'type' => 'text',
            ),
            'num' => array(
                'title' => $this->l('Products to show'),
                'type' => 'text',
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'type' => 'bool',
                'align' => 'center',
                'active' => 'status',
            )
        );

        if ($tab) {
            $fields_list['use_carousel'] = array(
                                                'title'        => $this->l('Use carousel'),
                                                'type'        => 'bool',
                                                'align'        => 'center',
                                                'active'    => 'use_carousel');
        }

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id_tab';
        $helper->table = 'categoryproducts';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->title = (!$tab?$this->l('Tabs list'):$this->l('Blocks list'));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper->generateList($tabs, $fields_list);
    }

    public function renderSettingsForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Visible items'),
                        'name' => 'TM_CPS_CAROUSEL_NB',
                        'class' => 'fixed-width-xs'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Items scroll'),
                        'name' => 'TM_CPS_CAROUSEL_ITEM_SCROLL',
                        'class' => 'fixed-width-xs'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Slide Width'),
                        'name' => 'TM_CPS_CAROUSEL_SLIDE_WIDTH',
                        'class' => 'fixed-width-xs'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Slide Margin'),
                        'name' => 'TM_CPS_CAROUSEL_SLIDE_MARGIN',
                        'class' => 'fixed-width-xs'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Auto scroll'),
                        'name' => 'TM_CPS_CAROUSEL_AUTO',
                        'desc' => $this->l('Use auto scroll in carousel.'),
                        'values' => array(
                                    array(
                                        'id' => 'active_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled')
                                    ),
                                    array(
                                        'id' => 'active_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled')
                                    )
                                ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Carousel speed'),
                        'name' => 'TM_CPS_CAROUSEL_SPEED',
                        'class' => 'fixed-width-xs',
                        'desc' => 'Slide transition duration (in ms)'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pause'),
                        'name' => 'TM_CPS_CAROUSEL_AUTO_PAUSE',
                        'class' => 'fixed-width-xs',
                        'desc' => 'The amount of time (in ms) between each auto transition'
                    ),

                    array(
                        'type' => 'switch',
                        'label' => $this->l('Random'),
                        'name' => 'TM_CPS_CAROUSEL_RANDOM',
                        'desc' => $this->l('Start carousel on a random item.'),
                        'values' => array(
                                    array(
                                        'id' => 'active_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled')
                                    ),
                                    array(
                                        'id' => 'active_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled')
                                    )
                                ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Carousel loop'),
                        'name' => 'TM_CPS_CAROUSEL_LOOP',
                        'desc' => $this->l('Show the first slide after the last slide has been reached.'),
                        'values' => array(
                                    array(
                                        'id' => 'active_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled')
                                    ),
                                    array(
                                        'id' => 'active_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled')
                                    )
                                ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Hide control at the end'),
                        'name' => 'TM_CPS_CAROUSEL_HIDE_CONTROL',
                        'desc' => $this->l('Control will be hidden on the last slide.'),
                        'values' => array(
                                    array(
                                        'id' => 'active_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled')
                                    ),
                                    array(
                                        'id' => 'active_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled')
                                    )
                                ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Pager'),
                        'name' => 'TM_CPS_CAROUSEL_PAGER',
                        'desc' => $this->l('Pager settings.'),
                        'values' => array(
                                    array(
                                        'id' => 'active_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled')
                                    ),
                                    array(
                                        'id' => 'active_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled')
                                    )
                                ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Control'),
                        'name' => 'TM_CPS_CAROUSEL_CONTROL',
                        'desc' => $this->l('Prev/Next buttons.'),
                        'values' => array(
                                    array(
                                        'id' => 'active_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled')
                                    ),
                                    array(
                                        'id' => 'active_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled')
                                    )
                                ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Auto control'),
                        'name' => 'TM_CPS_CAROUSEL_AUTO_CONTROL',
                        'desc' => $this->l('Play/Stop buttons.'),
                        'values' => array(
                                    array(
                                        'id' => 'active_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled')
                                    ),
                                    array(
                                        'id' => 'active_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled')
                                    )
                                ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Auto hover'),
                        'name' => 'TM_CPS_CAROUSEL_AUTO_HOVER',
                        'desc' => $this->l('Auto show will pause when mouse hovers over slider.'),
                        'values' => array(
                                    array(
                                        'id' => 'active_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled')
                                    ),
                                    array(
                                        'id' => 'active_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled')
                                    )
                                ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettingsForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='
                                .$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getSettingsFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    protected function getSettingsFieldsValues()
    {
        return array(
            'TM_CPS_CAROUSEL_NB' => Tools::getValue(
                'TM_CPS_CAROUSEL_NB',
                Configuration::get('TM_CPS_CAROUSEL_NB')
            ),
            'TM_CPS_CAROUSEL_SLIDE_WIDTH' => Tools::getValue(
                'TM_CPS_CAROUSEL_SLIDE_WIDTH',
                Configuration::get('TM_CPS_CAROUSEL_SLIDE_WIDTH')
            ),
            'TM_CPS_CAROUSEL_SLIDE_MARGIN' => Tools::getValue(
                'TM_CPS_CAROUSEL_SLIDE_MARGIN',
                Configuration::get('TM_CPS_CAROUSEL_SLIDE_MARGIN')
            ),
            'TM_CPS_CAROUSEL_AUTO' => Tools::getValue(
                'TM_CPS_CAROUSEL_AUTO',
                Configuration::get('TM_CPS_CAROUSEL_AUTO')
            ),
            'TM_CPS_CAROUSEL_ITEM_SCROLL' => Tools::getValue(
                'TM_CPS_CAROUSEL_ITEM_SCROLL',
                Configuration::get('TM_CPS_CAROUSEL_ITEM_SCROLL')
            ),
            'TM_CPS_CAROUSEL_SPEED' => Tools::getValue(
                'TM_CPS_CAROUSEL_SPEED',
                Configuration::get('TM_CPS_CAROUSEL_SPEED')
            ),
            'TM_CPS_CAROUSEL_AUTO_PAUSE' => Tools::getValue(
                'TM_CPS_CAROUSEL_AUTO_PAUSE',
                Configuration::get('TM_CPS_CAROUSEL_AUTO_PAUSE')
            ),
            'TM_CPS_CAROUSEL_RANDOM' => Tools::getValue(
                'TM_CPS_CAROUSEL_RANDOM',
                Configuration::get('TM_CPS_CAROUSEL_RANDOM')
            ),
            'TM_CPS_CAROUSEL_LOOP' => Tools::getValue(
                'TM_CPS_CAROUSEL_LOOP',
                Configuration::get('TM_CPS_CAROUSEL_LOOP')
            ),
            'TM_CPS_CAROUSEL_HIDE_CONTROL' => Tools::getValue(
                'TM_CPS_CAROUSEL_HIDE_CONTROL',
                Configuration::get('TM_CPS_CAROUSEL_HIDE_CONTROL')
            ),
            'TM_CPS_CAROUSEL_PAGER' => Tools::getValue(
                'TM_CPS_CAROUSEL_PAGER',
                Configuration::get('TM_CPS_CAROUSEL_PAGER')
            ),
            'TM_CPS_CAROUSEL_CONTROL' => Tools::getValue(
                'TM_CPS_CAROUSEL_CONTROL',
                Configuration::get('TM_CPS_CAROUSEL_CONTROL')
            ),
            'TM_CPS_CAROUSEL_AUTO_CONTROL' => Tools::getValue(
                'TM_CPS_CAROUSEL_AUTO_CONTROL',
                Configuration::get('TM_CPS_CAROUSEL_AUTO_CONTROL')
            ),
            'TM_CPS_CAROUSEL_AUTO_HOVER' => Tools::getValue(
                'TM_CPS_CAROUSEL_AUTO_HOVER',
                Configuration::get('TM_CPS_CAROUSEL_AUTO_HOVER')
            )
        );
    }

    protected function updateSettingsFieldsValues()
    {
        $form_values = $this->getSettingsFieldsValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    protected function updateTab()
    {
        if ((int)Tools::getValue('id_tab') > 0) {
            $tab = new CategoryProducts((int)Tools::getValue('id_tab'));
        } else {
            $tab = new CategoryProducts();
        }

        $tab->category = (int)Tools::getValue('category');
        $tab->num = (int)Tools::getValue('num');
        $tab->mode = (int)Tools::getValue('mode');
        $tab->status = (int)Tools::getValue('status');
        $tab->use_carousel = (int)Tools::getValue('use_carousel');

        /* Adds */
        if (!Tools::getValue('id_tab')) {
            if (!$tab->add()) {
                return $this->displayError($this->l('The tab could not be added.'));
            }
        } elseif (!$tab->update()) {
            /* Update */
            return $this->displayError($this->l('The tab could not be updated.'));
        }

        return $this->displayConfirmation($this->l('The tab is saved.'));
    }

    protected function deleteTab()
    {
        $tab = new CategoryProducts(Tools::getValue('id_tab'));
        $res = $tab->delete();
        if (!$res) {
            return $this->displayError($this->l('Error occurred when deleting the tab'));
        }
        return $this->displayConfirmation($this->l('Tab successfully deleted'));
    }

    protected function updateStatusTab()
    {
        $tab = new CategoryProducts(Tools::getValue('id_tab'));

        if ($tab->status == 1) {
            $tab->status = 0;
        } else {
            $tab->status = 1;
        }

        if (!$tab->update()) {
            return $this->displayError($this->l('The tab status could not be updated.'));
        }

        return $this->displayConfirmation($this->l('The tab status is successfully updated.'));
    }

    protected function updateStatusCarousel()
    {
        $tab = new CategoryProducts(Tools::getValue('id_tab'));

        if ($tab->use_carousel == 1) {
            $tab->use_carousel = 0;
        } else {
            $tab->use_carousel = 1;
        }

        if (!$tab->update()) {
            return $this->displayError($this->l('The carousel status could not be updated.'));
        }

        return $this->displayConfirmation($this->l('The carousel status is successfully updated.'));
    }

    private function getCategoriesList()
    {
        $category = new Category();
        $this->generateCategoriesOption($category->getNestedCategories((int)Configuration::get('PS_HOME_CATEGORY')));

        return $this->list;
    }

    protected function generateCategoriesOption($categories)
    {
        foreach ($categories as $category) {
            array_push(
                $this->list,
                array(
                    'id' => (int)$category['id_category'],
                    'name' => str_repeat('&nbsp;', $this->spacer_size * (int)$category['level_depth']).$category['name']
                )
            );
            if (isset($category['children']) && !empty($category['children'])) {
                $this->generateCategoriesOption($category['children']);
            }
        }
    }

    private function getTabList($tab = false, $active = false)
    {
        $ext = '';
        $extend = ' AND tcp.`mode` = 0';

        if ($tab) {
            $extend = ' AND tcp.`mode` = 1';
        }

        if ($active) {
            $ext = ' AND tcp.`status` = 1';
        }

        $sql = 'SELECT tcp.*, cl.`name`
                FROM '._DB_PREFIX_.'tmcategoryproducts tcp
                LEFT JOIN '._DB_PREFIX_.'tmcategoryproducts_shop tcps
                ON (tcp.`id_tab` = tcps.`id_tab`)
                LEFT JOIN '._DB_PREFIX_.'category_lang cl
                ON (tcp.`category` = cl.`id_category`)
                WHERE tcps.`id_shop` = '.$this->context->shop->id.'
                AND cl.`id_lang` = '.$this->context->language->id.'
                AND cl.`id_shop` = '.$this->context->shop->id.$extend.$ext;

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return $result;
    }

    private function getTabListCategoriesIds($tab = false)
    {
        $i = 0;
        $result = array();
        if (!$tab_list = $this->getTabList($tab, true)) {
            return false;
        }

        foreach ($tab_list as $category_id) {
            $result[$i]['id'] = $category_id['category'];
            $result[$i]['num'] = $category_id['num'];
            $result[$i]['carousel'] = $category_id['use_carousel'];
            $i++;
        }

        return $result;
    }

    private function getWarningMultishopHtmlSlider()
    {
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            return '<p class="alert alert-warning">'.
                        $this->l('You cannot manage this module settings from "All Shops" or "Group Shop" context,
                                    select the store you want to edit').
                    '</p>';
        } else {
            return '';
        }
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') != $this->name) {
            return;
        }
        
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path.'/views/js/tmcategoryproducts_admin.js');
    }

    public function hookHeader()
    {
        $this->context->smarty->assign('settings', array(
            'tm_cps_carousel_nb' => Configuration::get('TM_CPS_CAROUSEL_NB'),
            'tm_cps_carousel_slide_width' => Configuration::get('TM_CPS_CAROUSEL_SLIDE_WIDTH'),
            'tm_cps_carousel_slide_margin' => Configuration::get('TM_CPS_CAROUSEL_SLIDE_MARGIN'),
            'tm_cps_carousel_auto' => Configuration::get('TM_CPS_CAROUSEL_AUTO'),
            'tm_cps_carousel_item_scroll' => Configuration::get('TM_CPS_CAROUSEL_ITEM_SCROLL'),
            'tm_cps_carousel_speed' => Configuration::get('TM_CPS_CAROUSEL_SPEED'),
            'tm_cps_carousel_auto_pause' => Configuration::get('TM_CPS_CAROUSEL_AUTO_PAUSE'),
            'tm_cps_carousel_random' => Configuration::get('TM_CPS_CAROUSEL_RANDOM'),
            'tm_cps_carousel_loop' => Configuration::get('TM_CPS_CAROUSEL_LOOP'),
            'tm_cps_carousel_hide_control' => Configuration::get('TM_CPS_CAROUSEL_HIDE_CONTROL'),
            'tm_cps_carousel_pager' => Configuration::get('TM_CPS_CAROUSEL_PAGER'),
            'tm_cps_carousel_control' => Configuration::get('TM_CPS_CAROUSEL_CONTROL'),
            'tm_cps_carousel_auto_control' => Configuration::get('TM_CPS_CAROUSEL_AUTO_CONTROL'),
            'tm_cps_carousel_auto_hover' => Configuration::get('TM_CPS_CAROUSEL_AUTO_HOVER'),
        ));

        $this->context->controller->addJqueryPlugin(array('bxslider'));
        $this->context->controller->addCSS($this->_path.'/views/css/tmcategoryproducts.css');

        return $this->display($this->_path, '/views/templates/hook/header.tpl');
    }

    public function hookDisplayHome()
    {
        $i = 0;
        $result = array();
        $categories = $this->getTabListCategoriesIds(true);
        if ($categories) {
            foreach ($categories as $category) {
                $cat = new Category($category['id'], (int)$this->context->language->id);
                $result[$i]['id'] = $cat->id;
                $result[$i]['name'] = $cat->name;
                $result[$i]['carousel'] = $category['carousel'];
                $result[$i]['products'] = $cat->getProducts((int)$this->context->language->id, 1, $category['num']);
                $i++;
            }
        }

        $this->context->smarty->assign('blocks', $result);

        return $this->display($this->_path, '/views/templates/hook/tmcaregoryproducts-home.tpl');
    }

    public function hookDisplayHomeTab()
    {
        $i = 0;
        $result = array();
        $categories = $this->getTabListCategoriesIds(false);
        if ($categories) {
            foreach ($categories as $category) {
                $cat = new Category($category['id'], (int)$this->context->language->id);
                $result[$i]['id'] = $cat->id;
                $result[$i]['name'] = $cat->name;
                $i++;
            }
        }

        $this->context->smarty->assign('headings', $result);

        return $this->display($this->_path, '/views/templates/hook/tmcaregoryproducts-tab.tpl');
    }

    public function hookDisplayHomeTabContent()
    {
        $i = 0;
        $result = array();
        $categories = $this->getTabListCategoriesIds(false);
        if ($categories) {
            foreach ($categories as $category) {
                $cat = new Category($category['id'], (int)$this->context->language->id);
                $result[$i]['id'] = $cat->id;
                $result[$i]['name'] = $cat->name;
                $result[$i]['products'] = $cat->getProducts((int)$this->context->language->id, 1, $category['num']);
                $i++;
            }
        }

        $this->context->smarty->assign('items', $result);

        return $this->display($this->_path, '/views/templates/hook/tmcaregoryproducts-content.tpl');
    }
}
