<?php
/**
 * Plugin plxMySearch
 * @author	Stephane F
 **/
class plxMySearch extends plxPlugin {

	/**
	 * Constructeur de la classe
	 *
	 * @param	default_lang	langue par défaut
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function __construct($default_lang) {

		# appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		# droits pour accèder à la page config.php du plugin
		$this->setConfigProfil(PROFIL_ADMIN);
		if($this->getParam('savesearch'))
			$this->setAdminProfil(PROFIL_ADMIN);

		# déclaration des hooks
		$this->addHook('AdminTopEndHead', 'AdminTopEndHead');
		$this->addHook('plxShowConstruct', 'plxShowConstruct');
		$this->addHook('plxMotorPreChauffageBegin', 'plxMotorPreChauffageBegin');
		$this->addHook('plxShowStaticListEnd', 'plxShowStaticListEnd');
		$this->addHook('plxShowPageTitle', 'plxShowPageTitle');
		$this->addHook('SitemapStatics', 'SitemapStatics');
		$this->addHook('MySearchForm', 'form');
	}

	public function AdminTopEndHead() {
		if(basename($_SERVER['SCRIPT_NAME'])=='parametres_plugin.php') {
			echo '<link href="'.PLX_PLUGINS.'plxMySearch/tabs/style.css" rel="stylesheet" type="text/css" />'."\n";
		}
	}

	/**
	 * Méthode de traitement du hook plxShowConstruct
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function plxShowConstruct() {
		# infos sur la page statique
		$string  = "if(\$this->plxMotor->mode=='".$this->getParam('url')."') {";
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
		if(\$this->get && preg_match('/^".$this->getParam('url')."\/?/',\$this->get)) {
			\$this->mode = '".$this->getParam('url')."';
			\$prefix = str_repeat('../', substr_count(trim(PLX_ROOT.\$this->aConf['racine_statiques'], '/'), '/'));
			\$this->cible = \$prefix.'plugins/plxMySearch/form';
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
			echo "<?php \$class = \$this->plxMotor->mode=='".$this->getParam('url')."'?'active':'noactive'; ?>";
			echo "<?php array_splice(\$menus, ".($this->getParam('mnuPos')-1).", 0, '<li><a class=\"static '.\$class.'\" href=\"'.\$this->plxMotor->urlRewrite('?".$this->getParam('url')."').'\" title=\"".$this->getParam('mnuName_'.$this->default_lang)."\">".$this->getParam('mnuName_'.$this->default_lang)."</a></li>'); ?>";
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
			if($this->plxMotor->mode == "'.$this->getParam('url').'") {
				echo plxUtils::strCheck($this->plxMotor->aConf["title"])." - ".plxUtils::strCheck($this->plxMotor->plxPlugins->aPlugins["plxMySearch"]->getLang("L_PAGE_TITLE"));
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
		echo "\t\t<loc>".$plxMotor->urlRewrite("?'.$this->getParam('url').'")."</loc>\n";
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
	<form action="<?php echo $plxMotor->urlRewrite('?'.$plxPlugin->getParam('url')) ?>" method="post">
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
}
?>
