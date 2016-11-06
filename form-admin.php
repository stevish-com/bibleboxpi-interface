<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<?php if ( $message ) {
	echo $message;
}
?>

<p>Manage your settings below:</p>
<form method="POST">
    <input type="hidden" name="action" value="admin" />
    <p><label for="pass1">New Password (optional):</label><br/>
        <input type="password" name="pass1" id="pass1" /></p>
    <p><label for="pass2">New Password again (optional):</label><br/>
        <input type="password" name="pass2" id="pass2" /></p>
	<p><label for="ssid">SSID (Name of the Wi-Fi Network.)</label><br/>
		<input type="text" name="ssid" id="ssid" value="<?php echo $admin->config['ssid']; ?>" /></p>
	<p><label for="channel">Wi-Fi Channel:</label><br/>
		<input type="text" name="channel" id="channel" value="<?php echo $admin->config['channel']; ?>" /></p>
	<p><label for="hostname">Host Name (The Domain users will use, like biblebox.lan):</label><br/>
		<input type="text" name="hostname" id="hostname" value="<?php echo $admin->config['hostname']; ?>" /></p>
	<p><label for="txpower">Tx Power</label><br/>
		<input type="text" name="txpower" id="txpower" value="<?php echo $admin->config['txpower']; ?>" /></p>
	<p><label for="system_hostname">System Hostname</label><br/>
		<input type="text" name="system_hostname" id="system_hostname" value="<?php echo $admin->config['system_hostname']; ?>" /></p>
    <input type="submit" value="Update" />
</form>
<!--<form method="POST">
	<input type="hidden" name="action" value="unmount" />
	<input type="submit" value="Unmount USB Drive" />
</form>-->
<form method="POST">
	<input type="hidden" name="action" value="logout" />
	<input type="submit" value="Log Out" />
</form>
</body>
</html>
