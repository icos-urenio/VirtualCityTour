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
if($this->popupmodal == 1)
	JHTML::_('behavior.modal', 'a.modalwin', array('handler' => 'ajax')); /* fix */

JText::script('COM_VIRTUALCITYTOUR_WRITE_COMMENT'); 
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
		<div id="imc-menu">
			<!-- bootstrap buttons -->	
			
			<!-- Return to pois -->
			<div class="hr-button">
				<a class="btn imc-left" href="<?php echo VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour');?>"><i class="icon-arrow-left"></i> <?php echo JText::_('RETURN_TO_POIS');?></a>
			</div>
			
			<!-- New Poi -->
			<div class="hr-button">
				<a class="btn btn-primary imc-right" href="<?php echo VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&controller=virtualcitytour&task=addPoi');?>"><i class="icon-plus icon-white"></i> <?php echo JText::_('REPORT_AN_POI');?></a>
			</div>

			<!-- Vote +1 -->
			<div class="hr-button">
				<?php if($this->item->currentstatus != 3 || $this->allowVotingOnClose == 1) : ?>
					<?php if(!$this->guest) : ?>
						<?php if(!$this->hasVoted) :?>
							<a class="btn btn-success imc-right" href="javascript:vote(<?php echo $this->item->id; ?>, '<?php echo JUtility::getToken(); ?>');"><i class="icon-plus icon-white"></i> <?php echo JText::_('NEW_VOTE');?></a>
						<?php else : //already voted ?>
							<button class="btn btn-success imc-right disabled" disabled="disabled"><i class="icon-plus icon-white"></i> <?php echo JText::_('ALREADY_VOTED');?></button>
						<?php endif; ?>
					<?php else : //not logged?>
						<button class="btn btn-success imc-right disabled" disabled="disabled"><i class="icon-plus icon-white"></i> <?php echo JText::_('NEW_VOTE');?></button>
					<?php endif; ?>
				<?php else : ?>
					<button class="btn btn-success imc-right disabled" disabled="disabled"><i class="icon-plus icon-white"></i> <?php echo JText::_('NEW_VOTE');?></button>
				<?php endif;?>
			</div>	
			
			<!-- Print -->
			<?php 
				//make print link
				$url = VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&task=printPoi&poi_id='.$this->item->id.'&tmpl=component');
			?>
			<div class="hr-button">
				<a class="btn modalwin btn-success imc-right" rel="{size: {x: 700, y: 500}, handler:'iframe'}" href="<?php echo $url;?>"><i class="icon-print icon-white"></i> <?php echo JText::_('JGLOBAL_PRINT');?></a>
			</div>
		</div>
	</div>
	</div>
	</div>
	
	<div id="loading"><img src="<?php echo JURI::base().'components/com_virtualcitytour/images/ajax-loader.gif';?>" /></div>		
		
	<div class="row-fluid">
		<div class="span8">
			<div id="imc-poi-item-details">
				
				<h2 class="imc-poi-title"><?php echo $this->item->title;?></h2>

				<div style="clear: both;"></div>

				<div style="padding-top: 5px;" id="panorama"></div>
				<div style="padding-top: 15px;" id="markerHead"></div>	
				<div style="padding-top: 15px;" id="markerImages"></div>	
				<div style="padding-top: 15px;" id="markerInfo"></div>
								

				<div style="clear: both;"></div>
				
				
				<h2 class="imc-poi-title" style="padding-top: 15px;"><?php echo JText::_('COMMENTS'); ?></h2>
				<?php if($this->showcomments == 1) : ?>
					<div id="imc-comments-wrapper">
					<?php if(!empty($this->discussion)):?>
						<?php foreach ($this->discussion as $item) : ?>
							<div class="imc-chat">
								<span class="imc-chat-info"><?php echo JText::_('COMMENT_REPORTED') . ' ' . $item->progressdate_rel . ' ' .JText::_('BY') .' ' . $item->fullname; ?></span>
								<span class="imc-chat-desc"><?php echo $item->description;?></span>
							</div>
						<?php endforeach;?>
					<?php endif;?>
					</div>
					
					<?php if($this->item->currentstatus != 3 || $this->allowCommentingOnClose == 1) : ?> 
					<div id="imc-new-comment-wrapper">
						<?php if(!$this->guest) :?>
						<form name="com_virtualcitytour_comments" id="com_virtualcitytour_comments" method="post" action="#">
								<input type="hidden" name="option" value ="com_virtualcitytour" />
								<input type="hidden" name="controller" value="virtualcitytour" />
								<input type="hidden" name="task" value="addComment" />
								<input type="hidden" name="format" value="json" />
								<input type="hidden" name="poi_id" value="<?php echo $this->item->id; ?>" />
								<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
								<textarea id="imc-comment-area" name="description" style="max-height: 200px; min-height: 65px; max-width: 100%; min-width: 100%; width: 100%;"></textarea>
								<div id="commentBtn">
									<a class="btn imc-right" href="javascript:comment();"><i class="icon-pencil"></i> <?php echo JText::_('ADD_COMMENT');?></a>
								</div>
								<div id="commentIndicator" class="imc-right"></div>
								
								<?php //echo JUtility::getToken();?>
							</form>
						<?php else : //not logged?>
							<?php $return = base64_encode(VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&view=poi&poi_id='.$this->item->id)); ?>
								<div class="alert alert-error">
								<?php echo JText::_('ONLY_LOGGED_COMMENT');?>
								<?php echo JText::_('PLEASE_LOG');?>
								<?php /* UNCOMMENT IF YOU WANT login link 
								<?php $return = base64_encode(VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&view=poi&poi_id='.$this->item->id)); ?>
								<a class="modalwin strong-link" rel="{size: {x: 320, y: 350}}" href="index.php?option=com_users&view=login&tmpl=component&return=<?php echo $return; ?>"><span class="strong-link"><?php echo JText::_('PLEASE_LOG');?></span></a>
								*/ ?>
								</div>
						<?php endif;?>
					</div>
					<?php else : ?>
						<div class="alert alert-error"><?php echo JText::_('CANNOT_COMMENT_ON_CLOSED');?></div>
					<?php endif;?>	
				<?php endif;?>	
					
				
			</div>
		</div>
		<div class="span4">			
			<?php if($this->item->currentstatus != 3 || $this->allowVotingOnClose == 1) : ?>
				<?php if($this->guest) :?>
					<div class="alert alert-error">
					<?php echo JText::_('ONLY_LOGGED_VOTE');?>
					<?php echo JText::_('PLEASE_LOG');?>
					<?php /* UNCOMMENT IF YOU WANT login link 
					<?php $return = base64_encode(VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&view=poi&poi_id='.$this->item->id)); ?>
					<a class="modalwin strong-link" rel="{size: {x: 320, y: 350}}" href="index.php?option=com_users&view=login&tmpl=component&return=<?php echo $return; ?>"><span class="strong-link"><?php echo JText::_('PLEASE_LOG');?></span></a>
					*/?>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<div class="alert alert-error"><?php echo JText::_('CANNOT_VOTE_ON_CLOSED');?></div>
			<?php endif; ?>
			
			<div id="mapCanvas" style="height: 200px;"><?php echo JText::_('COM_VIRTUALCITYTOUR');?></div>
			
			<div id="imc-votes-wrapper">
				<div class="alert alert-success imc-flasher">
					<p class="imc-votes-desc"><?php echo JText::_('POI_VOTES_DESC');?>: <span class="imc-votes-counter"><?php echo $this->item->votes;?></span></p>
				</div>
			</div>
			<div id="imc-poi-general-info">
				<h3 class="imc-poi-title"><?php echo JText::_('COM_VIRTUALCITYTOUR_AT_GLANCE');?></h3>
				<span class="strong"><?php echo JText::_('CATEGORY');?></span><span class="desc"><?php echo $this->item->catname;?></span><br />
				<span class="strong"><?php echo JText::_('ADDRESS');?></span><span class="desc"><?php echo $this->item->address;?></span><br />
				<span class="strong"><?php echo JText::_('REPORTED_BY');?></span><span class="desc"><?php echo $this->item->fullname . ' ' . $this->item->reported_rel;?></span><br />
				<span class="strong"><?php echo JText::_('VIEWED');?></span><span class="desc"><?php echo $this->item->hits;?></span><br />
			</div>			
			
			<?php 
				$chl = JURI::current();
				$chs = 150;
				$choe = 'UTF-8';				
			?>
			<div style="text-align: center;">
			<img src="https://chart.googleapis.com/chart?cht=qr&amp;chl=<?php echo $chl;?>&amp;choe=<?php echo $choe;?>&amp;chs=<?php echo $chs;?>" alt="QR Code" title="QR Code" />
			</div>	
		</div>
	</div>
</div>
