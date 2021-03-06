<?php
/**
 * This file is part of Avatar Privacy.
 *
 * Copyright 2018-2020 Peter Putzer.
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

use Avatar_Privacy\Tools\Template as T;

/**
 * Required template variables:
 *
 * @var string $nonce            The nonce itself.
 * @var string $action           The nonce action.
 * @var string $upload_field     The name of the uploader `<input>` element.
 * @var string $erase_field      The name of the erase checkbox `<input>` element.
 * @var int    $user_id          The ID of the edited user.
 * @var string $current_avatar   The previously set user avatar.
 * @var bool   $can_upload       Whether the currently active user can upload files.
 * @var bool   $uploads_disabled Whether the uploads system has been disabled completely..
 */

if ( $uploads_disabled ) {
	// We integrate with some other plugin, so skip the description.
	$description = '';
} elseif ( $can_upload ) {
	if ( empty( $current_avatar ) ) {
		$description = \sprintf(
			/* translators: 1: gravatar.com URL, 2: rel attribute, 3: target attribute */
			\__( 'No local profile picture is set. Use the upload field to add a local profile picture or change your profile picture on <a href="%1$s" rel="%2$s" target="%3$s">Gravatar</a>.', 'avatar-privacy' ),
			\__( 'https://en.gravatar.com/', 'avatar-privacy' ),
			T::get_gravatar_link_rel(),
			T::get_gravatar_link_target()
		);
	} else {
		$description = \sprintf(
			/* translators: 1: gravatar.com URL, 2: rel attribute, 3: target attribute */
			\__( 'Replace the local profile picture by uploading a new avatar, or erase it (falling back on <a href="%1$s" rel="%2$s" target="%3$s">Gravatar</a>) by checking the delete option.', 'avatar-privacy' ),
			\__( 'https://en.gravatar.com/', 'avatar-privacy' ),
			T::get_gravatar_link_rel(),
			T::get_gravatar_link_target()
		);
	}
} else {
	if ( empty( $current_avatar ) ) {
		$description = \sprintf(
			/* translators: 1: gravatar.com URL, 2: rel attribute, 3: target attribute */
			\__( 'No local profile picture is set. Change your profile picture on <a href="%1$s" rel="%2$s" target="%3$s">Gravatar</a>.', 'avatar-privacy' ),
			\__( 'https://en.gravatar.com/', 'avatar-privacy' ),
			T::get_gravatar_link_rel(),
			T::get_gravatar_link_target()
		);
	} else {
		$description = \__( 'You do not have media management permissions. To change your local profile picture, contact the site administrator.', 'avatar-privacy' );
	}
}

?>
<div class="avatar-pricacy-profile-picture-upload">
	<?php echo /* @scrutinizer ignore-type */ \get_avatar( $user_id ); ?>

	<?php if ( $can_upload ) : ?>
		<?php \wp_nonce_field( $action, $nonce ); ?>
		<input type="file" id="<?php echo \esc_attr( $upload_field ); ?>" name="<?php echo \esc_attr( $upload_field ); ?>" accept="image/*" />
		<?php if ( ! empty( $current_avatar ) ) : ?>
			<label>
				<input type="checkbox" class="checkbox" id="<?php echo \esc_attr( $erase_field ); ?>" name="<?php echo \esc_attr( $erase_field ); ?>" value="true" />
				<?php \esc_html_e( 'Delete local avatar picture.', 'avatar-privacy' ); ?>
			</label>
		<?php endif; ?>
	<?php endif; ?>
	<span class="description indicator-hint" style="width:100%;margin-left:0;">
		<?php echo \wp_kses( $description, T::ALLOWED_HTML_LABEL ); ?>
	</span>
</div>
<?php
