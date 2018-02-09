<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

# récupération d'une instance de plxMotor
$plxMotor = plxMotor::getInstance();
$plxPlugin = $plxMotor->plxPlugins->getInstance('plxMySearch');

# initialisation des variables locales à la page
$content = '';
$searchword = '';
$format_date = '#num_day/#num_month/#num_year(4)';
$searchresults=false;
$method = $plxPlugin->getParam('method') == 'get' ? $_GET : $_POST;


if(!empty($method['searchfield']) OR !empty($method['searchcheckboxes'])) {

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
	if(isset($method['searchcheckboxes'])) {
		foreach($method['searchcheckboxes'] as $v) {
			if(isset($array[$v])) {
				$searchwords[] = strtolower($array[$v]);
			}
		}
	}

	# valeur de recherche de la zone de saisie libre
	$searchword = trim($method['searchfield']);
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

	# variable pour stocker les résultats
	$res_arts = null;
	$res_stats = null;
	
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
				$art_num = intval($art['numero']);
				$art_url = $art['url'];
				$art_title = plxUtils::strCheck($art['title']);
				$art_date = plxDate::formatDate($art['date'], $format_date);
				$res_arts[$art['categorie']][] = '<li>'.$art_date.': <a href="'.$plxMotor->urlRewrite('?article'.$art_num.'/'.$art_url).'">'.$art_title.'</a></li>';
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
						$stat_num = intval($k);
						$stat_url = $v['url'];
						$stat_title = plxUtils::strCheck($v['name']);
						$res_stats[] = '<li><a href="'.$plxMotor->urlRewrite('?static'.$stat_num.'/'.$stat_url).'">'.$stat_title.'</a></li>';
					}
				}
			}
		}
	}

}

# affichage du formulaire de recherche
if($plxPlugin->getParam('frmDisplay')) {
	plxMySearch::form(true);
}

# affichage des résultats de la recherche
if(isset($method['searchfield']) OR isset($method['searchcheckboxes'])) {
	if(empty($method['searchfield']) AND !isset($method['searchcheckboxes']))
		echo '<div class="search_words">'.$plxPlugin->getLang('L_FORM_NO_SEARCHWORD').'</div>';
	elseif($res_arts OR $res_stats) {
		echo '<div class="search_results">'.$plxPlugin->getLang('L_FORM_RESULTS').' : ';
		if($res_arts) {
			echo '<p class="search_articles">'.$plxPlugin->getLang('L_FORM_ARTICLES').' :</p>';
			foreach(array_keys($res_arts) as $idx => $cat) {
				if(isset($plxMotor->aCats[$cat]))
					$libcat = plxUtils::strCheck($plxMotor->aCats[$cat]['name']);
				elseif($cat=='home')
					$libcat = L_HOMEPAGE;
				else
					$libcat = L_UNCLASSIFIED;
				echo '<p class="search_category">'.$plxPlugin->getLang('L_FORM_CATEGORY').' : '.$libcat;
				echo '<ol>'.implode(' ', $res_arts[$cat]).'</ol>';
				echo '</p>';
			}
		}
		if($res_stats) {
			echo '<p class="search_statics">';
			echo $plxPlugin->getLang('L_FORM_STATICS').' :';
			echo '<ol>'.implode(' ', $res_stats).'</ol>';
			echo '</p>';
		}
		echo '</div>';
	} else
		echo '<div class="searchresults">'.$plxPlugin->getLang('L_FORM_NO_RESULT').'</div>';
}

?>
