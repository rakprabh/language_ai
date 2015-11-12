<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookOtherException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphSessionInfo;
	

class Facebook{
		
	public function __construct()
    	{   	
    		$this->autoload();
   	}
	
	public function getUserInfo(){


		$permissions = array(
			'email',        
			'public_profile',
		); 


		FacebookSession::setDefaultApplication(FB_APP_ID,FB_APP_SECRET);
		$helper = new FacebookRedirectLoginHelper("http://" . APP_ROOT . "/");
		
		// see if a existing session exists
		if ( isset( $_SESSION ) && isset( $_SESSION['fb_token'] ) ) {
		  // create new session from saved access_token
		  $session = new FacebookSession( $_SESSION['fb_token'] );
		
		  // validate the access_token to make sure it's still valid
		  try {
		    if ( !$session->validate() ) {
		      $session = null;
		    }
		  } catch ( Exception $e ) {
		    // catch any exceptions
		    $session = null;
		  }
		
		} else {
		  // no session exists
		
		  try {
		    $session = $helper->getSessionFromRedirect();
		  } catch( FacebookRequestException $ex ) {
		    // When Facebook returns an error
		  } catch( Exception $ex ) {
		    // When validation fails or other local issues
		    return $helper->getLoginUrl($permissions);
		  }
		
		}
		
		// see if we have a session
		if ( isset( $session ) ) {
		
		  // save the session
		  $_SESSION['fb_token'] = $session->getToken();
		  // create a session using saved token or the new one we generated at login

		  $session = new FacebookSession( $session->getToken() );
	  
	  
	 	 // validate the access_token to make sure it's still valid
		  try {
		    if ( !$session->validate() ) {
		      $session = null;
		    }
		  } catch ( Exception $e ) {
		    // catch any exceptions
		    $session = null;
		  }
		  
		  
		  $appSession = FacebookSession::newAppSession();
		  // graph api request for user data
		  
		  //$sessionInfo = $session->getSessionInfo()->asArray();
			
		  
		  $response = (new FacebookRequest($session, 'GET', '/me?fields=id,name,email'))->execute();
		  // get response
		  $graphObject = $response->getGraphObject()->asArray();
		  $graphObject["logoutUrl"]  = $helper->getLogoutUrl( $session, 'http://' . APP_ROOT .  '/index.php/welcome/logout' );
		  return $graphObject;
		
		} else {
		  return $helper->getLoginUrl($permissions);
		}
  }
	
	private function autoload(){
				
		if (version_compare(PHP_VERSION, '5.4.0', '<')) {
		  throw new Exception('The Facebook SDK v4 requires PHP version 5.4 or higher.');
		}
		
		/**
		 * Register the autoloader for the Facebook SDK classes.
		 * Based off the official PSR-4 autoloader example found here:
		 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
		 *
		 * @param string $class The fully-qualified class name.
		 * @return void
		 */
		spl_autoload_register(function ($class)
		{
		  // project-specific namespace prefix
		  $prefix = '';
		
		  $class = str_replace("\\","/", $class);
		  // base directory for the namespace prefix
		  //$base_dir =  __DIR__ . '\\Facebook\\';
		   $tmp  = explode("/",$class);
		   $finaltmp = sizeof($tmp) -1 ;
		
		   for($i =0;$i<$finaltmp;$i++){
		  		$prefix .= $tmp[$i] . "/";
		   }
		  
		  // does the class use the namespace prefix?
		  $len = strlen($prefix);
 
		  $base_dir =  __DIR__  . "/". $prefix;
		  
  
		  if (strncmp($prefix, $class, $len) !== 0) {
		    return;
		  }
		
		  // get the relative class name
		  $relative_class = substr($class, $len);

		  $file = $base_dir  . stripslashes ( $relative_class )  . '.php';

		  // if the file exists, require it
		  if (file_exists($file)) {
		    require $file;
		  }
		});
	}
}