<?php
/**
 * @version     2.5.x
 * @package     com_virtualcitytour
 * @copyright   Copyright (C) 2011 - 2013 URENIO Research Unit. All rights reserved.
 * @license     GNU Affero General Public License version 3 or later; see LICENSE.txt
 * @author      Ioannis Tsampoulatidis for the URENIO Research Unit
 */


// no direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_virtualcitytour')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// require helper file
JLoader::register('VirtualCityTourHelper', dirname(__FILE__) . '/helpers/virtualcitytour.php');

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JController::getInstance('Virtualcitytour');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
