<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'search.php');

// Manage "search" url and redirections accordingly
if (!empty($globals['base_search_url'])) {
	if (!empty($_SERVER['PATH_INFO']) ) {
		$q = preg_quote($globals['base_url'].$globals['base_search_url']);
		if(preg_match("{^$q}", $_SERVER['SCRIPT_URL'])) {
			$_REQUEST['q'] = urldecode(substr($_SERVER['PATH_INFO'], 1));
		}
	} elseif (!empty($_REQUEST['q'])) {
		$_REQUEST['q'] = substr(trim(strip_tags($_REQUEST['q'])), 0, 300);
		if (!preg_match('/\//', $_REQUEST['q']) ) {  // Freaking Apache rewrite that translate //+ to just one /
														// for example "http://" is converted to http:/
														// also it cheats the paht_info and redirections, so don't redirect
			header('Location: http://'. get_server_name().$globals['base_url'].$globals['base_search_url'].urlencode($_REQUEST['q']));
			die;
		}
	} elseif (isset($_REQUEST['q'])) {
		header('Location: http://'. get_server_name().$globals['base_url']);
		die;
	}
}


$page_size = 20;
$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;

$globals['noindex'] = true;

$response = get_search_links(false, $offset, $page_size);
do_header(_('búsqueda de'). ' "'.htmlspecialchars($_REQUEST['words']).'"');
do_tabs('main',_('búsqueda'), htmlentities($_SERVER['REQUEST_URI']));

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_rss_box();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

// Search form
echo '<div class="genericform" style="text-align: center">';
echo '<fieldset>';

print_search_form();
if(!empty($_REQUEST['q'])) {
	echo '<div style="font-size:85%;margin-top: 5px">';
	echo _('encontrados').': '.$response['rows'].', '._('tiempo total').': '.sprintf("%1.3f",$response['time']).' '._('segundos');
	echo '&nbsp;<a href="'.$globals['base_url'].'rss2.php?q='.urlencode($_REQUEST['q']).'" rel="rss"><img src="'.$globals['base_url'].'img/common/feed-icon-12x12.png" alt="rss2" height="12" width="12"  style="vertical-align:top"/></a>';
	echo '</div>';
}

echo '</fieldset>';
echo '</div>';


$link = new Link;
if ($response['ids']) {
	$rows = min($response['rows'], 1000);
	foreach($response['ids'] as $link_id) {
		$link->id=$link_id;
		$link->read();
		$link->print_summary('full', $link->status == 'published' ? 100 : 20);
	}
}

do_pages($rows, $page_size);
echo '</div>';
do_footer_menu();
do_footer();

function print_search_form() {
	echo '<form id="thisform" action="">';
	echo '<input type="text" name="q" value="'.htmlspecialchars($_REQUEST['words']).'" class="form-full"/>';
	echo '<input class="button" type="submit" value="'._('buscar').'" />';

	// Print field options
	echo '<br /><select name="p">';
	switch ($_REQUEST['p']) {
		case 'url':
		case 'tags':
		case 'title':
		case 'site':
			echo '<option value="'.$_REQUEST['p'].'" selected="selected">'.$_REQUEST['p'].'</option>';
			break;
		default:
			echo '<option value="" selected="selected">'._('campos...').'</option>';
			break;
	}
	foreach (array('url', 'tags', 'title', 'site') as $p) {
		if ($p != $_REQUEST['p']) {
			echo '<option value="'.$p.'">'.$p.'</option>';
		}
	}
	echo '<option value="">'._('todo el texto').'</option>';
	echo '</select>';

	// Print status options
	echo '&nbsp;&nbsp;<select name="s">';
	switch ($_REQUEST['s']) {
		case 'published':
		case 'queued':
		case 'discard':
		case 'autodiscard':
		case 'abuse':
			echo '<option value="'.$_REQUEST['s'].'" selected="selected">'.$_REQUEST['s'].'</option>';
			break;
		default:
			echo '<option value="" selected="selected">'._('estado...').'</option>';
			break;
	}
	foreach (array('published', 'queued', 'discard', 'autodiscard', 'abuse') as $p) {
		if ($p != $_REQUEST['s']) {
			echo '<option value="'.$p.'">'.$p.'</option>';
		}
	}
	echo '<option value="">'._('todas').'</option>';
	echo '</select>';

	// Select period
	echo '&nbsp;&nbsp;<select name="h">';
	if($_REQUEST['h'] > 0) {
		$date = get_date(time()-$_REQUEST['h']*3600);
		echo '<option value="'.$_REQUEST['h'].'" selected="selected">'.$date.'</option>';
	} else {
		echo '<option value="" selected="selected">'._('período...').'</option>';
	}
	echo '<option value="'.intval(24).'">'._('24 horas').'</option>';
	echo '<option value="'.intval(48).'">'._('48 horas').'</option>';
	echo '<option value="'.intval(24*7).'">'._('última semana').'</option>';
	echo '<option value="'.intval(24*30).'">'._('última mes').'</option>';
	echo '<option value="'.intval(24*180).'">'._('6 meses').'</option>';
	echo '<option value="'.intval(24*365).'">'._('1 año').'</option>';
	echo '<option value="">'._('todas').'</option>';
	echo '</select>';


	echo '&nbsp;&nbsp;<select name="o">';
	if($_REQUEST['o'] == 'date') {
		echo '<option value="date">'._('por fecha').'</option>';
		echo '<option value="">'._('por relevancia').'</option>';
	} else {
		echo '<option value="">'._('por relevancia').'</option>';
		echo '<option value="date">'._('por fecha').'</option>';
	}
	echo '</select>';

	echo '</form>';
}

?>
