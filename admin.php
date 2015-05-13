<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

$filename = PLX_ROOT.PLX_CONFIG_PATH.'plugins/plxMySearch.data.php';

if(isset($_GET['action']) AND $_GET['action']=='resetsearchstats') {

	if(isset($_GET['key']) AND $_GET['key']==$plxAdmin->aConf['clef']) {
		if(file_exists($filename))
			unlink($filename);
	}
	header('Location: plugin.php?p=plxMySearch');
	exit;
}

$data=array();
if($fileexists=file_exists($filename)) {
	$data = explode("\n", file_get_contents($filename));
}
?>
<?php
	echo '<p class="in-action-bar">'.$plxPlugin->getLang('L_DATA_LIST').'</p>';
	if(sizeof($data)>0) {
		$array = array_count_values($data);
		arsort($array);
		echo "<table>";
		echo '<thead><tr><th>'.$plxPlugin->getLang('L_TABLE_WORD').'</th><th>'.$plxPlugin->getLang('L_TABLE_WORD_COUNT').'</th></tr></thead>';
		echo '<tbody>';
		foreach($array as $key=>$value) {
			echo '<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
		}
		echo '</tbody>';
		echo "</table>";
	} else {
		echo '<p>'.$plxPlugin->getLang('L_NO_DATA').'.</p>';
	}
	if($fileexists)
		echo '<p><a href="plugin.php?p=plxMySearch&amp;action=resetsearchstats&amp;key='.$plxAdmin->aConf['clef'].'">'.$plxPlugin->getLang('L_RESET_STATS').'</a></p>';
?>