<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/** @var string $description */
?>

<div class="edit-form-info">
	<svg width="14px" height="14px" viewBox="0 0 14 14" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
		<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<g class="info-circle" transform="translate(-565, -159)" fill-rule="nonzero">
				<g transform="translate(508, 45)">
					<g id="icon-info" transform="translate(57, 114)">
						<path d="M7,0 C10.8659932,0 14,3.13400675 14,7 C14,10.8659932 10.8659932,14 7,14 C3.13400675,14 0,10.8659932 0,7 C0,3.13400675 3.13400675,0 7,0 Z M7.25,7 L5.75,7 C5.33578644,7 5,7.33578644 5,7.75 C5,8.16421356 5.33578644,8.5 5.75,8.5 L6.5,8.5 L6.5,10.25 C6.5,10.6642136 6.83578644,11 7.25,11 C7.66421356,11 8,10.6642136 8,10.25 L8,7.75 C8,7.33578644 7.66421356,7 7.25,7 Z M7,3 C6.44771525,3 6,3.44771525 6,4 C6,4.55228475 6.44771525,5 7,5 C7.55228475,5 8,4.55228475 8,4 C8,3.44771525 7.55228475,3 7,3 Z"></path>
					</g>
				</g>
			</g>
		</g>
	</svg>
</div>
<div class="tooltip-content" role="tooltip">
	<?php
	if ( is_string( $description ) ) {
		// phpcs:ignore
		echo $description;
	}
	?>
</div>
