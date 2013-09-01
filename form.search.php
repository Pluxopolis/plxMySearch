<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

# récuperation d'une instance de plxMotor
$plxMotor = plxMotor::getInstance();
$plxPlugin = $plxMotor->plxPlugins->getInstance('plxMySearch');

# initialisation des variables locales à la page
$content = '';
$searchword = '';
$format_date = '#num_day/#num_month/#num_year(4)';
$searchresults=false;

if(!empty($_POST['searchfield'])) {

	$word = plxUtils::strCheck(plxUtils::unSlash($_POST['searchfield']));

	# valeur de recherche
	$searchword = strtolower(htmlspecialchars(trim($_POST['searchfield'])));
	$searchword = plxUtils::unSlash($searchword);

	if($plxPlugin->getParam('savesearch')) {
		$filename=PLX_ROOT.PLX_CONFIG_PATH.'plugins/plxMySearch.data.php';
		if($f=fopen($filename,'a+')) {
			if(filesize($filename)>0)
				fwrite($f, "\n".$searchword);
			else
				fwrite($f, $searchword);
			fclose($f);
		}
	}

	# démarrage de la bufférisation écran
	ob_start();

	# recherche dans les articles
	$plxGlob_arts = clone $plxMotor->plxGlob_arts;
	$motif = '/^[0-9]{4}.['.$plxMotor->activeCats.',]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
	if($aFiles = $plxGlob_arts->query($motif,'art','rsort',0,false,'before')) {
		foreach($aFiles as $v) { # On parcourt tous les fichiers
			$art = $this->plxMotor->parseArticle(PLX_ROOT.$plxMotor->aConf['racine_articles'].$v);
			$searchstring = strtolower(plxUtils::strRevCheck($art['title'].$art['chapo'].$art['content']));
			$searchstring = plxUtils::unSlash($searchstring);
			if ($searchword!='' AND strpos($searchstring,$searchword) !== false) {
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
					if(strpos($searchstring,$searchword) !== false) {
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

	# récuperation du contenu à afficher
	$content = ob_get_clean();
}

# affichage du formulaire de recherche
if($plxPlugin->getParam('frmDisplay')) {
	plxMySearch::form(true);
}

# affichage des résultats de la recherche
if(isset($_POST['searchfield'])) {
	if(empty($_POST['searchfield']))
		echo '<p>'.$plxPlugin->getLang('L_FORM_NO_SEARCHWORD').'</p>';
	elseif($searchresults) {
		echo '<p>'.$plxPlugin->getLang('L_FORM_RESULTS').' : <strong>'.$word.'</strong></p>';
		echo '<ol class="search_results">'.$content.'</ol>';
	} else
		echo '<p>'.$plxPlugin->getLang('L_FORM_NO_RESULT').' : <strong>'.$word.'</strong></p>';
}
?>