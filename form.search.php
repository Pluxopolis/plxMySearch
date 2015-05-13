<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

function getParam($p) {
	return ($p=='' OR $p=='1');
}

# récupération d'une instance de plxMotor
$plxMotor = plxMotor::getInstance();
$plxPlugin = $plxMotor->plxPlugins->getInstance('plxMySearch');

# initialisation des variables locales à la page
$content = '';
$searchword = '';
$format_date = '#num_day/#num_month/#num_year(4)';
$searchresults=false;

if(!empty($_POST['searchfield']) OR !empty($_POST['searchcheckboxes'])) {

	# formatage des critères de recherches configurés dans l'admin du plugin
	$array = array();
	$cfg_params = explode(';', $plxPlugin->getParam('checkboxes_'.$plxPlugin->default_lang));
	foreach($cfg_params as $v) {
		$trim = trim($v);
		if($trim!='') {
			$array[plxUtils::title2url($trim)] = $trim;
		}
	}

	# valeurs de recherche à partir des cases à cocher
	$searchwords = array();
	if(isset($_POST['searchcheckboxes'])) {
		foreach($_POST['searchcheckboxes'] as $v) {
			if(isset($array[$v])) {
				$searchwords[] = strtolower($array[$v]);
			}
		}
	}

	# valeur de recherche de la zone de saisie libre
	$searchword = trim($_POST['searchfield']);
	if($searchword!='') {
		$searchwords[] = plxUtils::unSlash(htmlspecialchars(strtolower($searchword)));
	}

	# enregistrement des valeurs recherchées dans un fichier texte
	if($plxPlugin->getParam('savesearch') and sizeof($searchwords) > 0) {
		$filename=PLX_ROOT.PLX_CONFIG_PATH.'plugins/plxMySearch.data.php';
		if($f=fopen($filename,'a+')) {
			if(filesize($filename)>0)
				fwrite($f, "\n".implode(" ", $searchwords));
			else
				fwrite($f, implode(" ", $searchwords));
			fclose($f);
		}
	}

	# démarrage de la bufférisation écran
	ob_start();

	# recherche dans les articles
	$plxGlob_arts = clone $plxMotor->plxGlob_arts;
	$motif = '/^[0-9]{4}.(?:[0-9]|home|,)*(?:'.$plxMotor->activeCats.'|home)(?:[0-9]|home|,)*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
	if($aFiles = $plxGlob_arts->query($motif,'art','rsort',0,false,'before')) {
		foreach($aFiles as $v) { # On parcourt tous les fichiers

			$art = $plxMotor->parseArticle(PLX_ROOT.$plxMotor->aConf['racine_articles'].$v);
			$tags = implode(" ", array_map("trim", explode(',', strtolower($art['tags']))));
			$searchstring = strtolower(plxUtils::strRevCheck($art['title'].$art['chapo'].$art['content']).$tags);
			$searchstring = plxUtils::unSlash($searchstring);

			$searchok = false;
			foreach($searchwords as $word) {
				if (strpos($searchstring,$word) !== false) {
					$searchok =true;
				}
			}

			if($searchok) {
				$searchresults = true;
				$art_num = intval($art['numero']);
				$art_url = $art['url'];
				$art_title = plxUtils::strCheck($art['title']);
				$art_date = plxDate::formatDate($art['date'], $format_date);
				echo '<li>'.$art_date.': <a href="'.$plxMotor->urlRewrite('?article'.$art_num.'/'.$art_url).'">'.$art_title.'</a></li>';
			}
		}
	}

	# recherche dans les pages statiques
	if($plxMotor->aStats) {
		foreach($plxMotor->aStats as $k=>$v) {

			if($v['active']==1 AND $v['url']!=$plxMotor->mode) { # si la page est bien active
				$filename=PLX_ROOT.$plxMotor->aConf['racine_statiques'].$k.'.'.$v['url'].'.php';
				if(file_exists($filename)) {
					$searchstring  = strtolower(plxUtils::strRevCheck(file_get_contents($filename)));
					$searchstring = plxUtils::unSlash($searchstring);

					$searchok = false;
					foreach($searchwords as $word) {
						if (strpos($searchstring,$word) !== false) {
							$searchok =true;
						}
					}

					if($searchok) {
						$searchresults = true;
						$stat_num = intval($k);
						$stat_url = $v['url'];
						$stat_title = plxUtils::strCheck($v['name']);
						echo '<li><a href="'.$plxMotor->urlRewrite('?static'.$stat_num.'/'.$stat_url).'">'.$stat_title.'</a></li>';
					}
				}
			}
		}
	}

	# récupération du contenu à afficher
	$content = ob_get_clean();
}

# affichage du formulaire de recherche
if($plxPlugin->getParam('frmDisplay')) {
	plxMySearch::form(true);
}

# affichage des résultats de la recherche
if(isset($_POST['searchfield']) OR isset($_POST['searchcheckboxes'])) {
	if(empty($_POST['searchfield']) AND !isset($_POST['searchcheckboxes']))
		echo '<p>'.$plxPlugin->getLang('L_FORM_NO_SEARCHWORD').'</p>';
	elseif($searchresults) {
		echo '<p>'.$plxPlugin->getLang('L_FORM_RESULTS').' : </p>';
		echo '<ol class="search_results">'.$content.'</ol>';
	} else
		echo '<p>'.$plxPlugin->getLang('L_FORM_NO_RESULT').'</p>';
}

?>