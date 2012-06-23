<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/** Import library dependencies */
jimport('joomla.plugin.plugin');

/**
 * plgTiendaCharts_fusioncharts class.
 *
 * @extends JPlugin
 */
class plgTiendaCharts_fusioncharts extends JPlugin
{
    /**
     * constructor function
     * 
     * @param $subject
     * @param $options
     * @return void
     */
    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage( '', JPATH_ADMINISTRATOR );
    }

	/**
	 * renderTiendaChart function.
	 * 
	 * @access public
	 * @param mixed $data
	 * @param string $title. (default: 'A Tienda FusionChart')
	 * @param string $type. (default: 'Column2D')
	 * @param int $width. (default: 550)
	 * @param int $height. (default: 250)
	 * @return string
	 */
	function renderTiendaChart($data, $title='A Tienda FusionChart', $type='Column2D', $width=550, $height=250)
    {
        $chart = 'No chart produced yet.';
        
        if (!empty($data)) {
            // Include the FusionCharts Class file
            require_once( dirname(__FILE__).DS.'charts_fusioncharts_media'.DS.'FusionCharts_Gen.php' );
			
			if(version_compare(JVERSION,'1.6.0','ge')) {
    // Joomla! 1.6+ code here
  $path = 'plugins/tienda/charts_fusioncharts/charts_fusioncharts_media/';
} else {
    // Joomla! 1.5 code here
   $path = 'plugins/tienda/charts_fusioncharts_media/';
}
			
            JHTML::_('script', 'FusionCharts.js', $path);

            $title  = JText::_( $title );

            // Chart types
            switch ($type) {
                case 'Column3D':
                    $type = 'Column3D';
                    break;
                case 'Bar':
                case 'Bar2D':
                    $type = 'Bar2D';
                    break;
                case 'Line':
                    $type = 'Line';
                    break;
                default:
                    $type = 'Column2D';
                    break;
            }

            // Create a new instance of the FusionCharts class
            $FC = new FusionCharts($type, $width, $height);

            // Tell the object where the SWF files live.
            $FC->setSWFPath("../".$path ."Charts/");

            $max = 0;
            foreach($data as $obj) {
				$max = ($obj->value > $max) ? $obj->value : $max;
                $FC->addChartData($obj->value, 'name='.$obj->label);
            }

            // Set chart attributes
            $option = ($max == 0) ? 'yAxisMaxValue=100' : '';
            $strParam="caption=$title;showValues=0;rotateNames=1;decimalPrecision=0;formatNumberScale=0;$option";
            $FC->setChartParams($strParam);

            ob_start();
            $FC->renderChart();
            $chart = ob_get_contents();
            ob_end_clean();

        }
        return $chart;
	}

}
