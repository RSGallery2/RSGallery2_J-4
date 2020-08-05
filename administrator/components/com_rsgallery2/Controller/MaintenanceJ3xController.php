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
     * applyExistingJ3xData
     * J3x Configuration-, galleries, images and more data will be adjusted to RSG2 J4x form
     *
     * @since 5.0.0
     */
    public function applyExistingJ3xData()
    {
        $msg = "MaintenanceJ3xController.applyExistingJ3xData: ";
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

                $isOk = $j3xModel->applyExistingJ3xData();

                if ($isOk) {
                    $msg .= "Successful copied J3x DB galleries, J3x DB images and J3x configuration items";
                } else {
                    $msg .= "Error at copyDbJ3xGalleries2J4x items";
                    $msgType = 'error';
                }

            } catch (\RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing applyExistingJ3xData: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        $link = 'index.php?option=com_rsgallery2&view=Maintenance';
        $this->setRedirect($link, $msg, $msgType);
    }

    /**
     * Copies all old configuration items to new configuration
     *
     * @since 5.0.0
     */
    public function copyJ3xConfig2J4xOptions()
    {
        $msg = "MaintenanceJ3xController.copyJ3xConfig2J4xOptions: ";
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

                $isOk = $j3xModel->collectAndCopyJ3xConfig2J4xOptions();

                if ($isOk) {
                    $msg .= "Successful applied J3x configuration items";
                } else {
                    $msg .= "Error at collectAndCopyJ3xConfig2J4xOptions items";
                    $msgType = 'error';
                }

            } catch (\RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing collectAndCopyJ3xConfig2J4xOptions: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        $link = 'index.php?option=com_rsgallery2&view=MaintenanceJ3x&layout=DbCopyJ3xConfig';
        $this->setRedirect($link, $msg, $msgType);
    }

    /**
     * Copies all old J3x gallery items to J4 galleries
     *
     * @since 5.0.0
     */
    public function copyDbJ3xGalleries2J4x()
    {
        $msg = "MaintenanceJ3xController.copyDbJ3xGalleries2J4x: ";
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

                $isOk = $j3xModel->copyDbAllJ3xGalleries2J4x();

                if ($isOk) {
                    $msg .= "Successful applied J3x gallery items";
                } else {
                    $msg .= "Error at copyDbJ3xGalleries2J4x items";
                    $msgType = 'error';
                }

            } catch (\RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing copyDbJ3xGalleries2J4x: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        $link = 'index.php?option=com_rsgallery2&view=MaintenanceJ3x&layout=DBTransferJ3xGalleries';
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
                $j3xModel = $this->getModel('MaintenanceJ3x');

                // Remove the items.
                $isOk = $j3xModel->resetImagesTable();
                if ($isOk) {
                    $msg .= Text::_('COM_RSGALLERY2_IMAGES_TABLE_RESET_SUCCESS');
                } else {
                    $msg .= Text::_('COM_RSGALLERY2_IMAGES_TABLE_RESET_ERROR') ;
                }

            } catch (\RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing resetImagesTable: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        $link = 'index.php?option=com_rsgallery2&view=MaintenanceJ3x&layout=DbTransferJ3xImages';
        $this->setRedirect($link, $msg, $msgType);

        return $isOk;
    }

    /**
     * Copies all old J3x image items to J4 images
     *
     * @since 5.0.0
     */
    public function copyDbJ3xImages2J4x()
    {
        $msg = "MaintenanceJ3xController.copyDbJ3xImages2J4x: ";
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

                $isOk = $j3xModel->copyDbAllJ3xImages2J4x();
                if ($isOk) {
                    $msg .= "Successful applied J3x image items";
                } else {
                    $msg .= "Error at copyDbJ3xImages2J4x items";
                    $msgType = 'error';
                }

            } catch (\RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing copyDbJ3xImages2J4x: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        //$link = 'index.php?option=com_rsgallery2&view=galleries';
        $link = 'index.php?option=com_rsgallery2&view=MaintenanceJ3x&layout=DbTransferJ3xImages';
        $this->setRedirect($link, $msg, $msgType);
    }

    /**
     * Copies all old J3x image to J4 images path
     *
     * @since 5.0.0
     */
    public function moveAllJ3xImages2J4x()
    {
        $msg = "MaintenanceJ3xController.moveAllJ3xImages2J4x: ";
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

                // Collect image Ids  (ToDo: collect ids by db query in $j3xModel)
                $j3x_images = $j3xModel->j3x_imagesMergeList();
                $j3x_imageIds = [];
                foreach ($j3x_images as $j3x_image) {

                    $j3x_imageIds [] = $j3x_image->id;
                }

                $isOk = $j3xModel->moveImagesJ3x2J4xById($j3x_imageIds);
                if ($isOk) {
                    $msg .= "Successful moved all J3x image files";
                    $msgType = 'success'; // ToDo: use in all controllers
                } else {
                    $msg .= "Error at moveAllJ3xImages2J4x images";
                    $msgType = 'error';
                }

            } catch (\RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing moveAllJ3xImages2J4x: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        //$link = 'index.php?option=com_rsgallery2&view=galleries';
        $link = 'index.php?option=com_rsgallery2&view=MaintenanceJ3x&layout=TransferJ3xImages';
        $this->setRedirect($link, $msg, $msgType);
    }

    /**
     * Copies selected old J3x image items to J4 images path
     *
     * @since 5.0.0
     */
    public function moveSelectedJ3xImages2J4x()
    {
        $msg = "MaintenanceJ3xController.moveSelectedJ3xImages2J4x: ";
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
                // Get items to remove from the request.
                $cids = $this->input->get('cid', array(), 'array');
                $extension = $this->input->getCmd('extension', null);

                if (!is_array($cids) || count($cids) < 1) {
                    //$this->app->enqueueMessage(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'warning');
                    $msg .= Text::_($this->text_prefix . '_NO_ITEM_SELECTED');
                    $msgType = 'warning';
                } else {

                    $j3xModel = $this->getModel('MaintenanceJ3x');

                    $isOk = $j3xModel->moveImagesJ3x2J4xById($cids);
                    if ($isOk) {
                        $msg .= "Successful moved J3x image files";
                        $msgType = 'success'; // ToDo: use in all controllers
                    } else {
                        $msg .= "Error at moveSelectedJ3xImages2J4x images";
                        $msgType = 'error';
                    }
                }

            } catch (\RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing moveSelectedJ3xImages2J4x: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        //$link = 'index.php?option=com_rsgallery2&view=galleries';
        $link = 'index.php?option=com_rsgallery2&view=MaintenanceJ3x&layout=TransferJ3xImages';
        $this->setRedirect($link, $msg, $msgType);
    }

    /**
     * Copies all old J3x image to J4 images path
     *
     * @since 5.0.0
     */
    public function updateMovedJ3xImages2J4x()
    {
        $msg = "MaintenanceJ3xController.updateMovedJ3xImages2J4x: ";
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

                // Collect image Ids (ToDo: collect ids by db query in $j3xModel)
                $j3x_images = $j3xModel->j3x_imagesMergeList();
                $j3x_imageIds = [];
                foreach ($j3x_images as $j3x_image) {

                    $j3x_imageIds [] = $j3x_image->id;
                }

                $isOk = $j3xModel->updateMovedJ3xImages2J4x($j3x_imageIds);
                if ($isOk) {
                    $msg .= "Successful updated database for J3x -> J4x image files";
                } else {
                    $msg .= "Error at updateMovedJ3xImages2J4x: Update database for J3x -> J4x image files ";
                    $msgType = 'error';
                }

            } catch (\RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing updateMovedJ3xImages2J4x: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }

        }

        //$link = 'index.php?option=com_rsgallery2&view=galleries';
        $link = 'index.php?option=com_rsgallery2&view=MaintenanceJ3x&layout=TransferJ3xImages';
        $this->setRedirect($link, $msg, $msgType);
    }


    /**
     * Extract configuration variables from RSG2 config file to reset to original values
     *
     * @throws \Exception
     *
     * @since version
     */
    public function CheckImagePathsJ3x()
    {
        $isOk = false;

        $msg = "MaintenanceCleanUp.CheckImagePaths: ";
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

                $MaintModel = $this->getModel('MaintenanceJ3x');

                $isPathsExisting = $MaintModel->CheckImagePaths();
                if ($isPathsExisting) {
                    // config saved message
                    $msg .= Text::_('All paths to images j3x exist', true);
                }
                else
                {
                    $msg .= "Missing pathes for images j3x found '";
                    $msgType = 'warning';
                }

            } catch (\RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing CheckImagePaths: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }
        }

        $link = 'index.php?option=com_rsgallery2&view=Maintenance';
        $this->setRedirect($link, $msg, $msgType);
    }


    /**
     * Extract configuration variables from RSG2 config file to reset to original values
     *
     * @throws \Exception
     *
     * @since version
     */
    public function RepairImagePathsJ3x()
    {
        $isOk = false;

        $msg = "MaintenanceCleanUp.RepairImagePaths: ";
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

                $MaintModel = $this->getModel('MaintenanceJ3x');
                $isSaved = $MaintModel->RepairImagePaths();

                if ($isSaved) {
                    // config saved message
                    $msg .= Text::_('Image paths are created', true);
                }
                else
                {
                    $msg .= "Error at repair image paths'";
                    $msgType = 'warning';
                }

            } catch (\RuntimeException $e) {
                $OutTxt = '';
                $OutTxt .= 'Error executing RepairImagePaths: "' . '<br>';
                $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                $app = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }
        }

        $link = 'index.php?option=com_rsgallery2&view=Maintenance';
        $this->setRedirect($link, $msg, $msgType);
    }



} // class

