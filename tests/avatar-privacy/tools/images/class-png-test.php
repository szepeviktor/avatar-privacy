<?php
/**
 * This file is part of Avatar Privacy.
 *
 * Copyright 2019-2024 Peter Putzer.
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
 * @package mundschenk-at/avatar-privacy/tests
 * @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Avatar_Privacy\Tests\Avatar_Privacy\Tools\Images;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

use org\bovigo\vfs\vfsStream;

use Avatar_Privacy\Tools\Images\PNG;

use Avatar_Privacy\Tools\Images\Color;
use Avatar_Privacy\Exceptions\PNG_Image_Exception;

/**
 * Avatar_Privacy\Tools\Images\PNG unit test.
 *
 * @uses ::__construct
 * @coversDefaultClass \Avatar_Privacy\Tools\Images\PNG
 * @usesDefaultClass \Avatar_Privacy\Tools\Images\PNG
 *
 * @phpstan-import-type NormalizedHue from Color
 * @phpstan-import-type PercentValue from Color
 * @phpstan-import-type RGBValue from Color
 *
 * @phpstan-type RGBTriple array{ 0: RGBValue, 1: RGBValue, 2: RGBValue }
 */
class PNG_Test extends \Avatar_Privacy\Tests\TestCase {

	/**
	 * The system-under-test.
	 *
	 * @var PNG&m\MockInterface
	 */
	private $sut;

	/**
	 * Necessary helper.
	 *
	 * @var Color&m\MockInterface
	 */
	private $color;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @since 2.3.3 Renamed to `set_up`.
	 */
	protected function set_up() {
		parent::set_up();

		$png_data = \base64_decode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
			'iVBORw0KGgoAAAANSUhEUgAAABwAAAASCAMAAAB/2U7WAAAABl' .
			'BMVEUAAAD///+l2Z/dAAAASUlEQVR4XqWQUQoAIAxC2/0vXZDr' .
			'EX4IJTRkb7lobNUStXsB0jIXIAMSsQnWlsV+wULF4Avk9fLq2r' .
			'8a5HSE35Q3eO2XP1A1wQkZSgETvDtKdQAAAABJRU5ErkJggg=='
		);

		$filesystem = [
			'plugin' => [
				'my_parts_dir' => [
					'somefile.png' => $png_data,
				],
				'public'       => [
					'images' => [
						'monster-id'       => [
							'back.png'    => $png_data,
							'body_1.png'  => $png_data,
							'body_2.png'  => $png_data,
							'arms_S8.png' => $png_data,
							'legs_1.png'  => $png_data,
							'mouth_6.png' => $png_data,
						],
						'monster-id-empty' => [],
					],
				],
			],
		];

		// Set up virtual filesystem.
		vfsStream::setup( 'root', null, $filesystem );

		// Mock helpers.
		$this->color = m::mock( Color::class )->makePartial();

		$this->sut = m::mock( PNG::class, [ $this->color ] )->makePartial()->shouldAllowMockingProtectedMethods();
	}
	/**
	 * Tests ::__construct.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$color = m::mock( Color::class );
		$mock  = m::mock( PNG::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$this->invoke_method( $mock, '__construct', [ $color ] );

		// An attribute of the PNG_Parts_Generator superclass.
		$this->assert_attribute_same( $color, 'color', $mock );
	}

	/**
	 * Tests ::create.
	 *
	 * @covers ::create
	 */
	public function test_create_white() {
		// The base image.
		$width  = 200;
		$height = 100;

		// No problem with the image.
		Functions\expect( 'is_gd_image' )->once()->andReturn( true );

		$image = $this->sut->create( 'white', $width, $height );

		$this->assert_is_gd_image( $image );
		$this->assertSame( $width, \imageSX( $image ) );
		$this->assertSame( $height, \imageSY( $image ) );
		$this->assertSame(
			[
				'red'   => 255,
				'green' => 255,
				'blue'  => 255,
				'alpha' => 0,
			],
			\imageColorsForIndex( $image, \imageColorAt( $image, 1, 1 ) )
		);

		// Clean up.
		\imageDestroy( $image );
	}

	/**
	 * Tests ::create.
	 *
	 * @covers ::create
	 */
	public function test_create_black() {
		// The base image.
		$width  = 200;
		$height = 100;

		// No problem with the image.
		Functions\expect( 'is_gd_image' )->once()->andReturn( true );

		$image = $this->sut->create( 'black', $width, $height );

		$this->assert_is_gd_image( $image );
		$this->assertSame( $width, \imageSX( $image ) );
		$this->assertSame( $height, \imageSY( $image ) );
		$this->assertSame(
			[
				'red'   => 0,
				'green' => 0,
				'blue'  => 0,
				'alpha' => 0,
			],
			\imageColorsForIndex( $image, \imageColorAt( $image, 1, 1 ) )
		);

		// Clean up.
		\imageDestroy( $image );
	}

	/**
	 * Tests ::create.
	 *
	 * @covers ::create
	 */
	public function test_create_transparent() {
		// The base image.
		$width  = 200;
		$height = 100;

		// No problem with the image.
		Functions\expect( 'is_gd_image' )->once()->andReturn( true );

		$image = $this->sut->create( 'transparent', $width, $height );

		$this->assert_is_gd_image( $image );
		$this->assertSame( $width, \imageSX( $image ) );
		$this->assertSame( $height, \imageSY( $image ) );
		$this->assertSame(
			[
				'red'   => 0,
				'green' => 0,
				'blue'  => 0,
				'alpha' => 127,
			],
			\imageColorsForIndex( $image, \imageColorAt( $image, 1, 1 ) )
		);

		// Clean up.
		\imageDestroy( $image );
	}

	/**
	 * Tests ::create.
	 *
	 * @covers ::create
	 */
	public function test_create_invalid_type() {
		// The base image.
		$width  = 200;
		$height = 18;

		// No problem with the image.
		Functions\expect( 'is_gd_image' )->once()->andReturn( true );

		// Expect failure.
		$this->expectException( \InvalidArgumentException::class );

		$image = $this->sut->create( 'yellow', $width, $height );

		// Clean up.
		\imageDestroy( $image );
	}

	/**
	 * Tests ::create_from_file.
	 *
	 * @covers ::create_from_file
	 */
	public function test_create_from_file() {
		// The base image.
		$width  = 28;
		$height = 18;

		// No problem with the image.
		Functions\expect( 'is_gd_image' )->once()->andReturn( true );

		$image = $this->sut->create_from_file( vfsStream::url( 'root/plugin/my_parts_dir/somefile.png' ) );

		$this->assert_is_gd_image( $image );
		$this->assertSame( $width, \imageSX( $image ) );
		$this->assertSame( $height, \imageSY( $image ) );

		// Clean up.
		\imageDestroy( $image );
	}

	/**
	 * Tests ::create_from_file.
	 *
	 * @covers ::create_from_file
	 */
	public function test_create_from_file_invalid() {
		// We know this is not a GD image.
		Functions\expect( 'is_gd_image' )->once()->andReturn( false );

		Functions\expect( 'esc_html' )->once()->andReturnFirstArg();
		$this->expectException( PNG_Image_Exception::class );

		$this->assertNull( $this->sut->create_from_file( '/not/a/valid/PNG' ) );
	}

	/**
	 * Tests ::combine.
	 *
	 * @covers ::combine
	 */
	public function test_combine() {
		// The base image.
		$width  = 200;
		$height = 100;
		$base   = \imageCreateTrueColor( $width, $height );

		// Make the base image white.
		\imageFill( $base, 0, 0, \imageColorAllocate( $base, 255, 255, 255 ) );

		// The second image.
		$image = \imageCreateFromPNG( vfsStream::url( 'root/plugin/my_parts_dir/somefile.png' ) );

		// Store base image data for comparison.
		\ob_start();
		\imagePNG( $base );
		$orig_base_data = ob_get_clean();

		// Everything is fine here.
		Functions\expect( 'is_gd_image' )->twice()->andReturn( true );

		// Run the test.
		$this->assertNull( $this->sut->combine( $base, $image, $width, $height ) );

		// Get the new base image data.
		\ob_start();
		\imagePNG( $base );
		$new_base_data = ob_get_clean();

		// Check that they are different because of the applied image.
		$this->assertNotSame( $orig_base_data, $new_base_data );

		// Clean up.
		\imageDestroy( $base );
	}

	/**
	 * Tests ::combine.
	 *
	 * @covers ::combine
	 */
	public function test_combine_error() {
		// The base image.
		$width  = 200;
		$height = 100;
		$base   = \imageCreateTrueColor( $width, $height );

		// Make the base image white.
		\imageFill( $base, 0, 0, \imageColorAllocate( $base, 255, 255, 255 ) );

		// The second image does not exist.
		$image = 'fakename.png';

		// Store base image data for comparison.
		\ob_start();
		\imagePNG( $base );
		$orig_base_data = ob_get_clean();

		// We know the base is OK, but the second image is not a GD image.
		Functions\expect( 'is_gd_image' )->with( $base )->once()->andReturn( true );
		Functions\expect( 'is_gd_image' )->with( $image )->once()->andReturn( false );

		// Expect failure.
		$this->expectException( \InvalidArgumentException::class );

		// Run the test.
		$this->sut->combine( $base, $image, $width, $height );

		// Get the new base image data.
		\ob_start();
		\imagePNG( $base );
		$new_base_data = ob_get_clean();

		// Check that they are different because of the applied image.
		$this->assertSame( $orig_base_data, $new_base_data );

		// Clean up.
		\imageDestroy( $base );
	}

	/**
	 * Tests ::fill_hsl.
	 *
	 * @covers ::fill_hsl
	 *
	 * @uses \Avatar_Privacy\Tools\Images\Color::hsl_to_rgb
	 */
	public function test_fill_hsl() {
		// Input.
		$hue        = 345;
		$saturation = 99;
		$lightness  = 10;
		$x          = 23;
		$y          = 42;

		// The image.
		$width  = 200;
		$height = 100;
		$image  = \imageCreate( $width, $height );

		// We need a valid image.
		$this->assert_is_gd_image( $image );

		// No problem with the image.
		Functions\expect( 'is_gd_image' )->with( $image )->once()->andReturn( true );

		// Run the test.
		$this->sut->fill_hsl( $image, $hue, $saturation, $lightness, $x, $y );

		// Clean up.
		\imageDestroy( $image );
	}

	/**
	 * Tests ::fill_hsl.
	 *
	 * @covers ::fill_hsl
	 *
	 * @uses ::hsl_to_rgb
	 */
	public function test_fill_hsl_not_an_image(): void {
		// Input.
		$hue        = 0;
		$saturation = 99;
		$lightness  = 10;
		$x          = 23;
		$y          = 42;

		// The image.
		$resource = 'foo';

		// We know this is not a GD image.
		Functions\expect( 'is_gd_image' )->with( $resource )->once()->andReturn( false );

		// Expect failure.
		$this->expectException( \InvalidArgumentException::class );

		// @phpstan-ignore-next-line -- The resource is not a resource on purpose.
		$this->sut->fill_hsl( $resource, $hue, $saturation, $lightness, $x, $y );
	}

	/**
	 * Tests ::fill_hsl.
	 *
	 * @covers ::fill_hsl
	 *
	 * @uses \Avatar_Privacy\Tools\Images\Color::hsl_to_rgb
	 */
	public function test_fill_hsl_error(): void {
		// Input.
		$hue        = 0;
		$saturation = 99;
		$lightness  = 10;
		$x          = 23;
		$y          = 42;

		// The image.
		$width  = 200;
		$height = 100;
		$image  = \imageCreate( $width, $height );

		// Eat up all color slots.
		for ( $i = 0; $i < 256; ++$i ) {
			\imageColorAllocate( $image, 0, 0, 0 );
		}

		// We need a valid image.
		$this->assert_is_gd_image( $image );

		// We know this is a GD image.
		Functions\expect( 'is_gd_image' )->with( $image )->once()->andReturn( true );

		// Expect failure.
		Functions\expect( 'esc_html' )->once()->andReturnFirstArg();
		$this->expectException( PNG_Image_Exception::class );

		$this->sut->fill_hsl( $image, $hue, $saturation, $lightness, $x, $y );

		// Clean up.
		\imageDestroy( $image );
	}

	/**
	 * Tests ::hsl_to_rgb.
	 *
	 * @covers ::hsl_to_rgb
	 */
	public function test_hsl_to_rgb(): void {
		// Testdata.
		$hue        = 123;
		$saturation = 75;
		$lightness  = 23;
		$result     = [ 47, 11, 254 ]; // Not the real conversion!

		// Set up expectations.
		Functions\expect( '_deprecated_function' )->once();
		$this->color->shouldReceive( 'hsl_to_rgb' )->once()->with( $hue, $saturation, $lightness )->andReturn( $result );

		$this->assertSame( $result, $this->sut->hsl_to_rgb( $hue, $saturation, $lightness ) );
	}
}
