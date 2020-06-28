<?php
/**
 * @package     RSGallery2
 * @subpackage  com_rsgallery2
 * @copyright   (C) 2016-2018 RSGallery2 Team
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @author      finnern
 * RSGallery is Free Software
 */

namespace Joomla\Component\Rsgallery2\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;


/**
 * global $Rsg2DebugActive;
 *
 * if ($Rsg2DebugActive)
 * {
 * // Include the JLog class.
 * //    jimport('joomla.log.log');
 *
 * // identify active file
 * JLog::add('==> ctrl.config.php ');
 * }
 * /**/
class MaintenanceJ3xController extends AdminController
{

    /**
     * Constructor.
     *
     * @param array $config An optional associative array of configuration settings.
     * Recognized key values include 'name', 'default_task', 'model_path', and
     * 'view_path' (this list is not meant to be comprehensive).
     * @param MVCFactoryInterface $factory The factory.
     * @param CMSApplication $app The JApplication for the dispatcher
     * @param \JInput $input Input
     *
     * @since   1.0
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

    }

    /**
     * Copies all old configuration items to new configuration
     *
     * @since 5.0.0
     */
    public function copyOldJ3xConfig2J4xOptions()
    {
        $msg = "MaintenanceJ3xController.copyOldJ3xConfig2J4xOptions: ";
        $msgType = 'notice';

        Session::checkToken();

        $canAdmin = Factory::getUser()->authorise('core.manage', 'com_rsgallery2');
        if (!$canAdmin) {
            //Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $msg .= Text::_('JERROR_ALERTNOAUTHOR');
            $msgType = 'warning';
            // replace newlines with html line breaks.
            str_replace('\n', '<br>', $msg);
        } else {
            try {
                $j3xModel = $this->getModel('MaintenanceJ3x');
                $configModel = $this->getModel('ConfigRaw');

                $j3xConfigItems = $j3xModel->j3xConfigItems();
                $rsgConfig = ComponentHelper::getComponent('com_rsgallery2')->getParams();
                $j4xConfigItems = $rsgConfig->toArray();

                // Configuration test lists: untouchedRsg2Config, untouchedJ3xConfig, 1:1 merged, assisted merges
                list(
                    $assistedJ3xItems,
                    $assistedJ4xItems,
                    $mergedItems,
                    $untouchedJ3xItems,
                    $untouchedJ4xItems
                    ) = $j3xModel->MergeJ3xConfigTestLists($j3xConfigItems, $j4xConfigItems );

                if (count($mergedItems)) {
                    // ToDo: write later
                    // J3x config state: 0:not upgraded, 1:upgraded,  -1:upgraded and deleted
                    // Smuggle the J3x config state "upgraded:1" into the list
                    //$oldConfigItems ['j3x_config_upgrade'] = "1";

                    $isOk = $configModel->copyJ3xConfigItems2J4xOptions(
                        $j4xConfigItems,
                        $assistedJ3xItems,
//                        $assistedJ4xItems,
                        $mergedItems);
                    if ($isOk) {
                        $msg .= "Successful copied old configuration items";
                    } else {
                        $msg .= "Error at copyOldJ3xConfig2J4xOptions items";
                        $msgType = 'error';
                    }
                } else {
                    $msg .= "No old configuration items";
                    $msgType = 'warning';
                }
            } catch (RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing copyOldJ3xConfig2J4xOptions: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        $link = 'index.php?option=com_rsgallery2&view=MaintenanceJ3x&layout=DbCopyOldJ3xConfig';
        $this->setRedirect($link, $msg, $msgType);
    }

    /**
     * Copies all old J3x gallery items to J4 galleries
     *
     * @since 5.0.0
     */
    public function copyOldJ3xGalleries2J4x()
    {
        $msg = "MaintenanceJ3xController.copyOldJ3xGalleries2J4x: ";
        $msgType = 'notice';

        Session::checkToken();

        $canAdmin = Factory::getUser()->authorise('core.manage', 'com_rsgallery2');
        if (!$canAdmin) {
            //Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $msg .= Text::_('JERROR_ALERTNOAUTHOR');
            $msgType = 'warning';
            // replace newlines with html line breaks.
            str_replace('\n', '<br>', $msg);
        } else {
            try {
                $maint3xModel = $this->getModel('MaintenanceJ3x');

                $isOk = $maint3xModel->copyAllOldJ3xGalleries2J4x();

                if ($isOk) {
                    $msg .= "Successful copied old gallery items items";
                } else {
                    $msg .= "Error at copyOldJ3xGalleries2J4x items";
                    $msgType = 'error';
                }

            } catch (RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing copyOldJ3xGalleries2J4x: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        $link = 'index.php?option=com_rsgallery2&view=MaintenanceJ3x&layout=DBTransferOldJ3xGalleries';
        $this->setRedirect($link, $msg, $msgType);
    }

    /**
     *
     * @return bool
     *
     * @since version
     */
    public function resetImagesTable()
    {
        $isOk = false;

        $msg = "ImagesController.resetImagesTable: ";
        $msgType = 'notice';

        Session::checkToken();

        $canAdmin = Factory::getUser()->authorise('core.manage', 'com_rsgallery2');
        if (!$canAdmin) {
            $msg .= Text::_('JERROR_ALERTNOAUTHOR');
            $msgType = 'warning';
            // replace newlines with html line breaks.
            str_replace('\n', '<br>', $msg);
        } else {

            try {
                // Get the model.
                /** @var \Joomla\Component\Rsgallery2\Administrator\Model\MaintenanceJ3xModel */
                $maint3xModel = $this->getModel('MaintenanceJ3x');

                // Remove the items.
                $isOk = $maint3xModel->resetImagesTable();
                if ($isOk) {
                    $msg .= Text::_('COM_RSGALLERY2_IMAGES_TABLE_RESET_SUCCESS');
                } else {
                    $msg .= Text::_('COM_RSGALLERY2_IMAGES_TABLE_RESET_ERROR') ;
                }

            } catch (RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing resetImagesTable: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        $link = 'index.php?option=com_rsgallery2&view=MaintenanceJ3x&layout=DbTransferOldJ3xImages';
        $this->setRedirect($link, $msg, $msgType);

        return $isOk;
    }

    /**
     * Copies all old J3x gallery items to J4 galleries
     *
     * @since 5.0.0
     */
    public function copyOldJ3xImages2J4x() // copyOldJ3xImages2J4x
    {
        $msg = "MaintenanceJ3xController.copyOldJ3xImages2J4x: ";
        $msgType = 'notice';

        Session::checkToken();

        $canAdmin = Factory::getUser()->authorise('core.manage', 'com_rsgallery2');
        if (!$canAdmin) {
            //Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $msg .= Text::_('JERROR_ALERTNOAUTHOR');
            $msgType = 'warning';
            // replace newlines with html line breaks.
            str_replace('\n', '<br>', $msg);
        } else {
            try {
                $maint3xModel = $this->getModel('MaintenanceJ3x');

                $isOk = $maint3xModel->copyAllOldJ3xImages2J4x();
                if ($isOk) {
                    $msg .= "Successful copied old gallery items items";
                } else {
                    $msg .= "Error at copyOldJ3xImages2J4x items";
                    $msgType = 'error';
                }

            } catch (RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing copyOldJ3xImages2J4x: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        //$link = 'index.php?option=com_rsgallery2&view=galleries';
        $link = 'index.php?option=com_rsgallery2&view=MaintenanceJ3x&layout=DbTransferOldJ3xImages';
        $this->setRedirect($link, $msg, $msgType);
    }


} // class

