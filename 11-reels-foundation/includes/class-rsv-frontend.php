<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Frontend {
	private $current_reel = null;

	public function register() {
		add_shortcode( 'rsv_reels', array( $this, 'feed' ) );
		add_shortcode( 'rsv_create', array( $this, 'create' ) );
		add_shortcode( 'rsv_history', array( $this, 'history' ) );
		add_shortcode( 'rsv_insights', array( $this, 'insights' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'init', array( $this, 'rewrites' ) );
		add_action( 'template_redirect', array( $this, 'route_request' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_filter( 'wp_robots', array( $this, 'robots' ) );
	}

	public function register_assets() {
		wp_register_style( 'rsv', RSV_URL . 'assets/css/rsv.css', array(), RSV_VERSION );
		wp_register_script( 'rsv', RSV_URL . 'assets/js/rsv.js', array(), RSV_VERSION, true );
		wp_localize_script(
			'rsv',
			'RSV',
			array(
				'root'     => esc_url_raw( rest_url( RSV_Contracts::API_NAMESPACE ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'loggedIn' => is_user_logged_in(),
				'homeUrl'  => home_url( '/' ),
				'feedUrl'  => home_url( '/reels/' ),
				'i18n'     => array(
					'error'           => __( 'Something went wrong. Please retry.', RSV_TEXT_DOMAIN ),
					'traceId'         => __( 'Reference', RSV_TEXT_DOMAIN ),
					'login'           => __( 'Sign in to use this action.', RSV_TEXT_DOMAIN ),
					'saved'           => __( 'Saved.', RSV_TEXT_DOMAIN ),
					'pause'           => __( 'Pause feed', RSV_TEXT_DOMAIN ),
					'resume'          => __( 'Resume feed', RSV_TEXT_DOMAIN ),
					'enableAutoplay'  => __( 'Enable autoplay', RSV_TEXT_DOMAIN ),
					'disableAutoplay' => __( 'Disable autoplay', RSV_TEXT_DOMAIN ),
					'position'        => __( '%1$d of %2$d', RSV_TEXT_DOMAIN ),
					'reportSubmitted' => __( 'Report submitted.', RSV_TEXT_DOMAIN ),
					'clearHistory'    => __( 'Clear all private Reel history?', RSV_TEXT_DOMAIN ),
					'draftCreated'    => __( 'Draft created. Submit it for review from the publishing dashboard. Reel ID: %s', RSV_TEXT_DOMAIN ),
					'copied'          => __( 'Reel link copied.', RSV_TEXT_DOMAIN ),
					'copyLink'        => __( 'Copy this Reel link:', RSV_TEXT_DOMAIN ),
					'loadMore'        => __( 'Load more', RSV_TEXT_DOMAIN ),
					'loading'         => __( 'Loading…', RSV_TEXT_DOMAIN ),
				),
			)
		);
	}

	private function enqueue() {
		wp_enqueue_style( 'rsv' );
		wp_enqueue_script( 'rsv' );
	}

	public function rewrites() {
		add_rewrite_rule( '^reel/(reel_[a-f0-9-]{36})/?$', 'index.php?rsv_reel=$matches[1]', 'top' );
		add_rewrite_rule( '^reel/(reel_[a-f0-9-]{36})/([^/]+)/?$', 'index.php?rsv_reel=$matches[1]', 'top' );
		add_rewrite_rule( '^account/reels/history/?$', 'index.php?rsv_history=1', 'top' );
		add_rewrite_rule( '^account/reels/insights/?$', 'index.php?rsv_insights=1', 'top' );
	}

	public function query_vars( $vars ) {
		$vars[] = 'rsv_reel';
		$vars[] = 'rsv_history';
		$vars[] = 'rsv_insights';
		return $vars;
	}

	public function route_request() {
		if ( get_query_var( 'rsv_history' ) ) {
			$this->render_private_route( 'history' );
		}
		if ( get_query_var( 'rsv_insights' ) ) {
			$this->render_private_route( 'insights' );
		}
		$map = (array) get_option( 'rsv_page_map', array() );
		if ( ! empty( $map['history'] ) && is_page( absint( $map['history'] ) ) ) {
			wp_safe_redirect( home_url( '/account/reels/history/' ), 301 );
			exit;
		}
		if ( ! empty( $map['insights'] ) && is_page( absint( $map['insights'] ) ) ) {
			wp_safe_redirect( home_url( '/account/reels/insights/' ), 301 );
			exit;
		}

		$id = get_query_var( 'rsv_reel' );
		if ( ! $id ) {
			return;
		}
		$reel = RSV_Repository::find( sanitize_text_field( $id ), true );
		if ( ! RSV_Security::can_view_reel( $reel ) ) {
			status_header( 404 );
			nocache_headers();
			wp_die(
				esc_html__( 'This Reel is unavailable or restricted.', RSV_TEXT_DOMAIN ),
				esc_html__( 'Reel unavailable', RSV_TEXT_DOMAIN ),
				array( 'response' => 404 )
			);
		}
		$this->current_reel = $reel;
		add_filter( 'pre_get_document_title', array( $this, 'document_title' ) );
		add_action( 'wp_head', array( $this, 'seo_meta' ), 2 );
		$this->enqueue();
		get_header();
		echo '<main class="rsv-shell">' . $this->local_nav() . $this->render_reel( $reel, false, array( 'sort' => 'latest' ) ) . $this->single_neighbors( $reel ) . '</main>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		get_footer();
		exit;
	}

	private function render_private_route( $route ) {
		RSV_Helpers::no_cache_private();
		$this->enqueue();
		add_filter(
			'pre_get_document_title',
			static function () use ( $route ) {
				return 'history' === $route ? __( 'Reel History', RSV_TEXT_DOMAIN ) : __( 'Creator Reel Insights', RSV_TEXT_DOMAIN );
			}
		);
		get_header();
		echo 'history' === $route ? $this->history() : $this->insights(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		get_footer();
		exit;
	}

	public function document_title() {
		return $this->current_reel ? $this->current_reel['title'] : __( 'Reel', RSV_TEXT_DOMAIN );
	}

	public function seo_meta() {
		if ( ! $this->current_reel ) {
			return;
		}
		$dto    = RSV_Repository::public_dto( $this->current_reel );
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'VideoObject',
			'name'        => $dto['title'],
			'description' => wp_strip_all_tags( $dto['caption'] ),
			'uploadDate'  => $dto['published_at'],
			'duration'    => 'PT' . absint( $dto['duration_seconds'] ) . 'S',
			'url'         => $dto['url'],
			'author'      => array( '@type' => 'Person', 'name' => $dto['owner']['name'], 'url' => $dto['owner']['profile_url'] ),
		);
		echo '<link rel="canonical" href="' . esc_url( $dto['url'] ) . '">' . "\n";
		echo '<meta property="og:type" content="video.other"><meta property="og:title" content="' . esc_attr( $dto['title'] ) . '"><meta property="og:description" content="' . esc_attr( wp_trim_words( $dto['caption'], 30 ) ) . '"><meta property="og:url" content="' . esc_url( $dto['url'] ) . '">' . "\n";
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function feed() {
		$this->enqueue();
		$sort   = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'recommended';
		$sort   = in_array( $sort, array( 'recommended', 'latest' ), true ) ? $sort : 'recommended';
		$topic  = isset( $_GET['topic'] ) ? sanitize_key( wp_unslash( $_GET['topic'] ) ) : '';
		$topic  = RSV_Helpers::enum( $topic, RSV_Contracts::TOPICS, '' );
		$cursor = isset( $_GET['cursor'] ) ? sanitize_text_field( wp_unslash( $_GET['cursor'] ) ) : '';
		$data   = RSV_Repository::feed( array( 'limit' => 12, 'sort' => $sort, 'topic' => $topic, 'cursor' => $cursor ) );
		if ( is_wp_error( $data ) ) {
			return $this->state( 'error', __( 'Feed unavailable', RSV_TEXT_DOMAIN ), $data->get_error_message(), $this->error_trace( $data ) );
		}
		$map        = (array) get_option( 'rsv_page_map', array() );
		$create_url = ! empty( $map['create'] ) ? get_permalink( absint( $map['create'] ) ) : home_url( '/reels/create/' );
		$base_args  = array_filter( array( 'topic' => $topic ) );
		ob_start();
		?>
		<main class="rsv-shell" aria-labelledby="rsv-feed-title" data-rsv-sort="<?php echo esc_attr( $sort ); ?>" data-rsv-topic="<?php echo esc_attr( $topic ); ?>">
			<?php echo $this->local_nav(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<header class="rsv-hero">
				<div><span class="rsv-eyebrow"><?php esc_html_e( 'Educational short-video discovery', RSV_TEXT_DOMAIN ); ?></span><h1 id="rsv-feed-title"><?php esc_html_e( 'Reels', RSV_TEXT_DOMAIN ); ?></h1><p><?php esc_html_e( 'One-to-ten-minute educational Reels linked to File 10 verified media.', RSV_TEXT_DOMAIN ); ?></p></div>
				<?php if ( RSV_Security::can( RSV_Contracts::CAP_SUBMIT ) ) : ?><a class="rsv-button" href="<?php echo esc_url( $create_url ); ?>"><?php echo $this->icon( 'create' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Create Reel', RSV_TEXT_DOMAIN ); ?></span></a><?php endif; ?>
			</header>
			<div class="rsv-wellbeing" role="note"><?php esc_html_e( 'Take regular pauses. Autoplay is off until you choose it, and this feed never uses streaks, shame or forced infinite scrolling.', RSV_TEXT_DOMAIN ); ?></div>
			<form class="rsv-discovery-controls" method="get" action="<?php echo esc_url( home_url( '/reels/' ) ); ?>">
				<label><span><?php esc_html_e( 'Topic', RSV_TEXT_DOMAIN ); ?></span><select name="topic"><option value=""><?php esc_html_e( 'All approved topics', RSV_TEXT_DOMAIN ); ?></option><?php foreach ( RSV_Contracts::TOPICS as $allowed_topic ) : ?><option value="<?php echo esc_attr( $allowed_topic ); ?>" <?php selected( $topic, $allowed_topic ); ?>><?php echo esc_html( RSV_Helpers::label( $allowed_topic ) ); ?></option><?php endforeach; ?></select></label>
				<input type="hidden" name="sort" value="<?php echo esc_attr( $sort ); ?>">
				<button type="submit"><?php echo $this->icon( 'filter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Apply filter', RSV_TEXT_DOMAIN ); ?></span></button>
				<?php if ( $topic ) : ?><a href="<?php echo esc_url( add_query_arg( 'sort', $sort, home_url( '/reels/' ) ) ); ?>"><?php esc_html_e( 'Clear topic', RSV_TEXT_DOMAIN ); ?></a><?php endif; ?>
			</form>
			<nav class="rsv-sort" aria-label="<?php esc_attr_e( 'Sort Reels', RSV_TEXT_DOMAIN ); ?>">
				<a <?php echo 'recommended' === $sort ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url( add_query_arg( array_merge( $base_args, array( 'sort' => 'recommended' ) ), home_url( '/reels/' ) ) ); ?>"><?php esc_html_e( 'Recommended', RSV_TEXT_DOMAIN ); ?></a>
				<a <?php echo 'latest' === $sort ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url( add_query_arg( array_merge( $base_args, array( 'sort' => 'latest' ) ), home_url( '/reels/' ) ) ); ?>"><?php esc_html_e( 'Latest', RSV_TEXT_DOMAIN ); ?></a>
			</nav>
			<p class="rsv-ranking-note"><?php esc_html_e( 'Recommended ordering is explainable and excludes paid placement. Choose Latest for chronological control.', RSV_TEXT_DOMAIN ); ?></p>
			<div class="rsv-status" role="status" aria-live="polite"></div>
			<div class="rsv-feed" data-rsv-feed tabindex="0" aria-label="<?php esc_attr_e( 'Reels feed. Use Up and Down Arrow keys or the Previous and Next buttons.', RSV_TEXT_DOMAIN ); ?>">
			<?php
			if ( empty( $data['items'] ) ) {
				echo $this->state( 'empty', __( 'No published Reels found', RSV_TEXT_DOMAIN ), __( 'Try another approved topic or return later after new Reels are reviewed.', RSV_TEXT_DOMAIN ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				foreach ( $data['items'] as $item ) {
					$row = RSV_Repository::find( $item['id'], true );
					if ( $row ) {
						echo $this->render_reel( $row, true, array( 'sort' => $sort, 'topic' => $topic ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
			}
			?>
			</div>
			<nav class="rsv-feed-controls" aria-label="<?php esc_attr_e( 'Reel navigation', RSV_TEXT_DOMAIN ); ?>"><button type="button" data-rsv-prev><?php echo $this->icon( 'previous' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Previous', RSV_TEXT_DOMAIN ); ?></span></button><button type="button" data-rsv-autoplay aria-pressed="false"><?php echo $this->icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Enable autoplay', RSV_TEXT_DOMAIN ); ?></span></button><button type="button" data-rsv-pause><?php echo $this->icon( 'pause' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Pause feed', RSV_TEXT_DOMAIN ); ?></span></button><button type="button" data-rsv-next><?php echo $this->icon( 'next' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Next', RSV_TEXT_DOMAIN ); ?></span></button></nav>
			<?php if ( ! empty( $data['next_cursor'] ) ) : ?>
				<button class="rsv-load-more" type="button" data-rsv-load-more data-cursor="<?php echo esc_attr( $data['next_cursor'] ); ?>"><?php esc_html_e( 'Load more', RSV_TEXT_DOMAIN ); ?></button>
				<noscript><p class="rsv-noscript-next"><a href="<?php echo esc_url( add_query_arg( array_filter( array( 'sort' => $sort, 'topic' => $topic, 'cursor' => $data['next_cursor'] ) ), home_url( '/reels/' ) ) ); ?>"><?php esc_html_e( 'Next page of Reels', RSV_TEXT_DOMAIN ); ?></a></p></noscript>
			<?php endif; ?>
		</main>
		<?php
		return ob_get_clean();
	}

	public function render_reel( $reel, $feed_item = false, $context = array() ) {
		$dto      = RSV_Repository::public_dto( $reel );
		$playback = RSV_File10::playback( $reel['video_id'] );
		$sort     = RSV_Helpers::enum( $context['sort'] ?? 'recommended', array( 'recommended', 'latest' ), 'recommended' );
		$reasons  = RSV_Integrations::recommendation_explanation( $reel, $sort );
		ob_start();
		?>
		<article class="rsv-reel<?php echo $feed_item ? ' rsv-feed-item' : ' rsv-single'; ?>" data-rsv-reel="<?php echo esc_attr( $reel['public_id'] ); ?>" data-duration="<?php echo absint( $dto['duration_seconds'] ); ?>" tabindex="-1" aria-labelledby="rsv-title-<?php echo absint( $reel['id'] ); ?>">
			<div class="rsv-player"><?php echo $this->player( $playback, $dto ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<div class="rsv-overlay"><span class="rsv-topic"><?php echo esc_html( RSV_Helpers::label( $reel['topic'] ) ); ?></span><h2 id="rsv-title-<?php echo absint( $reel['id'] ); ?>"><?php echo esc_html( $reel['title'] ); ?></h2><p><?php echo esc_html( $reel['caption'] ); ?></p>
			<p class="rsv-byline"><a href="<?php echo esc_url( $dto['owner']['profile_url'] ); ?>"><?php echo esc_html( $dto['owner']['name'] ); ?></a> · <?php echo esc_html( $dto['owner']['label'] ); ?> · <?php echo esc_html( gmdate( 'i:s', $dto['duration_seconds'] ) ); ?></p>
			<details class="rsv-why"><summary><?php echo $this->icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Why this Reel?', RSV_TEXT_DOMAIN ); ?></span></summary><ul><?php foreach ( $reasons as $reason ) : ?><li><?php echo esc_html( $reason ); ?></li><?php endforeach; ?></ul></details>
			<div class="rsv-actions" role="group" aria-label="<?php esc_attr_e( 'Reel actions', RSV_TEXT_DOMAIN ); ?>"><button type="button" data-rsv-action="like" aria-pressed="false"><?php echo $this->icon( 'like' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Like', RSV_TEXT_DOMAIN ); ?></span></button><button type="button" data-rsv-action="dislike" aria-pressed="false"><?php echo $this->icon( 'dislike' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Dislike', RSV_TEXT_DOMAIN ); ?></span></button><button type="button" data-rsv-action="save" aria-pressed="false"><?php echo $this->icon( 'save' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Save', RSV_TEXT_DOMAIN ); ?></span></button><button type="button" data-rsv-share data-url="<?php echo esc_url( $dto['url'] ); ?>"><?php echo $this->icon( 'share' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Share', RSV_TEXT_DOMAIN ); ?></span></button><a href="<?php echo esc_url( $dto['url'] ); ?>"><?php echo $this->icon( 'open' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Open', RSV_TEXT_DOMAIN ); ?></span></a><?php if ( $dto['video_url'] ) : ?><a href="<?php echo esc_url( $dto['video_url'] ); ?>"><?php echo $this->icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Full video page', RSV_TEXT_DOMAIN ); ?></span></a><?php endif; ?><?php if ( $dto['comments_url'] ) : ?><a href="<?php echo esc_url( $dto['comments_url'] ); ?>"><?php echo $this->icon( 'comment' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Comments', RSV_TEXT_DOMAIN ); ?></span></a><?php endif; ?><?php if ( $dto['follow_url'] ) : ?><a href="<?php echo esc_url( $dto['follow_url'] ); ?>"><?php echo $this->icon( 'follow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Follow', RSV_TEXT_DOMAIN ); ?></span></a><?php endif; ?><?php if ( $dto['download_url'] ) : ?><a href="<?php echo esc_url( $dto['download_url'] ); ?>" download><?php echo $this->icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Download', RSV_TEXT_DOMAIN ); ?></span></a><?php endif; ?></div>
			<details class="rsv-report"><summary><?php echo $this->icon( 'report' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Report', RSV_TEXT_DOMAIN ); ?></span></summary><form data-rsv-report-form><label><?php esc_html_e( 'Reason', RSV_TEXT_DOMAIN ); ?><select name="reason" required><option value=""><?php esc_html_e( 'Choose reason', RSV_TEXT_DOMAIN ); ?></option><?php foreach ( RSV_Contracts::REPORT_REASONS as $reason ) : ?><option value="<?php echo esc_attr( $reason ); ?>"><?php echo esc_html( RSV_Helpers::label( $reason ) ); ?></option><?php endforeach; ?></select></label><label><?php esc_html_e( 'Details', RSV_TEXT_DOMAIN ); ?><textarea name="details" minlength="10" maxlength="4000" required></textarea></label><button type="submit"><?php esc_html_e( 'Submit report', RSV_TEXT_DOMAIN ); ?></button></form></details>
			<?php if ( $reel['disclosure'] ) : ?><p class="rsv-disclosure"><?php echo esc_html( $reel['disclosure'] ); ?></p><?php endif; ?><div class="rsv-item-status" role="status" aria-live="polite"></div></div>
		</article>
		<?php
		return ob_get_clean();
	}

	private function player( $playback, $dto ) {
		if ( is_wp_error( $playback ) || empty( $playback['playback']['url'] ) ) {
			$trace = is_wp_error( $playback ) ? $this->error_trace( $playback ) : '';
			return '<div class="rsv-state rsv-error" role="status"><p>' . esc_html__( 'Media is temporarily unavailable. Skip to the next Reel or retry later.', RSV_TEXT_DOMAIN ) . '</p>' . ( $trace ? '<p class="rsv-trace">' . esc_html__( 'Reference:', RSV_TEXT_DOMAIN ) . ' <code>' . esc_html( $trace ) . '</code></p>' : '' ) . '</div>';
		}
		$p      = $playback['playback'];
		$poster = ! empty( $dto['cover_id'] ) ? wp_get_attachment_image_url( $dto['cover_id'], 'large' ) : '';
		if ( 'iframe' === ( $p['type'] ?? '' ) ) {
			return '<iframe class="rsv-iframe" data-provider="remote" data-control-origin="' . esc_attr( RSV_File10::control_origin( $p['url'] ) ) . '" src="' . esc_url( $p['url'] ) . '" title="' . esc_attr( $dto['title'] ) . '" loading="lazy" allow="fullscreen; picture-in-picture" sandbox="' . esc_attr( $p['sandbox'] ?? 'allow-scripts allow-same-origin allow-presentation' ) . '" allowfullscreen></iframe><p class="rsv-remote-note">' . esc_html__( 'Resume/completion tracking is available only when File 10 supplies verified player progress.', RSV_TEXT_DOMAIN ) . '</p>';
		}
		$resume = absint( $playback['session']['resume_seconds'] ?? 0 );
		$html   = '<video class="rsv-video" controls playsinline muted preload="metadata" src="' . esc_url( $p['url'] ) . '" data-resume="' . $resume . '"' . ( $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . '>';
		foreach ( (array) ( $playback['video']['captions'] ?? array() ) as $caption ) {
			$url   = ! empty( $caption['url'] ) ? $caption['url'] : rest_url( 'vwlb/v1/captions/' . rawurlencode( $caption['public_id'] ?? '' ) );
			$html .= '<track kind="' . esc_attr( 'captions' === ( $caption['kind'] ?? '' ) ? 'captions' : 'subtitles' ) . '" srclang="' . esc_attr( $caption['language'] ?? 'en' ) . '" label="' . esc_attr( $caption['label'] ?? $caption['language'] ?? 'Captions' ) . '" src="' . esc_url( $url ) . '">';
		}
		return $html . esc_html__( 'Your browser does not support video playback.', RSV_TEXT_DOMAIN ) . '</video>';
	}

	private function single_neighbors( $reel ) {
		$neighbors = RSV_Repository::neighbors( $reel );
		if ( ! $neighbors['previous'] && ! $neighbors['next'] ) {
			return '';
		}
		$html = '<nav class="rsv-neighbors" aria-label="' . esc_attr__( 'Previous and next Reels', RSV_TEXT_DOMAIN ) . '">';
		if ( $neighbors['previous'] ) {
			$html .= '<a rel="prev" href="' . esc_url( $neighbors['previous']['url'] ) . '">' . $this->icon( 'previous' ) . '<span>' . esc_html( $neighbors['previous']['title'] ) . '</span></a>';
		}
		if ( $neighbors['next'] ) {
			$html .= '<a rel="next" href="' . esc_url( $neighbors['next']['url'] ) . '"><span>' . esc_html( $neighbors['next']['title'] ) . '</span>' . $this->icon( 'next' ) . '</a>';
		}
		return $html . '</nav>';
	}

	public function create() {
		RSV_Helpers::no_cache_private();
		$this->enqueue();
		if ( ! RSV_Security::can( RSV_Contracts::CAP_SUBMIT ) ) {
			return $this->state( 'restricted', __( 'Publishing access required', RSV_TEXT_DOMAIN ), __( 'Only the Founder, authorized administrators and File 00 verified doctors can create Reels.', RSV_TEXT_DOMAIN ) );
		}
		ob_start();
		?><main class="rsv-shell"><?php echo $this->local_nav(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><h1><?php esc_html_e( 'Create Reel', RSV_TEXT_DOMAIN ); ?></h1><p><?php esc_html_e( 'First create or select a File 10 video. File 11 never duplicates upload, transcoding, storage or playback ownership.', RSV_TEXT_DOMAIN ); ?></p><form class="rsv-form" data-rsv-create><label><?php esc_html_e( 'File 10 video ID', RSV_TEXT_DOMAIN ); ?><input name="video_id" type="number" min="1" required></label><label><?php esc_html_e( 'Title', RSV_TEXT_DOMAIN ); ?><input name="title" maxlength="255" required></label><label><?php esc_html_e( 'Topic', RSV_TEXT_DOMAIN ); ?><select name="topic" required><option value=""><?php esc_html_e( 'Choose topic', RSV_TEXT_DOMAIN ); ?></option><?php foreach ( RSV_Contracts::TOPICS as $topic ) : ?><option value="<?php echo esc_attr( $topic ); ?>"><?php echo esc_html( RSV_Helpers::label( $topic ) ); ?></option><?php endforeach; ?></select></label><label><?php esc_html_e( 'Language', RSV_TEXT_DOMAIN ); ?><input name="language" value="en-US" maxlength="20"></label><label><?php esc_html_e( 'Caption / description', RSV_TEXT_DOMAIN ); ?><textarea name="caption" maxlength="4000"></textarea></label><label><?php esc_html_e( 'Disclosure', RSV_TEXT_DOMAIN ); ?><input name="disclosure" maxlength="500"></label><label><?php esc_html_e( 'Visibility', RSV_TEXT_DOMAIN ); ?><select name="visibility"><?php foreach ( RSV_Contracts::VISIBILITIES as $visibility ) : ?><option value="<?php echo esc_attr( $visibility ); ?>"><?php echo esc_html( RSV_Helpers::label( $visibility ) ); ?></option><?php endforeach; ?></select></label><button class="rsv-button" type="submit"><?php echo $this->icon( 'create' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Create draft', RSV_TEXT_DOMAIN ); ?></span></button><div class="rsv-form-status" role="status" aria-live="polite"></div></form></main><?php
		return ob_get_clean();
	}

	public function history() {
		RSV_Helpers::no_cache_private();
		$this->enqueue();
		if ( ! is_user_logged_in() ) {
			return $this->state( 'restricted', __( 'Sign in required', RSV_TEXT_DOMAIN ), __( 'Sign in to view private Reel history.', RSV_TEXT_DOMAIN ) );
		}
		$items = RSV_Repository::history( get_current_user_id() );
		ob_start();
		?><main class="rsv-shell"><?php echo $this->local_nav(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><h1><?php esc_html_e( 'Reel History', RSV_TEXT_DOMAIN ); ?></h1><button type="button" data-rsv-clear-history><?php echo $this->icon( 'delete' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Clear history', RSV_TEXT_DOMAIN ); ?></span></button><?php if ( ! $items ) : echo $this->state( 'empty', __( 'No history yet', RSV_TEXT_DOMAIN ), __( 'Reels watched while signed in will appear here.', RSV_TEXT_DOMAIN ) ); else : ?><ol class="rsv-history"><?php foreach ( $items as $item ) : ?><li><?php if ( $item['available'] ) : ?><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a><?php else : ?><span><?php echo esc_html( $item['title'] ); ?></span><?php endif; ?><span><?php echo esc_html( gmdate( 'i:s', $item['progress']['position_seconds'] ) ); ?></span></li><?php endforeach; ?></ol><?php endif; ?></main><?php
		return ob_get_clean();
	}

	public function insights() {
		RSV_Helpers::no_cache_private();
		$this->enqueue();
		if ( ! ( RSV_Security::can( RSV_Contracts::CAP_INSIGHTS ) || RSV_Security::can( RSV_Contracts::CAP_SUBMIT ) ) ) {
			return $this->state( 'restricted', __( 'Access restricted', RSV_TEXT_DOMAIN ), __( 'Creator insights are available only to authorized publishers.', RSV_TEXT_DOMAIN ) );
		}
		$rows = RSV_Reels::insights( get_current_user_id() );
		ob_start();
		?><main class="rsv-shell"><?php echo $this->local_nav(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><h1><?php esc_html_e( 'Creator Reel Insights', RSV_TEXT_DOMAIN ); ?></h1><p><?php esc_html_e( 'Only privacy-safe aggregate metrics are shown; viewer identities are never exposed.', RSV_TEXT_DOMAIN ); ?></p><div class="rsv-table-wrap"><table><thead><tr><th><?php esc_html_e( 'Reel', RSV_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Views', RSV_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Completions', RSV_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Average seconds', RSV_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Rapid swipes', RSV_TEXT_DOMAIN ); ?></th></tr></thead><tbody><?php foreach ( $rows as $row ) : ?><tr><td><?php echo esc_html( $row['title'] ); ?></td><?php if ( $row['insufficient_data'] ) : ?><td colspan="4"><?php esc_html_e( 'Insufficient aggregate data', RSV_TEXT_DOMAIN ); ?></td><?php else : ?><td><?php echo absint( $row['views'] ); ?></td><td><?php echo absint( $row['completions'] ); ?></td><td><?php echo esc_html( round( (float) $row['average_seconds'], 1 ) ); ?></td><td><?php echo absint( $row['rapid_swipes'] ); ?></td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div></main><?php
		return ob_get_clean();
	}

	private function local_nav() {
		return '<nav class="rsv-local-nav" aria-label="' . esc_attr__( 'Page navigation', RSV_TEXT_DOMAIN ) . '"><button type="button" data-rsv-back>' . $this->icon( 'back' ) . '<span>' . esc_html__( 'Back', RSV_TEXT_DOMAIN ) . '</span></button><a href="' . esc_url( home_url( '/' ) ) . '">' . $this->icon( 'home' ) . '<span>' . esc_html__( 'Home', RSV_TEXT_DOMAIN ) . '</span></a></nav>';
	}

	private function state( $class, $title, $message, $trace_id = '' ) {
		$trace = $trace_id ? '<p class="rsv-trace">' . esc_html__( 'Reference:', RSV_TEXT_DOMAIN ) . ' <code>' . esc_html( $trace_id ) . '</code></p>' : '';
		return '<section class="rsv-state rsv-' . esc_attr( $class ) . '" role="status"><h2>' . esc_html( $title ) . '</h2><p>' . esc_html( $message ) . '</p>' . $trace . '<p><a href="' . esc_url( home_url( '/' ) ) . '">' . $this->icon( 'home' ) . '<span>' . esc_html__( 'Home', RSV_TEXT_DOMAIN ) . '</span></a></p></section>';
	}

	private function error_trace( $error ) {
		if ( ! is_wp_error( $error ) ) {
			return '';
		}
		$data = $error->get_error_data();
		return is_array( $data ) && ! empty( $data['trace_id'] ) ? sanitize_text_field( $data['trace_id'] ) : '';
	}

	private function icon( $name ) {
		$paths = array(
			'back'     => '<path d="M15 18l-6-6 6-6"/><path d="M9 12h11"/>',
			'home'     => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/>',
			'create'   => '<path d="M12 5v14M5 12h14"/>',
			'filter'   => '<path d="M4 6h16M7 12h10M10 18h4"/>',
			'previous' => '<path d="M15 18l-6-6 6-6"/>',
			'next'     => '<path d="M9 18l6-6-6-6"/>',
			'play'     => '<path d="m9 7 8 5-8 5z"/>',
			'pause'    => '<path d="M9 7v10M15 7v10"/>',
			'like'     => '<path d="M7 10v10H4V10h3Zm0 9h9.5a2 2 0 0 0 2-1.6l1-5A2 2 0 0 0 17.5 10H14l1-4-1-2-6 7v8Z"/>',
			'dislike'  => '<path d="M7 14V4H4v10h3Zm0-9h9.5a2 2 0 0 1 2 1.6l1 5a2 2 0 0 1-2 2.4H14l1 4-1 2-6-7V5Z"/>',
			'save'     => '<path d="M6 4h12v16l-6-4-6 4z"/>',
			'share'    => '<path d="M14 5h5v5"/><path d="m19 5-9 9"/><path d="M19 13v6H5V5h6"/>',
			'open'     => '<path d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5"/>',
			'comment'  => '<path d="M4 5h16v11H8l-4 4z"/>',
			'follow'   => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-4 2-6 6-6s6 2 6 6M18 8v6M15 11h6"/>',
			'download' => '<path d="M12 3v12M7 10l5 5 5-5M5 21h14"/>',
			'report'   => '<path d="M5 21V4M5 5h11l-2 4 2 4H5"/>',
			'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/>',
			'delete'   => '<path d="M4 7h16M9 7V4h6v3M7 7l1 14h8l1-14M10 11v6M14 11v6"/>',
		);
		$path = $paths[ $name ] ?? $paths['info'];
		return '<svg class="rsv-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
	}

	public function robots( $robots ) {
		if ( get_query_var( 'rsv_reel' ) ) {
			return $robots;
		}
		if ( get_query_var( 'rsv_history' ) || get_query_var( 'rsv_insights' ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
			return $robots;
		}
		$map = (array) get_option( 'rsv_page_map', array() );
		if ( is_page( array_filter( array( $map['create'] ?? 0, $map['history'] ?? 0, $map['insights'] ?? 0 ) ) ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}
}
