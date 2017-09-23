<?php
/**
 * @var $this CI_Model
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php isset($site_title) ? $site_title : $this->settings->get('windows_title');?></title>
    <?php
    echo html_css([
        'assets/bootstrap/css/bootstrap.min.css',
        'assets/bootstrap/css/bootstrap-grid.min.css',
        'assets/font-awesome/css/font-awesome.min.css',
        'assets/deskigniter/css/client.css'
    ])
    ?>
</head>
<body>
<div class="di-header">
    <div class="container">Hola mundo</div>
</div>
<nav class="navbar navbar-expand-lg navbar-default bg-default text-white">
    <div class="container">
        <a class="navbar-brand d-md-none" href="#">Navigation</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#"><i class="fa fa-home"></i> Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Link</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">Disabled</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div id="wrapper">
    <div id="header">
        <div id="logo">
            <a href="{{ settings.site_url }}"></a>
        </div>
    </div>
    <div id="navbar">
        <ul>
            <li><a href="{{ settings.site_url }}" {% if controller == 'home' or controller == 'login' %}class="current"{% endif %}>{{ LANG.HOME }}</a></li>
            {% if client_status == 1 %}
            <li><a href="{{ getUrl('view_tickets') }}" {% if controller == 'view_tickets' %}class="current"{% endif %}>{{ LANG.MY_TICKETS }}</a></li>
            {% endif %}
            <li><a href="{{ getUrl('submit_ticket') }}" {% if controller == 'submit_ticket' %}class="current"{% endif %}>{{ LANG.SUBMIT_A_TICKET }}</a></li>
            {% if settings.knowledgebase == 'yes' %}
            <li><a href="{{ getUrl('knowledgebase') }}" {% if controller == 'knowledgebase' %}class="current"{% endif %}>{{ LANG.KNOWLEDGEBASE }}</a></li>
            {% endif %}
            {% if settings.news == 'yes' %}
            <li><a href="{{ getUrl('news') }}" {% if controller == 'news' %}class="current"{% endif %}>{{ LANG.NEWS }}</a></li>
            {% endif %}
        </ul>
        <div id="language">
        	{% if settings.client_multilanguage %}
             <select name="language" onchange="location.href='{{settings.site_url}}/?lang='+this.value" class="language_selector">
            	{% for lang in client_languages %}
                <option value="{{ lang }}" {% if default_language == lang %}selected{% endif %}>{{ lang|capitalize }}</option>
                {% endfor %}
            </select>
            {% endif %}
        </div>
        <div class="clear"></div>
    </div>

    <div id="content">
            <div class="left_content">
            	<div class="memberbox">
                	{% if client_status != 1 %}

                <div class="mheader">{{ LANG.ACCOUNT_LOGIN }}</div>
                <div class="mcontent">
                    <form method="post" action="{{ getUrl('login') }}" autocomplete="off">
                        <input type="hidden" name="do" value="login" />
                        <input type="hidden" name="csrfhash" value="{{ getToken('login') }}" />
                        <div align="center" style="padding:16px;">
                            <input type="text" name="email" style="width:177px" placeholder="{{ LANG.YOUR_EMAIL_ADDRESS }}"  />
                        </div>
                        <div align="center" style="padding:0px 16px 16px 16px;">
                            <input type="password" name="password" style="width:177px" placeholder="{{ LANG.YOUR_PASSWORD }}" />
                        </div>
                        <div style="padding:0px 16px 16px 16px;">
                            <label><input type="checkbox" name="remember" value="1" /> {{ LANG.REMEMBER_ME }}</label>
                        </div>
                        <div style="padding:16px; border-top:1px solid #d8dbdf">
                            <table width="100%">
                                <tr>
                                    <td><a href="{{ getUrl('lost_password') }}">{{ LANG.LOST_PASSWORD }}</a></td>
                                    <td align="right"><input type="submit" name="btn" value="{{ LANG.LOGIN }}" /></td>
                                </tr>
                            </table>
                        </div>
                        {% if settings.googleoauth == 1 or settings.facebookoauth == 1 %}
                        <div align="center" style="padding:0px 16px 16px 16px;">
                            Or login with:
                            {% if settings.googleoauth == 1 %}
                            <a href="{{ getUrl() }}/googleOAuth" class="social_button"><span class="google"></span> Google</a>
                            {% endif %}
                            {% if settings.facebookoauth == 1 %}
                            <a href="{{ getUrl() }}/facebookOAuth" class="social_button"><span class="facebook"></span> Facebook</a>
                            {% endif %}
                        </div>
                        {% endif %}
                    </form>
                </div>
                {% else %}
                <div class="mheader">{{ LANG.ACCOUNT }}</div>
                <div class="mcontent">
                    <ul>
                        <li><a href="{{ getUrl('user_account','profile') }}">{{ LANG.MY_PROFILE }}</a></li>
                        <li><a href="{{ getUrl('user_account','preferences') }}">{{ LANG.PREFERENCES }}</a></li>
                        <li><a href="{{ getUrl('user_account','password') }}">{{ LANG.CHANGE_PASSWORD }}</a></li>
                        <li><a href="{{ getUrl('user_account','logout') }}">{{ LANG.LOGOUT }}</a></li>
                    </ul>
                </div>
                {% endif %}
    </div>
</div>
<div style="margin:0 10px 0 270px">
    {% block content %}{% endblock %}
