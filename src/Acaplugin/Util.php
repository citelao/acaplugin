<?php
namespace Acaplugin;

class Util {
	public static function get_groups_dropdown( $field ) {
		$posts = get_posts( array(
			'post_type' => 'acac_group'
		) );

		$rtn = array(
			0 => '--'
		);
		foreach($posts as $id => $post) {
			$rtn[$post->ID] = $post->post_title;
		}
		
		return $rtn;
	}
}