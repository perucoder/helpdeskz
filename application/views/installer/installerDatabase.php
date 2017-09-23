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
    <h4>Database settings</h4>
<?php
if(isset($error_msg)){
    echo '<div class="alert alert-danger">'.$error_msg.'</div>';
}
$dataForm = array(
    'agreement' => 1,
    'admin' => 1,
    'database' => 1,
    'name' => $this->input->post('name'),
    'email' => $this->input->post('email'),
    'username' => $this->input->post('username'),
    'password' => $this->input->post('password'),
    'site_name' => $this->input->post('site_name'),
    'site_url' => $this->input->post('site_url')
);
echo form_open('',array(),$dataForm).
    html_input([
        'label' => 'Database Host',
        'name' => 'db_host',
        'value' => (isset($_POST['db_host']) ? set_value('db_host') : 'localhost'),
        'required' => 'required'
    ]).
    html_input([
        'label' => 'Database Name',
        'name' => 'db_name',
        'value' => set_value('db_name'),
        'required' => 'required'
    ]).
    html_input([
        'label' => 'Database Username',
        'name' => 'db_user',
        'value' => (isset($_POST['db_user']) ? set_value('db_user') : 'root'),
        'required' => 'required'
    ]).
    html_input([
        'label' => 'Database Password',
        'name' => 'db_password',
        'type' => 'password',
        'value' => set_value('db_password')
    ]).
    html_input([
        'label' => 'Table Prefix',
        'name' => 'db_prefix',
        'value' => (isset($_POST['db_prefix']) ? set_value('db_prefix') : 'di_'),
        'required' => 'required'
    ])
?>
    <div class="form-group">
        <button class="btn btn-primary">Continue <i class="fa fa-arrow-circle-right"></i> </button>
    </div>
<?php
echo form_close();
include 'footer.php';
