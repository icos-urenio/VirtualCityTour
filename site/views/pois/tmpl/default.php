<?php
/**
 * @version     2.5.x
 * @package     com_virtualcitytour
 * @copyright   Copyright (C) 2011 - 2013 URENIO Research Unit. All rights reserved.
 * @license     GNU Affero General Public License version 3 or later; see LICENSE.txt
 * @author      Panagiotis Tsarchopoulos for the URENIO Research Unit
 */

// no direct access
defined('_JEXEC') or die;

//JHtml::_('behavior.tooltip');
//JHtml::_('behavior.formvalidation');
//load mootools for the ordering
JHtml::_('behavior.framework', true);

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<div id="imc-wrapper" class="imc <?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 0)) : ?>			
	<h1 class="title">
		<?php if ($this->escape($this->params->get('page_heading'))) :?>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		<?php else : ?>
			<?php echo $this->escape($this->params->get('page_title')); ?>
		<?php endif; ?>				
	</h1>
	<?php endif; ?>	

	<div class="row-fluid">
	  <div class="span12">
	  <div id="imc-header">
		<div id="imc-menu" class="poislist">
			<!-- Filters -->
			<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<input type="hidden" name="status[0]" value="0" />
				<input type="hidden" name="cat[0]" value="0" />
				<input type="hidden" name="limitstart" value="" />
				<input type="hidden" name="limit" value="<?php echo  $this->state->get('list.limit');?>" />
				<input type="hidden" name="task" value="" />
				
				<!-- Mega Menu -->
				<ul id="mega-menu">
					<li id="drop-1"><a id="btn-1" href="javascript:void(0);" class="btn"><i class="icon-list-alt"></i> <?php echo JText::_('COM_VIRTUALCITYTOUR_FILTER_SELECTION')?></a>
						<div class="megadrop dropdown_6columns">
							<div class="col_6">
								<h2><?php echo JText::_('COM_VIRTUALCITYTOUR_CATEGORIES')?></h2>
							</div>
							
							<?php foreach($this->arCat as $c){?>		
								<div class="col_2">
									<?php echo $c; ?>
								</div>					
							<?php }?>


							<div class="col_6" style="text-align: center;">
								<button type="submit" class="btn btn-success" name="Submit" value="<?php echo JText::_('COM_VIRTUALCITYTOUR_APPLY_FILTERS')?>"><i class="icon-ok icon-white"></i> <?php echo JText::_('COM_VIRTUALCITYTOUR_APPLY_FILTERS')?></button>
							</div>
						</div>
					</li>
					<?php /*
					<li id="drop-2"><a id="btn-2" href="javascript:void(0);" class="btn"><i class="icon-signal"></i> <?php echo JText::_('COM_VIRTUALCITYTOUR_ORDERING')?></a>
						<div class="megadrop dropdown_2columns">
							<div class="col_2">						
								<ul>
									<!-- dropdown menu links -->
									<li><?php  echo JHtml::_('grid.sort', JText::_('COM_VIRTUALCITYTOUR_BY_DATE'), 'a.ordering', $listDirn, $listOrder);?></li>
									<li><?php  echo JHtml::_('grid.sort', JText::_('COM_VIRTUALCITYTOUR_BY_VOTES'), 'a.votes', $listDirn, $listOrder);?></li>
									<li><?php  echo JHtml::_('grid.sort', JText::_('COM_VIRTUALCITYTOUR_BY_STATUS'), 'a.currentstatus', $listDirn, $listOrder);?></li>
								</ul>						
							</div>
						</div>
					</li>
					*/ ?>
					<li id="drop-3"><a id="btn-3" href="javascript:void(0);" class="btn"><i class="icon-check"></i> <?php echo JText::_('NUM_OF_POIS')?></a>
						<div class="megadrop dropdown_1column">
							<div class="col_1">						
								<ul>
									<!-- dropdown menu links -->
									<?php echo $this->getLimitBox; ?>
								</ul>						
							</div>
						</div>
					</li>
				</ul>
			</form>	
			<div class="header-button">
				<a class="btn btn-large btn-primary rr" href="<?php echo VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&task=addPoi');?>"><i class="icon-plus icon-white"></i> <?php echo JText::_('REPORT_AN_POI');?></a>
			</div>
			
		</div>
	  </div>	
	  </div>
	</div>
	
	<div id="loading"><img src="<?php echo JURI::base().'components/com_virtualcitytour/images/ajax-loader.gif';?>" /></div>
	
	<div class="row-fluid">
	
		<div class="span6">
		<div class="imc-wrapper">
			<?php if(empty($this->items)) : ?>
				<div class="alert alert-error width75">
				<?php echo JText::_('COM_VIRTUALCITYTOUR_FILTER_REVISION'); ?>
				</div>
			<?php endif; ?>
			
			<h2 class="imc-poi-title" id="markerTitle"></h2>
			<div id="panorama"></div>
			<div style="padding-top: 5px;" id="markerHead"></div>	
			<div style="padding-top: 5px;" id="markerImages"></div>	
			<div style="padding-top: 5px;" id="markerInfo"></div>
			<div style="float:right; padding-top: 5px;" id="detailsButton"></div>
			
		</div>	
		</div>
		<div class="span6">
			<div class="imc-wrapper">
			<div id="mapCanvas"><?php echo JText::_('COM_VIRTUALCITYTOUR');?></div>
			<?php if($this->credits == 1) : ?>
				<div style="margin-top: 30px;" class="alert alert-info"><?php echo JText::_('COM_VIRTUALCITYTOUR_INFOALERT');?></div>
			<?php endif; ?>
			</div>
		</div>	
	
	</div>
</div>

