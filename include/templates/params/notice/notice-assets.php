<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>

<style>
	.wpb-notice.updated {
		position: relative;
		display:flex;
		gap: 20px;
		align-items: center;
		margin: 0;
		border-left-color: #3172A7;
		padding: 20px;
	}
	.wpb-notice-image > img {
		width: 100px;
		height: 100px;
	}
	.wpb-notice-text {
		color: #656565;
	}
	.wpb-notice .wpb-notice-text .title {
		font-size: 18px;
		font-weight: 500;
		margin: 0;
		padding: 0;
	}
	.wpb-notice .wpb-notice-text .wpb-notice-context {
		font-size: 16px;
		margin: 6px 0 14px 0;
	}
	.wpb-notice-text button {
		border: none;
		margin: 0;
		padding: 10px 15px;
		text-align: inherit;
		font: inherit;
		appearance: none;
		font-size: 16px;
		border-radius: 5px;
		cursor: pointer;
	}
</style>
<script>
	window.vcAdminNonce = '<?php echo esc_js( vc_generate_nonce( 'vc-admin-nonce' ) ); ?>';

	(function ( $ ) {
		var addNoticeToDisableList = function ( notice_id ) {
			var data = {
				notice_id: notice_id,
				action: 'wpb_add_notice_to_close_list',
				_vcnonce: window.vcAdminNonce
			};
			$.ajax( {
				type: 'POST',
				url: window.ajaxurl,
				data: data,
			}).fail( function ( response ) {
				console.error( 'Failed to add notice to disable list', response)
			});
		};

		$( document ).off( 'click.wpb-notice-dismiss' ).on( 'click.wpb-notice-dismiss', '.wpb-notice-dismiss', function ( e ) {
			e.preventDefault();
			var $el = jQuery( this ).closest(
				'.wpb-notice' );
			$el.fadeTo( 100, 0, function () {
				$el.slideUp( 100, function () {
					$el.remove();
				} );
			} );
			addNoticeToDisableList( $el.attr('id').replace('wpb-notice-', '') );
		});
		$( document ).off( 'click.wpb-notice-button' ).on( 'click.wpb-notice-button', '.wpb-notice-button', function ( e ) {
			e.preventDefault();
			var $el = jQuery( this )

			var link = $el.attr('data-notice-link')

			if ( link ) {
				window.open(link, '_blank')
			}
		});
		$( document ).off( 'click.wpb-notice-image' ).on( 'click.wpb-notice-image', '.wpb-notice-image', function ( e ) {
			e.preventDefault();
			var $el = jQuery( this )

			var link = $el.attr('data-notice-link')

			if ( link ) {
				window.open(link, '_blank')
			}
		});
	})( window.jQuery );
</script>
