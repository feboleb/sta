<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_FAQ' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_FAQ {

		/**
		 * Render Schema.
		 *
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $data, $post ) {
			global $post;
			$schema  = array();
			$post_id = get_the_ID();
			if ( isset( $data['question-answer'] ) && ! empty( $data['question-answer'] ) ) {
				$schema['@context'] = 'https://schema.org';
				$schema['type']     = 'FAQPage';
				foreach ( $data['question-answer'] as $key => $value ) {
					$schema['mainEntity'][ $key ]['@type']                   = 'Question';
					$schema['mainEntity'][ $key ]['name']                    = $value['question'];
					$schema['mainEntity'][ $key ]['acceptedAnswer']['@type'] = 'Answer';
					$schema['mainEntity'][ $key ]['acceptedAnswer']['text']  = $value['answer'];

				}
			}

			return apply_filters( 'wp_schema_pro_schema_faq', $schema, $data, $post );
		}

	}
}
