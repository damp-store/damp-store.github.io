<?php
/**
* 2002-2015 TemplateMonster
*
* TemplateMonster Search
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

class TmSearchSearchModuleFrontController extends ModuleFrontController
{
    private $ajax_search = '';
    public function initContent()
    {
        $this->ajax_search = Tools::getValue('ajaxSearch');
        $id_lang = Tools::getValue('id_lang');
        if (!$id_lang) {
            $id_lang = $this->context->language->id;
        }

        $query = Tools::replaceAccentedChars(urldecode(Tools::getValue('q')));
        $result = array();
        $i = 0;
        if ($this->ajax_search) {
            $search_results = Search::find((int)$id_lang, $query, 1, 10);
            if (is_array($search_results['result'])) {
                foreach ($search_results['result'] as &$product) {
                    $usetax = (Product::getTaxCalculationMethod((int)Context::getContext()->customer->id) != PS_TAX_EXC);

                    $pr = new Product($product['id_product'], $this->context->language->id);
                    $cat = new Category($pr->id_category_default, $this->context->language->id);
                    $images = $pr->getImages($id_lang);
                    $manufacturer_name = Manufacturer::getNameById($pr->id_manufacturer);
                    foreach ($images as $image) {
                        if ($image['cover']) {
                            $img_url = $pr->id.'-'.$image['id_image'];
                            break;
                        }
                    }
                    $result[$i]['name'] = $pr->name[$id_lang];
                    $result[$i]['description_short'] = $pr->description_short[$id_lang];
                    $result[$i]['category'] = $cat->name;
                    $result[$i]['description_short'] = $pr->description_short[$id_lang];
                    $result[$i]['product_link'] = $this->context->link->getProductLink($pr->id, $pr->link_rewrite[$id_lang], $cat->link_rewrite[$id_lang]);
                    $result[$i]['img_url'] = $this->context->link->getImageLink($pr->link_rewrite[$id_lang], $img_url, ImageType::getFormatedName('small'));
                    if (!Configuration::get('PS_CATALOG_MODE')) { // do not display price if catalog mode on
                        $result[$i]['price'] = Tools::displayPrice($pr->getPriceStatic($pr->id, $usetax));
                    }
                    $result[$i]['price_old'] = (Tools::displayPrice($pr->price) > Tools::displayPrice($pr->getPriceStatic($pr->id, $usetax))
                                                ? Tools::displayPrice($pr->price)
                                                : '');
                    $result[$i]['reference'] = ($pr->reference?$pr->reference:'');
                    $result[$i]['manufacturer'] = ($manufacturer_name?$manufacturer_name:'');
                    $i++;
                }
            }
            $this->ajaxDie(Tools::jsonEncode($result));
        }

        parent::initContent();
    }
}
