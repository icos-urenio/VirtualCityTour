<?php
/**
 * @version     2.5.x
 * @package     com_virtualcitytour
 * @copyright   Copyright (C) 2011 - 2012 URENIO Research Unit. All rights reserved.
 * @license     GNU Affero General Public License version 3 or later; see LICENSE.txt
 * @author      Ioannis Tsampoulatidis for the URENIO Research Unit
 */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Content Component Category Tree
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.6
 */
class VirtualcitytourCategories extends JCategories
{
	public function __construct($options = array())
	{
		$options['table'] = '#__virtualcitytour';
		$options['extension'] = 'com_virtualcitytour';
		parent::__construct($options);
	}
}
