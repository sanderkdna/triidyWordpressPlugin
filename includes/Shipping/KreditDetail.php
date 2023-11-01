<?
	$user = wp_get_current_user();

	#print_r($user);

	$client_id = get_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID);
	$url =  TRIIDY_AUTOMATION_WEB_URL.'getlistaprestamos/'.$client_id.'/'.$user->user_login.'/';
	// echo $url;
	echo '<iframe id="inlineFrameExample" title="Detalle de Triidy_Automation.me" width="100%" height="800" src="'.$url.'"></iframe>';
?>