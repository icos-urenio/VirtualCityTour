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
 * HTML View class for the Virtualcitytour component
 */
class VirtualcitytourViewPoi extends JView
{
	protected $state;
	protected $print;
	protected $item;
	protected $params;
	protected $pageclass_sfx;
	protected $guest;
	protected $voted;
	protected $hasVoted;
	protected $language = '';
	protected $region = '';
	protected $lat = '';
	protected $lon = '';
	protected $searchterm = '';
	protected $categoryIcon = '';
	protected $zoom;
	protected $loadjquery;
	protected $loadbootstrap;
	protected $loadbootstrapcss;	
	protected $allowCommentingOnClose;
	protected $allowVotingOnClose;
	protected $popupmodal;
	protected $showcomments;
	protected $approvepoi;
	protected $loadjqueryui;
		
	function display($tpl = null)
	{
		$app		= JFactory::getApplication();
		$this->params		= $app->getParams();
		$this->print	= JRequest::getBool('print');
		//remove || from title
		$strip_title = $this->params->get('page_title');
		$strip_title = str_replace('||', '', $strip_title);
		$this->params->set('page_title', $strip_title);
		
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		
		$lang = $this->params->get('maplanguage');
		$region = $this->params->get('mapregion');
		$lat = $this->params->get('latitude');
		$lon = $this->params->get('longitude');
		$term = $this->params->get('searchterm');
		$zoom = $this->params->get('zoom');
		$this->allowCommentingOnClose = $this->params->get('allowcommentingonclose');
		$this->allowVotingOnClose = $this->params->get('allowvotingonclose');		
		$this->loadjquery = $this->params->get('loadjquery');
		$this->loadbootstrap = $this->params->get('loadbootstrap');
		$this->loadbootstrapcss = $this->params->get('loadbootstrapcss');
		$this->popupmodal = $this->params->get('popupmodal');
		$this->showcomments = $this->params->get('showcomments');
		$this->approvepoi = $this->params->get('approvepoi');
		$this->loadjqueryui = $this->params->get('loadjqueryui');		
		
		$this->language = (empty($lang) ? "en" : $lang);
		$this->region = (empty($region) ? "GB" : $region);
		$this->lat = (empty($lat) ? 40.54629751976399 : $lat);
		$this->lon = (empty($lon) ? 23.01861169311519 : $lon);
		$this->searchterm = (empty($term) ? "" : $term);
		$this->zoom = (empty($zoom) ? 17 : $zoom);
	
		
		
		//while inserting new poi: if model return false it redirects to view=poi&layout=edit	
		$layout = JRequest::getCmd('layout', 'default');		
		// Check for edit form.
		if ($layout == 'edit') {
			echo $this->displayError();
			return false;
		}		
		
		
		
		// Get some data from the models
		$this->state	= $this->get('State');
		
		/*note: I am using multiple models so I have to specify not only the method but the model as well - http://docs.joomla.org/Using_multiple_models_in_an_MVC_component */
		//TODO: Check if it would be best to move these lines to the model instead of view (here)...
		$this->item	= $this->get('Item');
		$this->hasVoted = $this->get('HasVoted');
		$this->assign('discussion', $this->get('Items', 'discussions'));		
		
		$this->categoryIcon = $this->get('CategoryIcon');
		
		
		//check if user is logged
		$user =& JFactory::getUser();
		$this->guest = $user->guest;		
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
		//update hits
		$model = $this->getModel();
		$model->hit();		
		
		
		// get the menu parameters
		$menuparams = $this->state->get("parameters.menu");
		$html5 = $menuparams->get("html5");

		//select if HTML5 or previous and load the appropriate template
		if($html5 == 0)
			$tpl = 'nohtml5';
		else
			$tpl = null;				
		parent::display($tpl);
		
		// Set the document
		$this->setDocument();
	}

	
	protected function displayError(){
		$link = VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&controller=virtualcitytour&task=addPoi');
		
		$html = '<div style="text-align:center;">';
		$html .= '<img src="' . JURI::root(true).'/components/com_virtualcitytour/images/error.png' . '" /><br />';
		$html .= '<a href="'.$link.'">'.JText::_('BACK_TO_FORM').'</a>';
		$html .= '</div>';
		return $html;
	}
	
	//leave this as is... maybe more markers will appear in future versions..
	protected function getMarkerArrayFromItem() {
		$ar[] = array('name'=>$this->item->title,
					'description'=>$this->item->description,
					'catid'=>$this->item->catid,
					'id'=>$this->item->id,
					'lat'=>$this->item->latitude,
					'lng'=>$this->item->longitude,
					'photo'=>$this->item->photo,
					'photos'=>$this->item->photos,
					'panoramas'=>$this->item->panoramas				
					);
		return $ar;
	}	
	
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		
		//make it social network friendly
		$document->setTitle($this->item->title);
		$document->setDescription(mb_substr($this->item->description, 0, 130, 'utf-8') . '...');
		
		if($this->loadbootstrapcss == 1){
			$document->addStyleSheet(JURI::root(true).'/components/com_virtualcitytour/bootstrap/css/bootstrap.min.css');
			$document->addStyleSheet(JURI::root(true).'/components/com_virtualcitytour/bootstrap/css/bootstrap-responsive.min.css');
		}		
		
		$document->addStyleSheet(JURI::root(true).'/components/com_virtualcitytour/css/virtualcitytour.css');	

		//add scripts
		if($this->loadjquery == 1){
			$document->addScript(JURI::root(true).'/components/com_virtualcitytour/js/jquery-1.7.1.min.js');
		}
		//jquery noConflict
		$document->addScriptDeclaration( 'var jImc = jQuery.noConflict();' );		
		
		if($this->loadjqueryui == 1){
			$document->addScript(JURI::root(true).'/components/com_virtualcitytour/js/jquery-ui-1.8.18.custom.min.js');
		}
		if($this->loadbootstrap == 1)
			$document->addScript(JURI::root(true).'/components/com_virtualcitytour/bootstrap/js/bootstrap.min.js');		
		
		//colorbox
		$document->addScript(JURI::root(true) . "/components/com_virtualcitytour/js/colorbox/jquery.colorbox-min.js");
		$document->addStyleSheet(JURI::root(true).'/components/com_virtualcitytour/js/colorbox/css/colorbox.css');
				
		$document->addScript(JURI::root(true).'/components/com_virtualcitytour/js/virtualcitytour.js');	
		
		//add google maps
		$document->addScript("https://maps.google.com/maps/api/js?sensor=false&language=".$this->language."&region=" . $this->region);
		$document->addScript(JURI::root(true).'/components/com_virtualcitytour/js/infobox_packed.js');	
		
		$document->addScriptDeclaration('var jsonMarkers = '.json_encode($this->getMarkerArrayFromItem()).';');
		
		$LAT = $this->lat;
		$LON = $this->lon;

		$googleMap = "
			var geocoder = new google.maps.Geocoder();
			var map = null;
			var gmarkers = [];
			
			function zoomIn() {
				map.setCenter(marker.getPosition());
				map.setZoom(map.getZoom()+1);
			}

			function zoomOut() {
				map.setCenter(marker.getPosition());
				map.setZoom(map.getZoom()-1);
			}
			
			// Creating a LatLngBounds object
			var bounds = new google.maps.LatLngBounds();			

			function initialize() {
				var LAT = ".$LAT.";
				var LON = ".$LON.";

				var latLng = new google.maps.LatLng(LAT, LON);
				map = new google.maps.Map(document.getElementById('mapCanvas'), {
				zoom: ".$this->zoom.",
				center: latLng,
				panControl: false,
				streetViewControl: false,
				zoomControlOptions: {
					style: google.maps.ZoomControlStyle.SMALL
				},
				mapTypeId: google.maps.MapTypeId.ROADMAP
				});

				for (var i = 0; i < jsonMarkers.length; i++) {
					var name = jsonMarkers[i].name;
					var description = jsonMarkers[i].description;
					var catid = jsonMarkers[i].catid;
					var id = jsonMarkers[i].id;
					var photo = jsonMarkers[i].photo;
					var photos = jsonMarkers[i].photos;
					var panoramas = jsonMarkers[i].panoramas;
						
					var point = new google.maps.LatLng(
						parseFloat(jsonMarkers[i].lat),
						parseFloat(jsonMarkers[i].lng)
					);
					
					var hasIcon = '" . $this->categoryIcon . "';
					if(hasIcon != ''){
						var icon = '" . JURI::root().$this->categoryIcon."';
						var shadow = '" . JURI::root(true). "/components/com_virtualcitytour/images/shadow.png". "';
						
						var marker = new google.maps.Marker({
							map: map,
							position: point,
							title: name,
							icon: icon,
							shadow: shadow
						});
					}else{
						var marker = new google.maps.Marker({
							map: map,
							position: point,
							title: name
						});
					}
					
					
					marker.catid = catid;
					marker.id = id;
					marker.photos = photos;
					marker.photo = photo;
					if(marker.photo)
					  	marker.photo_orig = photo.replace('/images/thumbs/','/images/');
					else
					  	marker.photo_orig = null;
					marker.panoramas = panoramas;
					marker.description = description;									
								
								
					gmarkers.push(marker);
				}

				resetBounds();
				jImc(\"#loading\").hide();
			}

			function resetBounds() {
				var a = 0;
				bounds = null;
				bounds = new google.maps.LatLngBounds();
				for (var i=0; i<gmarkers.length; i++) {
					if(gmarkers[i].getVisible()){
						a++;
						bounds.extend(gmarkers[i].position);	
					}
				}
				if(a > 0){
					map.fitBounds(bounds);
					var listener = google.maps.event.addListener(map, 'idle', function() { 
					  if (map.getZoom() > 16) map.setZoom(16); 
					  google.maps.event.removeListener(listener); 
					});
				}
			}

			function showInfo(marker){
				jImc(\"#markerTitle\").html('<span class=\"markerTitle\">' + marker.title + '</span>');
				jImc(\"#panorama\").html('');
				jImc(\"#markerHead\").html('');
				jImc(\"#markerInfo\").html('');
				jImc(\"#markerImages\").html('');
				
			
				if(marker.panoramas){
					jImc(\"#markerHead\").html(createInfoPanoramas(marker));
					//jImc(\"#markerImages\").html(createInfoImages(marker));
					//jImc(\"#markerInfo\").html(marker.description);	
				}
				else if(marker.photo){
					var img = '" . JURI::root()."/" . "' + marker.photo_orig;
					jImc(\"#markerHead\").html('<img src=\"'+img+'\" />');
					jImc(\"#markerInfo\").html(marker.description);
				}									
				else if(marker.photos){
					var arr = marker.photos.split(';');
					if(arr[i] != ''){
						var img = '" . JURI::root(true). "/images/virtualcitytour/". "' + marker.id + '/images/' + arr[i];
						jImc(\"#markerHead\").html('<img src=\"'+img+'\" />');				
					}					
					//jImc(\"#markerHead\").html(createInfoImages(marker));
					//jImc(\"#markerInfo\").html(marker.description);
				} 
				else {
					//jImc(\"#markerHead\").html(marker.description);
				}

				
				if(marker.photos || marker.photo){
					jImc(\"#markerImages\").html(createInfoImages(marker));
					jImc(\"a[rel='photos']\").colorbox();
				}
				
								
				if(marker.panoramas){
					jImc(\"#panorama\").show();
				}
				else{
					jImc(\"#panorama\").hide();
				}
				
				jImc(\"#markerInfo\").html(marker.description);								
								
				jImc(\"#wrapper-info\").show(500);
			}
				
			function createInfoImages(marker){
				var arr = marker.photos.split(';');
				var html = '';
				for(i = 0; i < arr.length; i++){
					if(arr[i] != ''){
						var thumb = '" . JURI::root(true). "/images/virtualcitytour/". "' + marker.id + '/images/thumbs/' + arr[i];
						var img = '" . JURI::root(true). "/images/virtualcitytour/". "' + marker.id + '/images/' + arr[i];
						html += '<a title=\"'+marker.title+'\" rel=\"photos\" href=\"'+img+'\">';
						html +=  '<img src=\"' ;
						html +=	thumb;
						html += '\" />';
						html += '</a>';
					}
				}
								
				if (marker.photo) {
					var thumb = marker.photo
					var img = marker.photo_orig;
					html += '<a title=\"'+marker.title+'\" rel=\"photos\" href=\"'+img+'\">';
					html +=  '<img src=\"' ;
					html +=	thumb;
					html += '\" />';
					html += '</a>';
							
				}
								
				return html;
			}
				
			function createInfoPanoramas(marker){
				var arr = marker.panoramas.split(';');
				var html = '';
				
				for(i = 0; i < arr.length; i++){
					if(arr[i] != ''){
						var pan = '" . JURI::root(). "images/virtualcitytour/". "' + marker.id + '/panoramas/original/' + arr[i];
						if(i == 0){
							embedFlash(pan);
							if(arr.length == 2){
								return html;
							}
							html += '<div class=\"btn-group\">';
						}
						
						html += '<button class=\"btn\" onclick=\"embedFlash(\''+pan+'\')\">' + (i+1) + '</button>';
						if(i == arr.length-1){
							html += '</div>';
						}
					}
				}
				
				return html;
			}
				
			function embedFlash(pan){
				var flash = '<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0\" id=\"RyubinPanorama\" >';
				flash += '<param name=\"wmode\" value=\"transparent\"> ';
				flash +='<embed src=\"". JURI::base()."components/com_virtualcitytour/pano/RyubinPanoPlayer5.swf\" wmode=\"transparent\" FlashVars=\"playmode=sphere&internal_ctrl=no&img_path='+pan+'&cursor_path=". JURI::base()."components/com_virtualcitytour/pano/my_cursor.png&xml_path=". JURI::base()."components/com_virtualcitytour/pano/panosettings.xml\" width=\"100%\" height=\"350px\" name=\"RyubinPanorama\" allowFullScreen=\"true\" type=\"application/x-shockwave-flash\" pluginspage=\"https://www.macromedia.com/go/getflashplayer\" />';
				flash += '</object>';
				jImc(\"#panorama\").html(flash);
			}
			
		";

		$documentReady = "
		jImc(document).ready(function() {
			initialize();
			showInfo(gmarkers[0]);
		});
		";
		
		//add the javascript to the head of the html document
		$document->addScriptDeclaration($googleMap);
		$document->addScriptDeclaration($documentReady);
		
		//also pass base so as to display comment image indicator
		$js = "var com_virtualcitytour = {};\n";
		$js.= "com_virtualcitytour.base = '".JURI::root(true)."';\n";
		$document->addScriptDeclaration($js);
		
		
	}
}
