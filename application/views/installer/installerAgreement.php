<?php
/**
 * @package HelpDeskZ
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link http://helpdeskz.com
 */

include 'header.php';
?>
    <h4>Welcome to DeskIgniter v<?php echo DESKIGNITER_VERSION;?></h4>
    <p>Welcome to DeskIgniter installation wizard! This will be easy and fun. If you need help, take a look to the ReadMe documentation
        (readme.html)</p>
<?php echo form_open();?>
        <button class="btn btn-primary">Continue <i class="fa fa-arrow-circle-right"></i> </button>
<?php
echo form_close();
include 'footer.php';
