<?php
/*
Plugin Name: Custom JS
Description: Add custom JavaScript to footer of theme
Version: 0.1
Author: Pavol Bokor
Author URI: https://www.4enzo.sk
Licence: GNU DPL v 3.0
*/

# get correct id for plugin
$thisfile_customjs=basename(__FILE__, ".php");
$customjs_file=GSDATAOTHERPATH .'CustomJS.xml';

# add in this plugin's language file
i18n_merge($thisfile_customjs) || i18n_merge($thisfile_customjs, 'en_US');

# register plugin
register_plugin(
	$thisfile_customjs,				# ID of plugin, should be filename minus php
	i18n_r($thisfile_customjs.'/CUSTOMJS_TITLE'),	# Title of plugin
	'0.1',						# Version of plugin
	'Pavol Bokor',					# Author of plugin
	'https://4enzo.sk',				# Author URL
	i18n_r($thisfile_customjs.'/CUSTOMJS_DESC'),	# Plugin Description
	'theme',					# Page type of plugin
	'customjs_show'					# Function that displays content
);

# hooks
add_action('theme-footer','customjq_echo_to_theme');
add_action('theme-footer','customjs_echo_to_theme');
add_action('theme-sidebar','createSideMenu',array($thisfile_customjs, i18n_r($thisfile_customjs.'/CUSTOMJS_TITLE')));

# load codemirror
register_script('codemirror', $SITEURL.$GSADMIN.'/template/js/codemirror/lib/codemirror-compressed.js', '0.2.0', FALSE);
register_style('codemirror-css',$SITEURL.$GSADMIN.'/template/js/codemirror/lib/codemirror.css','screen',FALSE);
register_style('codemirror-theme',$SITEURL.$GSADMIN.'/template/js/codemirror/theme/default.css','screen',FALSE);
queue_script('codemirror', GSBACK);
queue_style('codemirror-css', GSBACK);
queue_style('codemirror-theme', GSBACK);

# get XML data
if (file_exists($customjs_file)) {
	$customjs_data = getXML($customjs_file);
}

# print jQuery URL to theme footer
$echojq_to_theme = '';
if(isset($customjs_data->customjs_url_content)) $echojq_to_theme = $customjs_data->customjs_url_content;

function customjq_echo_to_theme() {
	global $echojq_to_theme;
	echo 
"
<!-- jQuery URL -->
<script src='".$echojq_to_theme."'></script>";
}

# print custom js to theme footer
$echojs_to_theme = '';
if(isset($customjs_data->customjs_js_content)) $echojs_to_theme = $customjs_data->customjs_js_content;

function customjs_echo_to_theme() {
	global $echojs_to_theme;
	echo 
"
<!-- Custom JS -->
<script type='text/javascript'>
" . $echojs_to_theme . "
</script>
";
}

function customjs_show() {
	global $customjs_file, $customjs_data, $thisfile_customjs;
	$success=$error=null;
	
	# submitted form
	if (isset($_POST['submit'])) {
		# check for errors and parse data
		if ($_POST['customjs_url_content'] != '') {
			if (filter_var($_POST['customjs_url_content'], FILTER_VALIDATE_URL)) {
				$resp['customjs_url_content'] = $_POST['customjs_url_content'];
			} else {
				$error .= i18n_r($thisfile_customjs.'/CUSTOMJS_URL_CONTENT_ERROR').' ';
			}
		}
		if ($_POST['customjs_js_content'] != '') {
			$resp['customjs_js_content'] = $_POST['customjs_js_content'];
		}
		
		# if there are no errors, save data
		if (!$error) {
			$xml = @new SimpleXMLElement('<item></item>');
			if(isset($resp['customjs_url_content'])) $xml->addChild('customjs_url_content', htmlspecialchars($resp['customjs_url_content']));
			if(isset($resp['customjs_js_content'])) $xml->addChild('customjs_js_content', htmlspecialchars($resp['customjs_js_content']));

			if (! $xml->asXML($customjs_file)) {
				$error = i18n_r('CHMOD_ERROR');
			} else {
				$customjs_data = getXML($customjs_file);
				$success = i18n_r('SETTINGS_UPDATED');
			}
		}
	}
	
	if($success) { 
		echo '<p style="color:#669933;"><b>'. $success .'</b></p>';
	} 
	if($error) { 
		echo '<p style="color:#cc0000;"><b>'. $error .'</b></p>';
	}
	?>
	
	<form method="post" action="<?php echo $_SERVER ['REQUEST_URI']; ?>">
		
		
		<h3><?php i18n($thisfile_customjs.'/CUSTOMJS_TITLE'); ?></h3>
		<?php
			$value = '';
			if(isset($customjs_data->customjs_url_content)) $value = $customjs_data->customjs_url_content;
		?>
		<p>
		<label for="lb_customjs_url_content" ><?php i18n($thisfile_customjs.'/CUSTOMJS_JQ_URL'); ?></label>
		<input id="lb_customjs_url_content" name="customjs_url_content" class="text" value="<?php echo $value; ?>" type="url" />
		</p>
		<?php
			$value = '';
			if(isset($customjs_data->customjs_js_content)) $value = $customjs_data->customjs_js_content;
		?>
		<p>
		<label for="lb_customjs_js_content" class="tooltip"><?php i18n($thisfile_customjs.'/CUSTOMJS_CONTENT'); ?> (?)<span class="tooltiptext"><?php i18n($thisfile_customjs.'/CUSTOMJS_TOOLTIP'); ?></span></label>
		
		<textarea id="lb_customjs_js_content" name="customjs_js_content" type="text"><?php echo $value; ?></textarea>
		</p>
		
		<p>
		<input type="submit" id="submit" class="submit" value="<?php i18n('BTN_SAVESETTINGS'); ?>" name="submit" />
		</p>
		
	</form>
	
	<small> <a href="https://github.com/bokorpavol/CustomJS" target="_blank">Custom JS on GitHub</a>::<a href="https://www.4enzo.sk/" target="_blank">Author website</a></small>
<style>
.tooltip .tooltiptext{visibility:hidden;width:250px;background-color:#000;color:#fff;text-align:center;border-radius:6px;padding:5px 0;position:absolute;z-index:1}.tooltip:hover .tooltiptext{visibility:visible}
</style>
<script>
window.onload=function(){var e=CodeMirror.newFoldFunction(CodeMirror.braceRangeFinder);function t(){var e=$(".CodeMirror-scroll");e.hasClass("fullscreen")?(e.removeClass("fullscreen"),e.height(t.beforeFullscreen.height),e.width(t.beforeFullscreen.width),r.refresh()):(t.beforeFullscreen={height:e.height(),width:e.width()},e.addClass("fullscreen"),e.height("100%"),e.width("100%"),r.refresh())}var r=CodeMirror.fromTextArea(document.getElementById("lb_customjs_js_content"),{lineNumbers:!0,matchBrackets:!0,indentUnit:4,indentWithTabs:!0,enterMode:"keep",mode:"text/css",tabMode:"shift",theme:"default",onGutterClick:e,extraKeys:{"Ctrl-Q":function(t){e(t,t.getCursor().line)},F11:t,Esc:t},onCursorActivity:function(){r.setLineClass(i,null),i=r.setLineClass(r.getCursor().line,"activeline")}}),i=r.setLineClass(0,"activeline")};
</script>	
	<?php
}
?>
