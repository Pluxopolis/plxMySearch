<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(!empty($_POST)) {
	$plxPlugin->setParam('frmDisplay', $_POST['frmDisplay'], 'numeric');
	$plxPlugin->setParam('frmLibButton', $_POST['frmLibButton'], 'string');
	$plxPlugin->setParam('mnuDisplay', $_POST['mnuDisplay'], 'numeric');
	$plxPlugin->setParam('mnuName', $_POST['mnuName'], 'string');
	$plxPlugin->setParam('mnuPos', $_POST['mnuPos'], 'numeric');
	$plxPlugin->setParam('template', $_POST['template'], 'string');
	$plxPlugin->setParam('url', plxUtils::title2url($_POST['url']), 'string');
	$plxPlugin->setParam('savesearch', $_POST['savesearch'], 'numeric');
	$plxPlugin->saveParams();
	header('Location: parametres_plugin.php?p=plxMySearch');
	exit;
}

$frmDisplay =  $plxPlugin->getParam('frmDisplay')=='' ? 1 : $plxPlugin->getParam('frmDisplay');
$frmLibButton =  $plxPlugin->getParam('frmLibButton')=='' ? $plxPlugin->getLang('L_FORM_BUTTON') : $plxPlugin->getParam('frmLibButton');
$mnuDisplay =  $plxPlugin->getParam('mnuDisplay')=='' ? 1 : $plxPlugin->getParam('mnuDisplay');
$mnuName =  $plxPlugin->getParam('mnuName')=='' ? $plxPlugin->getLang('L_DEFAULT_MENU_NAME') : $plxPlugin->getParam('mnuName');
$mnuPos =  $plxPlugin->getParam('mnuPos')=='' ? 2 : $plxPlugin->getParam('mnuPos');
$template = $plxPlugin->getParam('template')=='' ? 'static.php' : $plxPlugin->getParam('template');
$url = $plxPlugin->getParam('url')=='' ? 'search' : $plxPlugin->getParam('url');
$savesearch =  $plxPlugin->getParam('savesearch')=='' ? 0 : $plxPlugin->getParam('savesearch');

# On récupère les templates des pages statiques
$files = plxGlob::getInstance(PLX_ROOT.'themes/'.$plxAdmin->aConf['style']);
if ($array = $files->query('/^static(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}

?>

<h2><?php echo $plxPlugin->getInfo('title') ?></h2>

<form id="form_plxMySearch" action="parametres_plugin.php?p=plxMySearch" method="post">
	<fieldset>
		<p class="field"><label for="id_frmDisplay"><?php echo $plxPlugin->lang('L_FORM_DISPLAY') ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('frmDisplay',array('1'=>L_YES,'0'=>L_NO),$frmDisplay); ?>
		<p class="field"><label for="id_mnuDisplay"><?php echo $plxPlugin->lang('L_MENU_DISPLAY') ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('mnuDisplay',array('1'=>L_YES,'0'=>L_NO),$mnuDisplay); ?>
		<p class="field"><label for="id_mnuName"><?php $plxPlugin->lang('L_MENU_TITLE') ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('mnuName',$mnuName,'text','20-20') ?>
		<p class="field"><label for="id_mnuPos"><?php $plxPlugin->lang('L_MENU_POS') ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('mnuPos',$mnuPos,'text','2-5') ?>
		<p class="field"><label for="id_frmLibButton"><?php $plxPlugin->lang('L_MENU_LIB_BUTTON') ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('frmLibButton',$frmLibButton,'text','20-20') ?>
		<p class="field"><label for="id_template"><?php $plxPlugin->lang('L_TEMPLATE') ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('template', $aTemplates, $template) ?>
		<p class="field"><label for="id_url"><?php $plxPlugin->lang('L_PARAM_URL') ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('url',$url,'text','20-20') ?>
		<p class="field"><label for="id_savesearch"><?php echo $plxPlugin->lang('L_SAVE_SEARCH') ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('savesearch',array('1'=>L_YES,'0'=>L_NO),$savesearch); ?>
		<p>
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>" />
		</p>
	</fieldset>
</form>

<br />
<?php $plxPlugin->lang('L_HELP') ?> :
<pre style="color:#000;font-size:12px; background:#fff; padding: 10px 20px 20px 20px; border:1px solid #efefef">
<?php
echo (htmlspecialchars("
<?php eval(\$plxShow->callHook('MySearchForm')) ?>
"));
?>
</pre>