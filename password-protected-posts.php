<?php
namespace PasswordProtectedPosts;

/*
Plugin Name: Password Protected Posts
Description: Adds password protection to post archives and individual posts.
Version: 1.0
Author: Niku Hietanen
*/

function add_settings_page()
{
    add_options_page(
        'Password Protected Posts',
        'Password Protected Posts',
        'manage_options',
        'password-protected-posts',
        __NAMESPACE__ . '\render_settings_page'
    );
}
add_action('admin_menu', __NAMESPACE__ . '\add_settings_page');

function render_settings_page()
{
    ?>
    <div class="wrap">
        <h2>Password Protected Posts Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('ppp_options_group');
            do_settings_sections('password-protected-posts');
            wp_nonce_field('ppp_settings_nonce_action', 'ppp_settings_nonce');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function register_settings()
{
    register_setting('ppp_options_group', 'ppp_password');

    add_settings_section(
        'ppp_main_section',
        'Main Settings',
        null,
        'password-protected-posts'
    );

    add_settings_field(
        'ppp_password',
        'Protection Password',
        __NAMESPACE__ . '\render_settings_password_field',
        'password-protected-posts',
        'ppp_main_section'
    );
}
add_action('admin_init', __NAMESPACE__ . '\register_settings');


function render_settings_password_field()
{
    $password = get_option('ppp_password', '');
    echo "<input type='text' name='ppp_password' value='{$password}' />";
}

function render_password_field()
{
    $password = get_option('ppp_password', '');
    echo "<input type='password' name='ppp_password' value='{$password}' />";
}

function check_for_password()
{
    if (is_single() || is_archive()) {
        if (! isset($_COOKIE['ppp_auth']) || $_COOKIE['ppp_auth'] !== md5(get_option('ppp_password'))) {
            if (isset($_POST['ppp_password']) && $_POST['ppp_password'] === get_option('ppp_password')) {

                setcookie('ppp_auth', md5($_POST['ppp_password']), time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
                return;
            }
            render_password_form();
            exit();
        }
    }
}
add_action('template_redirect', __NAMESPACE__ . '\check_for_password');

function render_password_form()
{
    ?>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background-color: #f7f7f7;
        }

        .ppp-password-form {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px 30px;
            border-radius: 5px;
            background-color: #fff;
        }

        .ppp-password-form input[type="password"] {
            padding: 10px 15px;
            border-radius: 3px;
            border: 1px solid #ddd;
            margin-right: 10px;
            width: 200px;
        }

        .ppp-password-form input[type="submit"] {
            padding: 10px 15px;
            border-radius: 3px;
            border: none;
            background-color: #0073aa;
            color: #fff;
            cursor: pointer;
        }

        .ppp-password-form input[type="submit"]:hover {
            background-color: #005a87;
        }
    </style>
    <div class="ppp-password-form">
        <form method="post">
            <h3>You are trying to access password protected content</h3>
            <label>
                <p>Enter password to view this content: </p>
                <input type="password" name="ppp_password">
            </label>
            <input type="submit" value="Submit">
        </form>
    </div>
    <?php
}