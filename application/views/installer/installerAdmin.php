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
    <h4 class="mb-3">Configuration</h4>
<?php
if(isset($error_msg)){
    echo '<div class="alert alert-danger">'.$error_msg.'</div>';
}
$dataForm = array(
    'agreement' => 1,
    'admin' => 1
);
echo form_open('',array(),$dataForm);
echo '<h5>Administration</h5>';
echo      html_input([
        'label' => 'Full Name',
        'name' => 'name',
        'value' => set_value('name'),
        'required' => 'required',
    ]).
    html_input([
        'label' => 'Email Address',
        'name' => 'email',
        'type' => 'email',
        'value' => set_value('email'),
        'required' => 'required',
    ]).
    html_input([
        'label' => 'Username',
        'name' => 'username',
        'value' => set_value('username'),
        'required' => 'required'
    ]).
    html_input([
        'label' => 'Password',
        'name' => 'password',
        'type' => 'password',
        'required' => 'required',
        'help' => 'Minimum password length is 5 characters'
    ]).
    '<h5>Settings</h5>'.
    html_input([
        'label' => 'Help Desk Name',
        'name' => 'site_name',
        'value' => (isset($_POST['site_name']) ? set_value('site_name') : 'DeskIgniter Support Center')
    ]).
    html_input([
        'label' => 'Help Desk URL',
        'name' => 'site_url',
        'value' => (isset($_POST['site_url']) ? set_value('site_url') : 'http://'.$this->input->server('HTTP_HOST'))
    ])
?>

    <div class="form-group">
        <button class="btn btn-primary">Continue <i class="fa fa-arrow-circle-right"></i> </button>
    </div>
<?php
echo form_close();
include 'footer.php';
