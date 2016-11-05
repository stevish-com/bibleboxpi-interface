<html><head></head><body>
<?php
$config = unserialize( file_get_contents ('config') );
if ( ! $config ) {
    if ( 'setup' == $_POST['action'] ) {

    } else {
        include( 'form-setup.php' );
    }

<?php } else { ?>
    Please enter your password.
    <form method="POST">
        <input type="password" name="password" />
        <input type="submit" value="Log In">
    </form>

<?php }
if ( ! isset ( $_POST['pass'] ) ) {

}
?>
</body>
</html>
