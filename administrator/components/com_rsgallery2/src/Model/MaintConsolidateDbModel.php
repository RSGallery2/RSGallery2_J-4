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
use Rsgallery2\Component\Rsgallery2\Administrator\Helpers\ImageReferences;


class MaintConsolidateDbModel extends BaseDatabaseModel
{

	/**
     * Image artefacts as list
     * Each entry contains existing image objects (? where at least one is missing ?)
     *
	 * @var ImageReferences
     *
     * @since 4.3.0
     */
	protected $ImageReferences;

    /**
     * Returns List of image "artefacts"
     *
     * @return ImageReferences
     *
     * @since 4.3.0
     */
	public function GetImageReferences()
	{
		if (empty($this->ImageReferences))
		{
			$this->CreateDisplayImageData();
		}

		return $this->ImageReferences;
	}

	/**
	 * Collects image artefacts as list
     * Each entry contains existing image objects where at least one is missing
	 *
	 * @return string operation messages
     *
     * @since 4.3.0
     */
	public function CreateDisplayImageData()
	{
		// ToDo: Instead of message return HasError;
		$msg = ''; //  ": " . '<br>';

		// Include watermarked files to search and check for missing
		//$ImageReferences->UseWatermarked = $this->IsWatermarkActive();
		// $ImageReferences->UseWatermarked = true; // ToDO: remove
		//$ImageReferences       = new ImageReferences ($this->IsWatermarkActive());
		$ImageReferences       = new ImageReferences (1);
		$this->ImageReferences = $ImageReferences;

		try
		{
			$msg .= $ImageReferences->CollectImageReferences();
		}
		catch (RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing CollectImageReferences: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = JFactory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $msg;
	}

	/**
	 * Tells if watermark is activated on user config
	 *
	 * @return bool true when set in config data
     *
     * @since 4.3.0
     */
	/** read config more direct global$rsgconfig ...
	public function IsWatermarkActive()
	{
		if (empty($this->IsWatermarkActive))
		{
			$this->IsWatermarkActive = false;

			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select($db->quoteName('value'))
					->from($db->quoteName('#__rsgallery2_config'))
					->where($db->quoteName('name') . " = " . $db->quote('watermark'));
				$db->setQuery($query);

				$this->IsWatermarkActive = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$OutTxt = '';
				$OutTxt .= 'Error executing query: "' . $query . '"' . '<br>';
				$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

				$app = JFactory::getApplication();
				$app->enqueueMessage($OutTxt, 'error');

			}
		}

		return $this->IsWatermarkActive;
	}
	/**/





}

