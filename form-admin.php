<!DOCTYPE html>
<html lang="en">
<!-- Simple Login Page (No PHP yet!-->
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<p>Manage your settings below:</p>
<form method="POST">
    <input type="hidden" name="action" value="admin" />
    <p><label for="pass1">New Password (optional):</label><br/>
        <input type="password" name="pass1" id=pass1" /></p>
    <p><label for="pass2">New Password again (optional):</label><br/>
        <input type="password" name="pass2" /></p>
    <p><label for="ssid">SSID (Name of the Wi-Fi Network.)</label><br/>
        <input type="text" name="ssid" /></p>
    <input type="submit" value="Run Setup" />
</form>
</body>
</html>
