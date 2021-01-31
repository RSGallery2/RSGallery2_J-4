<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2005 - 2021 RSGallery2 Team 
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Rsgallery2\Component\Rsgallery2\Administrator\View\Config;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\Component\Admin\Administrator\Model\SysinfoModel;
use Rsgallery2\Component\Rsgallery2\Administrator\Helper\rsgallery2Version;

/**
 * Sysinfo View class for the Admin component
 *
 * @since __BUMP_VERSION__
 */
class JsonView extends AbstractView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since __BUMP_VERSION__
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null): void
	{
		// Access check.
		if (!Factory::getApplication()->getIdentity()->authorise('core.admin'))
		{
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		header('MIME-Version: 1.0');
		header('Content-Disposition: attachment; filename="RSG2.config.' . date('c') . '.json"');
		header('Content-Transfer-Encoding: binary');

		$data = $this->getLayoutData();

		echo json_encode($data, JSON_PRETTY_PRINT);

		Factory::getApplication()->close();
	}

	/**
	 * Get the data for the view
	 *
	 * @return  array
	 *
	 * @since __BUMP_VERSION__
	 */
	protected function getLayoutData()//:  array
	{
//		/** @var SysinfoModel $model */
//		$model = $this->getModel();
//
//		return [
//			'info'        => $model->getSafeData('info'),
//			'phpSettings' => $model->getSafeData('phpSettings'),
//			'config'      => $model->getSafeData('config'),
//			'directories' => $model->getSafeData('directory', true),
//			'phpInfo'     => $model->getSafeData('phpInfoArray'),
//			'extensions'  => $model->getSafeData('extensions')
//		];

        // ToDO: RSG2 version !!!
        $oRsg2Version = new rsgallery2Version();
        $Rsg2Version = $oRsg2Version->getShortVersion(); // getLongVersion, getVersion


        $rsgConfig = ComponentHelper::getComponent('com_rsgallery2')->getParams();
        $withInfo = [
            'RSG2_configuration' => $rsgConfig,
            'RSG2_version' => $Rsg2Version,
            'time_created' => date('c')
            ];

        return $withInfo;
    }
}
