<?
// SMARTY FUNCTIONS
require_once('../smarty/libs/Smarty.class.php');
$smy =new_Smarty('../www/templates/lg','../smarty');
assign('html_title','Welcome');

function new_Smarty($style_dir, $global_smarty_dir='.') {
	$ref = new Smarty();
	$ref->setTemplateDir($style_dir.'/layout/');
	$ref->setCompileDir($global_smarty_dir.'/templates_c/');
	$ref->setConfigDir($global_smarty_dir.'/configs/');
	$ref->setCacheDir($global_smarty_dir.'/cache/');
	$ref->setPluginsDir(array_merge(array($global_smarty_dir.'/plugins/'), $ref->getPluginsDir()));
	$ref->use_sub_dirs = true;
	return $ref;
}

function display_template($tpl='') {
	global $smy;
	if ($tpl)
		$smy->assign('body_template',$tpl);
	$smy->display('body.html');
}

function assign($var,$value) {
	global $smy;
	$smy->assign($var,$value);
}

function fetch_template($smyptr, $tpl) {
	return $smyptr->fetch($tpl);
}

function assign_fetch($smyptr,$var,$value) {
	$smyptr->assign($var,$value);
}
?>
