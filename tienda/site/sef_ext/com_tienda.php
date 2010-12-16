<?php
<?php
/**
 * <p>sh404SEF support for com_tienda component.</p>
 * 
 * @version	1.0.0
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

######################################################################################
#---------------= standard plugin initialize function - don't change =---------------#
global $sh_LANG, $sefConfig;
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
#---------------=  standard plugin initialize function - don't change =---------------#
######################################################################################

//remove common URL from GET vars list, so that they don't show up as query string in the URL
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('view');
shRemoveFromGETVarsList('lang');
if (!empty($Itemid))
  shRemoveFromGETVarsList('Itemid');
if (!empty($limit))
shRemoveFromGETVarsList('limit');
if (isset($limitstart))
  shRemoveFromGETVarsList('limitstart'); // limitstart can be zero


$task = isset($task) ? @$task : null;
$Itemid = isset($Itemid) ? @$Itemid : null;
$shName = shGetComponentPrefix($option);
$shName = empty($shSampleName) ?
		getMenuTitle($option, $task, $Itemid, null, $shLangName) : $shName;
//$shName = (empty($shName) || $shName == '/') ? 'calendar':$shName;
$title[] = $shName;

######################################################################################
#---------------= standard plugin finalize function - don't change =---------------#
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString,
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null),
      (isset($shLangName) ? @$shLangName : null));
}
#---------------=  standard plugin finalize function - don't change =---------------#
######################################################################################

?>
