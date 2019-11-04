<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_rsgallery2
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
use Joomla\CMS\Changelog\Changelog;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
/**/

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;


echo 'default.php: ' . realpath(dirname(__FILE__)) . '<br>';


?>

<form action="<?php echo Route::_('index.php?option=com_rsgallery2&view=galleries'); ?>"
      method="post" name="adminForm">
    <div class="row">
		<?php if (!empty($this->sidebar)) : ?>
            <div id="j-sidebar-container" class="col-md-2">
				<?php echo $this->sidebar; ?>
            </div>
		<?php endif; ?>
        <div class="<?php if (!empty($this->sidebar)) {echo 'col-md-10'; } else { echo 'col-md-12'; } ?>">
            <div id="j-main-container" class="j-main-container">



            </div>
        </div>
    </div>

	<?php echo HTMLHelper::_('form.token'); ?>
</form>

