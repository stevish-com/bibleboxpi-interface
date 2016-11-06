<?php
//Authenticate and load config first.
$admin = new Admin( isset($_POST['pass']) ? $_POST['pass'] : false );
if ( $admin->not_set_up ) {
	include ( 'form-setup.php' );
	die();
}
$message = '';

if ( isset( $_POST['action'] ) ) {
	switch ($_POST['action']) {
		case 'unmount':
			if ( $result = $admin->unmount() ) {
				$message = "There was an error (#$result). The USB Stick was not dismounted. You'll need to turn off the BibleBox and then remove the USB stick";
				include('form-admin.php');
				die();
			}
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
	private $config_dirs = array( // use trailing slashes
		"/media/usb0/config/", // usb config
		"/media/usb0/Config/", // usb config
		"/etc/biblebox/" // default config
	);
	private $config_dir = null;
	public $not_set_up = true;
	public $error = false;

    function __construct( $password ) {
		foreach($this->config_dirs as $dir) {
			if ( is_dir($dir) ) {
				$this->config_dir = $dir;
				break;
			}
		}
		if ( ! $this->config_dir ) {
			die("Config dir not found!");
		}
        $this->get_config();

        //If the config file is missing, $this->config will still be null. Show setup form or run setup
        if ( ! $this->config['hash'] ) {
            if ( 'setup' == $_POST['action'] && $_POST['pass1'] && $_POST['pass1'] == $_POST['pass2'] ) {
                //Do setup (create config variable, dump it to config file)
				$this->config['hash'] = password_hash($_POST['pass1'], PASSWORD_DEFAULT );

				$this->update_config_from_post();
				$this->save_config();

                header("Location: setup-complete.html");
                die();
            } elseif ( 'setup' == $_POST['action'] && $_POST['pass1'] != $_POST['pass2'] ) {
                $this->error = "Passwords did not match";
            } elseif ( 'setup' == $_POST['action'] ) {
				$this->error = "You must set up a password";
			}
        } else {
			$this->not_set_up = false;
            // There is a config file. So authenticate the user.
            $this->authenticate( $password );
        }
    }
    function authenticate($pass = false) {
		session_start();
        if ($pass) {
			if ( $this->too_many_failures() ) {
				die("Too Many Password Attempts. Wait a bit, go back and try again.");
			}
            if ( password_verify($pass, $this->config['hash']) ) {
                $this->config['token'] = $_SESSION['token'] = uniqid();
                $this->save_config();
                return true;
            } else {
                // They passed a password that's wrong. Send them to login-fail
				$this->config['login-fails'] .= time() . "\n";
                header('Location: login-fail.html?entered=');
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
                header('Location: login.html');
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
        exec('sudo -u www-data pumount /media/usb0', $return_text, $result_int);
		return $result_int;
    }

	function logout() {
		$this->config['token'] = '';
		$this->save_config();
		unset($_SESSION['token']);
		session_destroy();
	}

	function too_many_failures() {
		$now = time();
		$fails = explode("\n", $this->config['login-fails']);
		$fails_day = $fails_hour = $fails_minute = $fails_5_seconds = 0;
		foreach( $fails as $k => $fail ) {
			if ( $fail < $now - 86400 ) {
				unset( $fails[$k] );
				continue;
			}
			$fails_day ++;
			if ( $fail > $now - 5 ) {
				$fails_5_seconds++;
			} elseif ( $fail > $now - 60 ) {
				$fails_minute++;
				$fails_5_seconds++;
			} elseif ( $fail > $now - 3600 ) {
				$fails_hour++;
				$fails_minute++;
				$fails_5_seconds++;
			}
		}
		$this->config['login-fails'] = implode( "\n", $fails );
		$this->save_config();
		if ( $fails_day > 100 || $fails_hour > 30 || $fails_minute > 5 || $fails_5_seconds > 0 ) {
			return true;
		}
		return false;
	}
}