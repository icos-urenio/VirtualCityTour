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
?>

<div id="imc-wrapper">
	<div id="imc-print-header"><a href="javascript:window.print()"><i class="icon-print"></i> <?php echo JText::_('COM_VIRTUALCITYTOUR_CLICK_TO_PRINT');?></a></div>
	
	<h1><?php echo JText::_('COM_VIRTUALCITYTOUR'); ?></h1>
	<h2><?php echo $this->item->title;?></h2>
	

	<div id="imc-poi-general-info">
		<span class="strong"><?php echo JText::_('CATEGORY');?></span><span class="desc"><?php echo $this->item->catname;?></span><br />
		<span class="strong"><?php echo JText::_('ADDRESS');?></span><span class="desc"><?php echo $this->item->address;?></span><br />
		<span class="strong"><?php echo JText::_('REPORTED_BY');?></span><span class="desc"><?php echo $this->item->fullname . ' ' . $this->item->reported_rel;?></span><br />
		<span class="strong"><?php echo JText::_('VIEWED');?></span><span class="desc"><?php echo $this->item->hits;?></span><br />
		<span class="strong"><?php echo JText::_('POI_VOTES_DESC');?>: </span><span class="desc"><?php echo $this->item->votes;?></span>		
	</div>	
	
	<div id="imc-content">	
		<div id="imc-main-panel">
			
			<h3><?php echo JText::_('DESCRIPTION'); ?></h3>
			<div class="desc"><?php echo $this->item->description;?></div>	
			<?php if($this->item->photo != '') : ?>
				<div class="img-wrp"><img src="<?php echo preg_replace('/thumbs\//', '', $this->item->photo, 1);?>" /></div>
			<?php endif; ?>
			
		</div>
		<div id="imc-details-sidebar">			
			<div id="mapCanvas"><?php echo JText::_('COM_VIRTUALCITYTOUR');?></div>	
		</div>
		
		<?php if(!empty($this->discussion)):?>
		<div style="clear: both;"></div>
		<div id="imc-comments-wrapper">
			<h3><?php echo JText::_('COMMENTS'); ?></h3>
			<?php foreach ($this->discussion as $item) : ?>
				<div class="imc-chat">
					<span class="imc-chat-info"><?php echo JText::_('COMMENT_REPORTED') . ' ' . $item->progressdate_rel . ' ' .JText::_('BY') .' ' . $item->fullname; ?></span>
					<span class="imc-chat-desc"><?php echo $item->description;?></span>
				</div>
			<?php endforeach;?>
		</div>			
		<?php endif;?>			
	</div>
	
</div>	
