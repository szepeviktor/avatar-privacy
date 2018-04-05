<?php
/**
 * This file is part of Avatar Privacy.
 *
 * Copyright 2018 Peter Putzer.
 * Copyright 2007-2008 Shamus Young.
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

namespace Avatar_Privacy\Default_Icons\Wavatars;

use Scriptura\Color\Types\HSL;

/**
 * A wavatar generator.
 *
 * @since 1.0.0
 */
class Wavatars {

	const SIZE                = 80;
	const WAVATAR_BACKGROUNDS = 4;
	const WAVATAR_FACES       = 11;
	const WAVATAR_BROWS       = 8;
	const WAVATAR_EYES        = 13;
	const WAVATAR_PUPILS      = 11;
	const WAVATAR_MOUTHS      = 19;

	/**
	 * Creates a new Wavatars generator.
	 */
	public function __construct() {
		$this->monster_parts_dir = dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/public/images/wavatars';
	}

	/**
	 * Helper function for building a wavatar. This loads an image and adds it to
	 * our composite using the given color values.
	 *
	 * @param  resource $base The wavatar image resource.
	 * @param  string   $part The name of the body part image file (without the `.png` extension).
	 */
	private function apply_image( $base, $part ) {

		$file = "{$this->monster_parts_dir}/{$part}.png";
		$im   = @imagecreatefrompng( $file );
		if ( ! $im ) {
			return;
		}
		imagecopy( $base,$im, 0, 0, 0, 0, self::SIZE, self::SIZE );
		imagedestroy( $im );
	}

	/**
	 * Build the avatar icon.
	 *
	 * @param  string $seed The hashed mail address.
	 * @param  int    $size The icon size in pixels.
	 *
	 * @return string       The image data.
	 */
	public function build( $seed, $size = 80 ) {
		// Look at the seed (an md5 hash) and use pairs of digits to determine our
		// "random" parts and colors.
		$face      = 1 + ( hexdec( substr( $seed,  1, 2 ) ) % ( self::WAVATAR_FACES ) );
		$bg_color  = ( ( hexdec( substr( $seed,  3, 2 ) ) % 240 ) / 255 * 360 );
		$fade      = 1 + ( hexdec( substr( $seed,  5, 2 ) ) % ( self::WAVATAR_BACKGROUNDS ) );
		$wav_color = ( ( hexdec( substr( $seed,  7, 2 ) ) % 240 ) / 255 * 360 );
		$brow      = 1 + ( hexdec( substr( $seed,  9, 2 ) ) % ( self::WAVATAR_BROWS ) );
		$eyes      = 1 + ( hexdec( substr( $seed, 11, 2 ) ) % ( self::WAVATAR_EYES ) );
		$pupil     = 1 + ( hexdec( substr( $seed, 13, 2 ) ) % ( self::WAVATAR_PUPILS ) );
		$mouth     = 1 + ( hexdec( substr( $seed, 15, 2 ) ) % ( self::WAVATAR_MOUTHS ) );

		// Create backgound.
		$avatar = imagecreatetruecolor( self::SIZE, self::SIZE );

		// Pick a random color for the background.
		$c  = ( new HSL( $bg_color, 94, 20 ) )->toRGB();
		$bg = imagecolorallocate( $avatar, $c->red(), $c->green(), $c->blue() );
		imagefill( $avatar, 1, 1, $bg );
		$c  = ( new HSL( $wav_color, 94, 66 ) )->toRGB();
		$bg = imagecolorallocate( $avatar, $c->red(), $c->green(), $c->blue() );

		// Now add the various layers onto the image.
		$this->apply_image( $avatar, "fade$fade" );
		$this->apply_image( $avatar, "mask$face" );
		imagefill( $avatar, (int) ( self::SIZE / 2 ), (int) ( self::SIZE / 2 ), $bg );
		$this->apply_image( $avatar, "shine$face" );
		$this->apply_image( $avatar, "brow$brow" );
		$this->apply_image( $avatar, "eyes$eyes" );
		$this->apply_image( $avatar, "pupils$pupil" );
		$this->apply_image( $avatar, "mouth$mouth" );

		// Resize if needed.
		$out = $avatar;
		if ( self::SIZE !== $size ) {
			$out = imagecreatetruecolor( $size, $size );
			imagecopyresampled( $out, $avatar, 0, 0, 0, 0, $size, $size, self::SIZE, self::SIZE );
			imagedestroy( $avatar );
		}

		// Convert image to PNG format.
		$stream = new \Bcn\Component\StreamWrapper\Stream();
		imagepng( $out, \fopen( $stream, 'w' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen

		// Clean up.
		imagedestroy( $out );

		// Return image.
		return $stream->getContent();
	}
}
