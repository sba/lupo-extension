<?php
/**
 * @package		Joomla
 * @subpackage	LUPO
 * @copyright	Copyright (C) 2006 - 2014 databauer / Stefan Bauer
 * @author		Stefan Bauer
 * @link				http://www.ludothekprogramm.ch
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

// load tooltip behavior
JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_lupo'); ?>" method="post" enctype="multipart/form-data" name="adminForm">
	<?php echo JText::_( "COM_LUPO_ADMIN_XML_FILE" )?>: <input type="file" name="xmlfile" />
	<br>
	<input type="hidden" name="act" value="uploadxml" />
	<br>
	
	<input type="submit" name="submit" class="btn btn-large" value="<?php echo JText::_("COM_LUPO_ADMIN_UPLOAD_AND_GO")?>" />
</form>

