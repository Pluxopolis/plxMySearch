<?php
$plxPlugin = $plxAdmin->plxPlugins->getInstance('plxMySearch');
?>
<br />
<?php $plxPlugin->lang('L_HELP') ?> :
<pre style="color:#000;font-size:12px; background:#fff; padding: 10px 20px 20px 20px; border:1px solid #efefef">
<?php
echo (htmlspecialchars("
<?php eval(\$plxShow->callHook('MySearchForm')) ?>
"));
?>
</pre>