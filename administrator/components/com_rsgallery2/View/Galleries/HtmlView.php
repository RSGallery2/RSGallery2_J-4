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
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Rsgallery2\Administrator\Helper\Rsgallery2Helper;

/**
 * View class for a list of rsgallery2.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var  \JForm
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  array
	 */
	public $activeFilters;

	/**
	 * The sidebar markup
	 *
	 * @var  string
	 */
	protected $sidebar;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  \JObject
	 */
	protected $canDo;

	/**
	 * Is there a content type associated with this gallery alias
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $checkTags = false;

	protected $isDebugBackend;
	protected $isDevelop;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->filterForm    = $this->get('FilterForm');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->activeFilters = $this->get('ActiveFilters');

		//$section = $this->state->get('gallery.section') ? $this->state->get('gallery.section') . '.' : '';
		//$this->canDo = ContentHelper::getActions($this->state->get('gallery.component'), $section . 'gallery', $this->item->id);
		//$this->canDo = ContentHelper::getActions('com_rsgallery2', 'category', $this->state->get('filter.category_id'));
		$this->canDo = ContentHelper::getActions('com_rsgallery2');
		//$this->assoc = $this->get('Assoc');

		//--- config --------------------------------------------------------------------

		$rsgConfig = ComponentHelper::getComponent('com_rsgallery2')->getParams();
		//$compo_params = ComponentHelper::getComponent('com_rsgallery2')->getParams();
		$this->isDebugBackend = $rsgConfig->get('isDebugBackend');
		$this->isDevelop = $rsgConfig->get('isDevelop');

		/** ToDo: *
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}
		/**/

		//// Check if we have a content type for this alias
		//if (!empty(TagsHelper::getTypes('objectList', array($this->state->get('gallery.extension') . '.gallery'), true)))
		//{
		//	$this->checkTags = true;
		//}

		/**
		// Prepare a mapping from parent id to the ids of its children
		$this->ordering = array();
		foreach ($this->items as $item)
		{
			$this->ordering[$item->parent_id][] = $item->id;
		}
		/**/

//		Factory::getApplication()->input->set('hidemainmenu', true);
		$Layout = $this->getLayout();
		if ($Layout !== 'modal')
		{
			HTMLHelper::_('sidebar.setAction', 'index.php?option=com_rsgallery2&view=Upload');
			Rsgallery2Helper::addSubmenu('galleries');
			$this->sidebar = \JHtmlSidebar::render();

			// $Layout = Factory::getApplication()->input->get('layout');
			$this->addToolbar($Layout);

			// for first debug ToDo: remove ...

			$galleryModel = $this->getModel();

			$this->items         = $galleryModel->allGalleries();



		}
		else
		{
			/**
			// If we are forcing a language in modal (used for associations).
			if ($this->getLayout() === 'modal' && $forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'cmd'))
			{
				// Set the language field to the forcedLanguage and disable changing it.
				$this->form->setValue('language', null, $forcedLanguage);
				$this->form->setFieldAttribute('language', 'readonly', 'true');

				// Only allow to select galleries with All language or with the forced language.
				$this->form->setFieldAttribute('parent_id', 'language', '*,' . $forcedLanguage);

				// Only allow to select tags with All language or with the forced language.
				$this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
			}
			/**/
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar($Layout = 'default')
	{
		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		// on develop show open tasks if existing
		if (!empty ($this->isDevelop))
		{
			echo '<span style="color:red">'
				. 'Tasks: <br>'
				. '*  Can do ...<br>'
				. '*  archieved, trashed<br>'
//				. '*  <br>'
//				. '*  <br>'
//				. '*  <br>'
				. '</span><br><br>';
		}

		switch ($Layout)
		{
			case 'galleries_raw':
				ToolBarHelper::title(Text::_('COM_RSGALLERY2_GALLERIES_VIEW_RAW_DATA'), 'images');

				ToolBarHelper::editList('gallery.edit');

				// on develop show open tasks if existing
				if (!empty ($Rsg2DevelopActive))
				{
					echo '<span style="color:red">Task: Add delete function, Test add double name</span><br><br>';
				}
				break;


			default:
				ToolBarHelper::title(Text::_('COM_RSGALLERY2_MANAGE_GALLERIES'), 'images');

				ToolBarHelper::addNew('gallery.add');

				$dropdown = $toolbar->dropdownButton('status-group')
					->text('JTOOLBAR_CHANGE_STATUS')
					->toggleSplit(false)
					->icon('fa fa-ellipsis-h')
					->buttonClass('btn btn-action')
					->listCheck(true);

				$childBar = $dropdown->getChildToolbar();

				$childBar->publish('categories.publish')->listCheck(true);

				$childBar->unpublish('categories.unpublish')->listCheck(true);

				$childBar->archive('categories.archive')->listCheck(true);

				$childBar->checkin('categories.checkin')->listCheck(true);

				$childBar->trash('categories.trash')->listCheck(true);

				$toolbar->standardButton('refresh')
					->text('JTOOLBAR_REBUILD')
					->task('gallery.rebuild');



				ToolBarHelper::editList('gallery.edit');
//				ToolBarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'galleries.delete', 'JTOOLBAR_EMPTY_TRASH');
				ToolBarHelper::deleteList('', 'galleries.delete', 'JTOOLBAR_DELETE');

				// on develop show open tasks if existing
				if (!empty ($Rsg2DevelopActive))
				{
					echo '<span style="color:red">Task:  c) Search tools -> group by parent/ parent child tree ? </span><br><br>';
				}

				break;
			
		}

		$toolbar->preferences('com_rsgallery2');


		/** ? joomla media .... ?
		$extension = Factory::getApplication()->input->get('extension');
		$user = Factory::getUser();
		$userId = $user->id;

		$isNew = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Avoid nonsense situation.
		if ($extension == 'com_rsgallery2')
		{
			return;
		}

		// The extension can be in the form com_foo.section
		$parts = explode('.', $extension);
		$component = $parts[0];
		$section = (count($parts) > 1) ? $parts[1] : null;
		$componentParams = ComponentHelper::getParams($component);

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = Factory::getLanguage();
		$lang->load($component, JPATH_BASE, null, false, true)
		|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component, null, false, true);

		// Get the results for each action.
		$canDo = $this->canDo;

		// If a component galleries title string is present, let's use it.
		if ($lang->hasKey($component_title_key = $component . ($section ? "_$section" : '') . '_GALLERY_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE'))
		{
			$title = Text::_($component_title_key);
		}
		// Else if the component section string exits, let's use it
		elseif ($lang->hasKey($component_section_key = $component . ($section ? "_$section" : '')))
		{
			$title = Text::sprintf('COM_RSGALLERY2_GALLERY_' . ($isNew ? 'ADD' : 'EDIT')
					. '_TITLE', $this->escape(Text::_($component_section_key))
					);
		}
		// Else use the base title
		else
		{
			$title = Text::_('COM_RSGALLERY2_GALLERY_BASE_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE');
		}

		// Load specific css component
		HTMLHelper::_('stylesheet', $component . '/administrator/galleries.css', array('version' => 'auto', 'relative' => true));

		// Prepare the toolbar.
		ToolbarHelper::title(
			$title,
			'folder gallery-' . ($isNew ? 'add' : 'edit')
				. ' ' . substr($component, 4) . ($section ? "-$section" : '') . '-gallery-' . ($isNew ? 'add' : 'edit')
		);

		// For new records, check the create permission.
		if ($isNew && (count($user->getAuthorisedCategories($component, 'core.create')) > 0))
		{
			ToolbarHelper::saveGroup(
				[
					['apply', 'gallery.apply'],
					['save', 'gallery.save'],
					['save2new', 'gallery.save2new']
				],
				'btn-success'
			);

			ToolbarHelper::cancel('gallery.cancel');
		}

		// If not checked out, can save the item.
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_user_id == $userId);

			$toolbarButtons = [];

			// Can't save the record if it's checked out and editable
			if (!$checkedOut && $itemEditable)
			{
				$toolbarButtons[] = ['apply', 'gallery.apply'];
				$toolbarButtons[] = ['save', 'gallery.save'];

				if ($canDo->get('core.create'))
				{
					$toolbarButtons[] = ['save2new', 'gallery.save2new'];
				}
			}

			// If an existing item, can save to a copy.
			if ($canDo->get('core.create'))
			{
				$toolbarButtons[] = ['save2copy', 'gallery.save2copy'];
			}

			ToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);

			if (ComponentHelper::isEnabled('com_history') && $componentParams->get('save_history', 0) && $itemEditable)
			{
				$typeAlias = $extension . '.gallery';
				ToolbarHelper::versions($typeAlias, $this->item->id);
			}

			ToolbarHelper::cancel('gallery.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();

		// Compute the ref_key
		$ref_key = strtoupper($component . ($section ? "_$section" : '')) . '_GALLERY_' . ($isNew ? 'ADD' : 'EDIT') . '_HELP_KEY';

		// Check if thr computed ref_key does exist in the component
		if (!$lang->hasKey($ref_key))
		{
			$ref_key = 'JHELP_COMPONENTS_'
						. strtoupper(substr($component, 4) . ($section ? "_$section" : ''))
						. '_GALLERY_' . ($isNew ? 'ADD' : 'EDIT');
		}

		/*
		 * Get help for the gallery/section view for the component by
		 * -remotely searching in a language defined dedicated URL: *component*_HELP_URL
		 * -locally  searching in a component help file if helpURL param exists in the component and is set to ''
		 * -remotely searching in a component URL if helpURL param exists in the component and is NOT set to ''
		 *
		if ($lang->hasKey($lang_help_url = strtoupper($component) . '_HELP_URL'))
		{
			$debug = $lang->setDebug(false);
			$url = Text::_($lang_help_url);
			$lang->setDebug($debug);
		}
		else
		{
			$url = null;
		}

		ToolbarHelper::help($ref_key, $componentParams->exists('helpURL'), $url, $component);
		/**/
	}
	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   1.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering'     => Text::_('JGRID_HEADING_ORDERING'),
			'a.published'    => Text::_('JSTATUS'),
			'a.name'         => Text::_('JGLOBAL_TITLE'),
			'category_title' => Text::_('JCATEGORY'),
			'a.access'       => Text::_('JGRID_HEADING_ACCESS'),
			'a.language'     => Text::_('JGRID_HEADING_LANGUAGE'),
			'a.id'           => Text::_('JGRID_HEADING_ID'),
		);
	}
}
