<?php
/**
 * @author Tyler Menezes
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moodle multiauth
 *
 * Authentication Plugin: UReddit Authentication
 *
 * Authentication for UReddit users.
 *
 * Distributed under GPL (c)Markus Hagman 2004-2006
 *
 * 2008-06-11     UReddit Authentication functions v.0.1
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');

/**
 * UReddit authentication plugin.
 */
class auth_plugin_ureddit extends auth_plugin_base {

    /**
     * Constructor.
     */
    function auth_plugin_ureddit() {
        $this->authtype = 'ureddit';
        $this->config = get_config('auth/ureddit');
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password) {
    	// We'll do this a hackier way, because we don't need CURL support.
    	$postdata = http_build_query(
		    array(
		        'username' => $username,
		        'password' => $password
		    )
		);

        // Weird Moodle hack:
        $postdata = htmlspecialchars_decode($postdata);

		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => $postdata
		    )
		);
		$context  = stream_context_create($opts);

		$page = file_get_contents('http://ureddit.com/login', false, $context);

		// Okay, so if the UReddit servers redirected us to our user profile page, we logged in. If
		// we're still on the login page, we're logged out. We can't actually check if we're logged
		// in directly, because file_get_contents won't send the cookie.
		$result = strpos($page, "User:") !== false;


		return $result;
    }



    /**
     * Returns the user information for 'external' users. In this case the
     * attributes provided by UReddit
     *
     * @return array $result Associative array of user data
     */
    function get_userinfo($username) {
    	// Fill out some basic information we can get from the request directly.
    	$attr = array(	"username" => $username,
    					"institution" => "UReddit",
    					"firstname" => $username,
    					"lastname" => " ",
    					"email" => "$username@ureddit.com");

    	// See if we can get their Reddit profile:
    	$page = file_get_contents("http://ureddit.com/user/$username");
    	$redditLinkDelim = '<a href="http://www.reddit.com/message/compose/?to=';
    	$redditLinkPos = strpos($page, $redditLinkDelim);
    	if($redditLinkPos !== false){
    		$redditLinkPos += strlen($redditLinkDelim);

    		$redditUsername = substr($page, $redditLinkPos);
    		$redditUsername = substr($redditUsername, 0, strpos($redditUsername, '"'));

    		if($redditUsername != ""){
    			$attr["url"] = "http://www.reddit.com/user/$redditUsername";
    		}
    	}

    	return $attr;
    }

    function prevent_local_passwords() {
        return true;
    }

    function is_internal() {
        return false;
    }

    function can_change_password() {
        return true;
    }

    function change_password_url() {
        return "http://ureddit.com/settings";
    }

    function can_reset_password() {
        return true;
    }

    function reset_password_url(){
        return "http://ureddit.com/recover_password";
    }

    function can_signup() {
        return false;
    }
    
    function can_confirm() {
        return false;
    }
}