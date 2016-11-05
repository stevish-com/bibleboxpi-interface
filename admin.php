<?php
//Get the config variable from the single config file
//Config should include SSID and general options
//Doesn't necessarily need to store homepage messages, as those will be in index.html
$admin = new Admin( isset($_POST['pass']) ? $_POST['pass'] : false );

// If the config var is empty, take them to the setup screen
// This means deleting config resets the password and everything




?>



<?php
class Admin {
    private $config = null;

    function __construct( $password ) {
        // Single = intentional
        if ( $configraw = file_get_contents('config') ) {
            $this->config = unserialize( $configraw );
        }
        //If the config file is missing, $thi->config will still be null. Show setup form or run setup
        if ( null === $this->config ) {
            if ( 'setup' == $_POST['action'] ) {
                //Do setup (create config variable, dump it to config file)
            } else {
                include( 'form-setup.php' );
                die();
            }
        } else {
            // There is a config file. So authenticate the user.
            $this->authenticate( $password );
        }
    }
    function authenticate($pass = false) {
        global $config;
        if ($pass) {
            if (password_hash($pass, PASSWORD_DEFAULT) == $config['hash']) {
                $config['token'] = $_SESSION['token'] = uniqid();
                $this->save_config();
                return true;
            } else {
                // They passed a password that's wrong. Send them to login-fail
                header('Location: http:/login-fail.html');
                die();
            }
        } else {
            session_start();
            // Use simple session variable to validate user? Do we need to worry about session hijacking and stuff?
            // We probably won't be working in an SSL environment (imagine getting to a real internet connection
            // often enough to update your SSL certs. Not ideal)
            if ($_SESSION['token'] == $config['token']) {
                return true;
            } else {
                // They're not trying to log in or anything. They're simply not logged in. Send them to login.
                header('Location: http:/login.html');
                die();
            }
        }

    }

    function save_config() {
        global $config;
        file_put_contents('config', serialize($config));
    }
}