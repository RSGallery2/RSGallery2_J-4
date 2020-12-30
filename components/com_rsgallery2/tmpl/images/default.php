<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_rsgallery2
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

// https://blog.kulturbanause.de/2014/09/responsive-images-srcset-sizes-adaptive/

// ToDo:
// ToDo:

HTMLHelper::_('stylesheet', 'com_rsgallery2/site/images.css', array('version' => 'auto', 'relative' => true));


echo '';
// on develop show open tasks if existing
if (!empty ($this->isDevelopSite))
{
    echo '<span style="color:red">'
        . 'Tasks: <br>'
        . '* extract image and modal slider into layouts to be called<br>'
        . '* make rsgConfig global<br>'
        //	. '* <br>'
        //	. '* <br>'
        //	. '* <br>'
        //	. '* <br>'
        //	. '* <br>'
        . '</span><br><br>';
}


?>
<div class="rsg2__form rsg2_gallery-form">
    <form id="rsg2_gallery__form" action="<?php echo Route::_('index.php'); ?>" method="post" class="form-validate form-horizontal well">

        <h1> RSGallery2 "images" view </h1>
        <h2>Image Gallery</h2>

        <hr>

        <!--        <div class="col-md-4">-->
        <!--            <div class="thumbnail">-->
        <!--                <a href="--><?php //echo $image->UrlOriginalFile ?><!--" target="_blank">-->
        <!--                    <img src="--><?php //echo $smallestSizeUrl ?><!--"-->
        <!--                         alt="--><?php //echo $smallestSizeUrl ?><!--"-->
        <!--                         srcSet="--><?php //echo $srcSet; ?><!--"-->
        <!--                         style="width:100%">-->
        <!--                    <div class="caption">-->
        <!--                        <p>--><?php //echo $image->name ?><!--</p>-->
        <!--                    </div>-->
        <!--                </a>-->
        <!--            </div>-->
        <!--        </div>-->

        <div id="rsg2_gallery" class="rsg2_gallery" >

            <div class="rsg2_gallery__images" id="gallery"  data-toggle="modal" data-target="#exampleModal">

                <?php
                foreach ($this->items as $idx => $image) {
                ?>
                    <figure>
                            <img src="<?php echo $image->UrlThumbFile ?>"
                                 alt="<?php echo $image->name ?>"
                                 class="img-thumbnail rsg2_gallery__images_image"
                                 data-target="#carouselExample"
                                 data-slide-to="<?php echo $idx ?>"
                            >
                            <figcaption><?php echo $image->name; ?></figcaption>
                    </figure>
                <?php
                }
                ?>
            </div>

            <!-- Modal markup: https://getbootstrap.com/docs/4.4/components/modal/ -->
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <!-- Carousel markup goes here -->

                            <div id="carouselExample" class="carousel slide" data-ride="carousel">

                                <div class="carousel-inner">

                                    <?php
                                    $isActive="active";
                                    foreach ($this->items as $image) {
                                    ?>

                                        <div class="carousel-item <?php echo $isActive ?>" >
											<div class="d-flex align-items-center justify-content-center min-vw-100  min-vh-100">
    <!--                                        <img class="d-block " src="--><?php //echo $image->UrlDisplayFiles[400] ?><!--"-->
                                                <img class="d-block " src="<?php echo $image->UrlOriginalFile ?>"
                                                     alt="<?php echo $image->name ?>"
                                                >
                                            </div>
                                        </div>

                                    <?php
                                        $isActive="";
                                    }
                                    ?>


                                    <a class="carousel-control-prev" href="#carouselExample" role="button" data-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                    <a class="carousel-control-next" href="#carouselExample" role="button" data-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Next</span>
                                    </a>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>




    </form>
</div>





