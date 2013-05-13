<?php
/**
 * @version     2.5.x
 * @package     com_virtualcitytour
 * @copyright   Copyright (C) 2011 - 2013 URENIO Research Unit. All rights reserved.
 * @license     GNU Affero General Public License version 3 or later; see LICENSE.txt
 * @author      Ioannis Tsampoulatidis for the URENIO Research Unit
 */

// No direct access
defined('_JEXEC') or die;


class VirtualcitytourHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($vName = '')
	{

		JSubMenuHelper::addEntry(
			JText::_('COM_VIRTUALCITYTOUR_TITLE_POIS'),
			'index.php?option=com_virtualcitytour&view=pois',
			$vName == 'pois'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_VIRTUALCITYTOUR_SUBMENU_CATEGORIES'), 
			'index.php?option=com_categories&view=categories&extension=com_virtualcitytour', 
			$vName == 'categories'
		);
		JSubMenuHelper::addEntry(
				JText::_('COM_VIRTUALCITYTOUR_SUBMENU_COMMENTS'),
				'index.php?option=com_virtualcitytour&view=comments',
				$vName == 'comments'
		);
		JSubMenuHelper::addEntry(
				JText::_('COM_VIRTUALCITYTOUR_SUBMENU_REPORTS'),
				'index.php?option=com_virtualcitytour&view=reports',
				$vName == 'reports'
		);
		JSubMenuHelper::addEntry(
				JText::_('COM_VIRTUALCITYTOUR_SUBMENU_KEYS'),
				'index.php?option=com_virtualcitytour&view=keys',
				$vName == 'keys'
		);		
				
		// set some global property
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('.icon-48-poi {background-image: url(../media/com_virtualcitytour/images/virtualcitytour-48x48.png);}');
		$document->addStyleDeclaration('.icon-48-pois {background-image: url(../media/com_virtualcitytour/images/virtualcitytour-48x48.png);}');
		if ($vName == 'categories') 
		{
			$document->setTitle(JText::_('COM_VIRTUALCITYTOUR_ADMINISTRATION_CATEGORIES'));
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_virtualcitytour';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
	

	public static function getImages($itemId)
	{
		$html = '';
		$imagesPath = JPATH_SITE.DS.'images'.DS.'virtualcitytour'.DS.$itemId.DS.'images'.DS.'thumbs'.DS.'*.*';
		foreach (glob($imagesPath) as $filename) {
			$imgpath = JURI::root().'images/virtualcitytour/'.$itemId.'/images/thumbs/' . basename($filename);
			$imgbig = JURI::root().'images/virtualcitytour/'.$itemId.'/images/' . basename($filename);
	
			$html .= '<div class="thumb">';
			$html .= '<a class="modal" href="'.$imgbig.'"><img src="' .$imgpath. '" width="100" height="80" /></a>';
			$html .= '<br /><a href="#" onclick="deleteImage(\' '.$imgpath.'\' ,\''.basename($filename).'\');">Delete</a></div>';
	
		}
	
		return $html;
	}
	
	public static function getPanoramas($itemId)
	{
		$html = '';
		$imagesPath = JPATH_SITE.DS.'images'.DS.'virtualcitytour'.DS.$itemId.DS.'panoramas'.DS.'thumbs'.DS.'*.*';
		foreach (glob($imagesPath) as $filename) {
			$imgpath = JURI::root().'images/virtualcitytour/'.$itemId.'/panoramas/thumbs/' . basename($filename);
			$imgbig = JURI::root().'images/virtualcitytour/'.$itemId.'/panoramas/' . basename($filename);
	
			$html .= '<div class="thumb">';
			$html .= '<a class="modal" href="'.$imgbig.'"><img src="' .$imgpath. '" width="100" height="80" /></a>';
			$html .= '<br /><a href="#" onclick="deletePanorama(\' '.$imgpath.'\' ,\''.basename($filename).'\');">Delete</a></div>';
	
	
		}
	
		return $html;
	}	
}
