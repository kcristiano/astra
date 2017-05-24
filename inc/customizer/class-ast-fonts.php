<?php
/**
 * Helper class for font settings.
 *
 * @package     Astra
 * @author      Astra
 * @copyright   Copyright (c) 2017, Astra
 * @link        http://wpastra.com/
 * @since       Astra 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Astra Fonts
 */
final class Ast_Fonts {

	/**
	 * Get fonts to generate.
	 *
	 * @since 1.0.0
	 * @var array $fonts
	 */
	static private $fonts = array();

	/**
	 * Adds data to the $fonts array for a font to be rendered.
	 *
	 * @since 1.0.0
	 * @param string $name The name key of the font to add.
	 * @param array  $variants An array of weight variants.
	 * @return void
	 */
	static public function add_font( $name, $variants = array() ) {

		if ( 'inherit' == $name ) {
			return;
		}

		if ( is_array( $variants ) ) {
			$key = array_search( 'inherit' , $variants );
			if ( false !== $key ) {

				unset( $variants[ $key ] );

				if ( ! in_array( 400, $variants ) ) {
					$variants[] = 400;
				}
			}
		} elseif ( 'inherit' == $variants ) {
			$variants = 400;
		}

		if ( isset( self::$fonts[ $name ] ) ) {
			foreach ( (array) $variants as $variant ) {
				if ( ! in_array( $variant, self::$fonts[ $name ]['variants'] ) ) {
					self::$fonts[ $name ]['variants'][] = $variant;
				}
			}
		} else {
			self::$fonts[ $name ] = array(
				'variants' => (array) $variants,
			);
		}
	}

	/**
	 * Get Fonts
	 */
	static public function get_fonts() {

		do_action( 'ast_get_fonts' );
		return apply_filters( 'ast_add_fonts', self::$fonts );
	}

	/**
	 * Renders the <link> tag for all fonts in the $fonts array.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function render_fonts() {

		$font_list = self::get_fonts();

		$google_fonts = array();
		$font_subset = array();

		foreach ( $font_list as $name => $font ) {
			if ( ! empty( $name ) ) {

				// Add font variants.
				$google_fonts[ $name ] = $font['variants'];

				// Add Subset.
				$subset = apply_filters( 'ast_font_subset', '', $name );
				if ( ! empty( $subset ) ) {
					$font_subset[] = $subset;
				}
			}
		}

		$google_font_url = self::google_fonts_url( $google_fonts, $font_subset );
		wp_enqueue_style( 'ast-google-fonts', $google_font_url, array(), null, 'all' );
	}

	/**
	 * Google Font URL
	 * Combine multiple google font in one URL
	 *
	 * @link https://shellcreeper.com/?p=1476
	 * @param array $fonts 		Google Fonts array.
	 * @param array $subsets 	Font's Subsets array.
	 *
	 * @return string
	 */
	static public function google_fonts_url( $fonts, $subsets = array() ) {

		/* URL */
		$base_url  = '//fonts.googleapis.com/css';
		$font_args = array();
		$family    = array();

		/* Format Each Font Family in Array */
		foreach ( $fonts as $font_name => $font_weight ) {
			$font_name = str_replace( ' ', '+', $font_name );
			if ( ! empty( $font_weight ) ) {
				if ( is_array( $font_weight ) ) {
					$font_weight = implode( ',', $font_weight );
				}
				$family[] = trim( $font_name . ':' . urlencode( trim( $font_weight ) ) );
			} else {
				$family[] = trim( $font_name );
			}
		}

		/* Only return URL if font family defined. */
		if ( ! empty( $family ) ) {

			/* Make Font Family a String */
			$family = implode( '|', $family );

			/* Add font family in args */
			$font_args['family'] = $family;

			/* Add font subsets in args */
			if ( ! empty( $subsets ) ) {

				/* format subsets to string */
				if ( is_array( $subsets ) ) {
					$subsets = implode( ',', $subsets );
				}

				$font_args['subset'] = urlencode( trim( $subsets ) );
			}

			return add_query_arg( $font_args, $base_url );
		}

		return '';
	}
}
