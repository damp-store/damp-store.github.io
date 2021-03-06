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

require_once(dirname(__FILE__).'/../../facebook/autoload.php');

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphLocation;
use Facebook\GraphUser;
use Facebook\Entities\AccessToken;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookHttpable;

class TMSocialLoginFacebookLinkModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $back = $this->context->link->getModuleLink('tmsociallogin', 'facebooklink', array(), true, $this->context->language->id);

        if (!$this->context->customer->isLogged()) {
            Tools::redirect('index.php?controller=authentication&back='.urlencode($back));
        }

        $facebookid = Configuration::get('TMSOCIALLOGIN_FAPPID');
        $facebookkey = Configuration::get('TMSOCIALLOGIN_FAPPSECRET');

        FacebookSession::setDefaultApplication($facebookid, $facebookkey);
        // login helper with redirect_uri
        $helper = new FacebookRedirectLoginHelper($back);

        try {
            $session = $helper->getSessionFromRedirect();
        } catch (FacebookRequestException $ex) {
            // When Facebook returns an error
            $this->errors[] = Tools::displayError('Error: Authentication failed.');
        } catch (Exception $ex) {
            // When validation fails or other local issues
            $this->errors[] = Tools::displayError('Error: Authentication failed.');
        }

        // see if we have a session
        if (isset($session)) {
            // graph api request for user data
            $request = new FacebookRequest($session, 'GET', '/me', array('fields' => 'id,name'));
            $response = $request->execute();
            // get response
            $user = $response->getGraphObject(GraphUser::className());
            $user_id = $user->getProperty('id');
            $user_name = $user->getProperty('name');
        } else {
            Tools::redirect($helper->getLoginUrl(array('email')));
        }

        if (!$user_id) {
            Tools::redirect($helper->getLoginUrl(array('email')));
        }

        $customer_id = Db::getInstance()->getValue(
            'SELECT `id_customer`
            FROM `'._DB_PREFIX_.'customer_tmsociallogin`
            WHERE `social_id` = \''.$user_id.'\'
            AND `social_type` = \'facebook\'
            '
        );

        if ($customer_id > 0 && $customer_id != $this->context->customer->id) {
            $this->context->smarty->assign(array(
                'facebook_status' => 'error',
                'facebook_massage' => 'The Facebook account is already linked to another account.',
                'facebook_picture' => 'https://graph.facebook.com/'.$user_id.'/picture',
                'facebook_name' => $user_name
            ));
        } else if ($customer_id == $this->context->customer->id) {
            $this->context->smarty->assign(array(
                'facebook_status' => 'linked',
                'facebook_massage' => 'The Facebook account is already linked to your account.',
                'facebook_picture' => 'https://graph.facebook.com/'.$user_id.'/picture',
                'facebook_name' => $user_name
            ));
        } else {
            $facebook_id = Db::getInstance()->getValue('
                SELECT `social_id`
                FROM `'._DB_PREFIX_.'customer_tmsociallogin`
                WHERE `id_customer` = \''.(int)$this->context->customer->id.'\'
                AND `social_type` = \'facebook\'
                AND `id_shop` = '.(int)$this->context->getContext()->shop->id);

            if (!$facebook_id) {
                Db::getInstance()->insert(
                    'customer_tmsociallogin',
                    array(
                        'id_customer' => (int)$this->context->customer->id,
                        'social_id' => $user_id,
                        'social_type' => 'facebook'
                    )
                );
    
                $this->context->smarty->assign(array(
                    'facebook_status' => 'confirm',
                    'facebook_massage' => 'Your Facebook account has been linked to account.',
                    'facebook_picture' => 'https://graph.facebook.com/'.$user_id.'/picture',
                    'facebook_name' => $user_name
                ));
            } else {
                $this->context->smarty->assign(array(
                    'facebook_status' => 'error',
                    'facebook_massage' => 'Sorry, unknown error..',
                ));
            }
        }

        $this->setTemplate('facebooklink.tpl');
    }
}
