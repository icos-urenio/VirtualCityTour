<?php
/**
 * @version     2.5.x
 * @package     com_virtualcitytour
 * @copyright   Copyright (C) 2011 - 2012 URENIO Research Unit. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Ioannis Tsampoulatidis for the URENIO Research Unit
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');
jimport('joomla.application.categories');
jimport('joomla.html.pagination');

JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_virtualcitytour/tables');

/**
 * Model
 */
class VirtualcitytourModelDiscussions extends JModelList
{
	protected $mailNewCommentUser;
	protected $mailNewCommentAdmins;
	
	protected $items;
	private $poi_id = null;
	
	function getItems($poi_id = '')	//usually we don't use arguments but it's really useful for the pois view and model
	{
		
		$this->poi_id = $poi_id;
		
		if($this->poi_id == null || $this->poi_id == ''){
			$this->poi_id = JRequest::getVar('poi_id');
		}
		
		// Invoke the parent getItems method to get the main list
		$items = &parent::getItems();
		//$this->_total = count($items);
		
		
		// Convert the params field into an object, saving original in _params
		for ($i = 0, $n = count($items); $i < $n; $i++) {
			$item = &$items[$i];
			
			//calculate relative dates here
			$item->progressdate_rel = VirtualcitytourHelper::getRelativeTime($item->created);
		}
		
		$this->items = $items;
		return $items;	
	}

	protected function getListQuery()
	{
	
		//$user	= JFactory::getUser();
		//$groups	= implode(',', $user->getAuthorisedViewLevels());
		

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);

		$query->from('`#__virtualcitytour_comments` AS a');
		if($this->poi_id != null)
			$query->where('a.state = 1 AND a.virtualcitytourid='. (int) $this->poi_id);
		else
			$query->where('a.state = 1');

		// Join on user table.
		$query->select('u.name AS fullname');
		$query->join('LEFT', '#__users AS u on u.id = a.userid');			

		$query->order('a.created desc');
		
		return $query;
	}	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	
	protected function populateState($ordering = null, $direction = null)
	{
		
		// Initialise variables.
		$app	= JFactory::getApplication();
		// Load the parameters.
		$params	= $app->getParams();
				
		//$params	= JComponentHelper::getParams('com_virtualcitytour');

		$this->mailNewCommentAdmins = $params->get('mailnewcommentadmins');
		$this->mailNewCommentUser = $params->get('mailnewcommentuser');

 		// List state information
		// $value = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$value = $app->getUserStateFromRequest($this->context.'.list.limit', 'limit', 15); //set 15 as default do not use admin configuration...
		$this->setState('list.limit', $value);

		
		$value = $app->getUserStateFromRequest($this->context.'.limitstart', 'limitstart', 0);
		$this->setState('list.start', $value);
	}	
	
		
	public function commentNotificationMail($pk = 0, $userid = 0, $description = '')
	{
		// Initialise variables (populate state is not called from json calls).
		$app	= JFactory::getApplication();
		$params	= $app->getParams();
		$this->mailNewCommentAdmins = $params->get('mailnewcommentadmins');
		$this->mailNewCommentUser = $params->get('mailnewcommentuser');		
		
		//send notification mail to the category admin 
		
		//get the link to the commented poi
		$poiLink = 'http://'. $_SERVER['HTTP_HOST'] . VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&view=poi&poi_id='.$pk);

		//$poiAdminLink = JURI::root() . 'administrator/' . 'index.php?option=com_virtualcitytour&view=poi&layout=edit&id='.$table->id;
		/*fixing "You are not permitted to use that link to directly access that page"*/
		$poiAdminLink = JURI::root() . 'administrator/' . 'index.php?option=com_virtualcitytour&view=poi&task=poi.edit&id='.$pk; 
		
		
		$user = JFactory::getUser($userid);
		$app = JFactory::getApplication();
		$mailfrom	= $app->getCfg('mailfrom');
		$fromname	= $app->getCfg('fromname');
		$sitename	= $app->getCfg('sitename');		


		/* (A) ****--- Send notification mail to appropriate admin (as defined on category) */
		if($this->mailNewCommentAdmins == 1){
			//get the catid of the poi
			$catid = 0;
			$db		= $this->getDbo();
			$query	= $db->getQuery(true);
			$query->select('a.catid, a.userid');
			$query->from('`#__virtualcitytour` AS a');
			
			$query->where('a.id = ' . (int) $pk);		
			$db->setQuery($query);
			//$catid = $db->loadResult();
			$row = $db->loadAssoc();
			$catid = $row['catid'];
			$initialUser = JFactory::getUser($row['userid']);
			
			
			
			//get the recipient email(s) as defined in the "note" field of the selected category
			$poiRecipient = '';
			$db		= $this->getDbo();
			$query	= $db->getQuery(true);
			$query->select('a.note as note, a.title as title');
			$query->from('`#__categories` AS a');
			
			$query->where('a.id = ' . (int) $catid);		
			$db->setQuery($query);
			//$result = $db->loadResult();
			$row = $db->loadAssoc();
			if(!empty($row)){
				$poiRecipient = $row['note'];
				$arRecipient = explode(";",$poiRecipient);
				$arRecipient = array_filter($arRecipient, 'strlen');
				$categoryTitle = $row['title'];
			}		

			if(!empty($poiRecipient)){		//only if category note contains email(s)
				$subject = sprintf(JText::_('COM_VIRTUALCITYTOUR_MAIL_ADMINS_NEW_COMMENT_SUBJECT'), $user->name, $user->email);
				
				$body = sprintf(JText::_('COM_VIRTUALCITYTOUR_MAIL_ADMINS_NEW_COMMENT_BODY')
						, $categoryTitle
						, $description
						, $poiLink
						, $poiLink
						, $poiAdminLink
						, $poiAdminLink );			
				
				$mail = JFactory::getMailer();
				$mail->isHTML(true);
				$mail->Encoding = 'base64';
				foreach($arRecipient as $recipient)
					$mail->addRecipient($recipient);
				$mail->setSender(array($mailfrom, $fromname));
				$mail->setSubject($sitename.': '.$subject);
				$mail->setBody($body);
				$sent = $mail->Send();
				
			}
		}
		/* (B) ****--- Send notification mail to poi submitter */
		if($this->mailNewCommentUser == 1){
			$poiRecipient = $initialUser->email;
			if($poiRecipient != ''){		//check just in case...
				$subject = JText::_('COM_VIRTUALCITYTOUR_MAIL_USER_NEW_COMMENT_SUBJECT');
				$body = sprintf(JText::_('COM_VIRTUALCITYTOUR_MAIL_USER_NEW_COMMENT_BODY')
						, $description
						, $poiLink
						, $poiLink );
				
				$mail = JFactory::getMailer();
				$mail->isHTML(true);
				$mail->Encoding = 'base64';
				$mail->addRecipient($poiRecipient);
				$mail->setSender(array($mailfrom, $fromname));
				$mail->setSubject($sitename.': '.$subject);
				$mail->setBody($body);
				$sent = $mail->Send();			
			}		
		}
		
		return true;
	}
	
	public function comment($pk = 0, $userid = 0, $description = '')
	{
		
		$pk = (!empty($pk)) ? $pk : (int) $id = $this->getState('virtualcitytour.id');
		$db = $this->getDbo();


		$db->setQuery(
				'INSERT INTO #__virtualcitytour_comments ( virtualcitytourid, userid, description)' .
				' VALUES ( '.(int) $pk.', '. (int) $userid.', "'.$description.'")'
		);

		if (!$db->query()) {
				$this->setError($db->getErrorMsg());
				return false;
		}		
		
		//return the latest comment so as to be displayed with ajax in the frontend
		$query	= $db->getQuery(true);
		$query->select(
			'a.*'
			);
		$query->from('#__virtualcitytour_comments as a');
		$query->where('a.virtualcitytourid = ' . (int) $pk);
		$query->where('a.state = 1');

		// Join on user table.
		$query->select('u.name AS username');
		$query->join('LEFT', '#__users AS u on u.id = a.userid');	
		$query->order('created DESC');
		$db->setQuery((string) $query);
				

		if (!$db->query()) {
				$this->setError($db->getErrorMsg());
				return false;
		}

		//$comments = $db->loadResult();		//return first field of first row
		//$comments = $db->loadAssocList();		//return all rows
		$comments = $db->loadAssoc();			//return first row
		$comments['textual_descr'] = JText::_('COMMENT_REPORTED') . ' ' . VirtualcitytourHelper::getRelativeTime($comments['created']) . ' ' . JText::_('BY') . ' ' . $comments['username'];
		return $comments;
	}		
	
}
