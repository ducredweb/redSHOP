<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2005 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;
?>
	<fieldset class="adminform">
		<legend>
			<?php echo JText::_('COM_REDSHOP_PRODUCT_ATTRIBUTES'); ?>
		</legend>
		<table class="admintable" border="0">
			<tr>
				<td colspan="2"><?php echo JText::_('COM_REDSHOP_HINT_ATTRIBUTE'); ?></td>
			</tr>
			<tr>
				<td colspan="2" class="red_blue_blue">
					<?php echo JText::_('COM_REDSHOP_COPY_ATTRIBUTES_FROM_ATTRIBUTE_SET'); ?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_REDSHOP_COPY'); ?></td>
				<td><?php echo $this->lists['copy_attribute']; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_REDSHOP_PRODUCT_ATTRIBUTE_SET_LBL'); ?></td>
				<td><?php echo $this->lists['attributesSet']; ?></td>
			</tr>
			<tr>
				<td colspan="2">
					<a class="btn btn-success add_attribute btn-small"
					   href="#"> <?php echo '+ ' . JText::_('COM_REDSHOP_NEW_ATTRIBUTE'); ?></a>
				</td>
			</tr>
		</table>
	</fieldset>
	<hr/>
<?php echo RedshopLayoutHelper::render('product_detail.product_attribute', array('this' => $this));
