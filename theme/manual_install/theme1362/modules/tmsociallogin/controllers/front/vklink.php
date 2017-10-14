<?php
/**
* 2002-2015 TemplateMonster
*
* TemplateMonster Social Login
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

class TMSocialLoginVKLinkModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        if (!$this->context->customer->isLogged()) {
            $back = $this->context->link->getModuleLink('tmsociallogin', 'vklink', array(), true, $this->context->language->id);
            Tools::redirect('index.php?controller=authentication&back='.urlencode($back));
        }

        $user_id = Tools::getValue('user_id');
        $user_name = Tools::getValue('user_name');
        $profile_image_url = Tools::getValue('profile_image_url');
        
        $customer_id = Db::getInstance()->getValue(
            'SELECT `id_customer`
            FROM `'._DB_PREFIX_.'customer_tmsociallogin`
            WHERE `social_id` = \''.$user_id.'\'
            AND `social_type` = \'vk\'
            '
        );
        
        if ($customer_id > 0 && $customer_id != $this->context->customer->id) {
            $this->context->smarty->assign(array(
                'vk_status' => 'error',
                'vk_massage' => 'The VK account is already linked to another account.',
                'vk_picture' => $profile_image_url,
                'vk_name' => $user_name
            ));
        } elseif ($customer_id == $this->context->customer->id) {
            $this->context->smarty->assign(array(
                'vk_status' => 'linked',
                'vk_massage' => 'The VK account is already linked to your account.',
                'vk_picture' => $profile_image_url,
                'vk_name' => $user_name
            ));
        } else {
            $vk_id = Db::getInstance()->getValue('
                SELECT `social_id`
                FROM `'._DB_PREFIX_.'customer_tmsociallogin`
                WHERE `id_customer` = \''.(int)$this->context->customer->id.'\'
                AND `social_type` = \'vk\'
            '.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER));
            
            if (!$vk_id) {
                Db::getInstance()->insert(
                    'customer_tmsociallogin',
                    array(
                        'id_customer' => (int)$this->context->customer->id,
                        'social_id' => $user_id,
                        'social_type' => 'vk'
                    )
                );
                    
                $this->context->smarty->assign(array(
                    'vk_status' => 'confirm',
                    'vk_massage' => 'Your VK account has been linked to account.',
                    'vk_picture' => $profile_image_url,
                    'vk_name' => $user_name
                ));
            } else {
                $this->context->smarty->assign(array(
                    'vk_status' => 'error',
                    'vk_massage' => 'Sorry, unknown error.',
                ));
            }
        }

        $this->setTemplate('vklink.tpl');
    }
}
