<?php
/**
 * This file is part of Avatar Privacy.
 *
 * Copyright 2018 Peter Putzer.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  ***
 *
 * @package mundschenk-at/avatar-privacy
 * @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Avatar_Privacy\Data_Storage;

/**
 * A filesystem caching handler.
 *
 * @since 1.0.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Filesystem_Cache {

	const CACHE_DIR = 'avatar-privacy/';

	/**
	 * The base directory for the filesystem cache.
	 *
	 * @var string
	 */
	private $base_dir;

	/**
	 * The base URL for accessing cached files.
	 *
	 * @var string
	 */
	private $base_url;

	/**
	 * Information about the uploads directory.
	 *
	 * @var array
	 */
	private $upload_dir;

	/**
	 * Creates a new instance.
	 */
	public function __construct() {
		$this->get_base_dir();
	}

	/**
	 * Retrieves the base directory for caching files.
	 *
	 * @throws \RuntimeException A RuntimeException is thrown if the cache directory does not exist and can't be created.
	 *
	 * @return string
	 */
	protected function get_base_dir() {
		if ( empty( $this->base_dir ) ) {
			$upload_dir     = $this->get_upload_dir();
			$this->base_dir = "{$upload_dir['basedir']}/" . self::CACHE_DIR;

			if ( ! \wp_mkdir_p( $this->base_dir ) ) {
				throw new \RuntimeException( "The cache directory {$this->base_dir} could not be created." );
			}
		}

		return $this->base_dir;
	}

	/**
	 * Retrieves the base URL for accessing cached files.
	 *
	 * @return string
	 */
	protected function get_base_url() {
		if ( empty( $this->base_url ) ) {
			$upload_dir     = $this->get_upload_dir();
			$this->base_url = "{$upload_dir['baseurl']}/" . self::CACHE_DIR;
		}

		return $this->base_url;
	}

	/**
	 * Retrieves information about the upload directory.
	 *
	 * @return array
	 */
	private function get_upload_dir() {
		if ( empty( $this->upload_dir ) ) {
			$this->upload_dir = \wp_get_upload_dir();
		}

		return $this->upload_dir;
	}

	/**
	 * Stores data in the filesystem cache.
	 *
	 * @param  string $filename The filename (including any sub directory).
	 * @param  mixed  $data     The data.
	 * @param  bool   $force    Optional. The cached file will only be overwritten if set to true. Default false.
	 *
	 * @return bool             True if the file was successfully stored in the cache, false otherwise.
	 */
	public function set( $filename, $data, $force = false ) {
		$file = $this->get_base_dir() . $filename;
		$path = \dirname( $file );

		if ( \file_exists( $file ) && ! $force ) {
			return true;
		}

		if ( ! \wp_mkdir_p( $path ) || false === \file_put_contents( $file, $data, LOCK_EX ) ) { // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Retrieves the URL for the cached file.
	 *
	 * @param  string $filename The filename (including any sub directory).
	 *
	 * @return string
	 */
	public function get_url( $filename ) {
		return $this->get_base_url() . $filename;
	}
}