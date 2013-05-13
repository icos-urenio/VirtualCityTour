<?php
/**
 * @version     2.5.x
 * @package     com_virtualcitytour
 * @copyright   Copyright (C) 2011 - 2012 URENIO Research Unit. All rights reserved.
 * @license     GNU Affero General Public License version 3 or later; see LICENSE.txt
 * @author      Ioannis Tsampoulatidis for the URENIO Research Unit
 */

/**
 * @param	array	A named array
 * @return	array
 */
function VirtualcitytourBuildRoute(&$query)
{
	$segments = array();

	if (isset($query['view'])) {
		$segments[] = $query['view'];
		unset($query['view']);
	}
	if (isset($query['poi_id'])) {
		$segments[] = $query['poi_id'];
		unset($query['poi_id']);
	}	
	if (isset($query['task'])) {
		$segments[] = $query['task'];
		unset($query['task']);
	}

	if (isset($query['controller'])) {
		$segments[] = $query['controller'];
		unset($query['controller']);
	}
			
	return $segments;
}


function virtualcitytourParseRoute( $segments )
{
       $vars = array();
	   switch($segments[0])
       {
			case 'poi':
				$vars['view'] = 'poi';
				$vars['poi_id'] = (int) $segments[1];				   
			break;
			case 'pois':
				$vars['view'] = 'pois';
				if(@$segments[1] == 'addPoi')	//@ when canceling addnewpoi
					$vars['task'] = 'addPoi';
				if(@$segments[2] == 'virtualcitytour')
					$vars['controller'] = 'virtualcitytour';
			break;
			case 'addPoi':
				$vars['task'] = 'addPoi';
				$vars['controller'] = 'virtualcitytour';
			break;			
			case 'addComment':
				$vars['task'] = 'addComment';
				$vars['controller'] = 'virtualcitytour';
			break;
			case 'smartLogin':
				$vars['task'] = 'smartLogin';
				$vars['controller'] = 'virtualcitytour';
			break;	
			case 'printPoi':
				$vars['task'] = 'printPoi';
				$vars['controller'] = 'virtualcitytour';
				$vars['poi_id'] = (int) $segments[0];
			break;				
			case 'printPois':
				$vars['task'] = 'printPois';
				$vars['controller'] = 'virtualcitytour';
			break;			
       }
	   
	   //TODO: revision needed...
	   if(isset($segments[1])){
			switch($segments[1]){
				case 'addVote':
					$vars['task'] = 'addVote';
					$vars['controller'] = 'virtualcitytour';
					$vars['poi_id'] = (int) $segments[0];
				break;
				case 'addComment':
					$vars['task'] = 'addComment';
					$vars['controller'] = 'virtualcitytour';
					$vars['poi_id'] = (int) $segments[0];
				break;				
				case 'printPoi':
					$vars['task'] = 'printPoi';
					$vars['controller'] = 'virtualcitytour';
					$vars['poi_id'] = (int) $segments[0];
				break;			
				case 'printPois':
					$vars['task'] = 'printPois';
					$vars['controller'] = 'virtualcitytour';
				break;						
			}
	   }
	   
       return $vars;
}
