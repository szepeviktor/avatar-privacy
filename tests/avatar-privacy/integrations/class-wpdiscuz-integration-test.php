<?php
/**
 * This file is part of Avatar Privacy.
 *
 * Copyright 2019 Peter Putzer.
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

namespace Avatar_Privacy\Tests\Avatar_Privacy\Integrations;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use Avatar_Privacy\Integrations\WPDiscuz_Integration;

/**
 * Avatar_Privacy\Integrations\WPDiscuz_Integration unit test.
 *
 * @coversDefaultClass \Avatar_Privacy\Integrations\WPDiscuz_Integration
 * @usesDefaultClass \Avatar_Privacy\Integrations\WPDiscuz_Integration
 *
 * @uses ::__construct
 */
class WPDiscuz_Integration_Test extends \Avatar_Privacy\Tests\TestCase {

	/**
	 * The system-under-test.
	 *
	 * @var \Avatar_Privacy\Integrations\WPDiscuz_Integration
	 */
	private $sut;

	/**
	 * A test fixture.
	 *
	 * @var \Avatar_Privacy\Core
	 */
	private $core;

	/**
	 * A test fixture.
	 *
	 * @var \Avatar_Privacy\Components\Comments
	 */
	private $comments;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();

		$filesystem = [
			'uploads' => [
				'delete' => [
					'existing_file.txt'  => 'CONTENT',
				],
			],
			'plugin'  => [
				'public' => [
					'partials' => [
						'wpdiscuz' => [
							'use-gravatar.php' => 'USE_GRAVATAR_MARKUP',
						],
					],
				],
			],
		];

		// Set up virtual filesystem.
		$root = vfsStream::setup( 'root', null, $filesystem );
		set_include_path( 'vfs://root/' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_set_include_path

		// Helper mocks.
		$this->core     = m::mock( \Avatar_Privacy\Core::class );
		$this->comments = m::mock( \Avatar_Privacy\Components\Comments::class );

		// Partially mock system under test.
		$this->sut = m::mock( WPDiscuz_Integration::class, [ $this->core, $this->comments ] )->makePartial()->shouldAllowMockingProtectedMethods();
	}

	/**
	 * Tests ::__construct.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$mock     = m::mock( WPDiscuz_Integration::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$core     = m::mock( \Avatar_Privacy\Core::class );
		$comments = m::mock( \Avatar_Privacy\Components\Comments::class );

		$mock->__construct( $core, $comments );

		$this->assertAttributeSame( $core, 'core', $mock );
		$this->assertAttributeSame( $comments, 'comments', $mock );
	}

	/**
	 * Tests ::check.
	 *
	 * @covers ::check
	 */
	public function test_check() {
		$this->assertFalse( $this->sut->check() );

		Functions\when( 'wpDiscuz' )->justReturn( true );

		$this->assertTrue( $this->sut->check() );
	}

	/**
	 * Tests ::run.
	 *
	 * @covers ::run
	 */
	public function test_run() {
		Actions\expectAdded( 'init' )->once()->with( [ $this->sut, 'init' ] );

		$this->assertNull( $this->sut->run( $this->core ) );
	}

	/**
	 * Tests ::init.
	 *
	 * @covers ::init
	 */
	public function test_init() {
		Functions\expect( 'is_user_logged_in' )->once()->andReturn( false );
		Functions\expect( 'is_admin' )->once()->andReturn( false );

		Actions\expectAdded( 'wpdiscuz_submit_button_before' )->once()->with( [ $this->sut, 'print_gravatar_checkbox' ] );
		Actions\expectAdded( 'wp_enqueue_scripts' )->once()->with( [ $this->sut, 'enqeue_styles_and_scripts' ] );
		Actions\expectAdded( 'wpdiscuz_form_init' )->once()->with( [ $this->sut, 'store_cookie_consent_checkbox' ] );
		Actions\expectAdded( 'wpdiscuz_before_save_commentmeta' )->once()->with( [ $this->sut, 'set_comment_cookies' ] );

		$this->assertNull( $this->sut->init() );
	}

	/**
	 * Tests ::init.
	 *
	 * @covers ::init
	 */
	public function test_init_admin() {
		Functions\expect( 'is_user_logged_in' )->once()->andReturn( false );
		Functions\expect( 'is_admin' )->once()->andReturn( true );

		Actions\expectAdded( 'wpdiscuz_submit_button_before' )->never();
		Actions\expectAdded( 'wp_enqueue_scripts' )->never();
		Actions\expectAdded( 'wpdiscuz_form_init' )->once()->with( [ $this->sut, 'store_cookie_consent_checkbox' ] );
		Actions\expectAdded( 'wpdiscuz_before_save_commentmeta' )->once()->with( [ $this->sut, 'set_comment_cookies' ] );

		$this->assertNull( $this->sut->init() );
	}

	/**
	 * Tests ::init.
	 *
	 * @covers ::init
	 */
	public function test_init_user_logged_in() {
		Functions\expect( 'is_user_logged_in' )->once()->andReturn( true );
		Functions\expect( 'is_admin' )->never();

		Actions\expectAdded( 'wpdiscuz_submit_button_before' )->never();
		Actions\expectAdded( 'wp_enqueue_scripts' )->never();
		Actions\expectAdded( 'wpdiscuz_form_init' )->never();
		Actions\expectAdded( 'wpdiscuz_before_save_commentmeta' )->never();

		$this->assertNull( $this->sut->init() );
	}

	/**
	 * Tests ::print_gravatar_checkbox.
	 *
	 * @covers ::print_gravatar_checkbox
	 */
	public function test_print_gravatar_checkbox() {
		$this->expectOutputString( 'USE_GRAVATAR_MARKUP' );
		$this->assertNull( $this->sut->print_gravatar_checkbox() );
	}

	/**
	 * Tests ::enqeue_styles_and_scripts.
	 *
	 * @covers ::enqeue_styles_and_scripts
	 */
	public function test_enqeue_styles_and_scripts() {
		$version = '6.6.6';

		Functions\expect( 'plugin_dir_url' )->once()->with( \AVATAR_PRIVACY_PLUGIN_FILE )->andReturn( 'some/url' );

		$this->core->shouldReceive( 'get_version' )->once()->andReturn( $version );

		Functions\expect( 'wp_enqueue_script' )->once()->with( 'avatar-privacy-wpdiscuz-use-gravatar', m::type( 'string' ), [ 'jquery' ], $version, true );
		Functions\expect( 'wp_localize_script' )->once()->with( 'avatar-privacy-wpdiscuz-use-gravatar', 'avatarPrivacy', m::type( 'array' ) );

		$this->assertNull( $this->sut->enqeue_styles_and_scripts() );
	}

	/**
	 * Tests ::set_comment_cookies.
	 *
	 * @covers ::set_comment_cookies
	 */
	public function test_set_comment_cookies() {
		$comment  = m::mock( \WP_Comment::class );
		$user     = m::mock( \WP_User::class );
		$consent  = true;
		$checkbox = 'cookie_consent_checkbox';

		$this->setValue( $this->sut, 'cookie_consent_name', $checkbox, WPDiscuz_Integration::class );

		Functions\expect( 'wp_get_current_user' )->once()->andReturn( $user );

		$this->sut->shouldReceive( 'filter_input' )->once()->andReturn( $consent )->with( INPUT_POST, $checkbox, FILTER_VALIDATE_BOOLEAN )->andReturn( $consent );

		$this->comments->shouldReceive( 'set_comment_cookies' )->once()->with( $comment, $user, $consent );

		$this->assertNull( $this->sut->set_comment_cookies( $comment ) );
	}

	/**
	 * Tests ::store_cookie_consent_checkbox.
	 *
	 * @covers ::store_cookie_consent_checkbox
	 */
	public function test_store_cookie_consent_checkbox() {
		$form     = m::mock( \wpdFormAttr\Form::class );
		$checkbox = 'my_consent_checkbox';
		$fields   = [
			'field1_name' => [
				'foo'  => 'bar',
				'type' => '\Some\Fake\Class',
			],
			$checkbox     => [
				'bar'  => 'foo',
				'type' => \wpdFormAttr\Field\CookiesConsent::class,
			],
		];

		$form->shouldReceive( 'initFormFields' )->once();
		$form->shouldReceive( 'getFormCustomFields' )->once()->andReturn( $fields );

		$this->assertNull( $this->sut->store_cookie_consent_checkbox( $form ) );
		$this->assertSame( $checkbox, $this->getValue( $this->sut, 'cookie_consent_name', WPDiscuz_Integration::class ) );
	}
}
