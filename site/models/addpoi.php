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

require_once JPATH_COMPONENT_ADMINISTRATOR.'/models/poi.php';

/**
 * Model
 */
class VirtualcitytourModelAddpoi extends VirtualcitytourModelPoi
{
	protected $mailNewPoiUser;
	protected $mailNewPoiAdmins;
	

	/**
	 * Get the return URL.
	 *
	 * @return	string	The return URL.
	 * @since	1.6
	 */
	public function getReturnPage()
	{

		//return base64_encode(JRoute::_('index.php?option=com_virtualcitytour&view=pois'));
		//return base64_encode(JRoute::_('index.php?option=com_virtualcitytour&view=pois&Itemid='.JRequest::getint( 'Itemid' )));
		return base64_encode(VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&view=pois'));
	}
	
	
	protected function populateState()
	{
		$app = JFactory::getApplication();
		
		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);	
		
		$this->mailNewPoiAdmins = $params->get('mailnewpoiadmins');
		$this->mailNewPoiUser = $params->get('mailnewpoiuser');
		
		$return = JRequest::getVar('return', null, 'default', 'base64');

		if (!JUri::isInternal(base64_decode($return))) {
			$return = null;
		}

		$this->setState('return_page', base64_decode($return));		
	}


	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'components/com_virtualcitytour/models/forms/poi.js';
	}	
	 
    private function notifyByEmail($id, $data)
	{
		// Load the parameters.
		$app = JFactory::getApplication();
		$par	= $app->getParams();
		$this->mailNewPoiAdmins = $par->get('mailnewpoiadmins');
		$this->mailNewPoiUser = $par->get('mailnewpoiuser');
		
		//get the link to the newly created poi
		$poiLink = 'http://'. $_SERVER['HTTP_HOST'] . VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&view=poi&poi_id='.$id);

		//$poiAdminLink = JURI::root() . 'administrator/' . 'index.php?option=com_virtualcitytour&view=poi&layout=edit&id='.$table->id;
		
		/*fixing "You are not permitted to use that link to directly access that page"*/
		$poiAdminLink = JURI::root() . 'administrator/' . 'index.php?option=com_virtualcitytour&view=poi&task=poi.edit&id='.$id; 
		
		
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$mailfrom	= $app->getCfg('mailfrom');
		$fromname	= $app->getCfg('fromname');
		$sitename	= $app->getCfg('sitename');		


		/* (A) ****--- Send notification mail to appropriate admins (as defined on category note field) */
		if($this->mailNewPoiAdmins == 1){
			//get the recipient email(s) as defined in the "note" field of the selected category
			$poiRecipient = '';
			$db		= $this->getDbo();
			$query	= $db->getQuery(true);
			$query->select('a.note as note, a.title as title');
			$query->from('`#__categories` AS a');
			
			$query->where('a.id = ' . $data['catid']);		
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
				$subject = sprintf(JText::_('COM_VIRTUALCITYTOUR_MAIL_ADMINS_NEW_POI_SUBJECT'), $user->name, $user->email);
				
				$body = sprintf(JText::_('COM_VIRTUALCITYTOUR_MAIL_ADMINS_NEW_POI_BODY')
						, $categoryTitle
						, $data['title']
						, $data['address']
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

		/* (B) ****---   Send notification mail to the user who submitted the poi */
		if($this->mailNewPoiUser == 1){
			// recipient is the user
			$poiRecipient = $user->email;
			
			if($poiRecipient != ''){		//check just in case...
				$subject = JText::_('COM_VIRTUALCITYTOUR_MAIL_USER_NEW_POI_SUBJECT');
				$body = sprintf(JText::_('COM_VIRTUALCITYTOUR_MAIL_USER_NEW_POI_BODY')
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
				//also inform user at the frontend for the email that is about to receive
				JFactory::getApplication()->enqueueMessage( JText::_('COM_VIRTUALCITYTOUR_NEW_POI_SAVE_SUCCESS') );				
			}
		}
		
		return true;		
	}	 
	
	private function stringURLSafe($string)
	{
		//replace double byte whitespaces to single byte
		$str = preg_replace('/\xE3\x80\x80/', ' ', $string);
		// remove any '-' from the string as they will be used as concatenator.
		$str = str_replace('-', ' ', $str);
		//replace forbidden characters by whitespaces
		//$str = preg_replace($forbidden,' ', $str);
		$str = preg_replace( '#[:\#\*"@+=;!&%\\]\/\'\\\\|\[]#',"\x20", $str );
		//delete all '?'
		$str = str_replace('?', '', $str);
		//trim white spaces at beginning and end of alias
		$str = trim( $str );
		// remove any duplicate whitespace and replace whitespaces by hyphens
		$str =preg_replace('#\x20+#','-', $str);
		return $str;
	}	
	 
	/**
	* Get file (photo) from POST and save it
	* following the http://forum.joomla.org/viewtopic.php?t=650699 guidelines... override save on model... 
	*/	
	public function save($data)
	{
		if(empty($data['userid']) ){	
			//that means NON mobile.json version (mobile.json sends uid already filled)
			$data = JRequest::getVar('jform', array(), 'post', 'array');
			$uidPath = JFactory::getUser()->get('id');
		} else {
			$uidPath = $data['userid'];
		}

		$file = JRequest::getVar('jform', array(), 'files', 'array');
		
		$app		= JFactory::getApplication();
		$params		= $app->getParams();
		$approval = $params->get('approvepoi');

		$data['state'] = !$approval;
		
		if($approval == 1){
			JFactory::getApplication()->enqueueMessage( JText::_('COM_VIRTUALCITYTOUR_APPROVAL_PENDING') );
		}
		
		if ($file) {
			//Cannot use makeSafe with non-english characters (or better only ascii is supported)
			//$filename = JFile::makeSafe($file['name']['photo']);
			//use custom makeSafe instead...
			$filename = $this->stringURLSafe($file['name']['photo']);
			
			if($filename!=''){
				if($file['type']['photo'] != 'image/jpeg' && $file['type']['photo'] != 'image/png'){
					$this->setError(JText::_('ONLY_PNG_JPG'));
					return false;	
				}
		
				$src = $file['tmp_name']['photo'];
				$dest =  JPATH_SITE. DS ."images". DS . "virtualcitytour" . DS . $uidPath . DS . "images" . DS . $filename;
				$thumb_dest =  JPATH_SITE. DS ."images". DS . "virtualcitytour" . DS . $uidPath . DS . "images" . DS . "thumbs" . DS . $filename;

				//resize image here
				include_once(JPATH_COMPONENT.'/helpers/simpleimage.php');
				
				$image = new SimpleImage();
				$image->load($src);
				
				$width = $image->getWidth();
				$height = $image->getHeight();
				$new_height = $height;
				$new_width = $width;
				
				$target_width = 800;	//TODO: GET FROM PARAMETERS
				$target_height = 600;	//TODO: GET FROM PARAMETERS

				$target_ratio = $target_width / $target_height;
				$img_ratio = $width / $height;
				
				if ($target_ratio > $img_ratio) {
					$new_height = $target_height;
					$new_width = $img_ratio * $target_height;
				} else {
					$new_height = $target_width / $img_ratio;
					$new_width = $target_width;
				}

				if ($new_height > $target_height) {
					$new_height = $target_height;
				}
				if ($new_width > $target_width) {
					$new_height = $target_width;
				}		
				
				if($width > $target_width && $height > $target_height){
					if($new_height != $height || $new_width != $width){
						$image->resize($new_width,$new_height);
						$image->save($src);		
					}
				}
				 
				//always use constants when making file paths, to avoid the possibilty of remote file inclusion
				if(!JFile::upload($src, $dest)) 
				{
					echo JText::_( 'ERROR MOVING FILE' );
						return;
				}
				else
				{
					// success, exit with code 0 for Mac users, otherwise they receive an IO Error
					JPath::setPermissions($dest);
					//CREATE THUMBNAIL HERE
					$image->load($dest);
					$image->resize(80,60);	//TODO: GET FROM PARAMETERS
					$pathToThumb = JPATH_SITE.DS.'images'.DS.'virtualcitytour'.DS.$uidPath.DS.'images'.DS.'thumbs';
					if (!JFolder::exists($pathToThumb)){
						JFolder::create($pathToThumb);
					}
					
					//$thumb = $pathToThumb.DS.$fileName;
					$image->save($thumb_dest);
					JPath::setPermissions($thumb_dest);
					
					//update data with photo path
					$data['photo'] = 'images/virtualcitytour/'.$uidPath.'/images/thumbs/'.$filename;
					//$data['thumb'] = 'images/virtualcitytour/'.J$uidPath.'/images/thumbs/'.$filename;
					//$data['photo'] = 'images'.'/'.'virtualcitytour'.'/'.$uidPath.'/'.'images'.'/'.$fileName;
				}					
					
				/*
				if (JFile::upload($src, $dest, false) ){
					//update data with photo path
					$data['photo'] = 'images/virtualcitytour/'.$uidPath.'/images/'.$filename;
				} 
				*/
			}    
		}	
		
		
		// Initialise variables;
		$dispatcher = JDispatcher::getInstance();
		$table      = $this->getTable();
		$key         = $table->getKeyName();
		$pk         = (!empty($data[$key])) ? $data[$key] : (int)$this->getState($this->getName().'.id');
		$isNew      = true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0) {
				$table->load($pk);
				$isNew = false;
			}

			// Bind the data.
			if (!$table->bind($data)) {
				$this->setError($table->getError());
				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check()) {
				$this->setError($table->getError());
				return false;
			}

			// Trigger the onContentBeforeSave event.
			$result = $dispatcher->trigger($this->event_before_save, array($this->option.'.'.$this->name, &$table, $isNew));
			if (in_array(false, $result, true)) {
				$this->setError($table->getError());
				return false;
			}

			// Store the data.
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}

			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$dispatcher->trigger($this->event_after_save, array($this->option.'.'.$this->name, &$table, $isNew));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName)) {
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);

		//update timestamp
		$this->updateTimestamp();
		
		//notify admins and user
		$this->notifyByEmail($table->id, $data);	
		
		return true;
	}
	
	/* since 2.4.1 
	 * update timestamp on table timestamp to notify Android application for changes in DB
	 * */
	public function updateTimestamp()
	{
		$db = $this->getDbo();
		$db->setQuery(
				'UPDATE #__virtualcitytour_timestamp' .
				' SET triggered = MD5(RAND())' .
				' WHERE id = 1'
		);
			
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
	
		return true;
	}	
	
}
