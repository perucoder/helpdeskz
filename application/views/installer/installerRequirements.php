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
    <h4>DeskIgniter Requirements</h4>
    <ul class="list-group mb-3">
        <?php
        foreach ($check as $k => $v){
            echo '<li class="list-group-item '.($v['check'] == 0 ? 'text-danger' : '').'">'.$v['text'].' '.($v['check'] == 1 ? '<i class="fa fa-check-circle"></i>' : '<i class="fa fa-times-circle"></i>').'</li>';
        }
        ?>
    </ul>


<?php
if($requirements === true){
    if($error_msg){
        echo '<div class="alert alert-danger">'.$error_msg.'</div>';
    }
    echo form_open('',array(),array('agreement' => 1));
    echo '<button class="btn btn-primary">Continue <i class="fa fa-arrow-circle-right"></i> </button>';
    echo form_close();
}

include 'footer.php';
