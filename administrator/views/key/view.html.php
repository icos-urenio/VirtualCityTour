<?php
/**
 * @version     2.5.x
 * @package     com_virtualcitytour
 * @copyright   Copyright (C) 2011 - 2012 URENIO Research Unit. All rights reserved.
 * @license     GNU Affero General Public License version 3 or later; see LICENSE.txt
 * @author      Ioannis Tsampoulatidis for the URENIO Research Unit
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class VirtualcitytourViewKey extends JView
{
	protected $state;
	protected $item;
	protected $form;
	
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
        if (isset($this->item->checked_out)) {
		    $checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        } else {
            $checkedOut = false;
        }
		$canDo		= VirtualcitytourHelper::getActions();

		JToolBarHelper::title(JText::_('COM_VIRTUALCITYTOUR_TITLE_KEY'), 'item.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
		{

			JToolBarHelper::apply('key.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('key.save', 'JTOOLBAR_SAVE');
		}
		/*
		if (!$checkedOut && ($canDo->get('core.create'))){
			JToolBarHelper::custom('comment.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		*/
		// If an existing item, can save to a copy.
		/*
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('poi.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		*/
		if (empty($this->item->id)) {
			JToolBarHelper::cancel('key.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('key.cancel', 'JTOOLBAR_CLOSE');
		}

	}
	
		
}
