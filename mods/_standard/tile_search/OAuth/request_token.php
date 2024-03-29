<?php
/***********************************************************************/
/* ATutor															   */
/***********************************************************************/
/* Copyright (c) 2002-2010                                             */
/* Inclusive Design Institute	                                       */
/* http://atutor.ca													   */
/*																	   */
/* This program is free software. You can redistribute it and/or	   */
/* modify it under the terms of the GNU General Public License		   */
/* as published by the Free Software Foundation.					   */
/***********************************************************************/
// $Id: request_token.php 10055 2010-06-29 20:30:24Z cindy $

require_once('OAuth.php');
//require_once('../Shindig/ATutorOAuthDataStore.php');
require_once('mods/_standard/social/lib/Shindig/ATutorOAuthDataStore.php');

$oauthDataStore = new ATutorOAuthDataStore();
try {
  $server = new OAuthServer($oauthDataStore);
  $server->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());
  $server->add_signature_method(new OAuthSignatureMethod_PLAINTEXT());
  $request = OAuthRequest::from_request();
  var_dump($request);
  $token = $server->fetch_request_token($request);

  if ($token) {
	echo $token->to_string();
  }
} catch (OAuthException $e) {
  echo $e->getMessage();
} catch (Exception $e) {
  echo $e->getMessage();
}
?>
