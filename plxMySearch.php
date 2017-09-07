<?php
/**
 * Plugin plxMySearch
 * @author	Stephane F
 **/
class plxMySearch extends plxPlugin {

	private $url = ''; # parametre de l'url pour accèder à la page de recherche
	public $lang = '';

	/**
	 * Constructeur de la classe
	 *
	 * @param	default_lang	langue par défaut
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function __construct($default_lang) {

		# gestion du multilingue plxMyMultiLingue
		//if(preg_match('/([a-z]{2})\/(.*)/i', plxUtils::getGets(), $capture)) {
		//		$this->lang = $capture[1].'/';
		//}
		
		# gestion du multilingue plxMyMultiLingue
		$this->lang='';
		if(defined('PLX_MYMULTILINGUE')) {
			$lang = plxMyMultiLingue::_Lang();
			if(!empty($lang)) {
				if(isset($_SESSION['default_lang']) AND $_SESSION['default_lang']!=$lang) {
					$this->lang = $lang.'/';
				}
			}
		}		

		# appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);
		
		$this->url = $this->getParam('url')=='' ? 'search' : $this->getParam('url');

		# droits pour accèder à la page config.php du plugin
		$this->setConfigProfil(PROFIL_ADMIN);
		
		# droites pour accèder à la page admin.php du plugin
		if($this->getParam('savesearch'))
			$this->setAdminProfil(PROFIL_ADMIN);

		# déclaration des hooks
		$this->addHook('AdminTopEndHead', 'AdminTopEndHead');
		$this->addHook('AdminTopBottom', 'AdminTopBottom');

		# Si le fichier de langue existe on peut mettre en place la partie visiteur
		if(file_exists(PLX_PLUGINS.$this->plug['name'].'/lang/'.$default_lang.'.php')) {
			$this->addHook('plxShowConstruct', 'plxShowConstruct');
			$this->addHook('plxMotorPreChauffageBegin', 'plxMotorPreChauffageBegin');
			$this->addHook('plxShowStaticListEnd', 'plxShowStaticListEnd');
			$this->addHook('plxShowPageTitle', 'plxShowPageTitle');
			$this->addHook('SitemapStatics', 'SitemapStatics');
			$this->addHook('MySearchForm', 'form');
			if(defined('PLX_MYMULTILINGUE')) {
				$this->addHook('ThemeEndHead', 'ThemeEndHead');
			}			
		}

	}

	/**
	 * Méthode qui charge le code css nécessaire à la gestion de onglet dans l'écran de configuration du plugin
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminTopEndHead() {
		if(basename($_SERVER['SCRIPT_NAME'])=='parametres_plugin.php') {
			echo '<link href="'.PLX_PLUGINS.$this->plug['name'].'/tabs/style.css" rel="stylesheet" type="text/css" />'."\n";
		}
	}

	/**
	 * Méthode qui affiche un message si le plugin n'a pas la langue du site dans sa traduction
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminTopBottom() {

		echo '<?php
		$file = PLX_PLUGINS."'.$this->plug['name'].'/lang/".$plxAdmin->aConf["default_lang"].".php";
		if(!file_exists($file)) {
			echo "<p class=\"warning\">Plugin MySearch<br />".sprintf("'.$this->getLang('L_LANG_UNAVAILABLE').'", $file)."</p>";
			plxMsg::Display();
		}
		?>';

	}

	/**
	 * Méthode de traitement du hook plxShowConstruct
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function plxShowConstruct() {
		# infos sur la page statique
		$string  = "if(\$this->plxMotor->mode=='".$this->url."') {";
		$string .= "	\$array = array();";
		$string .= "	\$array[\$this->plxMotor->cible] = array(
			'name'		=> '".$this->getParam('mnuName_'.$this->default_lang)."',
			'menu'		=> '',
			'url'		=> 'search',
			'readable'	=> 1,
			'active'	=> 1,
			'group'		=> ''
		);";
		$string .= "	\$this->plxMotor->aStats = array_merge(\$this->plxMotor->aStats, \$array);";
		$string .= "}";
		echo "<?php ".$string." ?>";
	}

	/**
	 * Méthode de traitement du hook plxMotorPreChauffageBegin
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function plxMotorPreChauffageBegin() {

		$template = $this->getParam('template')==''?'static.php':$this->getParam('template');

		$string = "
		if(\$this->get && preg_match('/^".$this->url."\/?/',\$this->get)) {
			\$this->mode = '".$this->url."';
			\$prefix = str_repeat('../', substr_count(trim(PLX_ROOT.\$this->aConf['racine_statiques'], '/'), '/'));
			\$this->cible = \$prefix.\$this->aConf['racine_plugins'].'plxMySearch/form';
			\$this->template = '".$template."';
			return true;
		}
		";

		echo "<?php ".$string." ?>";
	}

	/**
	 * Méthode de traitement du hook plxShowStaticListEnd
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function plxShowStaticListEnd() {

		# ajout du menu pour accèder à la page de recherche
		if($this->getParam('mnuDisplay')) {
			echo "<?php \$status = \$this->plxMotor->mode=='".$this->url."'?'active':'noactive'; ?>";
			echo "<?php array_splice(\$menus, ".($this->getParam('mnuPos')-1).", 0, '<li class=\"static menu '.\$status.'\" id=\"static-search\"><a href=\"'.\$this->plxMotor->urlRewrite('?".$this->lang.$this->url."').'\" title=\"".$this->getParam('mnuName_'.$this->default_lang)."\">".$this->getParam('mnuName_'.$this->default_lang)."</a></li>'); ?>";
		}
	}

	/**
	 * Méthode qui renseigne le titre de la page dans la balise html <title>
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function plxShowPageTitle() {
		echo '<?php
			if($this->plxMotor->mode == "'.$this->url.'") {
				$this->plxMotor->plxPlugins->aPlugins["plxMySearch"]->lang("L_PAGE_TITLE");
				return true;
			}
		?>';
	}

	/**
	 * Méthode qui référence la page de recherche dans le sitemap
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function SitemapStatics() {
		echo '<?php
		echo "\n";
		echo "\t<url>\n";
		echo "\t\t<loc>".$plxMotor->urlRewrite("?'.$this->lang.$this->url.'")."</loc>\n";
		echo "\t\t<changefreq>monthly</changefreq>\n";
		echo "\t\t<priority>0.8</priority>\n";
		echo "\t</url>\n";
		?>';
	}

	/**
	 * Méthode statique qui affiche le formulaire de recherche
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public static function form($title=false) {

		$placeholder = '';

		# récupération d'une instance de plxMotor
		$plxMotor = plxMotor::getInstance();
		$plxPlugin = $plxMotor->plxPlugins->getInstance('plxMySearch');
		$searchword = '';
		if(!empty($_POST['searchfield'])) {
			$searchword = plxUtils::strCheck(plxUtils::unSlash($_POST['searchfield']));
		}
		if($plxPlugin->getParam('placeholder_'.$plxPlugin->default_lang)!='') {
			$placeholder=' placeholder="'.$plxPlugin->getParam('placeholder_'.$plxPlugin->default_lang).'"';
		}
	?>

<div class="searchform">
	<form action="<?php echo $plxMotor->urlRewrite('?'.$plxPlugin->lang.$plxPlugin->getParam('url')) ?>" method="post">
		<?php if($title) : ?>
		<p class="searchtitle">
			<?php
				if($plxPlugin->getParam('checkboxes_'.$plxPlugin->default_lang)=='')
					$plxPlugin->lang('L_FORM_SEARCHFIELD');
				else
					$plxPlugin->lang('L_FORM_SEARCHFIELD_2');
			?>&nbsp;:
		</p>
		<?php endif; ?>
		<div class="searchfields">
			<?php
			if($plxPlugin->getParam('checkboxes_'.$plxPlugin->default_lang)!='') {
				if($chk = explode(';', $plxPlugin->getParam('checkboxes_'.$plxPlugin->default_lang))) {
					echo '<ul>';
					foreach($chk as $k => $v) {
						$c = plxUtils::title2url(trim($v));
						$sel = "";
						if(isset($_POST['searchcheckboxes'])) {
							foreach($_POST['searchcheckboxes'] as $s) {
								if($s==$c) {
									$sel = ' checked="checked"';
								}
							}
						}
						echo '<li><input'.$sel.' class="searchcheckboxes" type="checkbox" name="searchcheckboxes[]" id="id_searchcheckboxes[]" value="'.$c.'" />&nbsp;'.plxUtils::strCheck($v).'</li>';
					}
					echo '</ul>';
				}
			}
			?>
			<p>
			<input type="text"<?php echo $placeholder ?> class="searchfield" name="searchfield" value="<?php echo $searchword ?>" />
			<input type="submit" class="searchbutton" name="searchbutton" value="<?php echo $plxPlugin->getParam('frmLibButton_'.$plxPlugin->default_lang) ?>" />
			</p>
		</div>
	</form>
</div>

	<?php
	}
	
	/** 
	 *
	 * Méthode d'ajout des <link rel="alternate"... sur les pages des plugins qui gèrent le multilingue
	 *
	 * @return	stdio
	 * @author	WorldBot
	 * 
	**/
	public function ThemeEndHead() {
		
		if(defined('PLX_MYMULTILINGUE')) {		
			$plxMML = is_array(PLX_MYMULTILINGUE) ? PLX_MYMULTILINGUE : unserialize(PLX_MYMULTILINGUE);
			$langues = empty($plxMML['langs']) ? array() : explode(',', $plxMML['langs']);
			$string = '';
			foreach($langues as $k=>$v)	{
				$url_lang="";
				if($_SESSION['default_lang'] != $v) $url_lang = $v.'/';
				$string .= 'echo "\t<link rel=\"alternate\" hreflang=\"'.$v.'\" href=\"".$plxMotor->urlRewrite("?'.$url_lang.$this->getParam('url').'")."\" />\n";';
			}
			echo '<?php if($plxMotor->mode=="'.$this->getParam('url').'") { '.$string.'} ?>';
		}
	}	
}
?>
