<?php
/**
 * @package     RSGallery2
 * @subpackage  com_rsgallery2
 * @copyright   (C) 2016-2019 RSGallery2 Team
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @author      finnern
 * RSGallery is Free Software
 */

namespace Joomla\Component\Rsgallery2\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * RSGallery2 Component Images Model
 *
 * @since  1.6
 */
class ImagesModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see     \JControllerLegacy
	 * @since   1.6
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
				'gallery_id', 'a.gallery_id',

                'published', 'a.published',

                'created', 'a.created',
                'created_by', 'a.created_by',

                'modified', 'a.modified',
                'modified_by', 'a.modified_by',

                'ordering', 'a.ordering',

                'hits', 'a.hits',
                'rating', 'a.rating',
                'votes', 'a.votes',
                'comments', 'a.comments',
				'tag',
			);
		}

		if (Associations::isEnabled())
		{
			$config['filter_fields'][] = 'association';
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
		$app = Factory::getApplication();


		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}


		//$forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');
		//// Adjust the context to support forced languages.
		//if ($forcedLanguage)
		//{
		//	$this->context .= '.' . $forcedLanguage;
		//}

		//$extension = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', 'com_rsgallery2', 'cmd');
		//$this->setState('filter.extension', $extension);
		//$parts = explode('.', $extension);

		//// Extract the component name
		//$this->setState('filter.component', $parts[0]);

		//// Extract the optional section name
		//$this->setState('filter.section', (count($parts) > 1) ? $parts[1] : null);

		$search   = $this->getUserStateFromRequest($this->context . '.search', 'filter_search');
		$this->setState('filter.search', $search);

		$gallery_id = $this->getUserStateFromRequest($this->context . '.filter.gallery_id', 'filter_gallery_id');
		$this->setState('filter.gallery_id', $gallery_id);

		// List state information.
		parent::populateState($ordering, $direction);

		//// Force a language.
		//if (!empty($forcedLanguage))
		//{
		//	$this->setState('filter.language', $forcedLanguage);
		//}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   4.3.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
//		$id .= ':' . $this->getState('filter.extension');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.gallery_id');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.access');
//		$id .= ':' . $this->getState('filter.language');
//		$id .= ':' . $this->getState('filter.level');
		$id .= ':' . $this->getState('filter.tag');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a database query to list images.
	 *
	 * @return  \JDatabaseQuery object.
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$user = Factory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				/**/
				'list.select',
				'a.id, '
				. 'a.name, '
				. 'a.alias, '
				. 'a.description, '
                . 'a.note, '
				. 'a.gallery_id, '
                . 'a.title, '

				. 'a.params, '
				. 'a.published, '
				. 'a.published_up, '
				. 'a.published_down, '

                . 'a.hits, '
				. 'a.rating, '
				. 'a.votes, '
				. 'a.comments, '

 //               . 'a.publish_up,'
 //               . 'a.publish_down,'

                . 'a.checked_out, '
				. 'a.checked_out_time, '
				. 'a.created, '
				. 'a.created_by, '
				. 'a.created_by_alias, '
				. 'a.modified, '
				. 'a.modified_by, '

				. 'a.ordering, '
                . 'a.approved, '
                . 'a.asset_id, '
				. 'a.access '
			)
		);
		$query->from('#__rsg2_images as a');

		/* parent gallery name */
		$query->select('gal.name as gallery_name')
			->join('LEFT', '#__rsg2_galleries AS gal ON gal.id = a.gallery_id'
			);

		//// Join over the language
		//$query->select('l.title AS language_title, l.image AS language_image')
		//	->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		/**
		// Join over the associations.
		$assoc = $this->getAssoc();

		if ($assoc)
		{
			$query->select('COUNT(asso2.id)>1 as association')
				->join('LEFT', '#__associations AS asso ON asso.id = a.id AND asso.context=' . $db->quote('com_rsgallery2.item'))
				->join('LEFT', '#__associations AS asso2 ON asso2.key = asso.key')
				->group('a.id, l.title, uc.name, ag.title, ua.name');
		}
		/**/

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where('a.level <= ' . (int) $level);
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = (string) $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published IN (0, 1))');
		}

        // Filter by search in name and others
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where(
				'a.name LIKE ' . $search
				. ' OR a.title LIKE ' . $search
				. ' OR a.description LIKE ' . $search
				. ' OR gal.name LIKE ' . $search
				. ' OR a.note LIKE ' . $search
				. ' OR a.created LIKE ' . $search
				. ' OR a.modified LIKE ' . $search
			);
		}

		// exclude root helloworld record
		// $query->where('a.id > 1');

		/**
		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}
		/**/

		// Filter by a single tag.
		/**
		$tagId = $this->getState('filter.tag');

		if (is_numeric($tagId))
		{
			$query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId)
				->join(
					'LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
					. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote($extension . '.category')
				);
		}
		/**/

		// Add the list ordering clause

/**
		// changes need changes above too -> populateState
		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');

		if ($orderCol == 'a.ordering' || $orderCol == 'ordering')
		{
			$orderCol = 'a.gallery_id ' . $orderDirn . ', a.ordering';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));
/**/

		$listOrdering = $this->getState('list.ordering', 'a.ordering');
		$listDirn = $db->escape($this->getState('list.direction', 'DESC'));

		if ($listOrdering == 'a.access')
		{
			$query->order('a.access ' . $listDirn . ', a.id ' . $listDirn);
		}
		else
		{
			$query->order($db->escape($listOrdering) . ' ' . $listDirn);
		}

		// Group by on Images for \JOIN with component tables to count items
		$query->group(
			/**/
			'a.id, '
			. 'a.name, '
			. 'a.alias, '
			. 'a.description, '
			. 'a.gallery_id, '
			. 'a.title, '

			. 'a.note, '
			. 'a.params, '
			. 'a.published, '
            . 'a.published_up, '
            . 'a.published_down, '

			. 'a.hits, '
			. 'a.rating, '
			. 'a.votes, '
			. 'a.comments, '

			. 'a.checked_out, '
			. 'a.checked_out_time, '
			. 'a.created, '
			. 'a.created_by, '
			. 'a.created_by_alias, '
			. 'a.modified, '
			. 'a.modified_by, '

			. 'a.ordering, '
            . 'a.approved, '
			. 'a.asset_id, '
			. 'a.access '
			/**/
		);

		return $query;
	}

	/**
	 * Method to determine if an association exists
	 *
	 * @return  boolean  True if the association exists
	 *
	 * @since   3.0
	 */
	public function getAssoc()
	{
		static $assoc = null;

		if (!is_null($assoc))
		{
			return $assoc;
		}

		$extension = $this->getState('filter.extension');

		$assoc = Associations::isEnabled();
		$extension = explode('.', $extension);
		$component = array_shift($extension);
		$cname = str_replace('com_', '', $component);

		if (!$assoc || !$component || !$cname)
		{
			$assoc = false;

			return $assoc;
		}

		$componentObject = $this->bootComponent($component);

		if ($componentObject instanceof AssociationServiceInterface && $componentObject instanceof CategoryServiceInterface)
		{
			$assoc = true;

			return $assoc;
		}

		$hname = $cname . 'HelperAssociation';
		\JLoader::register($hname, JPATH_SITE . '/components/' . $component . '/helpers/association.php');

		$assoc = class_exists($hname) && !empty($hname::$category_association);

		return $assoc;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   3.0.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if ($items != false)
		{
			$extension = $this->getState('filter.extension');

			$this->countItems($items, $extension);
		}

		return $items;
	}

	/**
	 * Method to load the countItems method from the extensions
	 *
	 * @param   \stdClass[]  &$items     The category items
	 * @param   string       $extension  The category extension
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function countItems(&$items, $extension)
	{
		$parts     = explode('.', $extension, 2);
		$section   = '';

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		$component = Factory::getApplication()->bootComponent($parts[0]);

		if ($component instanceof CategoryServiceInterface)
		{
			$component->countItems($items, $section);
		}
	}


	/**
	 * This function will retrieve the data of the n last uploaded images
	 *
	 * @param int $limit > 0 will limit the number of lines returned
	 *
	 * @return array rows with image name, gallery name, date, and user name as rows
	 *
	 * @since   4.3.0
	 * @throws Exception
	 */
	public static function latestImages($limit)
	{
		$latest = array();

		try
		{
			// Create a new query object.
			$db    = Factory::getDBO();
			$query = $db->getQuery(true);

			$query
				->select('*')
				->from($db->quoteName('#__rsg2_images'))
				->order($db->quoteName('id') . ' DESC');

			$db->setQuery($query, 0, $limit);
			$rows = $db->loadObjectList();

			foreach ($rows as $row)
			{
				$ImgInfo            = array();
				$ImgInfo['name']    = $row->name;
				$ImgInfo['gallery'] = ImagesModel::GalleryName($row->gallery_id);
				$ImgInfo['date']    = $row->date;

				//$ImgInfo['user'] = rsgallery2ModelImages::getUsernameFromId($row->userid);
				$user            = Factory::getUser($row->created_by);
				$ImgInfo['user'] = $user->get('username');

				$latest[] = $ImgInfo;
			}
		}
		catch (RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'latestImages: Error executing query: "' . $query . '"' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $latest;
	}

	/**
	 * This function will retrieve the data of the n last uploaded images
	 *
	 * @param int $limit > 0 will limit the number of lines returned
	 *
	 * @return array rows with image name, gallery name, date, and user name as rows
	 *
	 * @since   4.3.0
	 */
	static function allImages()
	{
		$latest = array();

		try
		{
			// Create a new query object.
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			//$query = 'SELECT * FROM `#__rsgallery2_files` WHERE (`date` >= '. $database->quote($lastweek)
			//	.' AND `published` = 1) ORDER BY `id` DESC LIMIT 0,5';

			$query
				->select('*')
				->from($db->quoteName('#__rsg2_images'))
				//->where date ... ???
				->order($db->quoteName('id') . ' DESC');

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			/**
			foreach ($rows as $row)
			{
			$ImgInfo         = array();
			$ImgInfo['name'] = $row->name;
			$ImgInfo['id']   = $row->id;

			//$ImgInfo['user'] = rsgallery2ModelGalleries::getUsernameFromId($row->uid);
			$user            = Factory::getUser($row->created_by);
			//$ImgInfo['user'] = $user->get('username');
			$ImgInfo['user'] = $user->name;
			//$ImgInfo['user'] = "*Finnern was auch immer";

			$latest[] = $ImgInfo;
			}
			/**/
		}
		catch (RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'allImages: Error executing query: "' . $query . '"' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $rows;
	}

	// ToDO: Rename as it may not be parent gallery name :-()
	protected static function GalleryName($id)
	{
		// Create a new query object.
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);

		//$sql = 'SELECT `name` FROM `#__rsgallery2_galleries` WHERE `id` = '. (int) $id;
		$query
			->select('name')
			->from('#__rsg2_galleries')
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);
		$db->execute();

		// http://docs.joomla.org/Selecting_data_using_JDatabase
		$name = $db->loadResult();
		$name = $name ? $name : Text::_('COM_RSGALLERY2_GALLERY_ID_ERROR');

		return $name;
	}

    /**
     * Reset image table to empty state
     * Deletes all galleries and initialises the root of the nested tree
     *
     * @return bool
     *
     * @since version
     */
    public static function resetImagesTable()
    {
        $isImagesReset = false;

        $id_galleries = '#__rsg2_images';

        try {
            $db = Factory::getDbo();

            //--- delete old rows -----------------------------------------------

            $query = $db->getQuery(true);

            $query->delete($db->quoteName($id_galleries));
            // all rows
            //$query->where($conditions);

            $db->setQuery($query);

            $isImagesReset = $db->execute();

        } //catch (\RuntimeException $e)
        catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage() . ' from resetImagesTable');
        }

        return $isImagesReset;
    }


} // class
