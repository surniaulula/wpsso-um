<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'SucomPluginData' ) ) {

	class SucomPluginData {

		public $author;
		public $author_homepage;
		public $banners;
		public $download_url;
		public $homepage;
		public $icons;
		public $id = 0;
		public $last_updated;
		public $license;
		public $name;
		public $num_ratings;
		public $plugin;
		public $rating;
		public $requires;
		public $sections;
		public $slug;
		public $tested;
		public $upgrade_notice;
		public $version;

		public static function data_from_json( $json_encoded ) {

			$json_data = json_decode( $json_encoded, $assoc = false );

			if ( empty( $json_data ) || ! is_object( $json_data ) )  {

				return null;
			}

			if ( empty( $json_data->plugin ) || empty( $json_data->version ) ) {

				return null;
			}

			$plugin_data = new SucomPluginData();

			foreach( get_object_vars( $json_data ) as $key => $value ) {

				$plugin_data->$key = $value;
			}

			return $plugin_data;
		}

		public function json_to_wp(){

			$plugin_data = new StdClass;

			foreach ( array(
				'id',
				'name',
				'slug',
				'plugin',
				'version',
				'tested',
				'requires',
				'homepage',
				'download_url',
				'author_homepage',
				'upgrade_notice',
				'banners',
				'icons',
				'rating',
				'num_ratings',
				'last_updated',
				'sections',
			) as $prop_name ) {

				if ( isset( $this->$prop_name ) ) {

					if ( 'download_url' === $prop_name ) {

						$plugin_data->download_link = $this->download_url;

					} elseif ( 'author_homepage' === $prop_name ) {

						if ( false === strpos( $this->author, '<a href' ) ) {

							$plugin_data->author = sprintf( '<a href="%s">%s</a>', $this->author_homepage, $this->author );

						} else {

							$plugin_data->author = $this->author;
						}

					} elseif ( 'sections' === $prop_name && empty( $this->$prop_name ) ) {

						$plugin_data->$prop_name = array( 'description' => '' );

					} elseif ( is_object( $this->$prop_name ) ) {

						$plugin_data->$prop_name = get_object_vars( $this->$prop_name );

					} else {

						$plugin_data->$prop_name = $this->$prop_name;
					}

				} elseif ( 'author_homepage' === $prop_name ) {

					$plugin_data->author = $this->author;
				}
			}

			return $plugin_data;
		}
	}
}
