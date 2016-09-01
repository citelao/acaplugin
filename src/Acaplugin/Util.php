<?php
namespace Acaplugin;

class Util {
	public static function get_groups_multicheck( $field ) {
		$posts = get_posts( array(
			'post_type' => 'acac_group'
		) );

		foreach($posts as $id => $post) {
			$rtn[$post->ID] = $post->post_title;
		}
		
		return $rtn;
	}

	public static function get_groups_dropdown( $field ) {
		return array_merge(
			array(0 => '--'),
			self::get_groups_multicheck( $field )
		);
	}
}