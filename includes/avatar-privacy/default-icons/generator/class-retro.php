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

namespace Avatar_Privacy\Default_Icons\Generator;

use Colors\RandomColor;

/**
 * Generates a "retro" SVG icon based on a hash.
 *
 * @since 1.0.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Retro implements Generator {

	/**
	 * The identicon instance.
	 *
	 * @var \Identicon\Identicon
	 */
	private $identicon;

	/**
	 * Creates a new instance.
	 */
	public function __construct() {
		$this->identicon = new \Identicon\Identicon( new \Identicon\Generator\SvgGenerator() );
	}

	/**
	 * Builds an icon based on the given seed returns the image data.
	 *
	 * @param  string $seed The seed data (hash).
	 * @param  int    $size Optional. The size in pixels. Default 128 (but really ignored).
	 *
	 * @return string
	 */
	public function build( $seed, $size = 128 ) {
		// Initialize random number with seed.
		\mt_srand( (int) hexdec( substr( $seed, 0, 8 ) ) );

		// Generate icon.
		$result = $this->identicon->getImageData( $seed, $size,
			/* @scrutinizer ignore-type */ RandomColor::one( [ 'luminosity' => 'bright' ] ),
			/* @scrutinizer ignore-type */ RandomColor::one( [ 'luminosity' => 'light' ] ) );

		// Restore randomness.
		\mt_srand();

		return $result;
	}

}