<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_rsgallery2
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Rsgallery2\Administrator\View\Galleries;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

use Joomla\Component\Rsgallery2\Administrator\Helper\Rsgallery2Helper;

/**
 * View class for a list of rsgallery2.
 *
 * @since  1.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The sidebar markup
	 *
	 * @var  string
	 */
	protected $sidebar;

	protected $buttons = [];

	protected $isDebugBackend;
	protected $isDevelop;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise an \Exception object.
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{

		echo 'HtmlView.php: ' . realpath(dirname(__FILE__)) . '<br>';

		$this->addToolbar();

		//--- config --------------------------------------------------------------------

		$rsgConfig = ComponentHelper::getComponent('com_rsgallery2')->getParams();
		//$compo_params = ComponentHelper::getComponent('com_rsgallery2')->getParams();
		$this->isDebugBackend = $rsgConfig->get('isDebugBackend');
		$this->isDevelop = $rsgConfig->get('isDevelop');

		//---  --------------------------------------------------------------------


		Rsgallery2Helper::addSubmenu('galleries');
		$this->sidebar = \JHtmlSidebar::render();
		HTMLHelper::_('sidebar.setAction', 'index.php?option=com_rsgallery2');

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function addToolbar()
	{
		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		// on develop show open tasks if existing
		if (!empty ($this->isDevelop))
		{
			echo '<span style="color:red">'
				. '*  Test ...<br>'
//				. '*  <br>'
//				. '*  <br>'
//				. '*  <br>'
				. '</span><br><br>';
		}

		// Set the title
		ToolBarHelper::title(Text::_('COM_RSGALLERY2_MANAGE_GALLERIES'), 'images');

		$toolbar->preferences('com_rsgallery2');
	}

}
