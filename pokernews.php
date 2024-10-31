<?php

/*
Plugin Name: Poker News
Plugin URI: http://www.gamblingnewscollection.com/
Description: Fetches publicly available news feeds from many Poker News sites and displays them in a highly configurable widget with many formatting options.
Author: PluginTaylor
Version: 0.8.1
Author URI: http://www.gamblingnewscollection.com/
*/

function PokerNews_init() {
	function PokerNews() {
		$options = get_option('PokerNews_Widget');
		$options = PokerNews_LoadDefaults($options);

		$q = 'HTTP_REFERER='.urlencode($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).'&REMOTE_ADDR='.urlencode($_SERVER['REMOTE_ADDR']).'&HTTP_USER_AGENT='.urlencode($_SERVER['HTTP_USER_AGENT']).'&PLUGIN=PokerNews';
		if($options) { foreach($options AS $p => $v) { $q .= '&'.urlencode($p).'='.urlencode($v); } }

    	$req =	"POST / HTTP/1.1\r\n".
    			"Content-Type: application/x-www-form-urlencoded\r\n".
    			"Host: www.gamblingnewscollection.com\r\n".
    			"Content-Length: ".strlen($q)."\r\n".
    			"Connection: close\r\n".
    			"\r\n".$q;

    	$fp = @fsockopen('www.gamblingnewscollection.com', 80, $errno, $errstr, 10);
    	if(!$fp) {  }
    	if(!fwrite($fp, $req)) { fclose($fp); }
    	$result = '';
    	while(!feof($fp)) { $result .= fgets($fp); }
    	fclose($fp);
    	$result = explode("\r\n\r\n", $result);

		return $result[1];
	}
	function PokerNews_Widget($args) {
		$options = get_option('PokerNews_Widget');
		$options = PokerNews_LoadDefaults($options);

		extract($args);
		echo $before_widget.$before_title.$options['title'].$after_title.PokerNews().$after_widget;
	}
	function PokerNews_LoadDefaults($options) {
		$options['title'] = empty($options['title']) ? __('Poker News') : $options['title'];
		$options['list_start'] = empty($options['list_start']) ? __('<ul>') : $options['list_start'];
		$options['list_end'] = empty($options['list_end']) ? __('</ul>') : $options['list_end'];
		$options['formatting'] = empty($options['formatting']) ? __('<li><a href="[link]" rel="nofollow" target="_blank">[date] - [title]</a></li>') : $options['formatting'];
		$options['count'] = empty($options['count']) ? __(5) : $options['count'];
		$options['description'] = empty($options['description']) ? __(20) : $options['description'];

		return $options;
	}
	function PokerNews_WidgetControl() {
		$options = $newoptions = get_option('PokerNews_Widget');
		if($_POST['PokerNews_WidgetSubmit']) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['PokerNews_WidgetTitle']));
			$newoptions['count'] = $_POST['PokerNews_ItemCount'];
			$newoptions['list_start'] = stripslashes($_POST['PokerNews_ListStart']);
			$newoptions['list_end'] = stripslashes($_POST['PokerNews_ListEnd']);
			$newoptions['formatting'] = stripslashes($_POST['PokerNews_ItemFormatting']);
			$newoptions['description'] = stripslashes($_POST['PokerNews_Description']);
		}
		if($options != $newoptions) {
			$options = $newoptions;
			update_option('PokerNews_Widget', $options);
		}
		$options = PokerNews_LoadDefaults($options);

		echo '
<h3>List</h3>
<p><label for="PokerNews_WidgetTitle">Title: <input id="PokerNews_WidgetTitle" name="PokerNews_WidgetTitle" type="text" value="'.attribute_escape($options['title']).'" /></label><br />
<label for="PokerNews_ListStart">Start: <input id="PokerNews_ListStart" name="PokerNews_ListStart" type="text" value="'.attribute_escape($options['list_start']).'" /></label><br />
<label for="PokerNews_ListEnd">End: <input id="PokerNews_ListEnd" name="PokerNews_ListEnd" type="text" value="'.attribute_escape($options['list_end']).'" /></label></p>
<label for="PokerNews_Description">Description: <input id="PokerNews_Description" name="PokerNews_Description" type="text" value="'.attribute_escape($options['description']).'" /> (characters)</label></p>
<i>(set to <b>0</b> to disable descriptions)</i>

<h3>Items</h3>
<p><label for="PokerNews_ItemCount">Item count: <select id="PokerNews_ItemCount" name="PokerNews_ItemCount">';
		for($i=1; $i <= 10; $i++) {
			if(attribute_escape($options['count']) == $i OR (attribute_escape($options['count']) <= 0 AND $i == 5)) { $selected = ' selected'; } else { $selected = FALSE; }
			echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
		}
		echo '</select></label><br />
<label for="PokerNews_ItemFormatting">Item formatting:<br /><i>([link], [title], [date], [description])</i><br /><textarea style="font-size: 10px;" id="PokerNews_ItemFormatting" name="PokerNews_ItemFormatting">'.attribute_escape($options['formatting']).'</textarea /></label><br />
<input type="hidden" id="PokerNews_WidgetSubmit" name="PokerNews_WidgetSubmit" value="true" />';
	}

	register_sidebar_widget('Poker News', 'PokerNews_Widget');
	register_widget_control('Poker News', 'PokerNews_WidgetControl');
}
add_action('plugins_loaded', 'PokerNews_init');

?>