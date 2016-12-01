<?php
namespace Acaplugin;

class Util {
	public static function get_groups_multicheck( $field ) {
		global $post_id;
		$posts = get_posts( array(
			'post_type' => 'acac_group',
			'connected_items' => $post_id,
			'nopaging' => true
		) );

		if( empty( $posts ) ) {
			return array();
		}

		foreach($posts as $id => $post) {
			$rtn[$post->ID] = $post->post_title;
		}

		return $rtn;
	}

	public static function get_groups_dropdown( $field ) {
		return array(0 => '--') + self::get_groups_multicheck( $field );
	}
}