<!DOCTYPE html>
<html lang="en">
<!-- Simple Login Page (No PHP yet!-->
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<?php if ( $message ) {
	echo $message;
} ?>
<p>Manage your settings below:</p>
<form method="POST">
    <input type="hidden" name="action" value="admin" />
    <p><label for="pass1">New Password (optional):</label><br/>
        <input type="password" name="pass1" id="pass1" /></p>
    <p><label for="pass2">New Password again (optional):</label><br/>
        <input type="password" name="pass2" id="pass2" /></p>
    <p><label for="ssid">SSID (Name of the Wi-Fi Network.)</label><br/>
        <input type="text" name="ssid" id="ssid" value="<?php echo $admin->config['ssid']; ?>" /></p>
    <input type="submit" value="Run Setup" />
</form>
<form method="POST">
    <input type="hidden" name="action" value="unmount" />
    <input type="submit" value="Unmount USB Drive" />
</form>
</body>
</html>
