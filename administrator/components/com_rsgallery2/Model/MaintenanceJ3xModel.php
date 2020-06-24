<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomla\Component\Rsgallery2\Administrator\Model;

defined('_JEXEC') or die;

use JModelLegacy;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use JTableNested;
use Joomla\CMS\Helper\ModuleHelper;

//use Joomla\CMS\Extension\Service\Provider\MVCFactory;
//use Joomla\Component\Rsgallery2\Administrator\Model\GalleryModel;


/**
 * Class MaintenanceJ3xModel
 * @package Joomla\Component\Rsgallery2\Administrator\Model
 *
 * Handles old J3x RSG23 data structures. Especially for transferring the config data
 *
 *
 */

class MaintenanceJ3xModel extends BaseDatabaseModel
{

    /**
     * @return array|mixed
     * @throws \Exception
     */
    static function OldConfigItems()
    {
        $oldItems = array();

        try {
            if (MaintenanceJ3xModel::J3xConfigTableExist()) {
                // Create a new query object.
                $db = Factory::getDbo();
                $query = $db->getQuery(true);

                $query
                    //->select('*')
                    ->select($db->quoteName(array('name', 'value')))
                    ->from($db->quoteName('#__rsgallery2_config'))
                    ->order($db->quoteName('name') . ' ASC');
                $db->setQuery($query);

                $oldItems = $db->loadAssocList('name', 'value');
            }
        } catch (RuntimeException $e) {
            $OutTxt = '';
            $OutTxt .= 'OldConfigItems: Error executing query: "' . $query . '"' . '<br>';
            $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

            $app = Factory::getApplication();
            $app->enqueueMessage($OutTxt, 'error');
        }

        return $oldItems;
    }

    /**
     * Merge J3x configuration into J4x
     * @param $J3xConfigItems
     * @param $configVars
     * @return array
     * @throws \Exception
     */

    // ToDo: There may other merged operation needed instead of 1:1 copy
    static function MergeJ3xConfiguration($J3xConfigItems, $configVars)
    {
        // component parameters to array
        $compConfig = [];
        $mergedConfigItems = [];

        try {

//			foreach ($configVars as  $key => $value)
//			{
//				$compConfig [$key] = $value;
//			}
//
//			// tell about merge version
//            // ToDo: use state table
//            $compConfig ['j3x_merged_cfg_version'] = '0.1';
//
//			// J3.5 old configuration vars
//            // ToDo instead: foreach ($configVars as  $key => $value) -> if 4key in J3 use J3 otherwise use J4 version
//			$mergedConfigItems = array_merge($J3xConfigItems, $compConfig);

            foreach ($configVars as $key => $value) {
                // Is J3x item ?
                if (array_key_exists($key, $J3xConfigItems)) {
                    $compConfig [$key] = $J3xConfigItems [$key];
                } else {
                    $compConfig [$key] = $value;
                }
            }

            // ToDo: transfer special cases


            ksort($mergedConfigItems);
        } catch (RuntimeException $e) {
            $OutTxt = '';
            $OutTxt .= 'OldConfigItems: Error executing MergeJ3xConfiguration: <br>';
            $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

            $app = Factory::getApplication();
            $app->enqueueMessage($OutTxt, 'error');
        }

        return $mergedConfigItems;
    }

    // ToDo: attention a double of this function exist. Remove either of them

    static function J3xConfigTableExist()
    {
        return MaintenanceJ3xModel::J3xTableExist('#__rsgallery2_config');
    }

    static function J3xGalleriesTableExist()
    {
        return MaintenanceJ3xModel::J3xTableExist('#__rsgallery2_galleries');
    }

    static function J3xImagesTableExist()
    {
        return MaintenanceJ3xModel::J3xTableExist('#__rsgallery2_files');
    }

    static function J3xTableExist($findTable)
    {
        $tableExist = false;

        try {
            $db = Factory::getDbo();
            $db->setQuery('SHOW TABLES');
            $existingTables = $db->loadColumn();

            $checkTable = $db->replacePrefix($findTable);

            $tableExist = in_array($checkTable, $existingTables);
        } catch (RuntimeException $e) {
            $OutTxt = '';
            $OutTxt .= 'J3xTableExist: Error executing query: "' . "SHOW_TABLES" . '"' . '<br>';
            $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

            $app = Factory::getApplication();
            $app->enqueueMessage($OutTxt, 'error');
        }

        return $tableExist;
    }


    public function j3x_galleriesList()
    {
        $galleries = array();

        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
//                ->select($db->quoteName(array('id', 'name', 'parent', 'ordering')))
                ->select('*')
                ->from('#__rsgallery2_galleries')
                ->order('ordering ASC');

            // Get the options.
            $db->setQuery($query);

            $galleries = $db->loadObjectList();

        }
        catch (RuntimeException $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }


        return $galleries;
    }

    public function j3x_galleriesListSorted()
    {
        $galleries = array();

        try {
            // fetch from db
            $dbGalleries = $this->j3x_galleriesList();

            // sort recursively
            $galleries = $this->j3x_galleriesSortedByParent($dbGalleries, 0, 0);
        }
        catch (RuntimeException $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }

        return $galleries;
    }

    private static function cmpJ3xGalleries($aGallery, $bGallery)
    {
        $a = $aGallery->ordering;
        $b = $bGallery->ordering;

        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }

    public function j3x_galleriesSortedByParent($dbGalleries, $parentId=0, $level=0)
    {
        $sortedGalleries = [];

        try {
            // parent galleries
            $galleries = [];

            // galleries of given level
            foreach ($dbGalleries as $gallery) {

                if ($gallery->parent == $parentId) {

                    $gallery->level = $level;

                    // collect gallery
                    $galleries [] = $gallery;

                    // add childs recursively
                    $id = $gallery->id;
                    $subGalleries [$id] = $this->j3x_galleriesSortedByParent($dbGalleries, $id, $level+1);
                }
            }

            // Sort galleries of level
            if ( count ($galleries) > 1) {
                usort($galleries, array ($this, 'cmpJ3xGalleries'));
            }

            // Collect sorted list with childs
            foreach ($galleries as $gallery) {
                $sortedGalleries[] = $gallery;

                // Add childs
                $id = $gallery->id;
                //echo 'array_push: $id' . $id . '<br>';

                foreach ($subGalleries [$id] as $subGallery) {
                    $sortedGalleries[] = $subGallery;
                }
            }
        }
        catch (RuntimeException $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }

        return $sortedGalleries;
    }

    public function j4x_galleriesList()
    {
        $galleries = array();

        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
//                ->select($db->quoteName(array('id', 'name', 'parent_id', 'level'))) // 'path'
                ->select('*')
                ->from('#__rsg2_galleries')
                ->order('level ASC');

            // Get the options.
            $db->setQuery($query);

            $galleries = $db->loadObjectList();

        }
        catch (RuntimeException $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }

        return $galleries;
    }


    public function j4_GalleriesToJ3Form($j4x_galleries)
    {
        $j3x_galleries = [];

        try {
            foreach ($j4x_galleries as $j4x_gallery) {

                // leave out root gallery in nested form
                if ($j4x_gallery->id != 1) {
                    $j3x_gallery = new \stdClass();

                    $j3x_gallery->id = $j4x_gallery->id;
                    $j3x_gallery->name = $j4x_gallery->name;

//                    // parent 1 is going to root
//                    if($j4x_gallery->parent_id == 1) {
//                        $j4x_gallery->parent_id = 0;
//                    }

                    $j3x_gallery->parent = $j4x_gallery->parent_id;
                    // $j3x_gallery->ordering = $j4x_gallery->level;
                    $j3x_gallery->ordering = $j4x_gallery->lft;

                    $j3x_galleries[] = $j3x_gallery;
                }
            }
        }
        catch (RuntimeException $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }

        return $j3x_galleries;
    }

    public function GalleriesListAsHTML($galleries)
    {
        $html = '';

        try {

            if ( ! empty ($galleries)) {
                // all root galleries and nested ones
                $html = $this->GalleriesOfLevelHTML($galleries, 0, 0);
            }
        } catch (RuntimeException $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }

        return $html;
    }

    private function GalleriesOfLevelHTML($galleries, $parentId, $level)
    {
        $html = [];

        try {

            $galleryHTML = [];

            foreach ($galleries as $gallery) {

                if ($gallery->parent == $parentId) {

                    // html of this gallery
                    $galleryHTML [] = $this->GalleryHTML($gallery, $level);

                    $subHtml = $this->GalleriesOfLevelHTML($galleries, $gallery->id, $level+1);
                    if (!empty ($subHtml)) {
                        $galleryHTML [] = $subHtml;
                    }
                }
            }

            // surround with <ul>
            if ( ! empty ($galleryHTML)) {

                $lineStart = str_repeat(" ", 3*($parentId));

                array_unshift ($galleryHTML,  $lineStart . '<ul class="list-group">');
                $galleryHTML [] = $lineStart . '</ul>';

                $html = $galleryHTML;
            }

        } catch (RuntimeException $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }

        return implode($html);
    }

    // ToDo use styling for nested from https://stackoverflow.com/questions/29063244/consistent-styling-for-nested-lists-with-bootstrap
    private function GalleryHTML($gallery, $level)
    {
        $html = [];

        $lineStart = str_repeat(" ", 3*($level+1));
        $identHtml = '';
        if ($level > 0) {
            $identHtml = '<span class="text-muted">';
            $identHtml .= str_repeat('⋮&nbsp;&nbsp;&nbsp;', $level - 1);
            $identHtml .= '</span>';
            $identHtml .= '-&nbsp;';
        }

        $id = $gallery->id;
        $parent = $gallery->parent;
        $order = $gallery->ordering;
        $name = $gallery->name;

        try {

            $html = <<<EOT
$lineStart<li class="list-group-item">
$lineStart   $identHtml<span> id: </span><span>$id</span>
$lineStart   <span> parent: </span><span>$parent</span>
$lineStart   <span> order: </span><span>$order</span>
$lineStart   <span> name:</span><span>$name</span>
$lineStart</li>
EOT;

        } catch (RuntimeException $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }

        return $html;
    }

//    public function copyJ3xItems2J4x ($J3xGalleryItemsSorted) {
//        $isOk = false;
//
//        try {
//
//            //$test = new BaseDatabaseModel (array());
//
//            //$galleryModel =  new GalleryModel ();
//            //$galleryModel = new \Joomla\Component\Rsgallery2\Administrator\Model\GalleryModel(array('ignore_request' => true));
////            $galleryModel = new \Joomla\Component\Rsgallery2\Administrator\Model\GalleryModel(['ignore_request' => true]);
////            $galleryModel =
////            $galleryModel = JModelLegacy::getInstance('GalleryModel', 'RSGallery2Model');
//            $galleryModel = $this->getInsconvertJ3xGallertance('gallerymodel', 'RSGallery2Model');
//
//            $isOk = True;
//
//            // galleries of given level
//            foreach ($J3xGalleryItemsSorted as $j3xGallery) {
//
//                $J4GalleryItem = $this->convertJ3xGallery($j3xGallery);
//
//                // do save
//                $isOk &= $galleryModel->save($J4GalleryItem);
//            }
//
//        }
//        catch (RuntimeException $e)
//        {
//            JFactory::getApplication()->enqueueMessage($e->getMessage());
//        }
//
//        return $isOk;
//    }
//

    public function convertJ3xGalleriesToJ4x ($J3xGalleryItemsSorted) {

        $J4Galleries = [];

        try {

            // galleries of given level
            foreach ($J3xGalleryItemsSorted as $j3xGallery) {

                $J4Galleries[] = $this->convertJ3xGallery($j3xGallery);
            }

        }
        catch (RuntimeException $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }

        return $J4Galleries;
    }


    private function convertJ3xGallery ($j3x_gallery) {

        $j4_GalleryItem = []; //new \stdClass();

        // `id` int(11) NOT NULL auto_increment,
        $j4_GalleryItem['id'] = $j3x_gallery->id;
        // `parent` int(11) NOT NULL default 0,
        $j4_GalleryItem['parent_id'] = $j3x_gallery->parent;
        // `name` varchar(255) NOT NULL default '',
        $j4_GalleryItem['name'] = $j3x_gallery->name;
        // `alias` varchar(255) NOT NULL DEFAULT '',
        $j4_GalleryItem['alias'] = $j3x_gallery->alias;
        // `description` text NOT NULL,
        $j4_GalleryItem['description'] = $j3x_gallery->description;
        // `published` tinyint(1) NOT NULL default '0',
        $j4_GalleryItem['published'] = $j3x_gallery->published;
        // `checked_out` int(11) unsigned NOT NULL default '0',
        $j4_GalleryItem['checked_out'] = $j3x_gallery->checked_out;
        // `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
        $j4_GalleryItem['checked_out_time'] = $j3x_gallery->checked_out_time;
        // `ordering` int(11) NOT NULL default '0',
        $j4_GalleryItem['ordering'] = $j3x_gallery->ordering;
        // `date` datetime NOT NULL default '0000-00-00 00:00:00',
        $j4_GalleryItem['date']= $j3x_gallery->date;
        // `hits` int(11) NOT NULL default '0',
        $j4_GalleryItem['hits'] = $j3x_gallery->hits;
        // `params` text NOT NULL,
        $j4_GalleryItem['params'] = $j3x_gallery->params;
        // `user` tinyint(4) NOT NULL default '0',
        $j4_GalleryItem['user'] = $j3x_gallery->user;
        // `uid` int(11) unsigned NOT NULL default '0',
        $j4_GalleryItem['uid'] = $j3x_gallery->uid;
        // `allowed` varchar(100) NOT NULL default '0',
        $j4_GalleryItem['allowed'] = $j3x_gallery->allowed;
        // `thumb_id` int(11) unsigned NOT NULL default '0',
        $j4_GalleryItem['thumb_id'] = $j3x_gallery->thumb_id;
        // `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
        $j4_GalleryItem['asset_id'] = $j3x_gallery->asset_id;
        // `access` int(10) unsigned DEFAULT NULL,
        $j4_GalleryItem['access'] = $j3x_gallery->access;

        return $j4_GalleryItem;
    }

    public function j3x_imagesList()
    {
        $galleries = array();

        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
//                ->select($db->quoteName(array('id', 'name', 'parent', 'ordering')))
                ->select('*')
                ->from('#__rsgallery2_files')
                ->order('id ASC');

            // Get the options.
            $db->setQuery($query);

            $galleries = $db->loadObjectList();

        }
        catch (RuntimeException $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }


        return $galleries;
    }

    public function j4x_imagesList()
    {
        $galleries = array();

        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
//                ->select($db->quoteName(array('id', 'name', 'parent_id', 'level'))) // 'path'
                ->select('*')
                ->from('#__rsg2_images')
                ->order('id ASC');

            // Get the options.
            $db->setQuery($query);

            $galleries = $db->loadObjectList();

        }
        catch (RuntimeException $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }

        return $galleries;
    }

    public function convertJ3xImagesToJ4x ($J3xImagesItems) {

        $J4Galleries = [];

        try {

            // galleries of given level
            foreach ($J3xImagesItems as $j3xImage) {

                $J4Galleries[] = $this->convertJ3xImage($j3xImage);
            }

        }
        catch (RuntimeException $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
        }

        return $J4Galleries;
    }


    private function convertJ3xImage ($j3x_image) {

        $j4_imageItem = []; //new \stdClass();

        //`id` serial NOT NULL,
        $j4_imageItem['id'] = $j3x_image->id;
        //`name` varchar(255) NOT NULL default '',
        $j4_imageItem['name'] = $j3x_image->name;
        //`alias` varchar(255) NOT NULL DEFAULT '',
        $j4_imageItem['alias'] = $j3x_image->alias;
        //`description` text NOT NULL,
        $j4_imageItem['description'] = $j3x_image->desc;

        //`gallery_id` int(9) unsigned NOT NULL default '0',
        $j4_imageItem['gallery_id'] = $j3x_image->gallery_id;
        //`title` varchar(255) NOT NULL default '',
        $j4_imageItem['title'] = $j3x_image->title;

        //`note` varchar(255) NOT NULL DEFAULT '',
        $j4_imageItem['note'] = ''; // $j3x_image->note;
        //`params` text NOT NULL,
        $j4_imageItem['params'] = $j3x_image->params;
        //`published` tinyint(1) NOT NULL default '1',
        $j4_imageItem['published'] = $j3x_image->published;
        //`hits` int(11) unsigned NOT NULL default '0',
        $j4_imageItem['hits'] = $j3x_image->hits;

        //`rating` int(10) unsigned NOT NULL default '0',
        $j4_imageItem['rating'] = $j3x_image->rating;
        //`votes` int(10) unsigned NOT NULL default '0',
        $j4_imageItem['votes'] = $j3x_image->votes;
        //`comments` int(10) unsigned NOT NULL default '0',
        $j4_imageItem['comments'] = $j3x_image->comments;

        //`publish_up` datetime,
        $j4_imageItem['publish_up'] = $j3x_image->publish_up;

//        $pub = new DateTime($item->publish_up);
//        $item->publish_down = $pub->add(new DateInterval('P30D'))->format('Y-m-d H:i:s');
        //`publish_down` datetime,
//        $j4_imageItem['publish_down'] = $j3x_image->publish_down;

        //`checked_out` int(10) unsigned NOT NULL DEFAULT 0,
        $j4_imageItem['checked_out'] = $j3x_image->checked_out;
        //`checked_out_time` datetime,
        $j4_imageItem['checked_out_time'] = $j3x_image->checked_out_time;
        //`created` datetime NOT NULL,
        $j4_imageItem['created'] = $j3x_image->date;
        //`created_by` int(10) unsigned NOT NULL DEFAULT 0,
        $j4_imageItem['created_by'] = $j3x_image->userid;
        //`created_by_alias` varchar(255) NOT NULL DEFAULT '',
        //$j4_imageItem['created_by_alias'] = $j3x_image->created_by_alias;
        //`modified` datetime NOT NULL,
        $j4_imageItem['modified'] = $j3x_image->date;;
        //`modified_by` int(10) unsigned NOT NULL DEFAULT 0,
        $j4_imageItem['modified_by'] = $j3x_image->userid;;

        //`ordering` int(9) unsigned NOT NULL default '0',
        $j4_imageItem['ordering'] = $j3x_image->ordering;
        //`approved` tinyint(1) unsigned NOT NULL default '1',
        $j4_imageItem['approved'] = $j3x_image->approved;

        //`asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
        $j4_imageItem['asset_id'] = $j3x_image->asset_id;
        //`access` int(10) NOT NULL DEFAULT 0,
        $j4_imageItem['access'] = $j3x_image->access;
        return $j4_imageItem;
    }

    /**/
}