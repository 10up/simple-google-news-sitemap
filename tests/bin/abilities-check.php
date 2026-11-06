<?php
/**
 * Manual test for the Abilities API abilities.
 *
 * Run with: wp eval-file wp-content/plugins/simple-google-news-sitemap/tests/bin/abilities-check.php
 *
 * The script creates temporary users and a post, then deletes them.
 *
 * @package simple-google-news-sitemap
 */

$results = [];
$check   = function ( $name, $pass, $detail = '' ) use ( &$results ) {
	$results[] = [ $name, $pass ? 'PASS' : 'FAIL', $detail ];
};

$get_ability   = wp_get_ability( 'simple-google-news-sitemap/get-sitemap' );
$regen_ability = wp_get_ability( 'simple-google-news-sitemap/regenerate-sitemap' );

$check( 'Category registered', (bool) wp_get_ability_category( 'simple-google-news-sitemap' ) );
$check( 'Get ability registered', (bool) $get_ability );
$check( 'Regenerate ability registered', (bool) $regen_ability );

$users   = [];
$post_id = 0;

try {
	$suffix = wp_generate_password( 4, false );
	foreach ( [ 'subscriber', 'editor', 'administrator' ] as $role ) {
		$users[ $role ] = wp_insert_user(
			[
				'user_login' => "sgns_test_{$role}_{$suffix}",
				'user_pass'  => wp_generate_password( 24 ),
				'user_email' => "sgns_{$role}_{$suffix}@example.test",
				'role'       => $role,
			]
		);
	}

	$post_id = wp_insert_post(
		[
			'post_type'   => 'post',
			'post_status' => 'publish',
			'post_title'  => "SGNS test post {$suffix}",
		]
	);

	$run = function ( $ability, $uid ) {
		wp_set_current_user( $uid );
		$allowed = $ability->check_permissions();
		return true === $allowed ? $ability->execute() : $allowed;
	};

	// Permissions.
	$res = $run( $get_ability, 0 );
	$check( 'Logged-out user cannot get the sitemap', true !== $res && ! is_array( $res ) );
	$res = $run( $get_ability, $users['subscriber'] );
	$check( 'Subscriber cannot get the sitemap', true !== $res && ! is_array( $res ) );
	$res = $run( $regen_ability, $users['editor'] );
	$check( 'Editor cannot regenerate the sitemap', true !== $res && ! is_array( $res ) );

	// Regenerate as admin.
	$res = $run( $regen_ability, $users['administrator'] );
	$check( 'Administrator can regenerate', is_array( $res ) && ! empty( $res['success'] ), is_wp_error( $res ) ? $res->get_error_message() : '' );
	$check( 'Regenerate counts the new post', is_array( $res ) && $res['count'] >= 1, wp_json_encode( $res ) );

	// Get as editor.
	$res    = $run( $get_ability, $users['editor'] );
	$titles = is_array( $res ) ? wp_list_pluck( $res['items'], 'title' ) : [];
	$check( 'Editor can get the sitemap', is_array( $res ) );
	$check( 'Sitemap lists the new post', in_array( "SGNS test post {$suffix}", $titles, true ) );

	// REST endpoint.
	$request = new WP_REST_Request( 'GET', '/wp-abilities/v1/abilities/simple-google-news-sitemap/get-sitemap/run' );
	wp_set_current_user( 0 );
	$check( 'REST run returns 401 or 403 when logged out', in_array( rest_do_request( $request )->get_status(), [ 401, 403 ], true ) );
	wp_set_current_user( $users['editor'] );
	$check( 'REST run returns 200 for an editor', 200 === rest_do_request( $request )->get_status() );

	// Regenerate over REST is POST. Only administrators may run it.
	$post = new WP_REST_Request( 'POST', '/wp-abilities/v1/abilities/simple-google-news-sitemap/regenerate-sitemap/run' );
	wp_set_current_user( 0 );
	$check( 'REST regenerate is denied when logged out', in_array( rest_do_request( $post )->get_status(), [ 401, 403 ], true ) );
	wp_set_current_user( $users['editor'] );
	$check( 'REST regenerate is denied for an editor', 403 === rest_do_request( $post )->get_status() );
	wp_set_current_user( $users['administrator'] );
	$check( 'REST regenerate works for an administrator', 200 === rest_do_request( $post )->get_status() );

	// Unknown input properties are rejected.
	$res = $regen_ability->execute( [ 'unexpected' => 1 ] );
	$check( 'Unknown input property is rejected', is_wp_error( $res ) );

	// Markup in titles is stripped.
	$strip = static function () {
		return [
			[
				'url'      => 'https://example.com/a',
				'title'    => '<script>x</script><b>Clean</b>',
				'modified' => 1700000000,
			],
		];
	};
	add_filter( 'simple_google_news_sitemap_data', $strip );
	wp_set_current_user( $users['editor'] );
	$res = $get_ability->execute();
	remove_filter( 'simple_google_news_sitemap_data', $strip );
	$check( 'Markup is stripped from titles', is_array( $res ) && 1 === $res['count'] && false === strpos( $res['items'][0]['title'], '<' ), wp_json_encode( $res ) );
} finally {
	if ( $post_id ) {
		wp_delete_post( $post_id, true );
	}
	require_once ABSPATH . 'wp-admin/includes/user.php';
	foreach ( $users as $uid ) {
		wp_delete_user( $uid );
	}
	delete_transient( 'simple_google_news_sitemap_data' );
	wp_set_current_user( 0 );
}

$failed = 0;
foreach ( $results as $row ) {
	WP_CLI::log( sprintf( '%s  %s  %s', $row[1], $row[0], $row[2] ) );
	if ( 'FAIL' === $row[1] ) {
		++$failed;
	}
}

if ( $failed ) {
	WP_CLI::error( "$failed check(s) failed." );
}
WP_CLI::success( 'All checks passed.' );
