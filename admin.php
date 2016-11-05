<html><head></head><body>
<?php
//Get the config variable from the single config file
//Config should include SSID and general options
//Doesn't necessarily need to store homepage messages, as those will be in index.html
$config = unserialize( file_get_contents ('config') );
authenticate();

if ( ! $config ) {
    if ( 'setup' == $_POST['action'] ) {
        //Do setup (create config variable, dump it to config file)
    } else {
        include( 'form-setup.php' );
    }

} else {
    if ( ! isset ( $_POST['pass'] ) ) {
        header('Location: http://biblebox.com/login.html');
        die();
    } elseif ( bbpi_hash($_POST['pass']) != $config['hash'] ) {
        // TODO: Brute Force Limiter. Should be done in a way that can be reset manually by plugging the USB stick into a computer
        header('Location: http://biblebox.com/login-fail.html');
        die();
    } else {
        include( 'form-admin.php' );
    }

}

?>
</body>
</html>


<?php

function authenticate() {
    session_start();
    
}