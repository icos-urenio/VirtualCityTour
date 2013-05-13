<?php
/**
 * @version     2.5.x
 * @package     com_virtualcitytour
 * @copyright   Copyright (C) 2011 - 2012 URENIO Research Unit. All rights reserved.
 * @license     GNU Affero General Public License version 3 or later; see LICENSE.txt
 * @author      Ioannis Tsampoulatidis for the URENIO Research Unit
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Virtualcitytour model.
 */
class VirtualcitytourModelPoi extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_VIRTUALCITYTOUR';


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Poi', $prefix = 'VirtualcitytourTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_virtualcitytour.poi', 'poi', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_virtualcitytour.edit.poi.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			//Do any procesing on fields here if needed
			if($item->votes == 0 || $item->votes == '')
				$item->votes = 0;	
				
			//keep poi status to session so before saving to check for changes...
			$session =& JFactory::getSession();
			$session->set( 'previousPoiStatus', $item->currentstatus );
			$session->set( 'previousCatid', $item->catid );
		}
		
		return $item;
	}


	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable(&$table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__virtualcitytour');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
			$table->reported = date('Y-m-d H:i:s');
		}
		
		
		if($table->reported == '0000-00-00 00:00:00'){
			$table->reported = date('Y-m-d H:i:s');
			$table->acknowledged = '0000-00-00 00:00:00';		
			$table->closed = '0000-00-00 00:00:00';		
		}
		
		if($table->currentstatus == '2'){
			if($table->acknowledged == '0000-00-00 00:00:00'){
				$table->acknowledged = date('Y-m-d H:i:s');
				$table->closed = '0000-00-00 00:00:00';
			}
		}
		if($table->currentstatus == '3'){
			if($table->closed == '0000-00-00 00:00:00'){
				$table->closed = date('Y-m-d H:i:s');
			}
			
		}
		
		if($table->userid == 0){
			$user =& JFactory::getUser();
			$table->userid = $user->id;  
		}
		
		/*TODO: Tide up the following lines to a decent member function */
		
		//get link to the poi
		$poiLink = 'http://' . $_SERVER['HTTP_HOST'] . JRoute::_('index.php?option=com_virtualcitytour&view=poi&poi_id='.$table->id);
		$poiLink = str_replace('/administrator', '', $poiLink);
		
		$user =& JFactory::getUser($table->userid);	//user's id needed. Not admin's
		$app = JFactory::getApplication();
		$mailfrom	= $app->getCfg('mailfrom');
		$fromname	= $app->getCfg('fromname');
		$sitename	= $app->getCfg('sitename');		

		$session =& JFactory::getSession();
		$prev 	 = $session->get( 'previousPoiStatus', -1 );		
		$prevCatid = $session->get( 'previousCatid', -1 );		
		
		$current = $table->currentstatus;		
		
		$app = JFactory::getApplication();
		
		// Load the parameters.
		$settings = &JComponentHelper::getParams('com_virtualcitytour');
		$mailCategoryChangeAdmins = $settings->get('mailcategorychangeadmins');		
		$mailStatusChangeUser = $settings->get('mailstatuschangeuser');		
		
		/* (A) ****---  Send notification mail for status change to the user who submitted the poi */		
		if($mailStatusChangeUser == 1) {
			$poiRecipient = $user->email;		
			if($prev != -1 && $poiRecipient != ''){	//just in case anything wrong with session...
				$subject = JText::_('COM_VIRTUALCITYTOUR_MAIL_USER_STATUS_CHANGE_SUBJECT');

				$mail = JFactory::getMailer();
				$mail->isHTML(true);
				$mailer->Encoding = 'base64';
				$mail->addRecipient($poiRecipient);
				$mail->setSender(array($mailfrom, $fromname));
				$mail->setSubject($sitename.': '.$subject);

				if($prev == 1 && $current == 2){	//open to ack
					
					$body = sprintf(JText::_('COM_VIRTUALCITYTOUR_CHANGE_STATUS_BODY_ACK')
							, $table->title
							, $poiLink
							, $poiLink );
											
					$mail->setBody($body);
					$sent = $mail->Send();			
					
					//inform admin with message than an email is sent to the user
					JFactory::getApplication()->enqueueMessage( sprintf(JText::_('COM_VIRTUALCITYTOUR_STATUS_CHANGE_INFO_MESSAGE_ACK'), $user->name, $user->email) );
					
				}
				if( ($prev == 2 && $current == 3) || ($prev == 1 && $current == 3) ){	//ack to close or open to close
					
					$body = sprintf(JText::_('COM_VIRTUALCITYTOUR_CHANGE_STATUS_BODY_CLOSE')
							, $table->title
							, $poiLink
							, $poiLink );					
					
					$mail->setBody($body);
					$sent = $mail->Send();				
					
					//inform admin with message than an email is sent to the user
					JFactory::getApplication()->enqueueMessage( sprintf(JText::_('COM_VIRTUALCITYTOUR_STATUS_CHANGE_INFO_MESSAGE_CLOSE'), $user->name, $user->email) );				
				}

			}
		}//settings
	}
}
