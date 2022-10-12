<?php
namespace The7\Adapters\Elementor;

defined( 'ABSPATH' ) || exit;

trait With_Post_Excerpt {

	protected $excerpt_words_limit = null;

	/**
	 * Return post excerpt with $words_limit words.
	 *
	 * @param int $words_limit
	 *
	 * @return string
	 */
	protected function get_post_excerpt( $words_limit = null ) {
		global $post;

		$post_back = $post;

		$words_limit = absint( $words_limit );
		$this->excerpt_words_limit = $words_limit;

		add_filter( 'excerpt_length', [ $this, 'modify_autoexertp_words_limit' ] );

		$excerpt = get_the_excerpt();

		remove_filter( 'excerpt_length', [ $this, 'modify_autoexertp_words_limit' ] );

		if ( $words_limit ) {
			$excerpt = wp_trim_words( $excerpt, $words_limit );
		}

		$excerpt = apply_filters( 'the_excerpt', $excerpt );

		// Restore original post in case some shortcode in the content will change it globally.
		$post = $post_back;

		return $excerpt;
	}

	/**
	 * @param int $limit
	 *
	 * @return int
	 */
	public function modify_autoexertp_words_limit( $limit ) {
		if ( $this->excerpt_words_limit ) {
			return $this->excerpt_words_limit;
		}

		return $limit;
	}

}
