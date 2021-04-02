<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Rsgallery2\Component\Rsgallery2\Administrator\Model;

\defined('_JEXEC') or die;

use JModelLegacy;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Rsgallery2\Component\Rsgallery2\Administrator\Model\ImagePaths;

class UploadModel extends BaseDatabaseModel
{

    /**
     * Check if at least one gallery exists
     * Regards the nested structure (ID=1 is only root of tree and no gallery)
     *
     * @return true on galleries found
     *
     * @since __BUMP_VERSION__
     */
    public static function is1GalleryExisting()
    {
        $is1GalleryExisting = false;

        try
        {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            // count gallery items
            $query->select('COUNT(*)')
                // ignore root item  where id is "1"
                ->where($db->quoteName('id') . ' != 1')
                ->from('#__rsg2_galleries');

            $db->setQuery($query, 0, 1);
            $IdGallery          = $db->loadResult();

            // > 0 galleries exist
            $is1GalleryExisting = !empty ($IdGallery);
        }
        catch (\RuntimeException $e)
        {
            $OutTxt = '';
            $OutTxt .= 'Error count for galleries in "__rsg2_galleries" table' . '<br>';
            $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

            $app = Factory::getApplication();
            $app->enqueueMessage($OutTxt, 'error');
        }

        return $is1GalleryExisting;
    }

    /**
     * Query for ID of latest gallery
     *
     * @return string ID of latest gallery
     *
     * @since 4.3.0
     */
    public static function IdLatestGallery()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $test = $db->quoteName('created') . ', ' . $db->quoteName('id') . ' DESC' . "";

        $query->select($db->quoteName('id'))
            ->from('#__rsg2_galleries')
            ->where($db->quoteName('id') . ' != 1' )
            ->setLimit(1)
//			->order($db->quoteName('created') . ' DESC');
//			->order( $db->quoteName('id') . ' DESC')
            ->order($db->quoteName('created')  . ' DESC' . ', ' . $db->quoteName('id') . ' DESC')
        ;

        $db->setQuery($query, 0, 1);
        $IdLatestGallery = $db->loadResult();

        return $IdLatestGallery;
    }




}

