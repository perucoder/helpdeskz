<?php
/**
 * @package HelpDeskZ
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link http://helpdeskz.com
 */

/**
 * @var $this CI_Controller
 */
include 'header.php';
?>
    <h4>Welcome to DeskIgniter v<?php echo DESKIGNITER_VERSION;?></h4>
    <p>Hey! Please select the action that you want to do:</p>
    <a href="<?php echo site_url('install/install');?>" class="btn btn-primary"><i class="fa fa-archive"></i> Install a fresh copy</a>
    <a href="<?php echo site_url('install/upgrade');?>" class="btn btn-dark"><i class="fa fa-bolt"></i> Upgrade my Site</a>

<?php
include 'footer.php';

