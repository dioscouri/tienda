<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class TiendaHelperSubscription extends TiendaHelperBase
{
    /**
     * Given a subscription ID, will cancel it
     * 
     * @param unknown_type $subscription_id
     * @return unknown_type
     */
    function cancel( $subscription_id )
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $subscription = JTable::getInstance('Subscriptions', 'TiendaTable');
        $subscription->subscription_id = $subscription_id;
        $subscription->subscription_enabled = 0;
        if (!$subscription->save())
        {
            $this->setError( $subscription->getError() );
            return false;
        }
        return true;
    }

    /**
     * Given a user_id and product_id, checks if the user has a valid subscription for it.
     * Optional date will check to see if subscription is/was/will-be valid on a certain date
     * 
     * @param $user_id
     * @param $product_id
     * @param $date (TBD) , $date=''
     * 
     * @return unknown_type
     */
    function isValid( $user_id, $product_id )
    {
        $date='';
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Subscriptions', 'TiendaModel' );
        $model->setState("filter_userid", $user_id );
        $model->setState("filter_productid", $product_id );
        $model->setState("filter_enabled", 1);
        if (!empty($date))
        {
            // TODO Enable this.  Add filters to model and set state here.
        }
        
        if ($subscriptions = $model->getList())
        {
            return true;
        }
        return false;
        
    }
    
    /**
     * 
     * Get's a subscription's history
     * @param $subscription_id
     * @return array
     */
    function getHistory( $subscription_id )
    {
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'SubscriptionHistory', 'TiendaModel' );
        $model->setState("filter_subscriptionid", $subscription_id );
        $model->setState("order", 'tbl.created_datetime' );
        $model->setState("direction", 'ASC');
        if ($data = $model->getList())
        {
            return $data;
        }
        return array();
    }
    
    /**
     * Checks for subscriptions that have expired,
     * sets them to expired, and sends out email notices
     *  
     * @return unknown_type
     */
    function checkExpired()
    {
        $date = JFactory::getDate();
        $today = $date->toFormat( "%Y-%m-%d 00:00:00" );
        
        // select all subs that have expired but still have status = '1';
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Subscriptions', 'TiendaModel' );        
        $model->setState("filter_datetype", 'expires' );
        $model->setState("filter_date_to", $today );
        $model->setState("filter_enabled", '1' );
        if ($list = $model->getList())
        {
            foreach ($list as $item)
            {
                $this->setExpired( $item->subscription_id, $item );
            }
        }

        if ($list = $model->getListByIssues() )
        {
            foreach ($list as $item)
            {
                $this->setExpired( $item->subscription_id, $item );
            }
        }
        
        // Update config to say this has been done
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $config = JTable::getInstance( 'Config', 'TiendaTable' );
        $config->load( array( 'config_name'=>'subscriptions_last_checked') );
        $config->config_name = 'subscriptions_last_checked';
        $config->value = $today;
        $config->save();
        
        // TODO send summary email to admins
    }
    
    /**
     * Marks a subscription as expired
     * and sends the expired email to the user
     * 
     * @param $subscription_id
     * @param object $item Single item from the Subscriptions model 
     * @return unknown_type
     */
    function setExpired( $subscription_id, $item='' )
    {
        // change status = '0'
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $subscription = JTable::getInstance('Subscriptions', 'TiendaTable');
        $subscription->subscription_id = $subscription_id;
        $subscription->subscription_enabled = 0;
        if (!$subscription->save())
        {
            $this->setError( $subscription->getError() );
            return false;
        }
        
        // fire plugin event onAfterExpiredSubscription        
        JPluginHelper::importPlugin( 'tienda' );
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onAfterExpiredSubscription', array( $subscription ) );
        
        if (empty($item))
        {
            JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
            $model = JModel::getInstance( 'Subscriptions', 'TiendaModel' );
            $model->setId( $subscription_id );
            $item = $model->getItem();
        }
        
        //remove user in juga group if integration exist
        Tienda::load( 'TiendaHelperJuga', 'helpers.juga' );
        $helperJuga = new TiendaHelperJuga();
        if($helperJuga->isInstalled())
        {              
        	if($helperJuga->doExpiredSubscription( $item ))
         	{
         		//do something 
         		//either send email or raised message
         	}         	
        }        

        // Email user that their subs expired
        if (!empty($item))
        {
            Tienda::load( "TiendaHelperBase", 'helpers._base' );
            $helper = TiendaHelperBase::getInstance('Email');
            $helper->sendEmailNotices($item, 'subscription_expired');
        }

        return true;
    }

    /**
     * Checks for subscriptions that expire x days in future
     * and sends out email notices
     *  
     * @return unknown_type
     */
    function checkExpiring()
    {
        $config = TiendaConfig::getInstance();
        
        // select all subs that expire in x days (where expires > today + x & expires < today + x + 1)
        $subscriptions_expiring_notice_days = $config->get( 'subscriptions_expiring_notice_days', '14' );
        $subscriptions_expiring_notice_days_end = $subscriptions_expiring_notice_days + '1';
        $date = JFactory::getDate();
        $today = $date->toFormat( "%Y-%m-%d 00:00:00" );
        
        $database =& JFactory::getDBO();
        $query = " SELECT DATE_ADD('".$today."', INTERVAL %s DAY) ";
        $database->setQuery( sprintf($query, $subscriptions_expiring_notice_days ) );
        $start_date = $database->loadResult();

        $database->setQuery( sprintf($query, $subscriptions_expiring_notice_days_end ) );
        $end_date = $database->loadResult();
        
        // select all subs that expire between those dates
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Subscriptions', 'TiendaModel' );
        $model->setState("filter_datetype", 'expires' );
        $model->setState("filter_date_from", $start_date );
        $model->setState("filter_date_to", $end_date );
        $model->setState("filter_enabled", '1' );
        if ($list = $model->getList())
        {
            Tienda::load( "TiendaHelperBase", 'helpers._base' );
            $helper = TiendaHelperBase::getInstance('Email');
            foreach ($list as $item)
            {
                // Send expiring email for $item
                $helper->sendEmailNotices($item, 'subscription_expiring');
            }
        }
        
        if ($list = $model->getListByIssues( $subscriptions_expiring_notice_days ) )
        {
            Tienda::load( "TiendaHelperBase", 'helpers._base' );
            $helper = TiendaHelperBase::getInstance('Email');
            foreach ($list as $item)
            {
                // Send expiring email for $item
                $helper->sendEmailNotices($item, 'subscription_expiring');
            }
        }
    }
    
    /**
     * Ensures a subscriber has access to files 
     * added after their subscription started
     * 
     * @param $subscriptions array of subscription objects
     * @param $files array optional array of files for the subscriptions, only sent if one kind of product is in subscriptions
     * @return unknown_type
     */
    function reconcileFiles( $subscriptions, $files=array() )
    {
        $errorMsg = '';
        $date = JFactory::getDate();
        $db = JFactory::getDBO();
        $nullDate = $db->getNullDate();
        
        // foreach of the subs
        foreach ( $subscriptions as $subscription )
        {
            $productfiles = $files;
            // if files is empty, get the product's files
            if (empty($productfiles))
            {
                JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
                $model = JModel::getInstance( 'ProductFiles', 'TiendaModel' );
                $model->setState( 'filter_product', $subscription->product_id );
                $model->setState( 'filter_enabled', 1 );
                $model->setState( 'filter_purchaserequired', 1 );
                $productfiles = $model->getList();
            }
            
            // if any of the files were created after the sub's last checked date,
            // add access for the sub's user
            foreach ($productfiles as $file)
            {
                if ($file->created_date > $subscription->checkedfiles_datetime || $file->created_date == $nullDate)
                {
                    // check: was the file added while the subscription was active?
                    if ( $subscription->subscription_enabled || 
                    (empty($subscription->subscription_enabled) && $subscription->created_datetime < $file->created_date && $file->created_date < $subscription->expires_datetime)
                    )
                    {
                        $productDownload = JTable::getInstance('ProductDownloads', 'TiendaTable');
                        $productDownload->product_id = $subscription->product_id;
                        $productDownload->productfile_id = $file->productfile_id;
                        $productDownload->productdownload_max = '-1'; // TODO For now, infinite. In the future, add a field to productfiles that allows admins to limit downloads per file per purchase
                        $productDownload->order_id = $subscription->order_id;
                        $productDownload->user_id = $subscription->user_id;
                        if (!$productDownload->save())
                        {
                            // track error
                            $error = true;
                            $errorMsg .= $productDownload->getError();
                            JFactory::getApplication()->enqueueMessage( $productDownload->getError(), 'notice' );
                            // TODO What to do with this error 
                        }                        
                    }
                }
            }
            
            $subtable = JTable::getInstance('Subscriptions', 'TiendaTable');
            $subtable->subscription_id = $subscription->subscription_id;
            $subtable->checkedfiles_datetime = $date->toMySQL();
            $subtable->save();
        }

    }

    /*
     * Method which calculates parameters for pro-rated subscriptions. It gets all information and returns an array with correct values for trial period
     * @param $prorated_date      		Mask for Pro-rated date
     * @param $prorated_term      		Whether the Pro-rated trial period is monthly or daily based
     * @param $subs_period_unit   		What kind of subscription we're using
     * @param $original_trial_price 	Original trial period price
     * @param $prorated_charge 				Whether we want ot use Prorated charge or initial trial period charge
     * @param $from_time 							From what time we want to calculate the pro-rated subscription (null means now)
     * 
     * @return Array									Array with necessary information (trial unit, price, interval, if there is a trial period )
     */
		function calculateProRatedTrial( $prorated_date, $prorated_term, $subs_period_unit, $original_trial_price, $prorated_charge, $from_time = null )
		{
			if( $from_time === null )
				$from_time = time();

			$result = array();
			$result['trial'] = 0;
			$result['interval'] = 0;
			$result['unit'] = 'D';
			$result['price'] = 0;
			
			$today_d = date( 'j', $from_time );
			$today_m = date( 'm', $from_time );
			$prorated_date = explode( '/', $prorated_date );
			$prorated_date[0] = ( int )$prorated_date[0];
			$prorated_date[1] = ( int )$prorated_date[1];
			if( $prorated_date[0] == '*' )
			{
				$prorated_date[0] = $today_m;
				if( $today_d > $prorated_date[1] ) // the date should be in the next month
					$prorated_date[0]++;
			}
			else 
			{
				if( $today_m > $prorated_date[0] || ( $today_m == $prorated_date[0] && $today_d > $prorated_date[1] ) ) // the subscription starts in the next year
					$prorated_date[0] += 12;
			}
			$next_date = mktime( date("H"), date("i"), date("s"), ( int )$prorated_date[0], ( int )$prorated_date[1] );
				
			// so i know the start of the subscription - now, i'm going to calculate the trial period						
			$end_date = gregoriantojd( date( 'n', $next_date ), date( 'j', $next_date ), date( 'Y', $next_date ) );
			$start_date = gregoriantojd( date( 'n' ), date( 'j' ), date( 'Y' ) );
			$trial_period_days = $end_date - $start_date;
			$trial_period = 0;
			switch( $prorated_term ) // calculate the trial period
			{
				case 'D' :
					$trial_period = $trial_period_days; // we already calculated the difference in days
					break;
				case 'M' :									
					$trial_period = $prorated_date[0] - $today_m;
					break;
			}

			// calculate the price of the trial period, if there is a any trial period
			if( $trial_period_days )
			{
				$trial_period_price = 0;
				switch( $subs_period_unit )
				{
					case 'Y' : // yearly
						switch( $prorated_term ) // calculate the trial period
						{
							case 'D' :
								$trial_period_price = $original_trial_price / 365 * $trial_period;
								break;
							case 'M' :											
								$trial_period_price = $original_trial_price / 12 * $trial_period;
								break;
						}
						break;
					case 'M' : // monthly
						switch( $prorated_term ) // calculate the trial period
						{
							case 'D' :
								$trial_period_price = $original_trial_price / 30.416 * $trial_period;
								break;
						}
						break;
				}
				if( !$trial_period_price && $trial_period_days ) // no price was set
				{
					switch( $subs_period_unit )
					{
						case 'Y' : // yearly
							$trial_period_price = $original_trial_price / 12;
							break;
						case 'M' : // monthly
							$trial_period_price = $original_trial_price;
							break;
					}
				}
				$result['trial'] = 1;
				$result['interval'] = $trial_period_days;
				$result['unit'] = 'D';
				
			if( $prorated_charge ) // pro-rated price
					$result['price'] = $trial_period_price;
				else
					$result['price'] = $original_trial_price;
			}
			return $result;
		}

		/*
		 * Gets object of the margin (the closest or the last one) issue of a product to a specific date
		 * 
		 * @param $product_id
		 * @param $direction 
		 * @param $date In case of null, the current date is used
		 * 
		 * @return Object of the last issue
		 */
		static function getMarginalIssue( $product_id, $direction = 'ASC' , $date = null )
		{
			if($date === null )
			{
				$date = JFactory::getDate();
				$date = $date->toFormat( "%Y-%m-%d" );
			}
			$db = JFactory::getDbo();
			$q = 'SELECT tbl.* FROM `#__tienda_productissues` tbl WHERE tbl.`product_id`='.$product_id.' AND tbl.`publishing_date` >= \''.$date.'\' ORDER BY tbl.`publishing_date` '.$direction.' LIMIT 0,1 ';
			$db->setQuery( $q );
			return $db->loadObject();
		}

		/*
		 * Gets number of issues within a date range
		 * 
		 * @param $product_id
		 * @param $start_date In case of null, the current date is used
		 * @param $end_date
		 * 
		 * @return Number of issues
		 */
		static function getNumberIssues( $product_id, $start_date = null , $end_date = null )
		{
			$db = JFactory::getDbo();
			Tienda::load( 'TiendaQuery', 'library.query' );
			$q = new TiendaQuery();
			$q->select( 'count( tbl.`product_issue_id` ) ' );
			$q->from( '`#__tienda_productissues` tbl' );
			$q->where( 'tbl.`product_id`='.$product_id );
			if( $start_date === null )
				$q->where( 'tbl.`publishing_date` >= NOW()' );
			else
				$q->where( 'tbl.`publishing_date` >= \''.$start_date.'\'' );

			if( $end_date === null )
				$q->where( 'tbl.`publishing_date` >= \''.$start_date.'\'' );
				
			$db->setQuery( (string)$q );
			return $db->loadResult();
		}

		/*
		 * Displays Subscription number
		 * 
		 * @param $num Number to display
		 * 
		 * @return String to be displayed
		 */
		static function displaySubNum( $num )
		{
			$digits = TiendaConfig::getInstance()->get( 'sub_num_digits', 8 );
			$result = (string)$num;
			$len = strlen( $num );
			for( ; $len < $digits; $len++ )
				$result = '0'.$result;
				
			return $result;
		}

}