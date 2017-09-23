<?php
/**
 * @package DeskIgniter
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team - All rights reserved
 * @link http://deskigniter.com
 */
include 'header.php';
?>
<h4 class="mb-3">Installation Completed</h4>
<?php
if($this->session->has_userdata('installer'))
{
    echo '<p>Installation has been successfully completed, <strong>for security reasons, remove the file:</strong>:<br><code class="">'.APPPATH.'controllers/Install.php'.'</code></p>';
}else{
    echo '<p>Installation is locked, you already have DeskIgniter installed in your site.</p>';
}
?>
<p><a href="<?php echo site_url('staff');?>" target="_blank">Click here to open staff panel</a></p>
<?php
include 'footer.php';
