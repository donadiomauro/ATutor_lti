<?php

	// POST
// [tile_course_id] => 7
// [cid] => 1
// [title] => UniBo lesson
	// [ordering] =>
	// [pid] => 0
	// [day] => 03
	// [month] => 06
	// [year] => 2012
	// [hour] => 12
	// [minute] => 26
	// [min] => 0
	// [alternatives] =>
	// [current_tab] => 0
	// [keywords] =>
	// [test_message] =>
	// [allow_test_export] => 0
	// [submit] => Import
	// [displayhead] => 0
	// [displaypaste] => 0
	// [complexeditor] => 0
	// [formatting] => 2
	// [head] => 7
	// [body_text] =>
	// [weblink_text] =>
// [url] => http://137.204.74.112/home/imscc/ims_export.php?to_a4a=1&course_id=7
	// [allow_a4a_import] => 1

	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
	ini_set("display_errors", 1);

	define('AT_INCLUDE_PATH', '../../../include/');
	require(AT_INCLUDE_PATH.'vitals.inc.php');

	require_once("ims-blti/blti_util.php");

/*
	echo '<pre>';
		print_r($GLOBALS);
	echo '</pre>';
	die();
*/

	new AContent_LiveContentLink();
/*
	$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
	header('Location: '. AT_BASE_HREF);
	exit();
*/
	die('<div>end - Mauro Donadio</div>');

?>

<?php

	class AContent_LiveContentLink{
		
		private $_AContent_URL	= '';
		private $_consumer_url	= '';

		//private $_LTI_Resource	= array('launch_URL'	=> 'http://localhost/AContent_local/oauth/tool.php',//'http://localhost/LTI_1.1/tool.php',		// endpoint
		//private $_LTI_Resource	= array('launch_URL'	=> 'http://137.204.74.112/oauth/tool.php',//'http://localhost/LTI_1.1/tool.php',		// endpoint
		private $_LTI_Resource	= array('launch_URL'	=> 'oauth/tool.php',//'http://localhost/LTI_1.1/tool.php',		// endpoint
										'key'			=> '12345',
										'secret'		=> 'secret');

		private $_Launch_Data	= array('resource_link_id'					=> '',		// 120988f929-274612
										'resource_link_title'				=> '',
										'resource_link_description'			=> '',
										//
										'user_id'							=> '',
										'roles'								=> '',
										'lis_person_name_full'				=> 'Jane Q. Public',
										'lis_person_name_family'			=> '',
										'lis_person_name_given'				=> '',
										'lis_person_contact_email_primary'	=> 'user@school.edu',
										'lis_person_sourcedid'				=> 'school.edu:user',
										// context = course
										'context_id'						=> '456434513',
										'context_title'						=> 'Design of Personal Environments',
										'context_label'						=> 'SI182',
										//
										'tool_consumer_instance_guid'		=> 'lmsng.school.edu',
										'tool_consumer_instance_desc'		=> 'University of School (LMSng)');

		public function __construct(){

			// OAuth 1.0
			
			if($this->_OAuth())
			{
				
				##
				## GET AND SET PARAMS
				##

				$this->_Launch_Data['resource_link_id']				= substr(uniqid('', true), 0, 16);
				// title
				$this->_Launch_Data['resource_link_title']			= htmlentities($_POST['title']);
				// description
				$this->_Launch_Data['resource_link_description']	= htmlentities($_POST['desc']);
				
				$userDetails	= $this->_getUserDetails();

				// user_id
				$this->_Launch_Data['user_id']					= $userDetails['user_id'];
				// roles
				// AT_course_enrollment
				// role
				$this->_Launch_Data['roles']					= $userDetails['user_role'];
				// fullname
				$this->_Launch_Data['lis_person_name_full']		= $userDetails['user_fullname'];

				/*
				lis_person_name_full
				lis_person_name_family
				lis_person_name_given
				lis_person_contact_email_primary
				lis_person_sourcedid
				*/

				/*
				context_id
				context_title
				context_label
				*/

				$this->_Launch_Data['tool_consumer_instance_guid']	= $this->_consumer_url;
				$this->_Launch_Data['tool_consumer_instance_desc']	= $GLOBALS['_config']['site_name'];
				
				##
				##
				##
/*
echo '<pre>';
	print_r($this->_Launch_Data);
echo '</pre>';

die();
*/

				// useless?
				//$cur_url = $this->curPageURL();
	
				$endpoint		= $this->_LTI_Resource['launch_URL'];
				

				$key			= $this->_LTI_Resource['key'];
				$secret			= $this->_LTI_Resource['secret'];
				
				$tool_consumer_instance_guid		= $this->_Launch_Data['tool_consumer_instance_guid'];
				$tool_consumer_instance_description	= $this->_Launch_Data['tool_consumer_instance_desc'];
	
				$parms			= signParameters($this->_Launch_Data, $endpoint, "POST", $key, $secret, "Press to Launch", $tool_consumer_instance_guid, $tool_consumer_instance_description);

				$content 		= postLaunchHTML($parms, $endpoint, true, "width=\"100%\" height=\"900\" scrolling=\"auto\" frameborder=\"1\" transparency");
				
				print($content);
			}
			else
			{
				die('Error: Missing OAuth auth!');
			}

		}

		
		private function _OAuth(){

			##
		    ## DEFINE MAIN VARIABLES
		    ##

			$this->_AContent_URL		= $GLOBALS['_config']['transformable_uri'];

			if($_SERVER['SERVER_NAME'] == 'localhost'){
				$path = explode(DIRECTORY_SEPARATOR, dirname($_SERVER['PHP_SELF']));
				$this->_consumer_url       = 'http://' . $_SERVER['SERVER_NAME'] . DIRECTORY_SEPARATOR . $path[1] . DIRECTORY_SEPARATOR;
			}else
				$this->_consumer_url       = 'http://' . $_SERVER['SERVER_NAME'] . DIRECTORY_SEPARATOR;

			define("ACONTENT_OAUTH_HOST",			$this->_AContent_URL);
			define("ACONTENT_REQUEST_TOKEN_URL",	ACONTENT_OAUTH_HOST . "oauth/request_token.php");
			define("ACONTENT_AUTHORIZE_URL",		ACONTENT_OAUTH_HOST . "oauth/authorization.php");
			define("ACONTENT_ACCESS_TOKEN_URL",		ACONTENT_OAUTH_HOST . "oauth/access_token.php");


		    ##
			## GET consumer_key and consumer_secret
		    ##

			// Register consumer
			$reg_consumer	= '';
			$reg_consumer	= file_get_contents(ACONTENT_OAUTH_HOST . '/oauth/register_consumer.php?consumer=' . $this->_consumer_url . '&expire=' . $GLOBALS['_config']['transformable_oauth_expire']);
			
			$reg_vars		= '';	
		    $reg_vars		= explode('&',$reg_consumer);
		
			for($i=0; $i<count($reg_vars); $i++){
				$tmp	= '';
				$tmp	= explode('=',$reg_vars[$i]);
		
				if($tmp[0] == 'consumer_key')
				{
					$config['key']		= '';
					$config['key']		= $tmp[1];
				}
				elseif($tmp[0] == 'consumer_secret')
				{
					$config['secret']	= '';
					$config['secret']	= $tmp[1];
				}
			}
			
			$config['request_token']	= ACONTENT_REQUEST_TOKEN_URL;


		    ##
			## OAuth 1a authentication
		    ##
		    
		    include 'Twitauth.class.php';

			$tw		= new Twitauth($config);
			$res	= $tw->getRequestToken();

			// return
			if($res == null)
				return false;
			else{

				// Sets the class private vars

				//$this->_LTI_Resource['launch_URL']	= $this->_AContent_URL . 'oauth/tool.php';
				$this->_LTI_Resource['launch_URL']	= $this->_AContent_URL . 'oauth/lti/tool.php';
				$this->_LTI_Resource['key']			= $config['key'];
				$this->_LTI_Resource['secret']		= $config['secret'];

				return true;
			}

		}

		private function _getUserDetails(){

			$userDetails	= array();
			
			$userDetails['user_id']			= $GLOBALS['_SESSION']['member_id'];

			if(get_instructor_status())
				$userDetails['user_role']	= 'Instructor';
			else
				$userDetails['user_role']	= 'Student';

			if($GLOBALS['_SESSION']['is_admin'])
				$userDetails['user_role']	= $userDetails['user_role'] . ' / Admin';

			$userDetails['user_fullname']	= get_display_name($GLOBALS['_SESSION']['member_id']);
			$userDetails['user_email']		= '';

			return $userDetails;
		}

		// page import
		private function _page_import(){
			echo 'Single page import of course = '.$_POST['tile_course_id'];

		}
	
		private function _REST_import(){

		}


	}
?>