<?php
/*
Plugin Name: BloggyFeed
Version: 1.0
Plugin URI: http://www.designskolan.net/bloggy-plugin-for-wordpress/
Description: BloggyFeed l&aring;ter dig visa valfritt antal meddelanden ifr&aring;n din Bloggy.
Mer info på <a href="http://www.designskolan.net/bloggy-plugin-for-wordpress/">designskolan.net</a>!
Author: Filip Stefansson
Author URI: http://www.designskolan.net
*/

/*  Copyright 2009  Filip Stefansson  (email : filip.stefansson@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//define('MAGPIE_CACHE_AGE', 120);
define('MAGPIE_CACHE_ON', 0); //2.7 Cache Bug
define('MAGPIE_INPUT_ENCODING', 'UTF-8');

	// Skapar funktionen
	function bloggy($username = '', $num = 1, $v_tid = true, $link = '', $tid_format = 'd/m H:i', $open = '_self', 		$s_links = true, $timeago = false, $users = true) {

				// Hämtar RSS-feeden
				include_once(ABSPATH . WPINC . '/rss.php');
				$messages = fetch_rss("http://" . $username . ".bloggy.se/rss/rss.xml");


					// Om användarnamet inte är satt
					if ($username == '') {
					echo '<ul class="bloggy_ul"><li>Anv&auml;ndarnamnet är inte satt!</li></ul>';
					}
		
							// Om det inte finns några meddelanden
							if ( empty($messages->items) ) {
							echo '<ul class="bloggy_ul"><li>Hittade inga meddelanden!</li></ul>';
							} else {
							
	
	// Om det finns meddelanden ska informationen hämtas och skrivas ut
	echo '<ul class="bloggy_ul">';
	foreach ( $messages->items as $message ) {

	// Sätter variablar
	$msg = $message['title'];
	$tid = strtotime($message['pubdate']);
	$url = $message['link'];
			
			
			// Om man vill visa hur länge sedan meddelandet skrevs
			if($timeago) {$tid = sprintf( __('%s sedan'), human_time_diff( $tid ) );}
			// Om inte
			else {$tid = date(__($tid_format), $tid);}
			

		
			// Om användaren vill visa länkar	
			if($s_links) $msg = hyperlinks($msg);
			
			// Om användaren vill visa andra bloggymedlemmar	
			if ($users) $msg = bloggy_users($msg);
			
		// Skriver ut informationen

		echo '<li><span class="bloggy-meddelande">' . $msg . '</span>';
		if ($link) echo '<a class="bloggy-link" href="' . $url . '" target="' . $open . '" title="Till inl&auml;gget!">' . $link .  '</a><br />' ;
		else echo '<br />';
		if ($v_tid){ echo '<span class="bloggy-tid">' . $tid . '</span></li>'; }
		else {
		echo '</li>';
		}
		
		
			$i++;
			if ( $i >= $num ) break;
		}
	}
		echo '</ul>';	

}




	// Funktion för att hitta länkar
function hyperlinks($text) {
$text = preg_replace('!http://([^\s<]+)!i','<a href="http://$1" class="bloggy-link" target="_blank">http://$1</a>',stripslashes($text));  
return $text;
};
	// Funktion för att hitta bloggy-användare
function bloggy_users($text) {
       $text = preg_replace('/([\.|\,|\:|\¡|\¿|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://$2.bloggy.se/\" class=\"blogger-link\">@$2</a>$3 ", $text);

return $text;
};



// Skapar en Widget
function widget_bloggy_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_bloggy($args) {
		

		// Hämtar info om temats Widget-inställningar
		extract($args);

		// Sparar informationen som tillhör Widgeten
		include_once(ABSPATH . WPINC . '/rss.php');
		$options = get_option('widget_bloggy');
		$title = $options['title'];
		$username = $options['username'];
		$num = $options['num'];
		$link = $options['link'];
		$open = $options['open'];	
		$v_tid = ($options['v_tid']) ? false : true;
		$tid_format = $options['tid_format'];
		$s_links = ($options['s_links']) ? true : false;
		$timeago = ($options['timeago']) ? true : false;
		$users = ($options['users']) ? true : false;
		$messages = fetch_rss("http://" . $username . ".bloggy.se/rss/rss.xml");

		// Skriver ut funktionen som visar meddelandena
		echo $before_widget . $before_title . $title . $after_title;
		bloggy($username, $num, $v_tid, $link, $tid_format, $open, $s_links, $timeago, $users);
		echo $after_widget;
	}

	
	
	function widget_bloggy_control() {

		$options = get_option('widget_bloggy');
		if ( !is_array($options) )
			$options = array('title'=>'', 'username'=>'', 'num'=>'1', 'link'=>'#', 'tid_format'=>'d/m H:i', 'open'=>'_self', 's_links'=>true, 'timeago'=>false, 'users'=>true);
		if ( $_POST['bloggy-submit'] ) {

			// Formaterar om informationen och sätter in den i databasen
			$options['title'] = strip_tags(stripslashes($_POST['bloggy_title']));
			$options['username'] = strip_tags(stripslashes($_POST['bloggy_username']));
			$options['num'] = strip_tags(stripslashes($_POST['bloggy_num']));
			$options['link'] = strip_tags(stripslashes($_POST['bloggy_link']));
			$options['open'] = strip_tags(stripslashes($_POST['bloggy_open']));
			$options['v_tid'] = isset($_POST['bloggy_v_tid']);
			$options['tid_format'] = strip_tags(stripslashes($_POST['bloggy_tid_format']));
			$options['s_links'] = isset($_POST['bloggy_s_links']);
			$options['timeago'] = isset($_POST['bloggy_timeago']);
			$options['users'] = isset($_POST['bloggy_users']);
			update_option('widget_bloggy', $options);
		}

		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$username = htmlspecialchars($options['username'], ENT_QUOTES);
		$num = htmlspecialchars($options['num'], ENT_QUOTES);
		$link = htmlspecialchars($options['link'], ENT_QUOTES);
		$open = htmlspecialchars($options['open'], ENT_QUOTES);
		$v_tid_checked = ($options['v_tid']) ? 'checked="checked"' : '';
		$tid_format = htmlspecialchars($options['tid_format'], ENT_QUOTES);
		$s_links_checked = ($options['s_links']) ? 'checked="checked"' : '';
		$timeago_checked = ($options['timeago']) ? 'checked="checked"' : '';
		$users_checked = ($options['users']) ? 'checked="checked"' : '';		

		// Skapar Widget-formen
		echo '<p style="text-align:right;"><label for="bloggy_title">' . __('Titel:') . ' <input style="width: 150px;" id="bloggy_title" name="bloggy_title" type="text" value="'.$title.'" /></label>
		<br /><small>Rubriken p&aring; din Widget!</small>
		</p>';
		
		
		echo '<p style="text-align:right;"><label for="bloggy_username">' . __('Bloggy-id:') . ' <input style="width: 150px;" id="bloggy_username" name="bloggy_username" type="text" value="'.$username.'" /></label><br /><small>Ditt anv&auml;ndarnamn p&aring; bloggy!</small></p>';

		
		
		echo '<p style="text-align:right;"><label for="bloggy_num">' . __('Antal meddelande:') . ' <input style="width: 25px;" id="bloggy_num" name="bloggy_num" type="text" value="'.$num.'" /></label></p>';
		

		echo '<p style="text-align:right;"><label for="bloggy_link">' . __('Utseende p&aring; l&auml;nk:') . ' <input style="width: 80px;" id="bloggy_link" name="bloggy_link" type="text" value="'.$link.'" /></label><br /><small>L&auml;mna tom om du inte vill visa n&aring;gon l&auml;nk!</small></p>';
		
		
		// nytt för ver. 0.3: Välj hur du vill öpnna länken
		echo '<p style="text-align:right;"><label for="bloggy_open">' . __('&Ouml;ppna:') . ' <select style="width: 100px; padding: 2px;" id="bloggy_open" name="bloggy_open" />';
		
		if($open == '_self') {
		echo '
		<option value="_self">i samma f&ouml;nster</option>
		<option value="_blank">i ett nytt f&ouml;nster</option>
		';
		}
		elseif($open == '_blank') {
		echo '
		<option value="_blank">i ett nytt f&ouml;nster</option>	
		<option value="_self">i samma f&ouml;nster</option>	
		';
		};

			echo '</select></label><br /><small>Just nu &ouml;ppnar den <strong>';

	if ($open == '_self'){ echo 'i samma f&ouml;nster'; }
	else
					 { echo 'i ett nytt f&ouml;nster'; };
					 
			echo '</strong>!</small></p>';
		
		
		
		
		echo '<p style="text-align:right;"><label for="bloggy_tid_format">' . __('Format p&aring; tiden:') . ' <input style="width: 80px;" id="bloggy_tid_format" name="bloggy_tid_format" type="text" value="'.$tid_format.'" /></label><br /><small>L&auml;s mer om hur man formaterar texten <a href="http://www.w3schools.com/PHP/func_date_date.asp" target="_blank">h&auml;r!</a></small></p>';		
		
		echo '<p style="text-align:right;"><label for="bloggy_timeago">' . __('Tid sen:') . ' <input id="bloggy_timeago" name="bloggy_timeago" type="checkbox"'.$timeago_checked.' /></label>
		<br /><small>Visa hur l&aring;ng tid sedan du postade meddelandet(g&ouml;r tidsformateringen ovan on&ouml;dig!)</small></p
		</p>';		
		
		
		echo '<p style="text-align:right;"><label for="bloggy_v_tid">' . __('G&ouml;m tiden:') . ' <input id="bloggy_v_tid" name="bloggy_v_tid" type="checkbox"'.$v_tid_checked.' /></label></p>';
		
		echo '<p style="text-align:right;"><label for="bloggy_s_links">' . __('G&ouml;r l&auml;nkar klickbara:') . ' <input id="bloggy_s_links" name="bloggy_s_links" type="checkbox"'.$s_links_checked.' /></label></p>';
		
		echo '<p style="text-align:right;"><label for="bloggy_users">' . __('G&ouml;r bloggy-anv&auml;ndare klickbara:') . ' <input id="bloggy_users" name="bloggy_users" type="checkbox"'.$users_checked.' /></label></p>';
		
		echo '<p><a href="http://www.designskolan.net/bloggy-plugin-for-wordpress/" target="_blank"><strong>Rapportera buggar</strong></a></p>';
		
		
		
		
	
		echo '<input type="hidden" id="bloggy-submit" name="bloggy-submit" value="1" />';
	}
	
	// Ser till att Widgeten syns
	register_sidebar_widget(array('BloggyFeed', 'widgets'), 'widget_bloggy');

	// Skapar själva boxen där inställnignarna sker
	register_widget_control(array('BloggyFeed', 'widgets'), 'widget_bloggy_control', 250, 100);
}


add_action('widgets_init', 'widget_bloggy_init');

?>