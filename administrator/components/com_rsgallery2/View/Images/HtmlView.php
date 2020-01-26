<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_rsgallery2
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Rsgallery2\Administrator\View\Images;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

use Joomla\CMS\Uri\Uri;
use Joomla\Component\Rsgallery2\Administrator\Helper\Rsgallery2Helper;

/**
 * View class for a list of rsgallery2.
 *
 * @since  1.0
 */
class HtmlView extends BaseHtmlView
{
	// ToDo: Use other rights instead of core.admin -> IsRoot ?
	// core.admin is the permission used to control access to 
	// the global config

	protected $UserIsRoot;

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
	 * The pagination object
	 *
	 * @var    Pagination
	 * @since  3.9.0
	 */
	protected $pagination;
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

	protected $HtmlPathThumb;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		//--- get needed form data ------------------------------------------

		// Check rights of user
//		$this->UserIsRoot = $this->CheckUserIsRoot();

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
//		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

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

		//--- thumb --------------------------------------------------------------------

		// ToDo: HtmlPathThumb path must be taken from model (? file model ?)
		$this->HtmlPathThumb = URI::base() . $rsgConfig->get('???imgPath_thumb') . '/';
		////echo 'ThumbPath: ' . JPATH_THUMB . '<br>';
		////echo 'ImagePathThumb: ' . $rsgConfig->imgPath_thumb . '<br>';
		////echo 'ImagePathThumb: ' . JURI_SITE . $rsgConfig->get('imgPath_thumb') . '<br>';
		//echo $this->HtmlPathThumb . '<br>';

		//--- sidebar --------------------------------------------------------------------

		$Layout = $this->getLayout();
		if ($Layout !== 'modal')
		{
			HTMLHelper::_('sidebar.setAction', 'index.php?option=com_rsgallery2&view=Upload');
			Rsgallery2Helper::addSubmenu('galleries');
			$this->sidebar = \JHtmlSidebar::render();

			// $Layout = Factory::getApplication()->input->get('layout');
			$this->addToolbar($Layout);

			// for first debug ToDo: remove ...

			$imageModel = $this->getModel();
			$dummyItems = $imageModel->allImages();
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

		//--- display --------------------------------------------------------------------

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function addToolbar($Layout = 'default')
	{
		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		switch ($Layout)
		{
			case 'images_raw':
		        // on develop show open tasks if existing
		        if (!empty ($this->isDevelop))
		        {
			        echo '<span style="color:red">'
				        . 'Tasks: <br>'
				        . '* Can do ...<br>'
                        . '* Add pagination<br>'
				        . '* archieved, trashed<br>'
                        . '* Add delete function<br>'
        //				. '*  <br>'
        //				. '*  <br>'
        //				. '*  <br>'
        //				. '*  <br>'
				        . '</span><br><br>';
		        }
        
				ToolBarHelper::title(Text::_('COM_RSGALLERY2_IMAGES_VIEW_RAW_DATA'), 'image');

				ToolBarHelper::editList('image.edit');
				ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'image.delete', 'JTOOLBAR_EMPTY_TRASH'); 
				break;


			default:
		        // on develop show open tasks if existing
		        if (!empty ($this->isDevelop))
		        {
			        echo '<span style="color:red">'
				        . 'Tasks: <br>'
				. '* HtmlPathThumb path must be taken from model (? file model ?) <br>'
				        . '* Can do ...<br>'
                        . '* Add pagination<br>'
				        . '* archieved, trashed<br>'
                        . '* Add delete function<br>'
                        . '* Delete function needs to delete watermarked too !<br>'
	                    . '* Search selection has on option too many<br>'
	                    . '* Search controls ...<br>'
                        . '* Sort by image count is wrong<br>'
	                    . '* Image not shown above title (data-original-title?)<br>'
        				. '* Search tools -> group by ?<br>'
        				. '* Batch : turn images .... <br>'
                //				. '*  <br>'
        //				. '*  <br>'
        //				. '*  <br>'
        //				. '*  <br>'
				        . '</span><br><br>';
		        }
        
				ToolBarHelper::title(Text::_('COM_RSGALLERY2_MANAGE_IMAGES'), 'image');

				ToolBarHelper::addNew('image.add');

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
					->task('image.rebuild');



				ToolBarHelper::editList('image.edit');
//				ToolBarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'image.delete', 'JTOOLBAR_EMPTY_TRASH');
				ToolBarHelper::deleteList('', 'image.delete', 'JTOOLBAR_DELETE');

				/**
				// Add a batch button
				$user = Factory::getUser();
				if ($user->authorise('core.create', 'com_rsgallery2')
					&& $user->authorise('core.edit', 'com_rsgallery2')
					&& $user->authorise('core.edit.state', 'com_rsgallery2')
				)
				{
					// Get the toolbar object instance
					$bar = Toolbar::getInstance('toolbar');

					$title = Text::_('JTOOLBAR_BATCH');

					// Instantiate a new JLayoutFile instance and render the batch button
					$layout = new LayoutFile('joomla.toolbar.batch');

					$dhtml = $layout->render(array('title' => $title));
					$bar->appendButton('Custom', $dhtml, 'batch');
				}
				/**/

				break;
		}

		$toolbar->preferences('com_rsgallery2');
	}




}

