<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(defined('PLX_MYMULTILINGUE')) {
	$array =  explode(',', PLX_MYMULTILINGUE);
	$aLangs = array_intersect($array, array('fr', 'en'));
} else {
	$aLangs = array($plxPlugin->default_lang);
}

if(!empty($_POST)) {
	$plxPlugin->setParam('frmDisplay', $_POST['frmDisplay'], 'numeric');
	$plxPlugin->setParam('mnuDisplay', $_POST['mnuDisplay'], 'numeric');
	$plxPlugin->setParam('mnuPos', $_POST['mnuPos'], 'numeric');
	$plxPlugin->setParam('template', $_POST['template'], 'string');
	$plxPlugin->setParam('url', plxUtils::title2url($_POST['url']), 'string');
	$plxPlugin->setParam('sTitle', $_POST['sTitle'], 'numeric');
	$plxPlugin->setParam('sChapo', $_POST['sChapo'], 'numeric');
	$plxPlugin->setParam('sContent',$_POST['sContent'], 'numeric');
	$plxPlugin->setParam('sTags', $_POST['sTags'], 'numeric');
	$plxPlugin->setParam('savesearch', $_POST['savesearch'], 'numeric');
	foreach($aLangs as $lang) {
		$plxPlugin->setParam('mnuName_'.$lang, $_POST['mnuName_'.$lang], 'string');
		$plxPlugin->setParam('placeholder_'.$lang, $_POST['placeholder_'.$lang], 'string');
		$plxPlugin->setParam('frmLibButton_'.$lang, $_POST['frmLibButton_'.$lang], 'string');
		$plxPlugin->setParam('checkboxes_'.$lang, $_POST['checkboxes_'.$lang], 'string');
	}
	$plxPlugin->saveParams();
	header('Location: parametres_plugin.php?p=plxMySearch');
	exit;
}

$var = array();
# initialisation des variables propres à chaque lanque
$langs = array();
foreach($aLangs as $lang) {
	# chargement de chaque fichier de langue
	$langs[$lang] = $plxPlugin->loadLang(PLX_PLUGINS.'plxMySearch/lang/'.$lang.'.php');
	$var[$lang]['mnuName'] =  $plxPlugin->getParam('mnuName_'.$lang)=='' ? $plxPlugin->getLang('L_DEFAULT_MENU_NAME') : $plxPlugin->getParam('mnuName_'.$lang);
	$var[$lang]['placeholder'] = $plxPlugin->getParam('placeholder_'.$lang)=='' ? '' : $plxPlugin->getParam('placeholder_'.$lang);
	$var[$lang]['frmLibButton'] =  $plxPlugin->getParam('frmLibButton_'.$lang)=='' ? $plxPlugin->getLang('L_FORM_BUTTON') : $plxPlugin->getParam('frmLibButton_'.$lang);
	$var[$lang]['checkboxes'] = $plxPlugin->getParam('checkboxes_'.$lang)=='' ? '' : $plxPlugin->getParam('checkboxes_'.$lang);
}
# initialisation des variables communes à chaque langue
$var['frmDisplay'] =  $plxPlugin->getParam('frmDisplay')=='' ? 1 : $plxPlugin->getParam('frmDisplay');
$var['mnuDisplay'] =  $plxPlugin->getParam('mnuDisplay')=='' ? 1 : $plxPlugin->getParam('mnuDisplay');
$var['mnuPos'] =  $plxPlugin->getParam('mnuPos')=='' ? 2 : $plxPlugin->getParam('mnuPos');
$var['template'] = $plxPlugin->getParam('template')=='' ? 'static.php' : $plxPlugin->getParam('template');
$var['url'] = $plxPlugin->getParam('url')=='' ? 'search' : $plxPlugin->getParam('url');
$var['savesearch'] =  $plxPlugin->getParam('savesearch')=='' ? 0 : $plxPlugin->getParam('savesearch');
$var['sTitle'] =  $plxPlugin->getParam('sTitle')=='' ? 1 : $plxPlugin->getParam('sTitle');
$var['sChapo'] =  $plxPlugin->getParam('sChapo')=='' ? 1 : $plxPlugin->getParam('sChapo');
$var['sContent'] =  $plxPlugin->getParam('sContent')=='' ? 1 : $plxPlugin->getParam('sContent');
$var['sTags'] =  $plxPlugin->getParam('sTags')=='' ? 1 : $plxPlugin->getParam('sTags');

# On récupère les templates des pages statiques
$files = plxGlob::getInstance(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$plxAdmin->aConf['style']);
if ($array = $files->query('/^static(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}
?>
<style>
form.inline-form label {
	width: 300px !important;
}
</style>
<div id="tabContainer">
<form class="inline-form" id="form_plxMySearch" action="parametres_plugin.php?p=plxMySearch" method="post">
	<div class="tabs">
		<ul>
			<li id="tabHeader_main"><?php $plxPlugin->lang('L_MAIN') ?></li>
			<?php
			foreach($aLangs as $lang) {
				echo '<li id="tabHeader_'.$lang.'">'.strtoupper($lang).'</li>';
			}
			?>
		</ul>
	</div>
	<div class="tabscontent">
		<div class="tabpage" id="tabpage_main">
			<fieldset>
				<p>
					<label for="id_url"><?php $plxPlugin->lang('L_PARAM_URL') ?>&nbsp;:</label>
					<?php plxUtils::printInput('url',$var['url'],'text','20-20') ?>
				</p>
				<p>
					<label for="id_mnuDisplay"><?php echo $plxPlugin->lang('L_MENU_DISPLAY') ?>&nbsp;:</label>
					<?php plxUtils::printSelect('mnuDisplay',array('1'=>L_YES,'0'=>L_NO),$var['mnuDisplay']); ?>
				</p>
				<p>
					<label for="id_mnuPos"><?php $plxPlugin->lang('L_MENU_POS') ?>&nbsp;:</label>
					<?php plxUtils::printInput('mnuPos',$var['mnuPos'],'text','2-5') ?>
				</p>
				<p>
					<label for="id_frmDisplay"><?php echo $plxPlugin->lang('L_FORM_DISPLAY') ?>&nbsp;:</label>
					<?php plxUtils::printSelect('frmDisplay',array('1'=>L_YES,'0'=>L_NO),$var['frmDisplay']); ?>
				</p>
				<p>
					<label for="id_savesearch"><?php echo $plxPlugin->lang('L_SAVE_SEARCH') ?>&nbsp;:</label>
					<?php plxUtils::printSelect('savesearch',array('1'=>L_YES,'0'=>L_NO),$var['savesearch']); ?>
				</p>
				<p>
					<label for="id_sTitle"><?php echo $plxPlugin->lang('L_SEARCH_TITLE') ?>&nbsp;:</label>
					<?php plxUtils::printSelect('sTitle',array('1'=>L_YES,'0'=>L_NO),$var['sTitle']); ?>
				</p>
				<p>
					<label for="id_sChapo"><?php echo $plxPlugin->lang('L_SEARCH_CHAPO') ?>&nbsp;:</label>
					<?php plxUtils::printSelect('sChapo',array('1'=>L_YES,'0'=>L_NO),$var['sChapo']); ?>
				</p>
				<p>
					<label for="id_sContent"><?php echo $plxPlugin->lang('L_SEARCH_CONTENT') ?>&nbsp;:</label>
					<?php plxUtils::printSelect('sContent',array('1'=>L_YES,'0'=>L_NO),$var['sContent']); ?>
				</p>
				<p>
					<label for="id_sTags"><?php echo $plxPlugin->lang('L_SEARCH_TAGS') ?>&nbsp;:</label>
					<?php plxUtils::printSelect('sTags',array('1'=>L_YES,'0'=>L_NO),$var['sTags']); ?>
				</p>
				<p>
					<label for="id_template"><?php $plxPlugin->lang('L_TEMPLATE') ?>&nbsp;:</label>
					<?php plxUtils::printSelect('template', $aTemplates, $var['template']) ?>
				</p>
			</fieldset>
		</div>
		<?php foreach($aLangs as $lang) : ?>
		<div class="tabpage" id="tabpage_<?php echo $lang ?>">
			<fieldset>
				<p>
					<label for="id_mnuName_<?php echo $lang ?>"><?php $plxPlugin->lang('L_MENU_TITLE') ?>&nbsp;:</label>
					<?php plxUtils::printInput('mnuName_'.$lang,$var[$lang]['mnuName'],'text','20-20') ?>
				</p>
				<p>
					<label for="id_placeholder_<?php echo $lang ?>"><?php $plxPlugin->lang('L_PLACEHOLDER') ?>&nbsp;:</label>
					<?php plxUtils::printInput('placeholder_'.$lang,$var[$lang]['placeholder'],'text','20-20') ?>
				</p>
				<p>
					<label for="id_frmLibButton_<?php echo $lang ?>"><?php $plxPlugin->lang('L_MENU_LIB_BUTTON') ?>&nbsp;:</label>
					<?php plxUtils::printInput('frmLibButton_'.$lang,$var[$lang]['frmLibButton'],'text','20-20') ?>
				</p>
				<p>
					<label for="id_checkboxes_<?php echo $lang ?>"><?php $plxPlugin->lang('L_CHECKBOXES') ?>&nbsp;:</label>
					<?php plxUtils::printInput('checkboxes_'.$lang,$var[$lang]['checkboxes'],'text','60-500') ?>
				</p>
			</fieldset>
		</div>
		<?php endforeach; ?>
	</div>
	<fieldset>
		<p class="in-action-bar">
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>" />
		</p>
	</fieldset>
</form>
</div>
<script type="text/javascript" src="<?php echo PLX_PLUGINS."plxMySearch/tabs/tabs.js" ?>"></script>