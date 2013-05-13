<?php
/**
 * @version     2.5.x
 * @package     com_virtualcitytour
 * @copyright   Copyright (C) 2011 - 2013 URENIO Research Unit. All rights reserved.
 * @license     GNU Affero General Public License version 3 or later; see LICENSE.txt
 * @author      Ioannis Tsampoulatidis for the URENIO Research Unit
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Pois list controller class.
 */
class VirtualcitytourControllerKeys extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'key', $prefix = 'VirtualcitytourModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		
		return $model;
	}
	
	public function updateCategoryTimestamp()
	{
		//get model and categories
		$model = $this->getModel('keys');
		$updated = $model->updateCategoryTimestamp();
		$this->setRedirect("index.php?option=com_virtualcitytour&view=keys");
	}	
}
