<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2010                                      */
/* Inclusive Design Institute                                   */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/
// $Id: index.php 10142 2010-08-17 19:17:26Z hwong $

define('AT_INCLUDE_PATH', '../../../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');
require_once('classes/ResultParser.class.php');

//$default_results_per_page = 25;
$default_results_per_page = 20;

$browse =  intval($browse);

if (!isset($_REQUEST["results_per_page"])) $_REQUEST["results_per_page"] = $default_results_per_page;

if ($_REQUEST['submit'] || isset($_REQUEST['p']))
{
	// ***
	// ACC
	// Add some filter to the input

	// Convert all applicable characters to HTML entities
	$_REQUEST['keywords']	= htmlentities($_REQUEST['keywords']);
	// Remove all % chars to prevent unnecessary execution of the SQL query
	$_REQUEST['keywords']	= str_replace('%', '', $_REQUEST['keywords']);
	// Escapes special characters in a string for use in an SQL statement
	$_REQUEST['keywords']	= mysql_real_escape_string($_REQUEST['keywords']);
	

	$keywords = trim($_REQUEST['keywords']);

	//$title = trim($_REQUEST['title']);
	//$description = trim($_REQUEST['description']);
	//$author = trim($_REQUEST['author']);
	$results_per_page = intval(trim($_REQUEST['results_per_page']));

	if($keywords <> "")
	// || $title <> "" || $description <> "" || $author <> "" || $_REQUEST["creativeCommons"] == "true"
	{
		$page = intval($_REQUEST['p']);
		if (!$page) {
			$page = 1;
		}	
	
		if ($results_per_page > $default_results_per_page || $results_per_page == 0)
			$results_per_page = $default_results_per_page;
		
		$startRecNumber = $results_per_page*($page - 1);
		
		$page_str = "results_per_page=".$results_per_page;
		$url_search = "&maxResults=".$results_per_page."&start=".$results_per_page*($page - 1);
	
		if ($keywords <> "")
		{
			$page_str .= SEP."keywords=".urlencode($keywords);
			$url_search .= "&keywords=".urlencode($keywords);
		}
	//	if ($title <> "") 
	//	{
	//		$page_str .= SEP."title=".urlencode($title);
	//		$url_search .= "&title=".urlencode($title);
	//	}
	//	if ($description <> "") 
	//	{
	//		$page_str .= SEP. "description=".urlencode($description);
	//		$url_search .= "&description=".urlencode($description);
	//	}
	//	if ($author <> "") 
	//	{
	//		$page_str .= SEP. "author=".urlencode($author);
	//		$url_search .= "&author=".urlencode($author);
	//	}
	//	
	//	if (isset($_REQUEST["search_type"])) 
	//		$page_str .= SEP."search_type=".$_REQUEST["search_type"];
	//	
	//	if ($_REQUEST["search_type"] == 0) $url_search .= "&allKeyWords=true";
	//	if ($_REQUEST["search_type"] == 1) $url_search .= "&anyKeyWords=true";
	//	if ($_REQUEST["search_type"] == 2) $url_search .= "&exactPhraseKeyWords=true";
	//	if ($_REQUEST["creativeCommons"] == "true") 
	//	{
	//		$page_str .= SEP. "creativeCommons=true";
	//		$url_search .= "&creativeCommons=true";
	//	}
		
		$url = AT_TILE_SEARCH_URL."?id=".$_config['transformable_web_service_id'].$url_search;

		$xml_results = file_get_contents($url);
		
		if (!$xml_results)
		{
			$infos = array('CANNOT_CONNECT_SERVER', AT_TILE_SEARCH_URL);
			$msg->addError($infos);
		}
		else
		{
			$resultParser = new ResultParser();

			$resultParser->parse($xml_results);
			$result_list = $resultParser->getParsedArray();
			
			$savant->assign('result_list', $result_list);
			$savant->assign('startRecNumber', $startRecNumber+1);
			$savant->assign('results_per_page', $results_per_page);
			$savant->assign('page_str', $page_str);
			$savant->assign('instructor_role', 1);
		}
	}
}

global $_custom_css, $onload;
$_custom_css = $_base_path . 'mods/_standard/tile_search/module.css'; // use a custom stylesheet
$onload = "document.form.keywords.focus();";

require (AT_INCLUDE_PATH.'header.inc.php');

$savant->display('tile_search/index.tmpl.php');
//var_dump($savant->display('tile_search/index.tmpl.php'));
//die();
require(AT_INCLUDE_PATH.'footer.inc.php');
?>