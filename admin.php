<?php
//Authenticate and load config first.
$admin = new Admin( isset($_POST['pass']) ? $_POST['pass'] : false );
if ( $admin->include ) {
	include ( $admin->include );
	die();
}
$message = '';

if ( isset( $_POST['action'] ) ) {
	switch ($_POST['action']) {
		case 'unmount':
			$admin->unmount();
			//And the config and everything are on the usb stick. No need to show them the admin form again.
			die("USB Stick unmounted.");
			break;
		case 'logout':
			$admin->logout();
			die("Logged Out.");
			break;
		case 'admin';
			if ($_POST['pass1'] && $_POST['pass1'] == $_POST['pass2']) {
				$admin->config['hash'] = password_hash($_POST['pass1'], PASSWORD_DEFAULT);
				$message .= "<div class='message'>Password Updated</div>";
			} elseif ($_POST['pass1'] || $_POST['pass2']) {
				$message .= "<div class='message'>Passwords didn't match. The password was not updated.</div>";
			}
			$message .= "<div class='message'>Configuration Updated</div>";
			$admin->update_config_from_post();
			$admin->save_config();
			include('form-admin.php');
			break;
	}
} else {
	include('form-admin.php');
}

class Admin {
    public $config = null;
	private $config_dir = "/var/www/html/biblebox/config/"; //always keep the trailing slash
	public $include = false;

    function __construct( $password ) {
        $this->get_config();

        //If the config file is missing, $this->config will still be null. Show setup form or run setup
        if ( ! $this->config['hash'] ) {
            if ( 'setup' == $_POST['action'] && $_POST['pass1'] && $_POST['pass1'] == $_POST['pass2'] ) {
                //Do setup (create config variable, dump it to config file)
				$this->config['hash'] = password_hash($_POST['pass1'], PASSWORD_DEFAULT );

				$this->update_config_from_post();
				$this->save_config();

                header("Location: http:/setup-complete.html");
                die();
            } else {
                //Todo: Add errors for no password or passwords don't match
                $this->include = 'form-setup.php';
            }
        } else {
            // There is a config file. So authenticate the user.
            $this->authenticate( $password );
        }
    }
    function authenticate($pass = false) {
		session_start();
        if ($pass) {
            if ( password_verify($pass, $this->config['hash']) ) {
                $this->config['token'] = $_SESSION['token'] = uniqid();
                $this->save_config();
                return true;
            } else {
                // They passed a password that's wrong. Send them to login-fail
                header('Location: http:/login-fail.html?entered=');
                die();
            }
        } else {
            // Use simple session variable to validate user? Do we need to worry about session hijacking and stuff?
            // We probably won't be working in an SSL environment (imagine getting to a real internet connection
            // often enough to update your SSL certs. Not ideal)
            if ( $this->config['token'] && $_SESSION['token'] == $this->config['token'] ) {
                return true;
            } else {
                // They're not trying to log in or anything. They're simply not logged in. Send them to login.
                header('Location: http:/login.html');
                die();
            }
        }

    }

	function update_config_from_post() {
		$skip_fields = array('action', 'pass1', 'pass2');
		foreach ( $_POST as $field => $value ) {
			if ( ! in_array( $field, $skip_fields ) ) {
				// Not a skipped field. Add it to the config variable
				$this->config[ $field ] = $value;
			}
		}

	}

    function save_config() {
		// No sanitizing necessary. It's just being written to a text file, so no security hole really.
		// We'll sanitize them when retrieving the config vars
        foreach( $this->config as $k => $v ) {
			$v = substr( $v, 0, 1000 ); // 1,000 character limit per variable, just to be sane.
			file_put_contents( $this->config_dir . $k . '.txt', $v );
		}
    }

    function get_config() {
        $dir = opendir( $this->config_dir );
		if ( $dir === false ) {
			die("Config dir not found");
		}
		$this->config = array();
		while ( ( $file = strtolower( readdir($dir) ) ) != false ) {
			if ( '.txt' == substr($file,-4) ) {
				$this->config[ substr($file, 0, -4) ] = file_get_contents( $this->config_dir . $file );
			}
		}
		
		//Todo: Sanitize config vars where necessary
    }

    function unmount() {
        exec('./unmount-usb.sh');
    }

	function logout() {
		$this->config['token'] = '';
		$this->save_config();
		unset($_SESSION['token']);
		session_destroy();
	}
}