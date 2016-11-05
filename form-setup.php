<!DOCTYPE html>
<html lang="en">
<!-- Simple Login Page (No PHP yet!-->
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<p>This BibleBox has not been set up. Please Configure it now.</p>
<form method="POST">
    <input type="hidden" name="action" value="setup" /> 
    <p><label for="pass1">Password:</label><br/>
        <input type="password" name="pass1" id="pass1" /></p>
    <p><label for="pass2">Password again:</label><br/>
        <input type="password" name="pass2" id="pass2" /></p>
    <p><label for="ssid">SSID (Name of the Wi-Fi Network)</label><br/>
        <input type="text" name="ssid" id="ssid" /></p>
    <input type="submit" value="Run Setup" />
</form>
</body>
</html>