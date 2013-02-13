<?php
/*
Plugin Name: Snippi Shortcode
Version: 1.0
Plugin URI: http://www.halgatewood.com/snippi-embed
Description: Embed Snippi\'s from snippi.com into your blog with this handy shortcode
Author: Hal Gatewood
Author URI: http://www.halgatewood.com

----

Copyright 2010-2013 Hal Gatewood

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
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

---
*/



// THE SHORTCODE
function snippi_shortcode($atts, $content = null)
{
	$id = $atts['id'];

	if(!isset($id) OR $id == "") { return $content; } // NO ID
	$type = (isset($atts['type']) == "raw") ? "raw" : "pretty";

	// QUEUE CSS AND JS
	wp_enqueue_style( 'snippi_css', plugins_url() . '/snippi-embeds/snippi.css' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'prettify', plugins_url() . '/snippi-embeds/js/prettify.js' );
	wp_enqueue_script( 'prettyPrint', plugins_url() . '/snippi-embeds/js/prettyPrint.js' );
	
	// GET TRANSIENT
	if(get_transient('snippi_' . $type . '_' . $id))
	{
		$code = get_transient('snippi_' . $type . '_' . $id);
	}
	else
	{
		// GET CODE FROM SNIPPI.COM
		if($type == "raw")
		{
			$code = file_get_contents("http://snippi.com/raw/{$id}");	
		}
		else
		{
			include_once('simple_html_dom.php');
			$html = file_get_html("http://snippi.com/s/{$id}"); 
			$code = $html->find('.code-wrapper', 0)->innertext;
			$code = '<div class="snippi-item"><div class="code-wrapper">' . $code . '</div></div>';
		}
		
		//SET TRANSIENT
		set_transient('snippi_' . $type . '_' . $id, $code, 2629743);
	}

	$embed = '<div id="snippi-' . $id . '" class="snippi snippi ' . $type . '">';
	do_action( 'snippi_before_html', $id );
	$embed .= apply_filters( 'snippi_html', $code );
	do_action( 'snippi_after_html', $id );
	$embed .= '</div>';

	return $embed;
}

add_shortcode('snippi', 'snippi_shortcode'); 

?>