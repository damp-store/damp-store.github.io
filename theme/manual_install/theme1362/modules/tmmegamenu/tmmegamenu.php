<?php
/**
* 2002-2015 TemplateMonster
*
* TM Mega Menu
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

require_once (dirname(__FILE__).'/classes/MegaMenu.php');
require_once (dirname(__FILE__).'/classes/MegaMenuHtml.php');
require_once (dirname(__FILE__).'/classes/MegaMenuLink.php');
require_once (dirname(__FILE__).'/classes/MegaMenuBanner.php');
require_once (dirname(__FILE__).'/classes/MegaMenuVideo.php');
require_once (dirname(__FILE__).'/classes/MegaMenuMap.php');

class Tmmegamenu extends Module
{
    private $menu = '';
    private $pattern = '/^([A-Z_]*)[0-9]+/';
    private $spacer_size = '5';
    private $page_name = '';
    private $megamenu_items = '';
    private $user_groups;
    protected $config_form = false;
    protected $has_map = false;

    public function __construct()
    {
        $this->name = 'tmmegamenu';
        $this->tab = 'front_office_features';
        $this->version = '1.6.0';
        $this->author = 'TemplateMonster (Alexander Grosul)';
        $this->default_language = Language::getLanguage(Configuration::get('PS_LANG_DEFAULT'));
        $this->languages = Language::getLanguages(false);

        $this->bootstrap = true;
        $this->secure_key = Tools::encrypt($this->name);
        $this->module_key = '832c9a16e83ca35e673a68c268c4607e';

        parent::__construct();

        $this->displayName = $this->l('TM Mega Menu');
        $this->description = $this->l('Mega Menu by Template Monster');

        $this->confirmUninstall = $this->l('Are you sure that you want to delete all your info?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        $this->clearCache();
        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook('displayTop')
            && $this->registerHook('actionObjectCategoryUpdateAfter')
            && $this->registerHook('actionObjectCategoryDeleteAfter')
            && $this->registerHook('actionObjectCategoryAddAfter')
            && $this->registerHook('actionObjectCmsUpdateAfter')
            && $this->registerHook('actionObjectCmsDeleteAfter')
            && $this->registerHook('actionObjectCmsAddAfter')
            && $this->registerHook('actionObjectSupplierUpdateAfter')
            && $this->registerHook('actionObjectSupplierDeleteAfter')
            && $this->registerHook('actionObjectSupplierAddAfter')
            && $this->registerHook('actionObjectManufacturerUpdateAfter')
            && $this->registerHook('actionObjectManufacturerDeleteAfter')
            && $this->registerHook('actionObjectManufacturerAddAfter')
            && $this->registerHook('actionObjectProductUpdateAfter')
            && $this->registerHook('actionObjectProductDeleteAfter')
            && $this->registerHook('actionObjectProductAddAfter')
            && $this->registerHook('categoryUpdate')
            && $this->createAjaxController();
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
        $this->clearCache();
        return parent::uninstall()
            && $this->removeAjaxContoller()
            && $this->refreshCustomCssFolder();
    }

    public function createAjaxController()
    {
        $tab = new Tab();
        $tab->active = 1;
        $languages = Language::getLanguages(false);
        if (is_array($languages)) {
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = 'tmmegamenu';
            }
        }
        $tab->class_name = 'AdminTMMegaMenu';
        $tab->module = $this->name;
        $tab->id_parent = - 1;
        return (bool)$tab->add();
    }

    private function removeAjaxContoller()
    {
        if ($tab_id = (int)Tab::getIdFromClassName('AdminTMMegaMenu')) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }
        return true;
    }

    protected function refreshCustomCssFolder()
    {
        $dir_files = Tools::scandir(Tmmegamenu::stylePath(), 'css');

        foreach ($dir_files as $file) {
            @unlink(Tmmegamenu::stylePath().$file);
        }

        return true;
    }

    public function getContent()
    {
        $output = '';
        $check_item_fields = '';
        $check_html_fields = '';
        $check_link_fields = '';
        $check_banner_fields = '';
        $check_video_fields = '';
        $check_map_fields = '';
        $megamenu = new MegaMenu();
        $update_cache = false;

        if ($message = $this->getWarningMultishop()) {
            return $message;
        }

        $this->setTemplateVariables(); // set loaded template variables

        // update main items
        if (Tools::isSubmit('updateItem') || Tools::isSubmit('updateItemStay')) {
            if (!$check_item_fields = $this->preUpdateItem()) {
                $item_id = $megamenu->updateItem();
                $this->context->smarty->assign('item', $megamenu->getItem($item_id));
                $this->parseStyle($megamenu->getItemUniqueCode($item_id));
                $this->clearCache();
                if (!Tools::isSubmit('updateItemStay')) {
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink(
                            'AdminModules',
                            true
                        ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
                    );
                } else {
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink(
                            'AdminModules',
                            true
                        ).'&configure='.$this->name.'&tab_module='
                        .$this->tab.'&module_name='.$this->name.'&editItem&id_item='.$item_id
                    );
                }
            } else {
                if ($item = Tools::getValue('id_item')) {
                    $this->context->smarty->assign('item', $megamenu->getItem($item));
                    $this->parseStyle($megamenu->getItemUniqueCode($item_id));
                    $this->clearCache();
                }
                $output .= $check_item_fields;
                $output .= $this->display($this->local_path, 'views/templates/admin/additem.tpl');
            }
        } elseif (Tools::getIsset('updateItemStatus')) {// update item status from table
            if (!$megamenu->changeItemStatus()) {
                $output .= $this->displayError($this->l('Can\'t update item status.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Status successfully updated.'));
            }
        } elseif (Tools::getIsset('deleteItem')) {
            if (!$megamenu->deleteItem()) {
                $output .= $this->displayError($this->l('Can\'t delete item.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Item successfully deleted.'));
            }
        } elseif (Tools::getIsset('editItem')) {
            if (!$megamenu->getItem()) {
                $output .= $this->displayError($this->l('Can\'t load item.'));
            } else {
                $this->context->smarty->assign('item', $megamenu->getItem());
                $this->parseStyle($megamenu->getItemUniqueCode());
                $this->clearCache();
            }
        }

        // Custom HTML manager
        if (Tools::isSubmit('updateHtml') || Tools::isSubmit('updateHtmlStay')) {
            if (!$check_html_fields = $this->preUpdateHTML()) {
                if ($html_id = $this->addHTML()) {
                    $this->clearCache();
                    if (Tools::isSubmit('updateHtmlStay')) {
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink(
                                'AdminModules',
                                true
                            ).'&configure='.$this->name.'&tab_module='
                            .$this->tab.'&module_name='.$this->name.'&editHtml&id_item='.$html_id
                        );
                    }
                } else {
                    $output .= $this->displayError($this->l('The HTML can\'t be saved.'));
                }
            } else {
                $this->clearCache();
                $output .= $check_html_fields;
                $output .= $this->renderAddHtml((int)Tools::getValue('id_item'));
            }
        } elseif (Tools::getIsset('deleteHtml')) {
            $html = new MegaMenuHTML((int)Tools::getValue('id_item'));
            if (!$html->delete()) {
                $output .= $this->displayError($this->l('Can\'t delete HTML item.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('HTML item successfully deleted.'));
            }
        }

        // Custom Links manager
        if (Tools::isSubmit('updateLink') || Tools::isSubmit('updateLinkStay')) {
            if (!$check_link_fields = $this->preUpdateLink()) {
                if ($link_id = $this->addLink()) {
                    $this->clearCache();
                    if (Tools::isSubmit('updateLinkStay')) {
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink(
                                'AdminModules',
                                true
                            ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='
                            .$this->name.'&editLink&id_item='.$link_id
                        );
                    }
                } else {
                    $output .= $this->displayError($this->l('The Link can\'t be saved.'));
                }
            } else {
                $this->clearCache();
                $output .= $check_link_fields;
                $output .= $this->renderAddLink((int)Tools::getValue('id_item'));
            }
        } elseif (Tools::getIsset('deleteLink')) {
            $link = new MegaMenuLink((int)Tools::getValue('id_item'));
            if (!$link->delete()) {
                $output .= $this->displayError($this->l('Can\'t delete Link.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Link successfully deleted.'));
            }
        }

        // Banners manager
        if (Tools::isSubmit('updateBanner') || Tools::isSubmit('updateBannerStay')) {
            if (!$check_banner_fields = $this->preUpdateBanner()) {
                if ($id_banner = $this->addBanner()) {
                    $this->clearCache();
                    if (Tools::isSubmit('updateBannerStay')) {
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink(
                                'AdminModules',
                                true
                            ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='
                            .$this->name.'&editBanner&id_item='.$id_banner
                        );
                    }
                } else {
                    $output .= $this->displayError($this->l('The Banner can\'t be saved.'));
                }
            } else {
                $this->clearCache();
                $output .= $check_banner_fields;
                $output .= $this->renderAddBanner((int)Tools::getValue('id_item'));
            }
        } elseif (Tools::getIsset('deleteBanner')) {
            $banner = new MegaMenuBanner((int)Tools::getValue('id_item'));
            if (!$banner->delete()) {
                $output .= $this->displayError($this->l('Can\'t delete Banner.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Banner successfully deleted.'));
            }
        }

        // Video manager
        if (Tools::isSubmit('updateVideo') || Tools::isSubmit('updateVideoStay')) {
            if (!$check_video_fields = $this->preUpdateVideo()) {
                if ($video_id = $this->addVideo()) {
                    $this->clearCache();
                    if (Tools::isSubmit('updateVideoStay')) {
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink(
                                'AdminModules',
                                true
                            ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='
                            .$this->name.'&editVideo&id_item='.$video_id
                        );
                    }
                } else {
                    $output .= $this->displayError($this->l('The Video can\'t be saved.'));
                }
            } else {
                $this->clearCache();
                $output .= $check_video_fields;
                $output .= $this->renderAddVideo((int)Tools::getValue('id_item'));
            }
        } elseif (Tools::getIsset('deleteVideo')) {
            $video = new MegaMenuVideo((int)Tools::getValue('id_item'));
            if (!$video->delete()) {
                $output .= $this->displayError($this->l('Can\'t delete Video.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Video successfully deleted.'));
            }
        }

        // Map manager
        if (Tools::isSubmit('updateMap') || Tools::isSubmit('updateMapStay')) {
            if (!$check_map_fields = $this->preUpdateMap()) {
                if ($map_id = $this->addMap()) {
                    $this->clearCache();
                    if (Tools::isSubmit('updateMapStay')) {
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink(
                                'AdminModules',
                                true
                            ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='
                            .$this->name.'&editMap&id_item='.$map_id
                        );
                    }
                } else {
                    $output .= $this->displayError($this->l('The Map can\'t be saved.'));
                }
            } else {
                $this->clearCache();
                $output .= $check_map_fields;
                $output .= $this->renderAddMap((int)Tools::getValue('id_item'));
            }
        } elseif (Tools::getIsset('deleteMap')) {
            $map = new MegaMenuMap((int)Tools::getValue('id_item'));
            if (!$map->delete()) {
                $output .= $this->displayError($this->l('Can\'t delete Map.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Map successfully deleted.'));
            }
        }

        $this->setTemplateVariables(); // refresh template  variables after changing

        if ((Tools::getIsset('addItem') || Tools::getIsset('editItem')) && !$check_item_fields) {
            $output .= $this->display($this->local_path, 'views/templates/admin/additem.tpl');
        } elseif ((Tools::getIsset('addHtml') || Tools::getIsset('editHtml')) && !$check_html_fields) {
            $output .= $this->renderAddHtml();
        } elseif ((Tools::getIsset('addLink') || Tools::getIsset('editLink')) && !$check_link_fields) {
            $output .= $this->renderAddLink();
        } elseif ((Tools::getIsset('addBanner') || Tools::getIsset('editBanner')) && !$check_banner_fields) {
            $output .= $this->renderAddBanner();
        } elseif ((Tools::getIsset('addVideo') || Tools::getIsset('editVideo')) && !$check_video_fields) {
            $output .= $this->renderAddVideo();
        } elseif ((Tools::getIsset('addMap') || Tools::getIsset('editMap')) && !$check_map_fields) {
            $output .= $this->renderAddMap();
        } else {
            if (!$check_item_fields && !$check_html_fields && !$check_link_fields
                && !$check_banner_fields && !$check_video_fields && !$check_map_fields) {
                $this->parseStyle('megamenu_custom_styles');
                $this->clearCache();
                $output .= $this->display($this->local_path, 'views/templates/admin/list.tpl');
            }
        }

        return $output;
    }

    /*****
    ******    Set/refresh all necessary variables for templates
    *****/
    protected function setTemplateVariables()
    {
        $megamenu = new MegaMenu();
        $megamenuhtml = new MegaMenuHtml();
        $megamenulink = new MegaMenuLink();
        $megamenubanner = new MegaMenuBanner();
        $megamenuvideo = new MegaMenuVideo();
        $megamenumap = new MegaMenuMap();

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('languages', $this->languages);
        $this->context->smarty->assign('default_language', $this->default_language);
        $this->context->smarty->assign('categTree', $this->initCategoriesQuery());
        $this->context->smarty->assign('cmsCatTree', $megamenu->getCMSCategories(true));
        $this->context->smarty->assign('tabs', $megamenu->getList());
        $this->context->smarty->assign('html_items', $megamenuhtml->getHtmlList());
        $this->context->smarty->assign('links', $megamenulink->getLinksList());
        $this->context->smarty->assign('banners', $megamenubanner->getBannersList());
        $this->context->smarty->assign('videos', $megamenuvideo->getVideosList());
        $this->context->smarty->assign('maps', $megamenumap->getMapsList());
        $this->context->smarty->assign('megamenu', $this->getMegamenuItems());
        $this->context->smarty->assign('image_baseurl', $this->_path.'images/');
        $this->context->smarty->assign('theme_url', $this->context->link->getAdminLink('AdminTMMegaMenu'));

        $this->context->smarty->assign('option_select', str_replace('\'', '\\\'', $this->renderChoicesSelect()));
        $this->context->smarty->assign('option_selected', $this->makeMenuOption());

        // buttons url
        $this->context->smarty->assign(
            'url_enable',
            $this->context->link->getAdminLink(
                'AdminModules',
                true
            )
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
        );

        $this->context->smarty->assign('branche_tpl_path', $this->local_path.'views/templates/admin/tree-branch.tpl');
    }

    /**
    *    Check if all fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateItem()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('name_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('Item name is required.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
    *    Check if all HTML fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateHTML()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('HTML item name is required.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
    *    Check if all link fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateLink()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Link name is required.');
        }
        if (Tools::isEmpty(Tools::getValue('url_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Link URL is required.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
    *    Check if all banner fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateBanner()
    {
        $errors = array();
        $imageexists = @getimagesize($_FILES['image_'.$this->default_language['id_lang']]['tmp_name']);
        $old_image = Tools::getValue('old_image');

        if (!$old_image && !$imageexists) {
            $errors[] = $this->l('The Banner image is required.');
        }
        if (Tools::isEmpty(Tools::getValue('url_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Banner URL is required.');
        }
        if (Tools::isEmpty(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Banner name is required.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
    *    Check if all video fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateVideo()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Video name is required.');
        }

        if (Tools::isEmpty(Tools::getValue('url_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Video URL is required.');
        }

        foreach ($this->languages as $lang) {
            if (!Tools::isEmpty(Tools::getValue('url_'.$lang['id_lang'])) && !$this->getVideoType(Tools::getValue('url_'.$lang['id_lang']))) {
                $errors[] = sprintf($this->l('%s - the Video URL is unknown.'), $lang['iso_code']);
            }
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
    *    Check if all map fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateMap()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Map name is required.');
        }

        if (Tools::isEmpty(Tools::getValue('latitude'))) {
            $errors[] = $this->l('The Map latitude is required.');
        } elseif (!Validate::isCoordinate(Tools::getValue('latitude'))) {
            $errors[] = $this->l('Bad Map latitude.');
        }

        if (Tools::isEmpty(Tools::getValue('longitude'))) {
            $errors[] = $this->l('The Map longitude is required.');
        } elseif (!Validate::isCoordinate(Tools::getValue('longitude'))) {
            $errors[] = $this->l('Bad Map longitude.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /*****
    ******    Add new html
    ******    @return html id if true or false
    *****/
    protected function addHTML()
    {
        $errors = array();
        /* Sets ID if needed */
        if (Tools::getValue('id_item')) {
            $html = new MegaMenuHTML((int)Tools::getValue('id_item'));
            if (!Validate::isLoadedObject($html)) {
                $errors[] .= $this->displayError($this->l('Invalid HTML ID'));
                return false;
            }
        } else {
            $html = new MegaMenuHTML();
        }

        $html->id_shop = (int)$this->context->shop->id;
        $html->specific_class = pSQL(Tools::getValue('specific_class'));

        /* Sets each langue fields */
        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            $html->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
            $html->content[$language['id_lang']] = Tools::getValue('content_'.$language['id_lang']);
        }

        /* Processes if no errors  */
        if (!$errors) {
            /* Adds */
            if (!Tools::getValue('id_item')) {
                if (!$html->add()) {
                    $errors[] = $this->displayError($this->l('The HTML could not be added.'));
                }
            } elseif (!$html->update()) {
                /* Update */
                $errors[] = $this->displayError($this->l('The HTML could not be updated.'));
            }
            $this->clearCache();

            if (!$errors) {
                return (int)$html->id;
            }
            return false;
        }
    }

    /*****
    ******    Add new custom link
    ******    @return link id if true or false
    *****/
    protected function addLink()
    {
        $errors = array();
        /* Sets ID if needed */
        if (Tools::getValue('id_item')) {
            $link = new MegaMenuLink((int)Tools::getValue('id_item'));
            if (!Validate::isLoadedObject($link)) {
                $errors[] .= $this->displayError($this->l('Invalid link ID'));
                return false;
            }
        } else {
            $link = new MegaMenuLink();
        }

        $link->id_shop = (int)$this->context->shop->id;
        $link->specific_class = pSQL(Tools::getValue('specific_class'));
        $link->blank = (bool)Tools::getValue('blank');

        /* Sets each langue fields */
        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            $link->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
            $link->url[$language['id_lang']] = Tools::getValue('url_'.$language['id_lang']);
        }

        /* Processes if no errors  */
        if (!$errors) {
            /* Adds */
            if (!Tools::getValue('id_item')) {
                if (!$link->add()) {
                    $errors[] = $this->displayError($this->l('The link could not be added.'));
                }
            } elseif (!$link->update()) {
                /* Update */
                $errors[] = $this->displayError($this->l('The link could not be updated.'));
            }
            $this->clearCache();
            if (!$errors) {
                return (int)$link->id;
            }
            return false;
        }
    }

    /*****
    ******    Add new banner whith images
    ******    @return banner id if true or false
    *****/
    protected function addBanner()
    {
        $errors = array();
        /* Sets ID if needed */
        if (Tools::getValue('id_item')) {
            $banner = new MegaMenuBanner((int)Tools::getValue('id_item'));
            if (!Validate::isLoadedObject($banner)) {
                $errors[] .= $this->displayError($this->l('Invalid banner ID'));
                return false;
            }
        } else {
            $banner = new MegaMenuBanner();
        }

        $banner->id_shop = (int)$this->context->shop->id;
        $banner->active = (int)Tools::getValue('active_slide');
        $banner->blank = (int)Tools::getValue('blank');
        $banner->specific_class = pSQL(Tools::getValue('specific_class'));

        /* Sets each langue fields */
        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            $banner->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
            $banner->url[$language['id_lang']] = Tools::getValue('url_'.$language['id_lang']);
            $banner->public_title[$language['id_lang']] = Tools::getValue('public_title_'.$language['id_lang']);
            $banner->description[$language['id_lang']] = Tools::getValue('description_'.$language['id_lang']);

            /* Uploads image and sets banner */
            $type = Tools::strtolower(Tools::substr(strrchr($_FILES['image_'.$language['id_lang']]['name'], '.'), 1));
            $imagesize = @getimagesize($_FILES['image_'.$language['id_lang']]['tmp_name']);
            if (isset($_FILES['image_'.$language['id_lang']])
                && isset($_FILES['image_'.$language['id_lang']]['tmp_name'])
                && !empty($_FILES['image_'.$language['id_lang']]['tmp_name'])
                && !empty($imagesize)
                && in_array(
                    Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)),
                    array('jpg', 'gif', 'jpeg', 'png')
                )
                && in_array($type, array('jpg', 'gif', 'jpeg', 'png'))) {
                $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                $salt = sha1(microtime());
                if ($error = ImageManager::validateUpload($_FILES['image_'.$language['id_lang']])) {
                    $errors[] = $error;
                } elseif (!$temp_name || !move_uploaded_file($_FILES['image_'.$language['id_lang']]['tmp_name'], $temp_name)) {
                    return false;
                } elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/images/'.$salt.'_'.$_FILES['image_'.$language['id_lang']]['name'], null, null, $type)) {
                    $errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
                }

                if (isset($temp_name)) {
                    @unlink($temp_name);
                }
                $banner->image[$language['id_lang']] = $salt.'_'.$_FILES['image_'.$language['id_lang']]['name'];
            } elseif (Tools::getValue('image_old_'.$language['id_lang']) != '') {
                $banner->image[$language['id_lang']] = Tools::getValue('image_old_'.$language['id_lang']);
            }
        }

        /* Processes if no errors  */
        if (!$errors) {
            /* Adds */
            if (!Tools::getValue('id_item')) {
                if (!$banner->add()) {
                    $errors[] = $this->displayError($this->l('The slide could not be added.'));
                }
            } elseif (!$banner->update()) {
                /* Update */
                $errors[] = $this->displayError($this->l('The slide could not be updated.'));
            }

            $this->clearCache();

            if (!$errors) {
                return $banner->id;
            }
            return false;
        }
    }

    /*****
    ******    Add new map whith marker
    ******    @return map id if true or false
    *****/
    protected function addMap()
    {
        $errors = array();
        /* Sets ID if needed */
        if (Tools::getValue('id_item')) {
            $map = new MegaMenuMap((int)Tools::getValue('id_item'));
            if (!Validate::isLoadedObject($map)) {
                $errors[] .= $this->displayError($this->l('Invalid map ID'));
                return false;
            }
        } else {
            $map = new MegaMenuMap();
        }

        $map->id_shop = (int)$this->context->shop->id;
        $map->latitude = pSQL(Tools::getValue('latitude'));
        $map->longitude = pSQL(Tools::getValue('longitude'));
        $map->scale = (int)Tools::getValue('scale');

        if (Tools::isEmpty(Tools::getValue('old_marker'))) {
            $map->marker = '';
        }

        if (isset($_FILES['marker']) && isset($_FILES['marker']['tmp_name']) && !empty($_FILES['marker']['tmp_name'])) {
            $random_name = Tools::passwdGen();
            if ($error = ImageManager::validateUpload($_FILES['marker'])) {
                $errors[] = $error;
            } elseif (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS')) || !move_uploaded_file($_FILES['marker']['tmp_name'], $tmp_name)) {
                return false;
            } elseif (!ImageManager::resize($tmp_name, dirname(__FILE__).'/img/markers/marker-'.$random_name.'-'.(int)$map->id_shop.'.jpg', 64, 64, 'png')) {
                $errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
            }
            unlink($tmp_name);
            $map->marker = 'marker-'.$random_name.'-'.(int)$map->id_shop.'.jpg';
        }

        /* Sets each langue fields */
        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            $map->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
            $map->description[$language['id_lang']] = Tools::getValue('description_'.$language['id_lang']);
        }

        /* Processes if no errors  */
        if (!$errors) {
            /* Adds */
            if (!Tools::getValue('id_item')) {
                if (!$map->add()) {
                    $errors[] = $this->displayError($this->l('The map could not be added.'));
                }
            } elseif (!$map->update()) {
                /* Update */
                $errors[] = $this->displayError($this->l('The map could not be updated.'));
            }

            $this->clearCache();

            if (!$errors) {
                return (int)$map->id;
            }
            return false;
        }
    }

    /*****
    ******    Add Video
    ******    @return Video id if true or false
    *****/
    protected function addVideo()
    {
        $errors = array();
        /* Sets ID if needed */
        if (Tools::getValue('id_item')) {
            $video = new MegaMenuVideo((int)Tools::getValue('id_item'));
            if (!Validate::isLoadedObject($video)) {
                $errors[] .= $this->displayError($this->l('Invalid video ID'));
                return false;
            }
        } else {
            $video = new MegaMenuVideo();
        }

        $video->id_shop = (int)$this->context->shop->id;

        /* Sets each langue fields */
        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            $video->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
            $video->url[$language['id_lang']] = Tools::getValue('url_'.$language['id_lang']);
            $video->type[$language['id_lang']] = $this->getVideoType(Tools::getValue('url_'.$language['id_lang']));
        }

        /* Processes if no errors  */
        if (!$errors) {
            /* Adds */
            if (!Tools::getValue('id_item')) {
                if (!$video->add()) {
                    $errors[] = $this->displayError($this->l('The link could not be added.'));
                }
            } elseif (!$video->update()) {
                /* Update */
                $errors[] = $this->displayError($this->l('The link could not be updated.'));
            }

            $this->clearCache();

            if (!$errors) {
                return (int)$video->id;
            }
            return false;
        }
    }

    /*****
    ******    Get category tree
    *****/
    protected function initCategoriesQuery($id_category = false)
    {
        $megamenu = new MegaMenu();
        if (!$id_category) {
            $from_category = Configuration::get('PS_HOME_CATEGORY');
        } else {
            $from_category = $id_category;
        }
        $category = new Category($from_category, $this->context->language->id);

        $result_ids = array();
        $result_parents = array();

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT c.id_parent, c.id_category, c.level_depth, cl.name
            FROM `'._DB_PREFIX_.'category` c
            INNER JOIN `'._DB_PREFIX_.'category_lang` cl
            ON (c.`id_category` = cl.`id_category`
            AND cl.`id_lang` = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('cl').')
            INNER JOIN `'._DB_PREFIX_.'category_shop` cs
            ON (cs.`id_category` = c.`id_category`
            AND cs.`id_shop` = '.(int)$this->context->shop->id.')
            WHERE c.`active` = 1
            ORDER BY `level_depth` ASC');

        foreach ($result as &$row) {
            $result_parents[$row['id_parent']][] = &$row;
            $result_ids[$row['id_category']] = &$row;
        }

        return $megamenu->getTree($result_parents, $result_ids, ($category ? $category->id : null));
    }

    public function renderChoicesSelect()
    {
        $megamenu = new MegaMenu();
        $spacer = str_repeat('&nbsp;', $this->spacer_size);
        if (!Tools::isEmpty(Tools::getValue('id_item'))) {
            $items = $megamenu->getMenuItem((int)Tools::getValue('id_item'));
        } else {
            $items = array();
        }

        $html = '<select multiple="multiple" id="availableItems" class="availible_items" autocomplete="off">';
        $html .= '<optgroup label="'.$this->l('CMS').'">';
        $html .= $this->getCMSOptions(0, 1, $this->context->language->id, $items);
        $html .= '</optgroup>';

        $html .= '<optgroup label="'.$this->l('Supplier').'">';

        $html .= '<option value="ALLSUP0">'.$this->l('All suppliers').'</option>';
        $suppliers = Supplier::getSuppliers(false, $this->context->language->id);
        foreach ($suppliers as $supplier) {
            $html .= '<option value="SUP'.$supplier['id_supplier'].'">'.$spacer.$supplier['name'].'</option>';
        }
        $html .= '</optgroup>';

        $html .= '<optgroup label="'.$this->l('Manufacturer').'">';

        $html .= '<option value="ALLMAN0">'.$this->l('All manufacturers').'</option>';
        $manufacturers = Manufacturer::getManufacturers(false, $this->context->language->id);
        foreach ($manufacturers as $manufacturer) {
            $html .= '<option value="MAN'.$manufacturer['id_manufacturer'].'">'.$spacer.$manufacturer['name'].'</option>';
        }
        $html .= '</optgroup>';

        $shop = new Shop((int)Shop::getContextShopID());
        $html .= '<optgroup label="'.$this->l('Categories').'">';

        $shops_to_get = Shop::getContextListShopID();

        foreach ($shops_to_get as $shop_id) {
            $html .= $this->generateCategoriesOption($megamenu->customGetNestedCategories($shop_id, null, (int)$this->context->language->id, true), $items);
        }
        $html .= '</optgroup>';

        if (Shop::isFeatureActive()) {
            $html .= '<optgroup label="'.$this->l('Shops').'">';
            $shops = Shop::getShopsCollection();
            foreach ($shops as $shop) {
                if (!$shop->setUrl() && !$shop->getBaseURL()) {
                    continue;
                }

                $html .= '<option value="SHOP'.(int)$shop->id.'">'.$spacer.$shop->name.'</option>';
            }
            $html .= '</optgroup>';
        }

        $html .= '<optgroup label="'.$this->l('HTML').'">';
        $new_html = new MegaMenuHtml();
        foreach ($new_html = $new_html->getHtmlList() as $new) {
            $html .= '<option value="HTML'.(int)$new['id_item'].'">'.$spacer.Tools::safeOutput($new['title']).'</option>';
        }

        $html .= '<optgroup label="'.$this->l('Custom Links').'">';
        $links = new MegaMenuLink();
        foreach ($links = $links->getLinksList() as $link) {
            $html .= '<option value="LNK'.(int)$link['id_item'].'">'.$spacer.Tools::safeOutput($link['title']).'</option>';
        }

        $html .= '<optgroup label="'.$this->l('Banners').'">';
        $links = new MegaMenuBanner();
        foreach ($links->getBannersList() as $banner) {
            $html .= '<option value="BNR'.(int)$banner['id_item'].'">'.$spacer.Tools::safeOutput($banner['title']).'</option>';
        }

        $html .= '<optgroup label="'.$this->l('Videos').'">';
        $videos = new MegaMenuVideo();
        foreach ($videos->getVideosList() as $video) {
            $html .= '<option value="VID'.(int)$video['id_item'].'">'.$spacer.Tools::safeOutput($video['title']).'</option>';
        }

        $html .= '<optgroup label="'.$this->l('Maps').'">';
        $maps = new MegaMenuMap();
        foreach ($maps->getMapsList() as $map) {
            $html .= '<option value="MAP'.(int)$map['id_item'].'">'.$spacer.Tools::safeOutput($map['title']).'</option>';
        }

        $html .= '<optgroup label="'.$this->l('Products').'">';
        $html .= '<option value="PRODUCT" style="font-style:italic">'.$spacer.$this->l('Choose product ID (link)').'</option>';
        $html .= '<option value="PRODUCTINFO" style="font-style:italic">'.$spacer.$this->l('Choose product ID (info)').'</option>';
        $html .= '</optgroup>';

        $html .= '</select>';

        return $html;
    }

    protected function makeMenuOption($megamenuitem = '')
    {
        $megamenu = new MegaMenu();
        if (!Tools::isEmpty($megamenuitem)) {
            $menu_item = $megamenuitem;
        } elseif (Tools::getValue('id_item')) {
            $menu_item = $megamenu->getMenuItem((int)Tools::getValue('id_item'));
        } else {
            $menu_item = array();
        }

        $id_lang = (int)$this->context->language->id;

        if (!Tools::isEmpty($megamenuitem)) {
            $html = '<select multiple="multiple" name="col-item-items" autocomplete="off">';
        } else {
            $html = '<select multiple="multiple" name="simplemenu_items[]" id="simplemenu_items">';
        }

        foreach ($menu_item as $item) {
            if (!$item) {
                continue;
            }

            preg_match($this->pattern, $item, $values);

            $id = (int)Tools::substr($item, Tools::strlen($values[1]), Tools::strlen($item));

            switch (Tools::substr($item, 0, Tools::strlen($values[1]))) {
                case 'CAT':
                    $category = new Category((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($category)) {
                        $html .= '<option selected="selected" value="CAT'.$id.'">'.$category->name.'</option>'.PHP_EOL;
                    }
                    break;

                case 'PRD':
                    $product = new Product((int)$id, true, (int)$id_lang);
                    if (Validate::isLoadedObject($product)) {
                        $html .= '<option selected="selected" value="PRD'.$id.'">'.$product->name.' (product link)</option>'.PHP_EOL;
                    }
                    break;

                case 'PRDI':
                    $product = new Product((int)$id, true, (int)$id_lang);
                    if (Validate::isLoadedObject($product)) {
                        $html .= '<option selected="selected" value="PRDI'.$id.'">'.$product->name.' (product info)</option>'.PHP_EOL;
                    }
                    break;

                case 'CMS':
                    $cms = new CMS((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($cms)) {
                        $html .= '<option selected="selected" value="CMS'.$id.'">'.$cms->meta_title.'</option>'.PHP_EOL;
                    }
                    break;

                case 'CMS_CAT':
                    $category = new CMSCategory((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($category)) {
                        $html .= '<option selected="selected" value="CMS_CAT'.$id.'">'.$category->name.'</option>'.PHP_EOL;
                    }
                    break;

                case 'ALLMAN':
                    $html .= '<option selected="selected" value="ALLMAN0">'.$this->l('All manufacturers').'</option>'.PHP_EOL;
                    break;

                case 'MAN':
                    $manufacturer = new Manufacturer((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($manufacturer)) {
                        $html .= '<option selected="selected" value="MAN'.$id.'">'.$manufacturer->name.'</option>'.PHP_EOL;
                    }
                    break;

                case 'ALLSUP':
                    $html .= '<option selected="selected" value="ALLSUP0">'.$this->l('All suppliers').'</option>'.PHP_EOL;
                    break;

                case 'SUP':
                    $supplier = new Supplier((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($supplier)) {
                        $html .= '<option selected="selected" value="SUP'.$id.'">'.$supplier->name.'</option>'.PHP_EOL;
                    }
                    break;

                case 'SHOP':
                    $shop = new Shop((int)$id);
                    if (Validate::isLoadedObject($shop)) {
                        $html .= '<option selected="selected" value="SHOP'.(int)$id.'">'.$shop->name.'</option>'.PHP_EOL;
                    }
                    break;

                case 'HTML':
                    $new_html = new MegaMenuHtml((int)$id);
                    if (Validate::isLoadedObject($new_html)) {
                        $html .= '<option selected="selected" value="HTML'.(int)$new_html->id.'">'
                                    .Tools::safeOutput($new_html->title[$id_lang]).
                                '</option>';
                    }
                    break;
                case 'LNK':
                    $link = new MegaMenuLink((int)$id);
                    if (Validate::isLoadedObject($link)) {
                        $html .= '<option selected="selected" value="LNK'.(int)$link->id.'">'.Tools::safeOutput($link->title[$id_lang]).'</option>';
                    }
                    break;
                case 'BNR':
                    $banner = new MegaMenuBanner((int)$id);
                    if (Validate::isLoadedObject($banner)) {
                        $html .= '<option selected="selected" value="BNR'.(int)$banner->id.'">'.Tools::safeOutput($banner->title[$id_lang]).'</option>';
                    }
                    break;
                case 'VID':
                    $video = new MegaMenuVideo((int)$id);
                    if (Validate::isLoadedObject($video)) {
                        $html .= '<option selected="selected" value="VID'.(int)$video->id.'">'.Tools::safeOutput($video->title[$id_lang]).'</option>';
                    }
                    break;
                case 'MAP':
                    $map = new MegaMenuMap((int)$id);
                    if (Validate::isLoadedObject($map)) {
                        $html .= '<option selected="selected" value="MAP'.(int)$map->id.'">'.Tools::safeOutput($map->title[$id_lang]).'</option>';
                    }
                    break;
            }
        }

        return $html.'</select>';
    }

    protected function getCMSOptions($parent = 0, $depth = 1, $id_lang = false, $items_to_skip = null, $id_shop = false)
    {
        $megamenu = new MegaMenu();
        $html = '';
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        $id_shop = ($id_shop !== false) ? $id_shop : Context::getContext()->shop->id;
        $categories = $megamenu->getCMSCategories(false, (int)$parent, (int)$id_shop);
        $pages = $megamenu->getCMSPages((int)$parent, (int)$id_lang, (int)$id_shop);

        $spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$depth);

        foreach ($categories as $category) {
            if (isset($items_to_skip) && !in_array('CMS_CAT'.$category['id_cms_category'], $items_to_skip)) {
                $html .= '<option value="CMS_CAT'.$category['id_cms_category'].'" style="font-weight: bold;">'.$spacer.$category['name'].'</option>';
            }
            $html .= $this->getCMSOptions($category['id_cms_category'], (int)$depth + 1, (int)$id_lang, $items_to_skip);
        }

        foreach ($pages as $page) {
            if (isset($items_to_skip) && !in_array('CMS'.$page['id_cms'], $items_to_skip)) {
                $html .= '<option value="CMS'.$page['id_cms'].'">'.$spacer.$page['meta_title'].'</option>';
            }
        }

        return $html;
    }

    protected function generateCategoriesOption($categories, $items_to_skip = null)
    {
        $html = '';

        foreach ($categories as $category) {
            if (isset($items_to_skip)) {
                $shop = (object)Shop::getShop((int)$category['id_shop']);
                $html .= '<option value="CAT'.(int)$category['id_category'].'">'
                    .str_repeat('&nbsp;', $this->spacer_size * (int)$category['level_depth']).$category['name'].' ('.$shop->name.')</option>';
            }

            if (isset($category['children']) && !empty($category['children'])) {
                $html .= $this->generateCategoriesOption($category['children'], $items_to_skip);
            }

        }
        return $html;
    }

    protected function makeMenuTop()
    {
        $megamenu = new MegaMenu();
        if ($top_items = $megamenu->getTopItems()) {
            $this->menu = '<ul class="menu clearfix top-level-menu tmmegamenu_item">';

            foreach ($top_items as $key => $top) {
                $item_num = $key + 1;
                $simple_class = '';
                $badge = '';

                if ($top['is_simple']) {
                    $simple_class = ' simple';
                }

                if ($top['badge']) {
                    $badge = '<span class="menu_badge '.$top['unique_code'].' top-level-badge tmmegamenu_item">'.$top['badge'].'</span>';
                }

                if (!$top['is_custom_url']) {
                    $top_item_url = $this->generateTopItemUrl($top['url']);
                } else {
                    $top_item_url = array('url' =>$top['url'], 'selected' => '');
                }

                $this->menu .= '<li class="'.$top['specific_class'].$simple_class.$top_item_url['selected'].' top-level-menu-li tmmegamenu_item '.$top['unique_code'].'">';
                if (!Tools::isEmpty($top_item_url['url'])) {
                    $this->menu .= '<a class="'.$top['unique_code'].' top-level-menu-li-a tmmegamenu_item" href="'.$top_item_url['url'].'">'.$top['title'].$badge.'</a>';
                } else {
                    $this->menu .= $top['title'];
                }

                if (!$top['is_mega']) {
                    $subitems = $megamenu->getMenuItem((int)$top['id_item'], 0, true);
                    if ($subitems) {
                        $this->menu .= '<ul class="is-simplemenu tmmegamenu_item first-level-menu '.$top['unique_code'].'">';
                        $this->menu .= $this->makeMenu($subitems);
                        $this->menu .= '</ul>';
                    }
                } else {
                    if ($rows = $megamenu->getMegamenuRow((int)$top['id_item'])) {
                        $this->menu .= '<div class="is-megamenu tmmegamenu_item first-level-menu '.$top['unique_code'].'">';
                        foreach ($rows as $row) {
                            $this->menu .= '<div id="megamenu-row-'.$item_num.'-'.$row.'" class="megamenu-row row megamenu-row-'.$row.'">';
                            if ($cols = $megamenu->getMegamenuRowCols((int)$top['id_item'], $row)) {
                                $sp_class = '';
                                foreach ($cols as $col) {
                                    if ($col['class']) {
                                        $sp_class = ' '.$col['class'];
                                    }
                                    $this->menu .= '<div id="column-'.$item_num.'-'.$row.'-'.$col['col'].'"
                                                    class="megamenu-col megamenu-col-'.$row.'-'.$col['col'].' col-sm-'.$col['width'].' '.$sp_class.'">';
                                    $this->menu .= '<ul class="content">';
                                    $this->menu .= $this->makeMenu(explode(',', $col['settings']));
                                    $this->menu .= '</ul>';
                                    $this->menu .= '</div>';
                                }
                            }
                            $this->menu .= '</div>';
                        }
                        $this->menu .= '</div>';
                    }
                }
                $this->menu .= '</li>';
            }
            $this->menu .= '</ul>';
        }
    }

    protected function makeMenu($subitems)
    {
        $id_lang = (int)$this->context->language->id;

        foreach ($subitems as $item) {
            if (!$item) {
                continue;
            }

            preg_match($this->pattern, $item, $value);
            $id = (int)Tools::substr($item, Tools::strlen($value[1]), Tools::strlen($item));

            switch (Tools::substr($item, 0, Tools::strlen($value[1]))) {
                case 'CAT':
                    $this->menu .= $this->generateCategoriesMenu(Category::getNestedCategories($id, $id_lang, true, $this->user_groups));
                    break;

                case 'PRD':
                    $selected = ($this->page_name == 'product' && (Tools::getValue('id_product') == $id)) ? ' class="sfHover product"' : ' class="product"';
                    $product = new Product((int)$id, true, (int)$id_lang);
                    if (!is_null($product->id)) {
                        $this->menu .= '<li'.$selected.'>
                                            <a href="'.Tools::HtmlEntitiesUTF8($product->getLink()).'" title="'.$product->name.'">'.$product->name.'</a>
                                        </li>'.PHP_EOL;
                    }
                    break;

                case 'PRDI':
                    $selected = ($this->page_name == 'product' && (Tools::getValue('id_product') == $id)) ?
                                ' class="sfHover product-info"' :
                                ' class="product-info"';
                    $product = new Product((int)$id, true, (int)$id_lang);
                    if (!is_null($product->id)) {
                        $this->menu .= '<li'.$selected.'>'.$this->generateProductInfo($id).'</li>'.PHP_EOL;
                    }
                    break;

                case 'CMS':
                    $selected = ($this->page_name == 'cms' && (Tools::getValue('id_cms') == $id)) ? ' class="sfHover cms-page"' : ' class="cms-page"';
                    $cms = CMS::getLinks((int)$id_lang, array($id));
                    if (count($cms)) {
                        $this->menu .= '<li'.$selected.'>
                                            <a href="'.Tools::HtmlEntitiesUTF8($cms[0]['link']).'" title="'
                                                .Tools::safeOutput($cms[0]['meta_title']).'">'.Tools::safeOutput($cms[0]['meta_title']).
                                            '</a>
                                        </li>'.PHP_EOL;
                    }
                    break;

                case 'CMS_CAT':
                    $category = new CMSCategory((int)$id, (int)$id_lang);
                    $selected = ($this->page_name == 'cms' && ((int)Tools::getValue('id_cms_category') == $category->id)) ?
                                ' class="sfHoverForce cms-category"' :
                                ' class="cms-category"';
                    if (count($category)) {
                        $this->menu .= '<li'.$selected.'>
                            <a href="'.Tools::HtmlEntitiesUTF8($category->getLink()).'" title="'.$category->name.'">'.$category->name.'</a>';
                            $this->getCMSMenuItems($category->id);
                        $this->menu .= '</li>'.PHP_EOL;
                    }
                    break;

                case 'ALLMAN':
                    $link = new Link;
                    $this->menu .= '<li class="all-manufacturers">
                                        <a href="'.$link->getPageLink('manufacturer').'" title="'.$this->l('All manufacturers').'">'.$this->l('All manufacturers').'</a>
                                        <ul>'.PHP_EOL;
                    $manufacturers = Manufacturer::getManufacturers();
                    foreach ($manufacturers as $manufacturer) {
                        $selected = ($this->page_name == 'manufacturer' && (Tools::getValue('id_supplier') == (int)$manufacturer['id_manufacturer'])) ?
                                    ' class="sfHoverForce manufacturer"' :
                                    ' class="manufacturer"';
                        $this->menu .= '<li'.$selected.'>
                                            <a href="'.$link->getManufacturerLink((int)$manufacturer['id_manufacturer'], $manufacturer['link_rewrite']).'"
                                                title="'.Tools::safeOutput($manufacturer['name']).'">'.Tools::safeOutput($manufacturer['name']).'</a>
                                        </li>'.PHP_EOL;
                    }
                    $this->menu .= '</ul>';
                    break;

                case 'MAN':
                    $selected = ($this->page_name == 'manufacturer' && (Tools::getValue('id_manufacturer') == $id)) ?
                                ' class="sfHover manufacturer"' :
                                ' class="manufacturer"';
                    $manufacturer = new Manufacturer((int)$id, (int)$id_lang);
                    if (!is_null($manufacturer->id)) {
                        if ((int)Configuration::get('PS_REWRITING_SETTINGS')) {
                            $manufacturer->link_rewrite = Tools::link_rewrite($manufacturer->name);
                        } else {
                            $manufacturer->link_rewrite = 0;
                        }
                        $link = new Link;
                        $this->menu .= '<li'.$selected.'>
                                            <a href="'.Tools::HtmlEntitiesUTF8($link->getManufacturerLink((int)$id, $manufacturer->link_rewrite)).'"
                                                title="'.Tools::safeOutput($manufacturer->name).'">'.Tools::safeOutput($manufacturer->name).'</a>
                                            </li>'.PHP_EOL;
                    }
                    break;

                case 'ALLSUP':
                    $link = new Link;
                    $this->menu .= '<li class="all-suppliers">
                                        <a href="'.$link->getPageLink('supplier').'" title="'.$this->l('All suppliers').'">'.$this->l('All suppliers').'</a>
                                        <ul>'.PHP_EOL;
                    $suppliers = Supplier::getSuppliers();
                    foreach ($suppliers as $supplier) {
                        $selected = ($this->page_name == 'supplier' && (Tools::getValue('id_supplier') == (int)$supplier['id_supplier'])) ?
                                    ' class="sfHoverForce supplier"' :
                                    ' class="supplier"';
                        $this->menu .= '<li'.$selected.'>
                                            <a href="'.$link->getSupplierLink((int)$supplier['id_supplier'], $supplier['link_rewrite']).'"
                                            title="'.Tools::safeOutput($supplier['name']).'">'.Tools::safeOutput($supplier['name']).'</a>
                                        </li>'.PHP_EOL;
                    }
                    $this->menu .= '</ul>';
                    break;

                case 'SUP':
                    $selected = ($this->page_name == 'supplier' && (Tools::getValue('id_supplier') == $id)) ?
                                ' class="sfHover supplier"' :
                                ' class="supplier"';
                    $supplier = new Supplier((int)$id, (int)$id_lang);
                    if (!is_null($supplier->id)) {
                        $link = new Link;
                        $this->menu .= '<li'.$selected.'>
                                            <a href="'.Tools::HtmlEntitiesUTF8($link->getSupplierLink((int)$id, $supplier->link_rewrite)).'"
                                            title="'.$supplier->name.'">'.$supplier->name.'</a>
                                        </li>'.PHP_EOL;
                    }
                    break;

                case 'SHOP':
                    $selected = ($this->page_name == 'index' && ($this->context->shop->id == $id)) ? ' class="sfHover shop"' : ' class="shop"';
                    $shop = new Shop((int)$id);
                    if (Validate::isLoadedObject($shop)) {
                        $link = new Link;
                        $this->menu .= '<li'.$selected.'>
                                            <a href="'.Tools::HtmlEntitiesUTF8($shop->getBaseURL()).'" title="'.$shop->name.'">'.$shop->name.'</a>
                                        </li>'.PHP_EOL;
                    }
                    break;

                case 'HTML':
                    $this->menu .= $this->generateCustomHtml($id);
                    break;
                case 'LNK':
                    $this->menu .= $this->generateCustomLink($id);
                    break;
                case 'BNR':
                    $this->menu .= $this->generateBanner($id);
                    break;
                case 'VID':
                    $this->menu .= $this->generateVideo($id);
                    break;
                case 'MAP':
                    $this->menu .= $this->generateMap($id);
                    break;
            }
        }
    }

    /*****
    ****** Get all categories items with nesting
    ****** $categories = category id
    ****** return: nested list with all categories
    *****/
    protected function generateCategoriesMenu($categories)
    {
        $html = '';

        foreach ($categories as $category) {
            if ($category['level_depth'] > 1) {
                $cat = new Category($category['id_category']);
                $link = Tools::HtmlEntitiesUTF8($cat->getLink());
            } else {
                $link = $this->context->link->getPageLink('index');
            }

            $html .= '<li'.(($this->page_name == 'category'
                && (int)Tools::getValue('id_category') == (int)$category['id_category']) ? ' class="sfHoverForce category"' : ' class="category"').'>';
            $html .= '<a href="'.$link.'" title="'.$category['name'].'">'.$category['name'].'</a>';

            if (isset($category['children']) && !empty($category['children'])) {
                $html .= '<ul>';
                    $html .= $this->generateCategoriesMenu($category['children'], 1);
                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        return $html;
    }

    /*****
    ******    Generate top item URL by element code (`url`)
    ******  return item url and active class if selected
    *****/
    public function generateTopItemUrl($url)
    {
        $link = new Link();
        preg_match($this->pattern, $url, $value);
        $id = (int)Tools::substr($url, Tools::strlen($value[1]), Tools::strlen($url));
        $type = Tools::substr($url, 0, Tools::strlen($value[1]));
        $selected = '';

        switch ($type) {
            case 'CAT':
                $url = $link->getCategoryLink($id);
                if ($this->page_name == 'category' && (int)Tools::getValue('id_category') == $id) {
                    $selected = ' sfHoverForce';
                }
                break;
            case 'CMS_CAT':
                $url = $link->getCMSCategoryLink($id);
                if ($this->page_name == 'cms' && ((int)Tools::getValue('id_cms_category') == $id)) {
                    $selected = ' sfHoverForce';
                }
                break;
            case 'CMS':
                $url = $link->getCMSLink($id);
                if ($this->page_name == 'cms' && Tools::getValue('id_cms') == $id) {
                    $selected = ' sfHoverForce';
                }
                break;
        }

        return array('url' => $url, 'selected' => $selected);
    }

    /*****
    ****** Get all cms items with nesting
    ****** $parent = paretn category id
    ****** $depth = depth level
    ****** $id_lang - current lang
    ****** return: nested list with all cms items (3 level max)
    *****/
    protected function getCMSMenuItems($parent, $depth = 1, $id_lang = false)
    {
        $megamenu = new MegaMenu();
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

        if ($depth > 3) {
            return;
        }

        $categories = $megamenu->getCMSCategories(false, (int)$parent, (int)$id_lang);
        $pages = $megamenu->getCMSPages((int)$parent);

        if (count($categories) || count($pages)) {
            $this->menu .= '<ul>';

            foreach ($categories as $category) {
                $cat = new CMSCategory((int)$category['id_cms_category'], (int)$id_lang);
                $selected = ($this->page_name == 'cms' && ((int)Tools::getValue('id_cms_category') == $cat->id)) ?
                            ' class="sfHoverForce cms-category"' :
                            ' class="cms-category"';

                $this->menu .= '<li '.$selected.'>';
                $this->menu .= '<a href="'.Tools::HtmlEntitiesUTF8($cat->getLink()).'">'.$category['name'].'</a>';
                $this->getCMSMenuItems($category['id_cms_category'], (int)$depth + 1);
                $this->menu .= '</li>';
            }

            foreach ($pages as $page) {
                $cms = new CMS($page['id_cms'], (int)$id_lang);
                $links = $cms->getLinks((int)$id_lang, array((int)$cms->id));

                $selected = ($this->page_name == 'cms' && ((int)Tools::getValue('id_cms') == $cms->id)) ? ' class="sfHoverForce cms-page"' : ' class="cms-page"';
                $this->menu .= '<li '.$selected.'>';
                $this->menu .= '<a href="'.$links[0]['link'].'">'.$cms->meta_title.'</a>';
                $this->menu .= '</li>';
            }

            $this->menu .= '</ul>';
        }
    }

    /****
    *****    Generating megamenu content in admin part
    ****/
    protected function getMegamenuItems()
    {
        $megamenu_items = $this->megamenu_items;
        $megamenu = new MegaMenu();
        $id_item = (int)Tools::getValue('id_item');

        // get rows for this megamenu
        if (!$rows = $megamenu->getMegamenuRow($id_item)) {
            return false;
        }

        // parse each row
        foreach ($rows as $row) {
            $megamenu_items .= '<div id="megamenu-row-'.$row.'" class="megamenu-row">';
            $megamenu_items .= '<div class="row">';
                $megamenu_items .= '<div class="add-column-button-container col-lg-6">';
                    $megamenu_items .= '<a class="btn btn-sm btn-success add-megamenu-col" onclick="return false;" href="#">'.$this->l('Add column').'</a>';
                $megamenu_items .= '</div>';
                $megamenu_items .= '<div class="remove-row-button col-lg-6 text-right">';
                    $megamenu_items .= '<a class="btn btn-sm btn-danger btn-remove-row" onclick="return false;" href="#">'.$this->l('Remove row').'</a>';
                $megamenu_items .= '</div>';
            $megamenu_items .= '</div>';

            // get columns for this megamenu row
            if (!$items = $megamenu->getMegamenuRowCols($id_item, $row)) {
                return false;
            }
            $row_data = '';
            $megamenu_items .= '<div class="megamenu-row-content row">';

            // generate each column for current row
            foreach ($items as $item) {
                $megamenu_items .= '<div id="column-'.$row.'-'.$item['col'].'" class="megamenu-col megamenu-col-'.$item['col'].' col-lg-'.$item['width'].'">';
                    $megamenu_items .= '<div class="megamenu-col-inner">';
                        $megamenu_items .= $this->classSelectGenerate((int)$item['width']);
                        $megamenu_items .= '<div class="form-group">';
                            $megamenu_items .= '<label>'.$this->l('Enter specific class').'</label>';
                            $megamenu_items .= '<input class="form-control" type="text" name="col-item-class" value="'.$item['class'].'" autocomplete="off" />';
                            $megamenu_items .= '<p class="help-block">'.$this->l('Can not contain special chars, only
                                                                                        _ is allowed.(Will be automatically replaced)').'</p>';
                        $megamenu_items .= '</div>';
                        $megamenu_items .= '<div class="form-group">';
                            $megamenu_items .= '<label>'.$this->l('Select content').'</label>';
                            $megamenu_items .= $this->renderChoicesSelect();
                        $megamenu_items .= '</div>';
                        $megamenu_items .= '<div class="form-group buttons-group">';
                            $megamenu_items .= '<a class="add-item-to-selected btn btn-sm btn-default" onclick="return false;" href="#">'.$this->l('Add').'</a>';
                            $megamenu_items .= '<a class="remove-item-from-selected btn btn-sm btn-default" onclick="return false;" href="#">'.$this->l('Remove').'</a>';
                        $megamenu_items .= '</div>';
                        $megamenu_items .= '<div class="form-group">';
                            $megamenu_items .= '<label>'.$this->l('Selected items').'</label>';
                            $megamenu_items .= $this->makeMenuOption(explode(',', $item['settings']));
                        $megamenu_items .= '</div>';
                        $megamenu_items .= '<div class="remove-block-button">';
                            $megamenu_items .= '<a href="#" class="btn btn-sm btn-default btn-remove-column" onclick="return false;">'.$this->l('Remove block').'</a>';
                        $megamenu_items .= '</div>';
                    $megamenu_items .= '</div>';// set hidden data for jquery (each colmn)
                    $megamenu_items .= '<input type="hidden"
                                                value="{col-'.$item['col'].'-'.$item['width'].'-('.$item['class'].')-'.$item['type'].'-['.$item['settings'].']}"
                                                name="col_content">';
                $megamenu_items .= '</div>';

                // set hidden data for jquery (each colmns for row)
                $row_data .= '{col-'.$item['col'].'-'.$item['width'].'-('.$item['class'].')-'.$item['type'].'-['.$item['settings'].']}';
            }
            $megamenu_items .= '</div>';
            $megamenu_items .= '<input type="hidden" name="row_content" value="'.$row_data.'" />';
            $megamenu_items .= '</div>';
        }

        return $megamenu_items;
    }

    /*****
    ****** Generate product info block by id
    ****** $id_product = product ID
    ****** return product info in html block
    *****/
    protected function generateProductInfo($id_product)
    {
        $output = '';
        $id_lang = $this->context->language->id;
        $product = new Product($id_product, true, (int)$id_lang);
        $images = $product->getImages($this->context->language->id);

        foreach ($images as $image) {
            if ($image['cover']) {
                $img = $product->id.'-'.$image['id_image'];
                break;
            }
        }

        $this->context->smarty->assign(array(
                                'product' => $product,
                                'image' => $img
                                ));

        $output .= $this->display($this->local_path, 'views/templates/hook/items/product.tpl');

        return $output;
    }

    /*****
    ****** Generate custom HTML block by id_item
    ****** $id_item = custom HTML ID
    ****** return custom HTML block
    *****/
    protected function generateCustomHtml($id_item)
    {
        $output = '';
        $html = new MegaMenuHtml((int)$id_item);
        if ($html) {
            $output .= '<li '.($html->specific_class?'class="'.$html->specific_class.' html"':'class="html"').'>
                            <h3>'.$html->title[(int)$this->context->language->id].'</h3>'
                            .$html->content[(int)$this->context->language->id].
                        '</li>';
        }
        return $output;
    }

    /*****
    ****** Generate custom Link by id_item
    ****** $id_item = custom Link ID
    ****** return custom Link element
    *****/
    protected function generateCustomLink($id_item)
    {
        $output = '';
        $link = new MegaMenuLink((int)$id_item);
        if ($link) {
            $output .= '<li '.($link->specific_class?'class="'.$link->specific_class.' custom-link"':'class="custom-link"').'>
                            <a '.($link->blank?'target="_blank"':'').' href="'.$link->url[(int)$this->context->language->id].'">'
                                .$link->title[(int)$this->context->language->id].
                            '</a>
                        </li>';
        }
        return $output;
    }

    /*****
    ****** Generate Banner by id_item
    ****** $id_item = Banner ID
    ****** return custom Link element
    *****/
    protected function generateBanner($id_item)
    {
        $output = '';
        $id_lang = (int)$this->context->language->id;
        $html = new MegaMenuBanner($id_item);

        if ($html) {
            $this->context->smarty->assign('image_baseurl', $this->_path.'images/');
            $this->context->smarty->assign('banner', array(
                                        'id' => $html->id,
                                        'specific_class' => $html->specific_class,
                                        'title' => $html->title[$id_lang],
                                        'url' => $html->url[$id_lang],
                                        'image' => $html->image[$id_lang],
                                        'blank' => $html->blank,
                                        'public_title' => $html->public_title[$id_lang],
                                        'description' => $html->description[$id_lang]
                                    ));
            $output .= $this->display($this->local_path, 'views/templates/hook/items/banner.tpl');
        }

        return $output;
    }

    /*****
    ****** Generate Video by id_item
    ****** $id_item = Video ID
    ****** return Video element
    *****/
    protected function generateVideo($id_item)
    {
        $output = '';
        $id_lang = (int)$this->context->language->id;
        $video = new MegaMenuVideo($id_item);

        if ($video) {
            $this->context->smarty->assign('video', array(
                                        'id' => $video->id,
                                        'title' => $video->title[$id_lang],
                                        'url' => $video->url[$id_lang],
                                        'type' => $video->type[$id_lang]
                                    ));
            $output .= $this->display($this->local_path, 'views/templates/hook/items/video.tpl');
        }

        return $output;
    }

    /*****
    ****** Generate Map by id_item
    ****** $id_item = Map ID
    ****** return Map element
    *****/
    protected function generateMap($id_item)
    {
        $output = '';
        $id_lang = (int)$this->context->language->id;
        $map = new MegaMenuMap($id_item);

        if ($map) {
            $this->has_map = true;
            $this->context->smarty->assign('map', array(
                                        'unic_identificator' => Tools::passwdGen(),
                                        'id' => $map->id,
                                        'title' => $map->title[$id_lang],
                                        'description' => $map->description[$id_lang],
                                        'latitude' => $map->latitude,
                                        'longitude' => $map->longitude,
                                        'scale' => $map->scale,
                                        'icon' => $map->marker ? $this->_path.'img/markers/'.$map->marker : ''
                                    ));
            $output .= $this->display($this->local_path, 'views/templates/hook/items/map.tpl');
        }

        return $output;
    }

    /*****
    ****** Generate select for width checking
    ****** $width = current block width
    ****** return: select with all width types and current selected
    *****/
    private function classSelectGenerate($width)
    {
        $output = '';
        $output .= '<div class="form-group">';
        $output .= '<label>'.$this->l('Set column width.').'</label>';
        $output .= '<select class="form-control" name="col-item-type" autocomplete="off">';
        for ($i = 2; $i < 13; $i++) {
            $selected = '';
            if ($width == $i) {
                $selected = 'selected="selected"';
            }
            $output .= '<option value="'.$i.'" '.$selected.'>col-'.$i.'</option>';
        }
        $output .= '</select>';
        $output .= '</div>';

        return $output;
    }

    /*****
    ******    Generate form for Html blocks creating
    *****/
    private function renderAddHtml($id_html = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('editHtml') && (int)Tools::getValue('id_item') > 0)?$this->l('Update Html block'):$this->l('Add Html block'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter HTML item name'),
                        'name' => 'title',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Specific class'),
                        'name' => 'specific_class',
                        'required' => false,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('HTML content'),
                        'name' => 'content',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateHtml',
                ),
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & Stay'),
                        'type' => 'submit',
                        'name' => 'updateHtmlStay',
                    ),
                )
            ),
        );
        if ((Tools::getIsset('editHtml') && (int)Tools::getValue('id_item') > 0) || $id_html) {
            if ($id_html) {
                $id = $id_html;
            } else {
                $id = Tools::getValue('id_item');
            }
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_item', 'value' => (int)$id);
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();

        $helper->identifier = 'id_item';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
                                '&configure='.$this->name.
                                '&tab_module='.$this->tab.
                                '&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getHtmlFieldsValues($id_html),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    /*****
    ******    Fill Html blocks form fields
    *****/
    private function getHtmlFieldsValues($id)
    {
        if ($id) {
            $megamenuhtml  = new MegaMenuHtml((int)$id);
        } elseif (Tools::getIsset('editHtml') && (int)Tools::getValue('id_item') > 0) {
            $megamenuhtml  = new MegaMenuHtml((int)Tools::getValue('id_item'));
        } else {
            $megamenuhtml = new MegaMenuHtml();
        }

        $fields_values = array(
            'id_item' => Tools::getValue('id_item', $megamenuhtml->id),
            'specific_class' => Tools::getValue('specific_class', $megamenuhtml->specific_class),
        );

        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $fields_values['title'][$lang['id_lang']] = Tools::getValue('name_'.(int)$lang['id_lang'], $megamenuhtml->title[$lang['id_lang']]);
            $fields_values['content'][$lang['id_lang']] = Tools::getValue('content_'.(int)$lang['id_lang'], $megamenuhtml->content[$lang['id_lang']]);
        }

        return $fields_values;
    }

    /*****
    ******    Generate form for Links creating
    *****/
    private function renderAddLink($id_link = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('editLink') && (int)Tools::getValue('id_item') > 0)?$this->l('Update Link'):$this->l('Add new Link'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Link name'),
                        'name' => 'title',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Link URL'),
                        'name' => 'url',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Specific class'),
                        'name' => 'specific_class',
                        'required' => false,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Open in new window'),
                        'name' => 'blank',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateLink',
                ),
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & Stay'),
                        'type' => 'submit',
                        'name' => 'updateLinkStay',
                    ),
                )
            ),
        );
        if ((Tools::getIsset('editLink') && (int)Tools::getValue('id_item') > 0) || $id_link > 0) {
            if ($id_link) {
                $id = $id_link;
            } else {
                $id = (int)Tools::getValue('id_item');
            }
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_item', 'value' => $id);
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();

        $helper->identifier = 'id_item';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
                                '&configure='.$this->name.
                                '&tab_module='.$this->tab.
                                '&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getlinkFieldsValues($id_link),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    /*****
    ******    Fill Links form fields
    *****/
    private function getLinkFieldsValues($id)
    {
        if ($id) {
            $megamenulink  = new MegaMenuLink((int)$id);
        } elseif (Tools::getIsset('editLink') && (int)Tools::getValue('id_item') > 0) {
            $megamenulink  = new MegaMenuLink((int)Tools::getValue('id_item'));
        } else {
            $megamenulink = new MegaMenulink();
        }

        $fields_values = array(
            'id_item' => Tools::getValue('id_item', $megamenulink->id),
            'specific_class' => Tools::getValue('specific_class', $megamenulink->specific_class),
            'blank' => Tools::getValue('blank', $megamenulink->blank),
        );

        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $fields_values['title'][$lang['id_lang']] = Tools::getValue('name_'.(int)$lang['id_lang'], $megamenulink->title[$lang['id_lang']]);
            $fields_values['url'][$lang['id_lang']] = Tools::getValue('url_'.(int)$lang['id_lang'], $megamenulink->url[$lang['id_lang']]);
        }

        return $fields_values;
    }

    /*****
    ******    Generate form for Banners creating
    *****/
    private function renderAddBanner($id_banner = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('editBanner') && (int)Tools::getValue('id_item') > 0)
                        ?$this->l('Update Banner')
                        :$this->l('Add new Banner'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'files_lang',
                        'label' => $this->l('Select a file'),
                        'name' => 'image',
                        'required' => true,
                        'lang' => true,
                        'desc' => sprintf($this->l('Maximum image size: %s.'), ini_get('upload_max_filesize'))
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Banner name'),
                        'name' => 'title',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Banner URL'),
                        'name' => 'url',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Specific class'),
                        'name' => 'specific_class',
                        'required' => false,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Public Title'),
                        'name' => 'public_title',
                        'required' => false,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Description'),
                        'name' => 'description',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Open in new window'),
                        'name' => 'blank',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateBanner',
                ),
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & Stay'),
                        'type' => 'submit',
                        'name' => 'updateBannerStay',
                    ),
                ),
            ),
        );
        if ((Tools::getIsset('editBanner') && (int)Tools::getValue('id_item') > 0) || $id_banner > 0) {
            if ($id_banner) {
                $id = $id_banner;
            } else {
                $id = (int)Tools::getValue('id_item');
            }

            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_item', 'value' => $id);
            $banner = new MegaMenuBanner($id);
            $fields_form['form']['images'] = $banner->image;
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
                                '&configure='.$this->name.
                                '&tab_module='.$this->tab.
                                '&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code
            ),
            'fields_value' => $this->getBannerFieldsValues($id_banner),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'image_baseurl' => $this->_path.'images/'
        );

        $helper->override_folder = '/';

        return $helper->generateForm(array($fields_form));
    }

    /*****
    ******    Fill Banner form fields
    *****/
    private function getBannerFieldsValues($id = false)
    {
        if ($id) {
            $megamenubanner  = new MegaMenuBanner((int)$id);
        } elseif (Tools::getIsset('editBanner') && (int)Tools::getValue('id_item') > 0) {
            $megamenubanner  = new MegaMenuBanner((int)Tools::getValue('id_item'));
        } else {
            $megamenubanner = new MegaMenuBanner();
        }

        $fields_values = array(
            'id_item' => Tools::getValue('id_item', $megamenubanner->id),
            'specific_class' => Tools::getValue('specific_class', $megamenubanner->specific_class),
            'blank' => Tools::getValue('blank', $megamenubanner->blank),
        );

        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $fields_values['title'][$lang['id_lang']] = Tools::getValue(
                'name_'.(int)$lang['id_lang'],
                $megamenubanner->title[$lang['id_lang']]
            );
            $fields_values['url'][$lang['id_lang']] = Tools::getValue(
                'url_'.(int)$lang['id_lang'],
                $megamenubanner->url[$lang['id_lang']]
            );
            $fields_values['image'][$lang['id_lang']] = Tools::getValue(
                'image_'.(int)$lang['id_lang'],
                $megamenubanner->image[$lang['id_lang']]
            );
            $fields_values['public_title'][$lang['id_lang']] = Tools::getValue(
                'public_title_'.(int)$lang['id_lang'],
                $megamenubanner->public_title[$lang['id_lang']]
            );
            $fields_values['description'][$lang['id_lang']] = Tools::getValue(
                'description_'.(int)$lang['id_lang'],
                $megamenubanner->description[$lang['id_lang']]
            );
        }

        return $fields_values;
    }

    /*****
    ******    Generate form for Video creating
    *****/
    private function renderAddVideo($id_video = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('editVideo') && (int)Tools::getValue('id_item') > 0)?$this->l('Update Video'):$this->l('Add new Video'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'videos_lang',
                        'name' => 'video',
                        'lang' => true,
                        'label' => $this->l('Video')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Video name'),
                        'name' => 'title',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Video URL'),
                        'name' => 'url',
                        'required' => true,
                        'lang' => true,
                        'col' => 3,
                        'desc' => $this->l('Video url should be like https://www.youtube.com/v/video_id or http://player.vimeo.com/video/video_id')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateVideo',
                ),
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & Stay'),
                        'type' => 'submit',
                        'name' => 'updateVideoStay',
                    ),
                )
            ),
        );
        if ((Tools::getIsset('editVideo') && (int)Tools::getValue('id_item') > 0) || $id_video > 0) {
            if ($id_video) {
                $id = $id_video;
            } else {
                $id = (int)Tools::getValue('id_item');
            }
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_item', 'value' => $id);
            $video = new MegaMenuVideo($id);
            $fields_form['form']['videos'] = $video->url;
            $fields_form['form']['types'] = $video->type;
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;

        $helper->identifier = 'id_item';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
                                '&configure='.$this->name.
                                '&tab_module='.$this->tab.
                                '&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getVideoFieldsValues($id_video),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        $helper->override_folder = '/';

        return $helper->generateForm(array($fields_form));
    }

    /*****
    ******    Fill Video form fields
    *****/
    private function getVideoFieldsValues($id)
    {
        if ($id) {
            $megamenuvideo  = new MegaMenuVideo((int)$id);
        } elseif (Tools::getIsset('editVideo') && (int)Tools::getValue('id_item') > 0) {
            $megamenuvideo  = new MegaMenuVideo((int)Tools::getValue('id_item'));
        } else {
            $megamenuvideo = new MegaMenuVideo();
        }

        $fields_values = array(
            'id_item' => Tools::getValue('id_item', $megamenuvideo->id)
        );

        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $fields_values['title'][$lang['id_lang']] = Tools::getValue('name_'.(int)$lang['id_lang'], $megamenuvideo->title[$lang['id_lang']]);
            $fields_values['url'][$lang['id_lang']] = Tools::getValue('url_'.(int)$lang['id_lang'], $megamenuvideo->url[$lang['id_lang']]);
            $fields_values['type'][$lang['id_lang']] = Tools::getValue('type_'.(int)$lang['id_lang'], $megamenuvideo->type[$lang['id_lang']]);
        }

        return $fields_values;
    }

    /*****
    ******    Generate form for Html blocks creating
    *****/
    private function renderAddMap($id_map = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('editMap') && (int)Tools::getValue('id_item') > 0)?$this->l('Update Map'):$this->l('Add Map'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'megamenu_map',
                        'name' => 'map',
                        'lang' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Map item name'),
                        'name' => 'title',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Map latitude'),
                        'name' => 'latitude',
                        'required' => true,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Map longitude'),
                        'name' => 'longitude',
                        'required' => true,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'marker_prev',
                        'name' => 'marker_prev',
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Marker'),
                        'name' => 'marker',
                        'value' => true,
                        'desc' => $this->l('64px * 64px')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Map scale'),
                        'name' => 'scale',
                        'required' => false,
                        'lang' => false,
                        'col' => 3,
                        'desc' => $this->l('8 by default')
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Map description'),
                        'name' => 'description',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateMap',
                ),
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & Stay'),
                        'type' => 'submit',
                        'name' => 'updateMapStay',
                    ),
                )
            ),
        );
        if ((Tools::getIsset('editMap') && (int)Tools::getValue('id_item') > 0) || $id_map) {
            if ($id_map) {
                $id = $id_map;
            } else {
                $id = Tools::getValue('id_item');
            }
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_item', 'value' => (int)$id);
            $map = new MegaMenuMap($id);
            $fields_form['form']['id'] = $id;
            $fields_form['form']['latitude'] = $map->latitude;
            $fields_form['form']['longitude'] = $map->longitude;
            $fields_form['form']['marker'] = $map->marker;
            $fields_form['form']['scale'] = $map->scale;
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'old_marker', 'value' => $map->marker);
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;

        $helper->identifier = 'id_item';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
                                '&configure='.$this->name.
                                '&tab_module='.$this->tab.
                                '&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getMapFieldsValues($id_map),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'marker_url' => $this->_path.'img/markers/'
        );

        return $helper->generateForm(array($fields_form));
    }

    /*****
    ******    Fill Html blocks form fields
    *****/
    private function getMapFieldsValues($id)
    {
        if ($id) {
            $megamenumap  = new MegaMenuMap((int)$id);
        } elseif (Tools::getIsset('editMap') && (int)Tools::getValue('id_item') > 0) {
            $megamenumap  = new MegaMenuMap((int)Tools::getValue('id_item'));
        } else {
            $megamenumap = new MegaMenuMap();
        }
        $fields_values = array(
            'id_item' => Tools::getValue('id_item', $megamenumap->id),
            'latitude' => Tools::getValue('latitude', $megamenumap->latitude),
            'longitude' => Tools::getValue('longitude', $megamenumap->longitude),
            'scale' => Tools::getValue('scale', $megamenumap->scale ? $megamenumap->scale : 8),
            'marker' => Tools::getValue('marker', $megamenumap->marker),
            'old_marker' => Tools::getValue('old_marker', $megamenumap->marker)
        );

        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $fields_values['title'][$lang['id_lang']] = Tools::getValue('title_'.(int)$lang['id_lang'], $megamenumap->title[$lang['id_lang']]);
            $fields_values['description'][$lang['id_lang']] = Tools::getValue('description_'.(int)$lang['id_lang'], $megamenumap->description[$lang['id_lang']]);
        }

        return $fields_values;
    }

    public function clearCache()
    {
        $this->_clearCache('menu.tpl');
    }

    public function getVideoType($link)
    {
        if (strpos($link, 'youtube') > 0) {
            return 'youtube';
        } elseif (strpos($link, 'vimeo') > 0) {
            return 'vimeo';
        } else {
            return false;
        }
    }

    public static function stylePath()
    {
        return dirname(__FILE__).'/views/css/items/';
    }

    /*****
    ******     Parse styles fron tab's unique css file
    ******    $name = unique_code of tab
    ******    @return nothing
    ******    add smarty variables for each style row
    *****/
    protected function parseStyle($name = '')
    {
        if (!file_exists($this->stylePath().$name.'.css')) {
            return;
        }

        $file_content = Tools::file_get_contents($this->stylePath().$name.'.css');
        $styles = explode('}', $file_content);
        foreach ($styles as $style) {
            if (!Tools::isEmpty($style)) {
                $class = explode('{', $style);
                $class_data = array();
                $class_name = str_replace('tmmegamenu_item', '', str_replace(''.$name.'', '', str_replace('.', '', trim($class[0]))));
                if (isset($class[1]) && !Tools::isEmpty($class[1])) {
                    $class_style = explode(';', $class[1]);
                    foreach ($class_style as $style_attr) {
                        if (!Tools::isEmpty($style_attr)) {
                            $style_el = explode(':', trim($style_attr));
                            $element_value = $style_el[1];
                            // replace url() if it's background-image field value
                            if ($style_el[0] == 'background-image') {
                                $element_value_replace = array('url(', ')');
                                $element_value = str_replace($element_value_replace, '', $style_el[1]);
                            }
                            $class_data[str_replace('-', '_', $style_el[0])] = $element_value;
                        }
                    }
                }
                $items_to_replace = array(':', '-');
                $this->context->smarty->assign(array(''.str_replace($items_to_replace, '_', $class_name).'' => $class_data));
            }
        }
    }

    public static function generateUniqueStyles()
    {
        $dir_files = Tools::scandir(Tmmegamenu::stylePath(), 'css');
        $active_files = MegaMenu::getItemAllUniqueCodes();
        $combined_css = '';
        if (file_exists(Tmmegamenu::stylePath().'megamenu_custom_styles.css')) {
            $combined_css .= Tools::file_get_contents(Tmmegamenu::stylePath().'megamenu_custom_styles.css');
        }

        foreach ($dir_files as $dir_file) {
            if (file_exists(Tmmegamenu::stylePath().$dir_file) && in_array(str_replace('.css', '', $dir_file), $active_files)) {
                $combined_css .= Tools::file_get_contents(Tmmegamenu::stylePath().$dir_file);
            }
        }

        if (!Tools::isEmpty($combined_css)) {
            // combine all custom style to one css file
            $file = fopen(Tmmegamenu::stylePath().'combined_unique_styles.css', 'w');
            fwrite($file, $combined_css);
            fclose($file);
        } else {
            // remove cobined css file if no custom style exists
            if (file_exists(Tmmegamenu::stylePath().'combined_unique_styles.css')) {
                @unlink(Tmmegamenu::stylePath().'combined_unique_styles.css');
            }
        }
        return true;
    }

    protected function getWarningMultishop()
    {
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            return $this->displayError($this->l('You cannot manage slides
                                                    items from "All Shops" or "Group Shop" context,
                                                    select the store you want to edit'));
        } else {
            return false;
        }
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') != $this->name) {
            return;
        }

        $default_country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));
        $this->context->controller->addJquery();
        $this->context->controller->addJqueryUI('ui.sortable');
        $this->context->controller->addJqueryUi('ui.widget');
        $this->context->controller->addJqueryPlugin('tagify');
        $this->context->controller->addJqueryPlugin('colorpicker');
        $this->context->controller->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
        $this->context->controller->addJS(_PS_JS_DIR_.'admin/tinymce.inc.js');
        $this->context->controller->addJS('http'.((Configuration::get('PS_SSL_ENABLED')
                                                && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'))
                                                ? 's'
                                                : '').'://maps.google.com/maps/api/js?sensor=true&amp;region='.Tools::substr($default_country->iso_code, 0, 2));
        $this->context->controller->addJS($this->_path.'views/js/admin.js');
        $this->context->controller->addCSS($this->_path.'views/css/admin.css');
    }

    protected function addGoogleScript($cached)
    {
        $default_country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));
        $google_part = '://maps.google.com/';
        $google_script = 'http'.((Configuration::get('PS_SSL_ENABLED')
                && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'))
                ? 's'
                : '').'://maps.google.com/maps/api/js?region='.Tools::substr($default_country->iso_code, 0, 2);
        $entry = strpos(implode(',', $this->context->controller->js_files), $google_part);
        if ($cached && !$entry) {
            $this->context->controller->addJS($google_script);
        } else if (!$cached && $this->has_map && !$entry) {
            $this->context->controller->addJS($google_script);
        }
    }

    public function hookActionObjectCategoryAddAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectCategoryUpdateAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectCategoryDeleteAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectCmsUpdateAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectCmsDeleteAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectCmsAddAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectSupplierUpdateAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectSupplierDeleteAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectSupplierAddAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectManufacturerUpdateAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectManufacturerDeleteAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectManufacturerAddAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectProductUpdateAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectProductAddAfter($params)
    {
        $this->clearCache();
    }

    public function hookCategoryUpdate($params)
    {
        $this->clearCache();
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'views/js/hoverIntent.js');
        $this->context->controller->addJS($this->_path.'views/js/superfish.js');
        $this->context->controller->addJS($this->_path.'views/js/tmmegamenu.js');
        $this->context->controller->addCSS($this->_path.'views/css/tmmegamenu.css');
        $this->context->controller->addCSS($this->_path.'views/css/items/combined_unique_styles.css');
    }

    public function hookDisplayTop($params, $hook = 'top')
    {
        if (!$this->isCached('menu.tpl', $this->getCacheId('tmmegamenu'))) {
            $this->user_groups = ($this->context->customer->isLogged() ?
                $this->context->customer->getGroups() : array(Configuration::get('PS_UNIDENTIFIED_GROUP')));
            $this->page_name = Dispatcher::getInstance()->getController();
            if (Tools::isEmpty($this->menu)) {
                $this->makeMenuTop();
            }
            $this->smarty->assign('MENU', $this->menu);
            $this->smarty->assign('hook', $hook);
            $this->addGoogleScript(false);
        } else {
            $this->addGoogleScript(true);
        }
        return $this->display(__FILE__, 'views/templates/hook/menu.tpl', $this->getCacheId('tmmegamenu'));
    }

    public function hookLeftColumn()
    {
        return $this->hookDisplayTop(false, 'left_column');
    }

    public function hookRightColumn()
    {
        return $this->hookDisplayTop(false, 'right_column');
    }

    public function hookFooter()
    {
        return $this->hookDisplayTop(false, 'footer');
    }
}
