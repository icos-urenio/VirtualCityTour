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
class VirtualcitytourViewPoi extends JView
{
	protected $state;
	protected $item;
	protected $form;
	
	protected $language = '';
	protected $region = '';
	protected $lat = '';
	protected $lon = '';
	protected $searchterm = '';
	protected $poir;
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

		$lang = $this->state->params->get('maplanguage');
		$region = $this->state->params->get('mapregion');
		$lat = $this->state->params->get('latitude');
		$lon = $this->state->params->get('longitude');
		$term = $this->state->params->get('searchterm');
		
		$this->language = (empty($lang) ? "en" : $lang);
		$this->region = (empty($region) ? "GB" : $region);
		$this->lat = (empty($lat) ? 40.54629751976399 : $lat);
		$this->lon = (empty($lon) ? 23.01861169311519 : $lon);
		$this->searchterm = (empty($term) ? "" : $term);

		$this->poir = &JFactory::getUser($this->item->userid);
		
		$this->assign('displayPanoramas', $this->getPanoramas());
		$this->assign('displayImages', $this->getImages());		

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}



		$this->addToolbar();
		parent::display($tpl);
		
		// Set the document
		$this->setDocument();		
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

		JToolBarHelper::title(JText::_('COM_VIRTUALCITYTOUR_TITLE_POI'), 'item.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
		{

			JToolBarHelper::apply('poi.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('poi.save', 'JTOOLBAR_SAVE');
		}
		if (!$checkedOut && ($canDo->get('core.create'))){
			JToolBarHelper::custom('poi.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		// If an existing item, can save to a copy.
		/*
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('poi.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		*/
		if (empty($this->item->id)) {
			JToolBarHelper::cancel('poi.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('poi.cancel', 'JTOOLBAR_CLOSE');
		}

	}
	
	protected function setDocument() 
	{
		$isNew = $this->item->id == 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_VIRTUALCITYTOUR_VIRTUALCITYTOUR_CREATING') : JText::_('COM_VIRTUALCITYTOUR_VIRTUALCITYTOUR_EDITING'));
		
		$document->addScript("https://maps.google.com/maps/api/js?sensor=false&language=".$this->language."&region=" . $this->region);

		$LAT = $this->form->getValue('latitude');
		$LON = $this->form->getValue('longitude');
		if($isNew || $LAT == '' || $LON == ''){
			$LAT = $this->lat;
			$LON = $this->lon;
		}
		
		$googleMapInit = "
			var geocoder = new google.maps.Geocoder();
			var map;
			var marker;
			
			function zoomIn() {
				map.setCenter(marker.getPosition());
				map.setZoom(map.getZoom()+1);
			}

			function zoomOut() {
				map.setCenter(marker.getPosition());
				map.setZoom(map.getZoom()-1);
			}
			
			
			function codeAddress() {
				var address = document.getElementById('address').value + ' ".$this->searchterm."';
				geocoder.geocode( { 'address': address, 'language': '".$this->language."'}, function(results, status) {
				  if (status == google.maps.GeocoderStatus.OK) {
					map.setCenter(results[0].geometry.location);
					marker.setPosition(results[0].geometry.location);
					
					document.getElementById('jform_latitude').value = results[0].geometry.location.lat();
					document.getElementById('jform_longitude').value = results[0].geometry.location.lng();					
					
					updateMarkerAddress(results[0].formatted_address);			

				  } else {
					alert('".JText::_('COM_VIRTUALCITYTOUR_ADDRESS_NOT_FOUND')."');
				  }
				});		
			}
			
			
			function geocodePosition(pos) {
			  geocoder.geocode({
				latLng: pos,
				language: '".$this->language."'
			  }, function(responses) {
				if (responses && responses.length > 0) {
				  updateMarkerAddress(responses[0].formatted_address);
				} else {
				  updateMarkerAddress('".JText::_('COM_VIRTUALCITYTOUR_ADDRESS_NOT_FOUND')."');
				}
			  });
			}

			//function updateMarkerStatus(str) {
			//  document.getElementById('markerStatus').innerHTML = str;
			//}

			function updateMarkerPosition(latLng) {
			  document.getElementById('info').innerHTML = [
				latLng.lat(),
				latLng.lng()
			  ].join(', ');
			  //update fields
			  document.getElementById('jform_latitude').value = latLng.lat();
			  document.getElementById('jform_longitude').value = latLng.lng();
			}

			function updateMarkerAddress(str) {
			  document.getElementById('near_address').innerHTML = str;
			  document.getElementById('jform_address').value = str;
			}

			
			function initialize() {
			  var LAT = ".$LAT.";
			  var LON = ".$LON.";

			  var latLng = new google.maps.LatLng(LAT, LON);
			  map = new google.maps.Map(document.getElementById('mapCanvas'), {
				zoom: 17,
				center: latLng,
				panControl: false,
				streetViewControl: false,
				zoomControlOptions: {
					style: google.maps.ZoomControlStyle.SMALL
				},
				mapTypeId: google.maps.MapTypeId.ROADMAP
			  });
			  
			  marker = new google.maps.Marker({
				position: latLng,
				title: '".JText::_('COM_VIRTUALCITYTOUR_REPORT_LOCATION')."',
				map: map,
				draggable: true
			  });
			  
			  
			  var infoString = '".JText::_('COM_VIRTUALCITYTOUR_DRAG_MARKER')."';
				
			  var infowindow = new google.maps.InfoWindow({
				content: infoString
			  });
			  
			  
			  // Update current position info.
			  updateMarkerPosition(latLng);
			  geocodePosition(latLng);
			  
			  // Add dragging event listeners.
			  google.maps.event.addListener(marker, 'dragstart', function() {
				infowindow.close();
				updateMarkerAddress('".JText::_('COM_VIRTUALCITYTOUR_MOVING')."');
			  });
			  
			  google.maps.event.addListener(marker, 'drag', function() {
				updateMarkerPosition(marker.getPosition());
			  });
			  
			  google.maps.event.addListener(marker, 'dragend', function() {
				infowindow.open(map, marker);
				geocodePosition(marker.getPosition());
			  });
			  
		  
			  
			  infowindow.open(map, marker);
			}

			// Onload handler to fire off the app.
			google.maps.event.addDomListener(window, 'load', initialize);
		";
		
		//add the javascript to the head of the html document
		$document->addScriptDeclaration($googleMapInit);
	

		/* PREPARE SWF UPLOAD MECHANISM START */
		
		//add the links to the external files into the head of the webpage (note the 'administrator' in the path, which is not nescessary if you are in the frontend)
		
		$document->addScript(JURI::root().'administrator/components/com_virtualcitytour/swfupload/swfupload.js');
		$document->addScript(JURI::root().'administrator/components/com_virtualcitytour/swfupload/swfupload.queue.js');
		$document->addScript(JURI::root().'administrator/components/com_virtualcitytour/swfupload/fileprogress.js');
		$document->addScript(JURI::root().'administrator/components/com_virtualcitytour/swfupload/handlers.js');
		$document->addStyleSheet(JURI::root().'administrator/components/com_virtualcitytour/swfupload/default.css');
		
		$session = & JFactory::getSession();
		
		//I need to know the path to the images
		$imgpath = JURI::root().'images/virtualcitytour/'.$this->item->id.'/images';
		$imgthumbpath = JURI::root().'images/virtualcitytour/'.$this->item->id.'/images/thumbs';
		$panpath = JURI::root().'images/virtualcitytour/'.$this->item->id.'/panoramas';
		$panthumbpath = JURI::root().'images/virtualcitytour/'.$this->item->id.'/panoramas/thumbs';
		
		
		
		
		$swfUploadHeadJs ='
		var current_id = '.(int) $this->item->id.';
		var upload1, upload2;
		window.onload = function()
		{
		var settings =
		{
		//this is the path to the flash file, you need to put your components name into it
		flash_url : "'.JURI::root().'administrator/components/com_virtualcitytour/swfupload/swfupload.swf",
		//we can not put any vars into the url for complicated reasons, but we can put them into the post...
		upload_url: "index.php",
		post_params:
		{
		"option" : "com_virtualcitytour",
		"controller" : "virtualcitytourController",
		"task" : "uploadImage",
		"id" : "'.(int) $this->item->id.'",
		"'.$session->getName().'" : "'.$session->getId().'",
		"format" : "raw",
		"uploadtype" : "image"
		},
		//you need to put the session and the "format raw" in there, the other ones are what you would normally put in the url
		file_size_limit : "4 MB",
		//client side file chacking is for usability only, you need to check server side for security
		file_types : "*.jpg;*.jpeg;*.gif;*.png",
		file_types_description : "All Files",
		file_upload_limit : 100,
		file_queue_limit : 100,
		custom_settings :
		{
		progressTarget : "fsUploadProgress",
		cancelButtonId : "btnCancel",
		imgPath : "'.$imgpath.'",
		imgThumbPath : "'.$imgthumbpath.'"
		},
		debug: false,
		// Button settings
		button_image_url: "'.JURI::root().'administrator/components/com_virtualcitytour/swfupload/images/SmallSpyGlassWithTransperancy_17x18.png",
		button_placeholder_id : "spanButtonPlaceholder",
		button_width: 250,
		button_height: 18,
		button_text : \'<span class="button">Add new photos <span class="buttonSmall">(4 MB Max)</span></span>\',
		button_text_style : \'.button { font-family: Helvetica, Arial, sans-serif; font-size: 12pt; } .buttonSmall { font-size: 10pt; }\',
		button_text_left_padding: 18,
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.HAND,
		// The event handler functions are defined in handlers.js
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess,
		upload_complete_handler : uploadComplete,
		queue_complete_handler : queueComplete // Queue plugin event
		};
		var settings2 =
		{
		//this is the path to the flash file, you need to put your components name into it
		flash_url : "'.JURI::root().'administrator/components/com_virtualcitytour/swfupload/swfupload.swf",
		//we can not put any vars into the url for complicated reasons, but we can put them into the post...
		upload_url: "index.php",
		post_params:
		{
		"option" : "com_virtualcitytour",
		"controller" : "virtualcitytourController",
		"task" : "uploadImage",
		"id" : "'.(int) $this->item->id.'",
		"'.$session->getName().'" : "'.$session->getId().'",
		"format" : "raw",
		"uploadtype" : "panorama"
		},
		//you need to put the session and the "format raw" in there, the other ones are what you would normally put in the url
		file_size_limit : "4 MB",
		//client side file chacking is for usability only, you need to check server side for security
		file_types : "*.jpg;*.jpeg;*.gif;*.png",
		file_types_description : "All Files",
		file_upload_limit : 100,
		file_queue_limit : 100,
		custom_settings :
		{
		progressTarget : "fsUploadProgress2",
		cancelButtonId : "btnCancel2",
		imgPath : "'.$panpath.'",
		imgThumbPath : "'.$panthumbpath.'"
		},
		debug: false,
		// Button settings
		button_image_url: "'.JURI::root().'administrator/components/com_virtualcitytour/swfupload/images/SmallSpyGlassWithTransperancy_17x18.png",
		button_placeholder_id : "spanButtonPlaceholder2",
		button_width: 250,
		button_height: 18,
		button_text : \'<span class="button">Add new panoramas <span class="buttonSmall">(4 MB Max)</span></span>\',
		button_text_style : \'.button { font-family: Helvetica, Arial, sans-serif; font-size: 12pt; } .buttonSmall { font-size: 10pt; }\',
		button_text_left_padding: 18,
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.HAND,
		// The event handler functions are defined in handlers.js
		file_queued_handler : fileQueued2,
		file_queue_error_handler : fileQueueError2,
		file_dialog_complete_handler : fileDialogComplete2,
		upload_start_handler : uploadStart2,
		upload_progress_handler : uploadProgress2,
		upload_error_handler : uploadError2,
		upload_success_handler : uploadSuccess2,
		upload_complete_handler : uploadComplete2,
		queue_complete_handler : queueComplete2 // Queue plugin event
		};
				
				
				
		upload1 = new SWFUpload(settings);
		upload2 = new SWFUpload(settings2);
		};
		';
		
		//add the javascript to the head of the html document
		$document->addScriptDeclaration($swfUploadHeadJs);
		
		/* PREPARE SWF UPLOAD MECHANISM END*/
		
		
		JText::script('COM_VIRTUALCITYTOUR_VIRTUALCITYTOUR_ERROR_UNACCEPTABLE');		
		
		
	}	
	protected function getImages() {
		return VirtualcitytourHelper::getImages($this->item->id);
	
	}
	
	protected function getPanoramas() {
		return VirtualcitytourHelper::getPanoramas($this->item->id);
	}	
}
