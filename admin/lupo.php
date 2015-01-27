<?php
/**
 * @package		Joomla
 * @subpackage	LUPO
 * @copyright	Copyright (C) databauer / Stefan Bauer
 * @author		Stefan Bauer
 * @link				http://www.ludothekprogramm.ch
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_lupo')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Set some global property
$document = JFactory::getDocument();
$document->addStyleDeclaration('.icon-generic:before {content: "" }'); //remove icon
//$document->addStyleDeclaration('.strong {font-weight: bold;}');

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by Lupo
$controller = JControllerLegacy::getInstance('Lupo');

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();


if(isset($_FILES['xmlfile'])){
	$xmlfile = 'lupospiele.xml';
	$xmlpath = 'components/com_lupo/xml_upload/';
	$gamespath = '../images/spiele/';
	
	$zip = new ZipArchive;
	if($_FILES['xmlfile']['tmp_name']==''){
		JFactory::getApplication()->enqueueMessage( JText::_( "Keine Datei ausgewählt!" ), 'error' );
	} else {
		$res = $zip->open($_FILES['xmlfile']['tmp_name']);
		if ($res === TRUE) {
			$zip->extractTo($xmlpath, array($xmlfile)); //extract xml
			$zip->extractTo($gamespath); //extract full archive
			$zip->close();
			
			unlink($gamespath . $xmlfile); //remove xmlfile from images folder
			
			
			if (file_exists($xmlpath .$xmlfile)) {
				$xml = simplexml_load_file($xmlpath . $xmlfile);
				if($xml==false){
					JFactory::getApplication()->enqueueMessage( JText::_( "Fehler in XML Definition" ), 'error' );
				} else {
					$db =& JFactory::getDBO();
					
					$db->setQuery('TRUNCATE #__lupo_game');
					$db->query();
						
					$db->setQuery('TRUNCATE #__lupo_game_editions');
					$db->query();
					
					$db->setQuery('TRUNCATE #__lupo_categories');
					$db->query();

					$db->setQuery('TRUNCATE #__lupo_agecategories');
					$db->query();
					
					foreach($xml->categories->category as $category){
						$db->setQuery('INSERT INTO #__lupo_categories SET 
										id='.$db->quote($category['id']).'
										, title='.$db->quote($category['desc']).'
										, alias='.$db->quote(JApplication::stringURLSafe($category['desc'])).'
										, sort='.$db->quote($category['sort']));
						$db->query();
					}
					
					foreach($xml->age_categories->category as $category){
						$db->setQuery('INSERT INTO #__lupo_agecategories SET 
										id='.$db->quote($category['id']).'
										, title='.$db->quote($category['desc']).'
										, alias='.$db->quote(JApplication::stringURLSafe($category['desc'])).'
										, sort='.$db->quote($category['sort']));
						$db->query();
					}
					
					$n=0;
					foreach($xml->games->game as $game){
						$db->setQuery('INSERT INTO #__lupo_game SET 
										number='.$db->quote($game['number']).'
										, catid='.$db->quote($game['catid']).'
										, age_catid='.$db->quote($game['age_catid']).'
										, title='.$db->quote($game->title).'
										, description='.$db->quote($game->description).'
										, days='.$db->quote($game['days']).'
										, fabricator='.$db->quote($game->fabricator).'
										, play_duration='.$db->quote($game->play_duration).'
										, players='.$db->quote($game->players)
										);
						$db->query();
						$gameid = $db->insertid();
						
						foreach($game->editions->edition as $edition){
							$n++;					
							$db->setQuery('INSERT INTO #__lupo_game_editions SET 
											gameid='.$db->quote($gameid).'
											, `index`='.$db->quote($edition['index']).'
											, edition='.$db->quote($edition['edition']).'
											, acquired_date='.$db->quote($edition['acquired_date']).'
											, tax='.$db->quote($edition['tax'])
											);
							$db->query();
						}		
						
					}		
					JFactory::getApplication()->enqueueMessage( $n . ' Spiele importiert' );		
				}
			} else {
				JFactory::getApplication()->enqueueMessage( JText::_( "Konnte $xmlfile nicht öffnen." ), 'error' );
			}
		} else {
			JFactory::getApplication()->enqueueMessage( JText::_( "Konnte hochgeladene Datei nicht entpacken! zip-Datei erwartet." ), 'error' );	
		}
	} 
}