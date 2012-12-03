<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaModelBase', 'models._base' );

class TiendaModelCheckout extends TiendaModelBase
{
    function getTable($name='Config', $prefix='TiendaTable', $options = array())
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        return parent::getTable($name, $prefix, $options);
    }

    public function validate( $values, $options=array() )
    {
        // fail if user hasn't checked terms & condition
        if( $this->defines->get('require_terms', '0') && empty($values["terms-conditions"])) {
            $this->setError( JText::_('COM_TIENDA_PLEASE_CHECK_THE_TERMS_CONDITIONS') );
        }
        
        // fail if no user->id and email address fails validation
        jimport('joomla.mail.helper');
        if( $values["user_id"] < 1 && !JMailHelper::isEmailAddress($values['email_address'])) {
            $this->setError( JText::_('COM_TIENDA_PLEASE_ENTER_CORRECT_EMAIL') );
        }
        
        // fail if registering new user but one of passwords is empty
        if( $values["user_id"] < 1 && $values["checkout_method"] == 'register' && (empty($values["register-new-password"]) || empty($values["register-new-password2"]) ) ) {
            $this->setError( JText::_('COM_TIENDA_PASSWORD_INVALID') );
        }
        
        // fail if registering new user but passwords don't match
        if( $values["user_id"] < 1 && $values["checkout_method"] == 'register' && $values["register-new-password"] != $values["register-new-password2"] ) {
            $this->setError( JText::_('COM_TIENDA_PASSWORDS_DO_NOT_MATCH') );
        }
                
        // fail if registering new user but account exists for email address provided
        $userHelper = new TiendaHelperUser();
        if ( $values["user_id"] < 1 && $values["checkout_method"] == 'register' && $userHelper->emailExists($values['email_address']) ) {
            $this->setError( JText::_('COM_TIENDA_EMAIL_ALREADY_EXIST') );
        }
        
        // fail if user logged in and guest/register method selected
        if( $values["user_id"] > 0 && ( $values["checkout_method"] == 'register' || $values["checkout_method"] == 'guest') ) {
            $this->setError( JText::_('COM_TIENDA_CANNOT_REGISTER_OR_GUEST_CHECKOUT_WHEN_LOGGED_IN') );
        }
        
        // fail if password doesn't validate and validation is enabled
        if( $this->defines->get('password_php_validate', '0')) {
            Tienda::load( 'TiendaHelperUser', 'helpers.user' );
            $userHelper = new TiendaHelperUser();
            $validate_pass = $userHelper->validateUserPassword( $values['register-new-password'] );
            if (!$validate_pass[0])
            {
                foreach ($validate_pass[1] as $error) 
                {
                    $this->setError( $error );
                }
            }
        }
                
        return $this->check();
    }
    
    function validateCoupon( $values, $options=array() )
    {
        $coupon_code = $values['coupon_code']; 
        $user_id = $values['user_id'];
        
        // check if coupon code is valid
        Tienda::load( 'TiendaHelperCoupon', 'helpers.coupon' );
        $helper_coupon = new TiendaHelperCoupon();
        $coupon = $helper_coupon->isValid( $coupon_code, 'code', $user_id );
        if (!$coupon)
        {
            $this->setError( $helper_coupon->getError() );
            return $this->check();
        }
        
        if (!empty($values['coupons']) && in_array($coupon->coupon_id, $values['coupons']))
        {
            $this->setError( JText::_('COM_TIENDA_THIS_COUPON_ALREADY_ADDED_TO_THE_ORDER') );
            return $this->check();
        }

        // Check per product coupon code
        if (!empty($values['cartitems']) && $coupon->coupon_type == '1') 
        {
            $ids = array();
            foreach($values['cartitems'] as $item)
            {
                if (is_int($item)) 
                {
                    $ids[] = $item;
                }
                elseif(is_object($item)) 
                {
                    $ids[] = $item->product_id;
                }
            }
            
            if (!empty($ids)) 
            {
                if(!$check = $helper_coupon->checkByProductIds($coupon->coupon_id, $ids))
                {
                    $this->setError( JText::_('COM_TIENDA_THIS_COUPON_NOT_RELATED_TO_PRODUCT_IN_YOUR_CART') );
                    return $this->check();
                }
            }
        }

        return $coupon;
    }
    
    public function save( $values, $options=array() )
    {
        $result = new stdClass();
        $result->error = false;
        
        Tienda::load( 'TiendaHelperUser', 'helpers.user' );
        $userHelper = new TiendaHelperUser();
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
        $userinfo = JTable::getInstance('UserInfo', 'TiendaTable');
        
        $user_id = $values["user_id"];
        $create_account = ($values["checkout_method"] == 'register') ? true : false;
        $guest_checkout = $this->defines->get('guest_checkout_enabled', '1');
        
        if ( $values["checkout_method"] == "guest" )
        {
            // save the real user info in the userinfo table
            $userinfo->user_id = $user_id;
            $userinfo->email = $values['email_address'];
            if (!$userinfo->save()) 
            {
                $result->error = true;
                $this->setError( $userinfo->getError() );
            }
            
            // save the billing and shipping addresses?
            //$this->setAddresses($submitted_values, true, true);
        }
        elseif ( $values["checkout_method"] == "register" )  
        {
            // create a new user from billing info
            $details = array(
                    'email' => $values['email_address'],
                    'name' => @$values['billing_input_first_name'].' '.@$values['billing_input_middle_name'].' '.@$values['billing_input_last_name'],
                    'username' => $values['email_address']
            );
            
            if ( strlen(trim($details['name'])) == 0 ) {
                $details['name'] = JText::_('COM_TIENDA_USER');
            }
            
            $details['password']    = $values["register-new-password"];
            $details['password2']   = $values["register-new-password2"];
            
            if (!$user = $userHelper->createNewUser( $details, false )) 
            {
                $result->error = true;
                //$this->setError( $user->getError() );
            }
            else
            {
                $userHelper->login( 
                    array('username' => $user->username, 'password' => $details['password'])
                );
                
                $user_id = $user->id;

                $userinfo->load( array('user_id' => $user_id ) );
                $userinfo->user_id = $user_id;
                $userinfo->first_name = @$values['billing_input_first_name'];
                $userinfo->last_name = @$values['billing_input_last_name'];
                $userinfo->company = @$values['billing_input_company'];
                $userinfo->middle_name = @$values['billing_input_middle_name'];
                $userinfo->phone_1 = @$values['billing_input_phone_1'];
                $userinfo->email = $values['email_address'];
                if (!$userinfo->save()) 
                {
                    $result->error = true;
                    $this->setError( $userinfo->getError() );
                }
            }
        }
        
        $result->user_id = $user_id;
        $result->userinfo = $userinfo;
        $this->result = $result;
        
        DSCModel::addIncludePath( JPATH_SITE . '/components/com_tienda/models' );
        $model = DSCModel::getInstance('Userinfo', 'TiendaModel' );
        $model->clearCache();
        
        if ($result->error) 
        {
            return false;
        }
        
        return $result;
    }
}