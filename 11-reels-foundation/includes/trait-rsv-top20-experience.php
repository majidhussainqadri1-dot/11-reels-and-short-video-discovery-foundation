<?php
defined( 'ABSPATH' ) || exit;

trait RSV_Top20_Experience_Trait {
	public static function rest_pre_dispatch( $result, $server, $request ) {
		unset( $server );
		if ( null !== $result ) {
			return $result;
		}

		$route  = $request->get_route();
		$method = $request->get_method();


		if ( '/rsv/v1/history' === $route && 'GET' === $method && is_user_logged_in() ) {
			RSV_Helpers::no_cache_private();
			return rest_ensure_response(
				self::history_cursor(
					get_current_user_id(),
					$request->get_param( 'limit' ) ?: 50,
					$request->get_param( 'cursor' ) ?: ''
				)
			);
		}

		if ( 'POST' === $method && is_user_logged_in() && preg_match( '#^/rsv/v1/reels/reel_[a-f0-9-]{36}/(view-session|progress)$#', $route ) && ! empty( self::preferences()['history_paused'] ) ) {
			RSV_Helpers::no_cache_private();
			return rest_ensure_response( array( 'paused' => true, 'session_id' => '', 'saved' => false ) );
		}

		// Do not run object-dependent publication validation before authorization.
		// Unauthorized callers must reach the canonical permission callback without
		// receiving an object-existence or readiness oracle from this filter.
		if ( 'POST' === $method && preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/publish$#', $route, $match ) ) {
			$reel = RSV_Repository::find( $match[1], true );
			if ( $reel && RSV_Security::can( RSV_Contracts::CAP_PUBLISH, $reel, 'publish_reel' ) ) {
				$gate = self::publication_gate( $reel );
				if ( is_wp_error( $gate ) ) {
					return $gate;
				}
			}
		}

		return $result;
	}

	public static function rest_post_dispatch( $response, $server, $request ) {
		unset( $server );
		$route = $request->get_route();
		if ( in_array( $route, array( '/rsv/v1/history', '/rsv/v1/preferences', '/rsv/v1/value-insights' ), true ) && $response instanceof WP_REST_Response ) {
			$response->header( 'Cache-Control', 'private, no-store, max-age=0' );
			$response->header( 'Pragma', 'no-cache' );
			$response->header( 'X-Robots-Tag', 'noindex, nofollow, noarchive' );
		}

		if ( '/rsv/v1/reels' !== $route || 'POST' !== $request->get_method() || is_wp_error( $response ) || ! ( $response instanceof WP_REST_Response ) || $response->get_status() >= 400 ) {
			return $response;
		}

		$data = $response->get_data();
		if ( ! is_array( $data ) || empty( $data['id'] ) ) {
			return $response;
		}

		$reel = RSV_Repository::find( $data['id'], true );
		if ( ! $reel ) {
			return self::rest_error_response( RSV_Helpers::error( 'rsv_create_reel_missing', __( 'The created Reel could not be verified.', RSV_TEXT_DOMAIN ), 500 ) );
		}

		$context = self::save_context( $reel, (array) $request->get_json_params() );
		if ( is_wp_error( $context ) ) {
			$compensation = self::compensate_failed_reel_create( $reel, $request->get_header( 'Idempotency-Key' ), $context );
			if ( is_wp_error( $compensation ) ) return self::rest_error_response( $compensation );
			if ( true !== $compensation ) return self::rest_error_response( RSV_Helpers::error( 'rsv_create_compensation_failed', __( 'The incomplete Reel could not be rolled back safely.', RSV_TEXT_DOMAIN ), 500 ) );
			return self::rest_error_response( $context );
		}

		$data['source_safety'] = $context;
		$response->set_data( $data );
		return $response;
	}

	private static function compensate_failed_reel_create( $reel, $idempotency_key, $error ) {
		if ( ! is_array( $reel ) || 'draft' !== ( $reel['status'] ?? '' ) || absint( $reel['owner_id'] ?? 0 ) !== get_current_user_id() ) {
			return false;
		}

		global $wpdb;
		$reel_id = absint( $reel['id'] );
		return RSV_DB::transaction(
			static function () use ( $wpdb, $reel_id, $idempotency_key, $error ) {
				if ( false === $wpdb->delete( RSV_Helpers::table( 'reel_context' ), array( 'reel_id' => $reel_id ), array( '%d' ) ) ) return RSV_Helpers::error( 'rsv_create_compensation_context_failed', __( 'The incomplete Reel context could not be rolled back safely.', RSV_TEXT_DOMAIN ), 500 );
				if ( false === $wpdb->delete( RSV_Helpers::table( 'outbox' ), array( 'object_type' => 'reel', 'object_id' => $reel_id ), array( '%s', '%d' ) ) ) return RSV_Helpers::error( 'rsv_create_compensation_outbox_failed', __( 'The incomplete Reel event evidence could not be rolled back safely.', RSV_TEXT_DOMAIN ), 500 );
				if ( false === $wpdb->delete( RSV_Helpers::table( 'audit' ), array( 'object_type' => 'reel', 'object_id' => $reel_id ), array( '%s', '%d' ) ) ) return RSV_Helpers::error( 'rsv_create_compensation_audit_failed', __( 'The incomplete Reel audit evidence could not be rolled back safely.', RSV_TEXT_DOMAIN ), 500 );
				$deleted = $wpdb->delete(
					RSV_Helpers::table( 'reels' ),
					array( 'id' => $reel_id, 'owner_id' => get_current_user_id(), 'status' => 'draft' ),
					array( '%d', '%d', '%s' )
				);
				if ( $idempotency_key ) {
					$idempotency_deleted = $wpdb->delete(
						RSV_Helpers::table( 'idempotency' ),
						array(
							'actor_id' => get_current_user_id(),
							'scope_key' => 'create_reel',
							'idem_key' => RSV_Helpers::text( $idempotency_key, 120 ),
						),
						array( '%d', '%s', '%s' )
					);
					if ( false === $idempotency_deleted ) return RSV_Helpers::error( 'rsv_create_compensation_idempotency_failed', __( 'The incomplete Reel request state could not be rolled back safely.', RSV_TEXT_DOMAIN ), 500 );
				}
				if ( 1 !== $deleted ) {
					return RSV_Helpers::error( 'rsv_create_compensation_failed', __( 'The incomplete Reel could not be rolled back safely.', RSV_TEXT_DOMAIN ), 500 );
				}
				if ( ! RSV_Helpers::audit( 'reel_create_compensation', $reel_id, 'rollback', 'draft', 'removed', is_wp_error( $error ) ? $error->get_error_code() : 'context_failure' ) ) {
					return RSV_Helpers::error( 'rsv_create_compensation_evidence_failed', __( 'The incomplete Reel was removed but rollback evidence could not be recorded.', RSV_TEXT_DOMAIN ), 500 );
				}
				return true;
			}
		);
	}

	private static function rest_error_response( $error ) {
		$data   = is_wp_error( $error ) ? (array) $error->get_error_data() : array();
		$status = isset( $data['status'] ) ? absint( $data['status'] ) : 500;
		return new WP_REST_Response(
			array(
				'code' => is_wp_error( $error ) ? $error->get_error_code() : 'rsv_unknown_error',
				'message' => is_wp_error( $error ) ? $error->get_error_message() : __( 'The request could not be completed.', RSV_TEXT_DOMAIN ),
				'data' => $data,
			),
			$status
		);
	}

	public static function rest_stories( $request ) {
		$data = self::public_stories( $request->get_param( 'limit' ) ?: 20, $request->get_param( 'cursor' ) ?: '' );
		return is_wp_error( $data ) ? $data : rest_ensure_response( $data );
	}

	public static function rest_story( $request ) {
		$data = self::story_dto( self::story_row( $request['id'], true ) );
		return $data ? rest_ensure_response( $data ) : RSV_Helpers::error( 'rsv_story_not_found', __( 'Story not found.', RSV_TEXT_DOMAIN ), 404 );
	}

	public static function rest_story_create( $request ) {
		$result = self::create_story( (array) $request->get_json_params(), $request->get_header( 'Idempotency-Key' ) );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	public static function rest_story_publish( $request ) {
		$result = self::publish_story( $request['id'], absint( $request->get_param( 'version' ) ) );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	public static function rest_highlight_add( $request ) {
		$result = self::add_highlight( $request['id'], $request->get_param( 'title' ), $request->get_param( 'highlight_id' ), $request->get_header( 'Idempotency-Key' ) );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	public static function rest_highlights( $request ) {
		return rest_ensure_response( array( 'items' => self::public_highlights( absint( $request['owner_id'] ), $request->get_param( 'limit' ) ?: 20 ) ) );
	}

	public static function rest_responses( $request ) {
		return rest_ensure_response( array( 'items' => self::public_responses( $request['id'], $request->get_param( 'limit' ) ?: 20 ) ) );
	}

	public static function rest_response_create( $request ) {
		$result = self::create_response( $request['id'], (array) $request->get_json_params(), $request->get_header( 'Idempotency-Key' ) );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	public static function rest_response_publish( $request ) {
		$result = self::publish_response( $request['id'], absint( $request->get_param( 'version' ) ) );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	public static function rest_preferences() {
		RSV_Helpers::no_cache_private();
		return rest_ensure_response( self::preferences() );
	}

	public static function rest_preferences_update( $request ) {
		RSV_Helpers::no_cache_private();
		$result = self::save_preferences( (array) $request->get_json_params() );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	public static function rest_signal( $request ) {
		$result = self::record_signal( RSV_Repository::find( $request['id'], true ), $request->get_param( 'signal' ) );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	public static function rest_value_insights() {
		RSV_Helpers::no_cache_private();
		return rest_ensure_response( array( 'items' => self::value_insights( get_current_user_id() ) ) );
	}

	public static function register_assets() {
		wp_register_style( 'rsv-top20', RSV_URL . 'assets/css/rsv-top20.css', array( 'rsv' ), RSV_VERSION );
		wp_register_script( 'rsv-top20', RSV_URL . 'assets/js/rsv-top20.js', array( 'rsv' ), RSV_VERSION, true );
		if ( self::is_context() ) {
			self::enqueue_assets();
		}
	}

	private static function is_context() {
		if ( is_admin() ) {
			return false;
		}
		if ( get_query_var( 'rsv_reel' ) || get_query_var( 'rsv_history' ) || get_query_var( 'rsv_insights' ) || get_query_var( 'rsv_stories' ) || get_query_var( 'rsv_story' ) || get_query_var( 'rsv_highlights' ) || get_query_var( 'rsv_preferences' ) || get_query_var( 'rsv_value_insights' ) || get_query_var( 'rsv_respond' ) ) {
			return true;
		}
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return false !== strpos( $request_uri, '/reels' );
	}

	private static function enqueue_assets() {
		wp_enqueue_style( 'rsv' );
		wp_enqueue_script( 'rsv' );
		wp_enqueue_style( 'rsv-top20' );
		wp_enqueue_script( 'rsv-top20' );
		wp_localize_script(
			'rsv-top20',
			'RSV_TOP20',
			array(
				'root' => esc_url_raw( rest_url( RSV_Contracts::API_NAMESPACE ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'loggedIn' => is_user_logged_in(),
				'storiesUrl' => home_url( '/reels/stories/' ),
				'preferencesUrl' => home_url( '/account/reels/preferences/' ),
				'insightsUrl' => home_url( '/account/reels/value-insights/' ),
				'respondUrl' => home_url( '/reels/respond/' ),
				'preferences' => self::preferences(),
				'canSubmit' => self::can_submit(),
				'i18n' => array(
					'naturalStop' => __( 'Natural pause: take a moment before continuing.', RSV_TEXT_DOMAIN ),
					'sessionLimit' => __( 'Your chosen Reel session limit has been reached.', RSV_TEXT_DOMAIN ),
					'lateNight' => __( 'Late-night reminder: consider resting and returning later.', RSV_TEXT_DOMAIN ),
					'continue' => __( 'Continue by choice', RSV_TEXT_DOMAIN ),
					'error' => __( 'Additional Reel context is temporarily unavailable.', RSV_TEXT_DOMAIN ),
					'stories' => __( 'Stories / Status', RSV_TEXT_DOMAIN ),
					'wellbeing' => __( 'Well-being', RSV_TEXT_DOMAIN ),
					'valueInsights' => __( 'Value insights', RSV_TEXT_DOMAIN ),
					'sourceSafety' => __( 'Source, transcript and safety', RSV_TEXT_DOMAIN ),
					'source' => __( 'Open source', RSV_TEXT_DOMAIN ),
					'safety' => __( 'Safety', RSV_TEXT_DOMAIN ),
					'transcript' => __( 'Open transcript', RSV_TEXT_DOMAIN ),
					'response' => __( 'Create an attributed response/remix', RSV_TEXT_DOMAIN ),
				),
			)
		);
	}

	public static function enhance_shortcode( $output, $tag, $attr, $match ) {
		unset( $attr, $match );
		if ( 'rsv_create' === $tag ) {
			$fields = '<fieldset class="rsv-top20-context-fields"><legend>' . esc_html__( 'Source, accessibility and response context', RSV_TEXT_DOMAIN ) . '</legend>';
			$fields .= '<label>' . esc_html__( 'Source label', RSV_TEXT_DOMAIN ) . '<input name="source_title" maxlength="255" required></label>';
			$fields .= '<label>' . esc_html__( 'Source URL (optional)', RSV_TEXT_DOMAIN ) . '<input name="source_url" type="url" maxlength="500"></label>';
			$fields .= '<label>' . esc_html__( 'Transcript URL', RSV_TEXT_DOMAIN ) . '<input name="transcript_url" type="url" maxlength="500"></label>';
			$fields .= '<label>' . esc_html__( 'Safety summary', RSV_TEXT_DOMAIN ) . '<textarea name="safety_summary" maxlength="2000" required></textarea></label>';
			$fields .= '<label><input name="allow_response" type="checkbox" value="1" checked> ' . esc_html__( 'Allow attributed educational responses/remixes', RSV_TEXT_DOMAIN ) . '</label>';
			$fields .= '<label>' . esc_html__( 'Patient-media reuse policy', RSV_TEXT_DOMAIN ) . '<select name="patient_reuse_policy"><option value="explicit-consent-required">' . esc_html__( 'Explicit verified consent required', RSV_TEXT_DOMAIN ) . '</option><option value="not-applicable">' . esc_html__( 'Not a patient case', RSV_TEXT_DOMAIN ) . '</option><option value="disabled">' . esc_html__( 'Responses disabled', RSV_TEXT_DOMAIN ) . '</option></select></label></fieldset>';
			$output = preg_replace( '/<\/form>/', $fields . '</form>', $output, 1 );
		}
		if ( 'rsv_reels' === $tag ) {
			$output .= self::render_context_index();
		}
		if ( 'rsv_insights' === $tag && self::can_insights() ) {
			$output .= self::render_value_insights();
		}
		return $output;
	}

	private static function render_context_index() {
		$data = RSV_Repository::feed( array( 'limit' => 12, 'sort' => 'recommended' ) );
		if ( is_wp_error( $data ) || empty( $data['items'] ) ) {
			return '';
		}
		$html = '<section class="rsv-context-index"><h2>' . esc_html__( 'Sources and safety for this page', RSV_TEXT_DOMAIN ) . '</h2><ul>';
		foreach ( $data['items'] as $item ) {
			$context = $item['source_safety'] ?? array();
			$html .= '<li><strong>' . esc_html( $item['title'] ) . '</strong>: ' . esc_html( $context['source_title'] ?? __( 'Source pending review', RSV_TEXT_DOMAIN ) ) . '</li>';
		}
		return $html . '</ul></section>';
	}

	public static function single_context_before_footer() {
		if ( ! get_query_var( 'rsv_reel' ) ) {
			return;
		}
		$reel = RSV_Repository::find( get_query_var( 'rsv_reel' ), true );
		if ( RSV_Security::can_view_reel( $reel ) ) {
			echo wp_kses_post( self::render_context_card( $reel ) );
		}
	}

	private static function render_context_card( $reel ) {
		$context = self::public_context( $reel );
		$html = '<section class="rsv-context-card"><h2>' . esc_html__( 'Source, transcript and safety', RSV_TEXT_DOMAIN ) . '</h2><p><strong>' . esc_html__( 'Source', RSV_TEXT_DOMAIN ) . ':</strong> ' . esc_html( $context['source_title'] ?: __( 'Pending reviewed source label', RSV_TEXT_DOMAIN ) );
		if ( $context['source_url'] ) {
			$html .= ' — <a data-rsv-source-open data-reel="' . esc_attr( $reel['public_id'] ) . '" rel="noopener noreferrer nofollow" href="' . esc_url( $context['source_url'] ) . '">' . esc_html__( 'Open source', RSV_TEXT_DOMAIN ) . '</a>';
		}
		$html .= '</p><p><strong>' . esc_html__( 'Safety', RSV_TEXT_DOMAIN ) . ':</strong> ' . esc_html( $context['safety_summary'] ) . '</p>';
		if ( $context['transcript_url'] ) {
			$html .= '<p><a rel="noopener noreferrer nofollow" href="' . esc_url( $context['transcript_url'] ) . '">' . esc_html__( 'Open transcript', RSV_TEXT_DOMAIN ) . '</a></p>';
		}
		if ( $context['response_allowed'] && self::can_submit() ) {
			$html .= '<p><a href="' . esc_url( add_query_arg( 'source', $reel['public_id'], home_url( '/reels/respond/' ) ) ) . '">' . esc_html__( 'Create an attributed response/remix', RSV_TEXT_DOMAIN ) . '</a></p>';
		}
		return $html . '</section>';
	}

	public static function route_request() {
		if ( get_query_var( 'rsv_stories' ) ) {
			$body = self::can_submit() ? self::render_story_form() : '';
			self::render_page( __( 'Stories / Status', RSV_TEXT_DOMAIN ), $body . self::render_stories() );
		}
		if ( get_query_var( 'rsv_story' ) ) {
			$story = self::story_row( get_query_var( 'rsv_story' ), true );
			$data  = self::story_dto( $story );
			if ( ! $data ) status_header( 404 );
			$body = $data ? self::render_story_dto( $data ) : '<p>' . esc_html__( 'Story unavailable.', RSV_TEXT_DOMAIN ) . '</p>';
			if ( $data && is_array( $story ) && absint( $story['owner_id'] ) === get_current_user_id() && self::can_submit() ) $body .= self::render_highlight_form( $story );
			self::render_page( __( 'Story', RSV_TEXT_DOMAIN ), $body );
		}
		if ( get_query_var( 'rsv_highlights' ) ) self::render_page( __( 'Highlights', RSV_TEXT_DOMAIN ), self::render_highlights( absint( get_query_var( 'rsv_highlights' ) ) ) );
		if ( get_query_var( 'rsv_preferences' ) ) self::render_page( __( 'Reel well-being and privacy', RSV_TEXT_DOMAIN ), self::render_preferences(), true );
		if ( get_query_var( 'rsv_value_insights' ) ) self::render_page( __( 'Value insights', RSV_TEXT_DOMAIN ), self::render_value_insights(), true );
		if ( get_query_var( 'rsv_respond' ) ) self::render_page( __( 'Attributed Reel response', RSV_TEXT_DOMAIN ), self::render_response_form(), true );
	}

	private static function render_page( $title, $body, $private = false ) {
		if ( $private ) RSV_Helpers::no_cache_private();
		self::enqueue_assets();
		add_filter( 'pre_get_document_title', static function () use ( $title ) { return $title; } );
		get_header();
		echo '<main class="rsv-shell rsv-top20-page"><nav class="rsv-local-nav"><a href="' . esc_url( home_url( '/reels/' ) ) . '">' . esc_html__( 'Reels', RSV_TEXT_DOMAIN ) . '</a> <a href="' . esc_url( home_url( '/reels/stories/' ) ) . '">' . esc_html__( 'Stories / Status', RSV_TEXT_DOMAIN ) . '</a> <a href="' . esc_url( home_url( '/account/reels/preferences/' ) ) . '">' . esc_html__( 'Well-being', RSV_TEXT_DOMAIN ) . '</a></nav><h1>' . esc_html( $title ) . '</h1>' . $body . '</main>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- body is generated by internal escaped renderers.
		get_footer();
		exit;
	}

	private static function render_story_form() {
		$options = '';
		foreach ( RSV_Contracts::STORY_TYPES as $type ) $options .= '<option value="' . esc_attr( $type ) . '">' . esc_html( RSV_Helpers::label( $type ) ) . '</option>';
		return '<section class="rsv-story-create"><h2>' . esc_html__( 'Create Story / Status', RSV_TEXT_DOMAIN ) . '</h2><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="rsv_top20_story"><input type="hidden" name="idempotency_key" value="' . esc_attr( wp_generate_uuid4() ) . '">' . wp_nonce_field( 'rsv_top20_story', '_rsv_nonce', true, false ) . '<label>' . esc_html__( 'Source Reel public ID', RSV_TEXT_DOMAIN ) . '<input name="reel_id" required></label><label>' . esc_html__( 'Story type', RSV_TEXT_DOMAIN ) . '<select name="story_type">' . $options . '</select></label><label>' . esc_html__( 'Title', RSV_TEXT_DOMAIN ) . '<input name="title" maxlength="255" required></label><label>' . esc_html__( 'Body', RSV_TEXT_DOMAIN ) . '<textarea name="body" maxlength="2000"></textarea></label><label>' . esc_html__( 'Internal destination URL (optional)', RSV_TEXT_DOMAIN ) . '<input name="destination_url" type="url" maxlength="500"></label><label>' . esc_html__( 'Hours before expiry', RSV_TEXT_DOMAIN ) . '<input name="expires_in_hours" type="number" min="1" max="24" value="24"></label><button>' . esc_html__( 'Submit Story for review', RSV_TEXT_DOMAIN ) . '</button></form></section>';
	}

	private static function render_highlight_form( $story ) {
		return '<section class="rsv-highlight-create"><h2>' . esc_html__( 'Add to Highlight', RSV_TEXT_DOMAIN ) . '</h2><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="rsv_top20_highlight"><input type="hidden" name="story_id" value="' . esc_attr( $story['public_id'] ) . '"><input type="hidden" name="idempotency_key" value="' . esc_attr( wp_generate_uuid4() ) . '">' . wp_nonce_field( 'rsv_top20_highlight', '_rsv_nonce', true, false ) . '<label>' . esc_html__( 'New Highlight title', RSV_TEXT_DOMAIN ) . '<input name="title" maxlength="160"></label><label>' . esc_html__( 'Existing Highlight public ID (optional)', RSV_TEXT_DOMAIN ) . '<input name="highlight_id" maxlength="80"></label><button>' . esc_html__( 'Add Story to Highlight', RSV_TEXT_DOMAIN ) . '</button></form></section>';
	}

	private static function render_stories() {
		$data = self::public_stories( 30, '' );
		if ( is_wp_error( $data ) || empty( $data['items'] ) ) return '<p>' . esc_html__( 'No active Stories are available.', RSV_TEXT_DOMAIN ) . '</p>';
		$html = '';
		foreach ( $data['items'] as $item ) $html .= self::render_story_dto( $item );
		return $html;
	}

	private static function render_story_dto( $data ) {
		return '<article class="rsv-story"><h2><a href="' . esc_url( $data['url'] ) . '">' . esc_html( $data['title'] ) . '</a></h2><p>' . esc_html( $data['body'] ) . '</p><p><a href="' . esc_url( $data['reel']['url'] ) . '">' . esc_html__( 'Open source Reel', RSV_TEXT_DOMAIN ) . '</a></p></article>';
	}

	private static function render_highlights( $user_id ) {
		$items = self::public_highlights( $user_id, 30 );
		if ( ! $items ) return '<p>' . esc_html__( 'No consent-current Highlights are available.', RSV_TEXT_DOMAIN ) . '</p>';
		$html = '';
		foreach ( $items as $item ) {
			$html .= '<section><h2>' . esc_html( $item['title'] ) . '</h2>';
			foreach ( $item['items'] as $story ) $html .= self::render_story_dto( $story );
			$html .= '</section>';
		}
		return $html;
	}

	private static function render_preferences() {
		if ( ! is_user_logged_in() ) return '<p>' . esc_html__( 'Sign in to control private history and well-being settings.', RSV_TEXT_DOMAIN ) . '</p>';
		$preferences = self::preferences();
		$disabled = $preferences['mandatory_youth_safe'] ? ' disabled' : '';
		$html = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="rsv_top20_preferences"><input type="hidden" name="version" value="' . absint( $preferences['version'] ) . '">' . wp_nonce_field( 'rsv_top20_preferences', '_rsv_nonce', true, false );
		$html .= '<label><input type="checkbox" name="youth_safe" value="1"' . checked( $preferences['youth_safe'], true, false ) . $disabled . '> ' . esc_html__( 'Youth-safe recommendations', RSV_TEXT_DOMAIN ) . '</label>';
		$html .= '<label><input type="checkbox" name="history_paused" value="1"' . checked( $preferences['history_paused'], true, false ) . '> ' . esc_html__( 'Pause private watch history', RSV_TEXT_DOMAIN ) . '</label>';
		$html .= '<label>' . esc_html__( 'Session reminder minutes', RSV_TEXT_DOMAIN ) . '<input type="number" name="session_limit_minutes" min="5" max="60" value="' . absint( $preferences['session_limit_minutes'] ) . '"></label>';
		$html .= '<label>' . esc_html__( 'Natural stop after Reel changes', RSV_TEXT_DOMAIN ) . '<input type="number" name="natural_stop_every" min="5" max="25" value="' . absint( $preferences['natural_stop_every'] ) . '"></label>';
		$html .= '<label><input type="checkbox" name="late_night_reminder" value="1"' . checked( $preferences['late_night_reminder'], true, false ) . '> ' . esc_html__( 'Late-night reminder', RSV_TEXT_DOMAIN ) . '</label><button>' . esc_html__( 'Save preferences', RSV_TEXT_DOMAIN ) . '</button></form>';
		if ( $preferences['mandatory_youth_safe'] ) $html .= '<p>' . esc_html__( 'Youth-safe mode is required by the active age claim.', RSV_TEXT_DOMAIN ) . '</p>';
		return $html;
	}

	private static function render_response_form() {
		if ( ! self::can_submit() ) return '<p>' . esc_html__( 'Verified publishing access is required.', RSV_TEXT_DOMAIN ) . '</p>';
		$source = isset( $_GET['source'] ) ? sanitize_text_field( wp_unslash( $_GET['source'] ) ) : '';
		$idempotency_key = wp_generate_uuid4();
		return '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="rsv_top20_response"><input type="hidden" name="idempotency_key" value="' . esc_attr( $idempotency_key ) . '">' . wp_nonce_field( 'rsv_top20_response', '_rsv_nonce', true, false ) . '<label>' . esc_html__( 'Source Reel public ID', RSV_TEXT_DOMAIN ) . '<input name="source_reel_id" value="' . esc_attr( $source ) . '" required></label><label>' . esc_html__( 'Your response Reel public ID', RSV_TEXT_DOMAIN ) . '<input name="response_reel_id" required></label><label>' . esc_html__( 'Attribution text', RSV_TEXT_DOMAIN ) . '<input name="attribution_text" maxlength="500"></label><label><input type="checkbox" name="patient_reuse_consent" value="1"> ' . esc_html__( 'Verified patient-media reuse consent exists where required', RSV_TEXT_DOMAIN ) . '</label><button>' . esc_html__( 'Submit attributed response for review', RSV_TEXT_DOMAIN ) . '</button></form>';
	}

	private static function render_value_insights() {
		if ( ! self::can_insights() ) return '<p>' . esc_html__( 'Creator insight access is restricted.', RSV_TEXT_DOMAIN ) . '</p>';
		$rows = self::value_insights( get_current_user_id() );
		if ( ! $rows ) return '<p>' . esc_html__( 'No Reel insight rows are available.', RSV_TEXT_DOMAIN ) . '</p>';
		$html = '<table><thead><tr><th>' . esc_html__( 'Reel', RSV_TEXT_DOMAIN ) . '</th><th>' . esc_html__( 'Completion', RSV_TEXT_DOMAIN ) . '</th><th>' . esc_html__( 'Source opens', RSV_TEXT_DOMAIN ) . '</th><th>' . esc_html__( 'Shares', RSV_TEXT_DOMAIN ) . '</th><th>' . esc_html__( 'Saves', RSV_TEXT_DOMAIN ) . '</th><th>' . esc_html__( 'Meaningful comments', RSV_TEXT_DOMAIN ) . '</th><th>' . esc_html__( 'Harm reports', RSV_TEXT_DOMAIN ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $row ) {
			$html .= '<tr><td>' . esc_html( $row['title'] ) . '</td>';
			if ( $row['insufficient_data'] ) {
				$html .= '<td colspan="6">' . esc_html__( 'Insufficient aggregate data', RSV_TEXT_DOMAIN ) . '</td>';
			} else {
				$html .= '<td>' . absint( $row['completions'] ) . '/' . absint( $row['views'] ) . '</td><td>' . absint( $row['source_opens'] ) . '</td><td>' . absint( $row['shares'] ) . '</td><td>' . ( null === $row['saves'] ? esc_html__( 'Unavailable', RSV_TEXT_DOMAIN ) : absint( $row['saves'] ) ) . '</td><td>' . ( null === $row['meaningful_comments'] ? esc_html__( 'Unavailable', RSV_TEXT_DOMAIN ) : absint( $row['meaningful_comments'] ) ) . '</td><td>' . absint( $row['harm_reports'] ) . '</td>';
			}
			$html .= '</tr>';
		}
		return $html . '</tbody></table>';
	}

	private static function redirect_with_status( $url, $result ) {
		wp_safe_redirect( add_query_arg( is_wp_error( $result ) ? array( 'rsv_status' => 'error', 'rsv_message' => $result->get_error_code() ) : array( 'rsv_status' => 'success' ), $url ) );
		exit;
	}

	public static function admin_story() {
		if ( ! is_user_logged_in() ) auth_redirect();
		check_admin_referer( 'rsv_top20_story', '_rsv_nonce' );
		$data = wp_unslash( $_POST );
		self::redirect_with_status( home_url( '/reels/stories/' ), self::create_story( $data, sanitize_text_field( $data['idempotency_key'] ?? '' ) ) );
	}

	public static function admin_response() {
		if ( ! is_user_logged_in() ) auth_redirect();
		check_admin_referer( 'rsv_top20_response', '_rsv_nonce' );
		$data = wp_unslash( $_POST );
		self::redirect_with_status( home_url( '/reels/respond/' ), self::create_response( sanitize_text_field( $data['source_reel_id'] ?? '' ), $data, sanitize_text_field( $data['idempotency_key'] ?? '' ) ) );
	}

	public static function admin_highlight() {
		if ( ! is_user_logged_in() ) auth_redirect();
		check_admin_referer( 'rsv_top20_highlight', '_rsv_nonce' );
		$data = wp_unslash( $_POST );
		self::redirect_with_status( home_url( '/reels/stories/' ), self::add_highlight( sanitize_text_field( $data['story_id'] ?? '' ), sanitize_text_field( $data['title'] ?? '' ), sanitize_text_field( $data['highlight_id'] ?? '' ), sanitize_text_field( $data['idempotency_key'] ?? '' ) ) );
	}

	public static function admin_preferences() {
		if ( ! is_user_logged_in() ) auth_redirect();
		check_admin_referer( 'rsv_top20_preferences', '_rsv_nonce' );
		self::redirect_with_status( home_url( '/account/reels/preferences/' ), self::save_preferences( wp_unslash( $_POST ) ) );
	}

	public static function robots( $robots ) {
		if ( get_query_var( 'rsv_history' ) || get_query_var( 'rsv_insights' ) || get_query_var( 'rsv_preferences' ) || get_query_var( 'rsv_value_insights' ) || get_query_var( 'rsv_respond' ) ) {
			$robots['noindex'] = true;
			$robots['nofollow'] = true;
			$robots['noarchive'] = true;
		}
		return $robots;
	}

	public static function maintenance() {
		self::expire_stories();
		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . RSV_Helpers::table( 'value_signals' ) . ' WHERE day_key<%s', gmdate( 'Y-m-d', time() - 180 * DAY_IN_SECONDS ) ) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . RSV_Helpers::table( 'value_signal_receipts' ) . ' WHERE created_at<%s', gmdate( 'Y-m-d H:i:s', time() - 180 * DAY_IN_SECONDS ) ) );
	}
}
