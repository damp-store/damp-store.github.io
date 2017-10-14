<?php
/**
* 2002-2015 TemplateMonster
*
* TM Search
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

class TmSearchAjaxSearchModuleFrontController extends ModuleFrontController
{
    private $ajax_search = '';
    public function initContent()
    {
        $tmsearch = new Tmsearch();
        $tmsearchclass = new TmSearchSearch();
        $id_lang = Tools::getValue('id_lang');
        $category_id = Tools::getValue('category');

        if (!$id_lang) {
            $id_lang = $this->context->language->id;
        }

        $query = Tools::replaceAccentedChars(urldecode(Tools::getValue('q')));
        $output = array();


        $search_results = $tmsearchclass->tmfind((int)$id_lang, $query, $category_id, 1, 10, 'position', 'desc', true);

        if (is_array($search_results)) {
            foreach ($search_results as &$product) {
                $usetax = (Product::getTaxCalculationMethod((int)Context::getContext()->customer->id) != PS_TAX_EXC);

                $pr = new Product($product['id_product'], true, $this->context->language->id);
                $this->context->smarty->assign(
                    'tmsearchsettings',
                    array(
                        'tmsearch_image' => (bool)Configuration::get('PS_TMSEARCH_AJAX_IMAGE'),
                        'tmsearch_description' => (bool)Configuration::get('PS_TMSEARCH_AJAX_DESCRIPTION'),
                        'tmsearch_price' => (bool)Configuration::get('PS_TMSEARCH_AJAX_PRICE'),
                        'tmsearch_reference' => (bool)Configuration::get('PS_TMSEARCH_AJAX_REFERENCE'),
                        'tmsearch_manufacturer' => (bool)Configuration::get('PS_TMSEARCH_AJAX_MANUFACTURER'),
                        'display_supplier' => (bool)Configuration::get('PS_TMSEARCH_AJAX_SUPPLIERS')
                    )
                );

                $this->context->smarty->assign('usetax', $usetax);
                $this->context->smarty->assign('product', $pr);

                $output[] = $tmsearch->display($tmsearch->getLocalPath(), '/views/templates/hook/_items/row.tpl');
            }
        }

        if (!count($output)) {
            $l = new Tmsearch();
            $this->ajaxDie(Tools::jsonEncode(array('empty' => $l->l('No product found'))));
        }

        $total = count($output);

        $this->ajaxDie(Tools::jsonEncode(array('result' => $output, 'total' => $total)));

        parent::initContent();
    }
}
