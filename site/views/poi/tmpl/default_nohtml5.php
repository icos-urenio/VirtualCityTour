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
<style type="text/css">  @import url("<?php echo JURI::root(true).'/components/com_virtualcitytour/css/virtualcitytour_nohtml5.css'; ?>"); </style>

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
	
	<div id="imc-header">
		<div id="imc-menu">
			<!-- bootstrap buttons -->	
			
			<!-- Return to pois -->
			<div class="btn-group imc-left">
				<a class="btn" href="<?php echo VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour');?>"><i class="icon-arrow-left"></i> <?php echo JText::_('RETURN_TO_POIS');?></a>
			</div>
			
			<!-- New Poi -->
			<div class="btn-group imc-right">
				<a class="btn btn-primary" href="<?php echo VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&controller=virtualcitytour&task=addPoi');?>"><i class="icon-plus icon-white"></i> <?php echo JText::_('REPORT_AN_POI');?></a>
			</div>

			<!-- Vote +1 -->
			<div class="btn-group imc-right">
				<?php if($this->item->currentstatus != 3 || $this->allowVotingOnClose == 1) : ?>
					<?php if(!$this->guest) : ?>
						<?php if(!$this->hasVoted) :?>
							<a class="btn btn-success" href="javascript:vote(<?php echo $this->item->id; ?>, '<?php echo JUtility::getToken(); ?>');"><i class="icon-plus icon-white"></i> <?php echo JText::_('NEW_VOTE');?></a>
						<?php else : //already voted ?>
							<button class="btn btn-success disabled" disabled="disabled"><i class="icon-plus icon-white"></i> <?php echo JText::_('ALREADY_VOTED');?></button>
						<?php endif; ?>
					<?php else : //not logged?>
						<button class="btn btn-success disabled" disabled="disabled"><i class="icon-plus icon-white"></i> <?php echo JText::_('NEW_VOTE');?></button>
					<?php endif; ?>
				<?php else : ?>
					<button class="btn btn-success disabled" disabled="disabled"><i class="icon-plus icon-white"></i> <?php echo JText::_('NEW_VOTE');?></button>
				<?php endif;?>
			</div>	
			
			<!-- Print -->
			<?php 
				//make print link
				$url = VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&task=printPoi&poi_id='.$this->item->id.'&tmpl=component');
				/*
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
				$attribs['title']	= JText::_('JGLOBAL_PRINT');
				$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
				$attribs['rel']		= 'nofollow';
				echo JHtml::_('link', $url, 'moo', $attribs);
				*/
			?>
			<a class="btn modalwin btn-success imc-right" rel="{size: {x: 700, y: 500}, handler:'iframe'}" href="<?php echo $url;?>"><i class="icon-print icon-white"></i> <?php echo JText::_('JGLOBAL_PRINT');?></a>
			
		</div>
	</div>
	
	<div id="loading"><img src="<?php echo JURI::base().'components/com_virtualcitytour/images/ajax-loader.gif';?>" /></div>		
		
	<div id="imc-content">	
		<div id="imc-main-panel">
			<div id="imc-poi-item-details">
				
				<h2 class="imc-poi-title"><?php echo $this->item->title;?></h2>
				<div style="clear: both;"></div>
				<?php if($this->item->photo != '') : ?>
					<div class="img-wrp"><img src="<?php echo preg_replace('/thumbs\//', '', $this->item->photo, 1);?>" /></div>
				<?php endif; ?>
				<h3><?php echo JText::_('DESCRIPTION'); ?></h3>
				<div id="imc-poi-description">
					<p class="desc"><?php echo $this->item->description;?></p>
				</div>
				<div style="clear: both;"></div>
				
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
		<div id="imc-details-sidebar">			
			<?php if($this->item->currentstatus != 3 || $this->allowVotingOnClose == 1) : ?>
				<?php if($this->guest) :?>
					<div class="alert alert-error">
					<?php echo JText::_('ONLY_LOGGED_VOTE');?>
					<?php $return = base64_encode(VirtualcitytourHelper::generateRouteLink('index.php?option=com_virtualcitytour&view=poi&poi_id='.$this->item->id)); ?>
					<a class="modalwin strong-link" rel="{size: {x: 320, y: 350}}" href="index.php?option=com_users&view=login&tmpl=component&return=<?php echo $return; ?>"><span class="strong-link"><?php echo JText::_('PLEASE_LOG');?></span></a>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<div class="alert alert-error"><?php echo JText::_('CANNOT_VOTE_ON_CLOSED');?></div>
			<?php endif; ?>
			
			<div id="mapCanvas"><?php echo JText::_('COM_VIRTUALCITYTOUR');?></div>
			
			<div id="imc-votes-wrapper">
				<div class="alert alert-success imc-flasher">
					<p class="imc-votes-desc"><?php echo JText::_('POI_VOTES_DESC');?>: <span class="imc-votes-counter"><?php echo $this->item->votes;?></span></p>
				</div>
			</div>
			<div id="imc-poi-general-info">
				<h2><?php echo JText::_('COM_VIRTUALCITYTOUR_AT_GLANCE');?></h2>
				<span class="strong"><?php echo JText::_('CATEGORY');?></span><span class="desc"><?php echo $this->item->catname;?></span><br />
				<span class="strong"><?php echo JText::_('ADDRESS');?></span><span class="desc"><?php echo $this->item->address;?></span><br />
				<span class="strong"><?php echo JText::_('REPORTED_BY');?></span><span class="desc"><?php echo $this->item->fullname . ' ' . $this->item->reported_rel;?></span><br />
				<span class="strong"><?php echo JText::_('VIEWED');?></span><span class="desc"><?php echo $this->item->hits;?></span><br />
			</div>				
	
		</div>
	</div>
</div>
