<?php
defined('ABSPATH') || exit;

final class SRL_Plugin {
    const MIN_DURATION = 60;
    const MAX_DURATION = 600;
    const PAGE_SIZE = 20;

    public static function dependency_ready() {
        return class_exists('SVW_Helpers')
            && class_exists('SVW_Interactions')
            && defined('SVW_VERSION')
            && defined('SVW_Helpers::TYPE')
            && defined('SVW_Helpers::TAX');
    }

    public static function activate() {
        if (!self::dependency_ready()) {
            deactivate_plugins(plugin_basename(SRL_FILE));
            wp_die(
                esc_html__('Activate a compatible File 10 Video Wall plugin before activating Reels.', 'sabri-reels'),
                '',
                array('response' => 400, 'back_link' => true)
            );
        }

        $administrator = get_role('administrator');
        if ($administrator) {
            $administrator->add_cap('manage_reels');
        }

        self::install_schema();
        self::ensure_pages();
        update_option('srl_version', SRL_VERSION, false);
        update_option('srl_db_version', SRL_DB_VERSION, false);
        set_transient('srl_notice', '1', 120);
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private static function install_schema() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $collate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . 'srl_history';
        dbDelta("CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            reel_id bigint(20) unsigned NOT NULL,
            progress int unsigned NOT NULL DEFAULT 0,
            completed tinyint(1) unsigned NOT NULL DEFAULT 0,
            replays int unsigned NOT NULL DEFAULT 0,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_reel (user_id,reel_id),
            KEY reel_id (reel_id),
            KEY updated_at (updated_at)
        ) {$collate};");
    }

    private static function ensure_pages() {
        $platform = (array) get_option('spf_page_map', array());
        $map = (array) get_option('srl_page_map', array());

        $map['feed'] = self::page(
            !empty($platform['reels']) ? absint($platform['reels']) : 0,
            'Reels',
            'reels',
            '[srl_reels]'
        );
        $map['create'] = self::page(!empty($map['create']) ? absint($map['create']) : 0, 'Create Reel', 'create-reel', '[srl_create]');
        $map['saved'] = self::page(!empty($map['saved']) ? absint($map['saved']) : 0, 'Saved Reels', 'saved-reels', '[srl_saved]');
        $map['history'] = self::page(!empty($map['history']) ? absint($map['history']) : 0, 'Recently Watched Reels', 'reel-history', '[srl_history]');

        update_option('srl_page_map', $map, false);
        if (!empty($map['feed'])) {
            $platform['reels'] = $map['feed'];
            update_option('spf_page_map', $platform, false);
        }
    }

    private static function page($id, $title, $slug, $shortcode) {
        $page = $id ? get_post($id) : get_page_by_path($slug);
        if ($page instanceof WP_Post) {
            $managed = get_post_meta($page->ID, '_srl_managed', true)
                || get_post_meta($page->ID, '_spf_managed_page', true)
                || false !== strpos((string) $page->post_content, '[sabri_platform_module')
                || false !== strpos((string) $page->post_content, $shortcode);

            if ($managed) {
                $result = wp_update_post(
                    array(
                        'ID' => $page->ID,
                        'post_title' => $title,
                        'post_content' => $shortcode,
                        'post_status' => 'publish',
                    ),
                    true
                );
                if (is_wp_error($result)) {
                    return 0;
                }
                update_post_meta($page->ID, '_srl_managed', '1');
                return (int) $page->ID;
            }

            // Never overwrite an unrelated page that merely owns the requested slug.
            $slug .= '-sabri';
        }

        $new_id = wp_insert_post(
            array(
                'post_title' => $title,
                'post_name' => $slug,
                'post_content' => $shortcode,
                'post_status' => 'publish',
                'post_type' => 'page',
            ),
            true
        );
        if (is_wp_error($new_id)) {
            return 0;
        }
        update_post_meta($new_id, '_srl_managed', '1');
        return (int) $new_id;
    }

    public function run() {
        $this->maybe_upgrade();

        add_shortcode('srl_reels', array($this, 'feed'));
        add_shortcode('srl_create', array($this, 'form'));
        add_shortcode('srl_saved', array($this, 'saved'));
        add_shortcode('srl_history', array($this, 'history'));

        add_action('admin_post_srl_submit', array($this, 'submit'));
        add_action('wp_ajax_srl_progress', array($this, 'progress'));
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_post_srl_review', array($this, 'review'));
        add_action('wp_enqueue_scripts', array($this, 'assets'), 30);
        add_action('template_redirect', array($this, 'private_response_headers'));

        add_filter('wp_robots', array($this, 'robots'));
        add_filter('wp_privacy_personal_data_exporters', array($this, 'privacy_exporters'));
        add_filter('wp_privacy_personal_data_erasers', array($this, 'privacy_erasers'));
    }

    private function maybe_upgrade() {
        if ((string) get_option('srl_db_version', '') !== SRL_DB_VERSION || (string) get_option('srl_version', '') !== SRL_VERSION) {
            self::install_schema();
            self::ensure_pages();
            update_option('srl_db_version', SRL_DB_VERSION, false);
            update_option('srl_version', SRL_VERSION, false);
        }
    }

    public function feed() {
        $sort = isset($_GET['reel_sort']) ? sanitize_key(wp_unslash($_GET['reel_sort'])) : 'trending';
        if (!in_array($sort, array('trending', 'latest'), true)) {
            $sort = 'trending';
        }
        $page = isset($_GET['reel_page']) ? max(1, absint($_GET['reel_page'])) : 1;

        $args = array(
            'post_type' => SVW_Helpers::TYPE,
            'post_status' => 'publish',
            'posts_per_page' => self::PAGE_SIZE,
            'paged' => $page,
            'ignore_sticky_posts' => true,
            'meta_query' => array(
                array('key' => '_svw_is_reel', 'value' => '1'),
            ),
        );
        if ('latest' === $sort) {
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
        } else {
            $args['meta_key'] = '_svw_score';
            $args['orderby'] = array('meta_value_num' => 'DESC', 'date' => 'DESC');
        }

        $query = new WP_Query($args);
        $map = (array) get_option('srl_page_map', array());
        $create_url = !empty($map['create']) ? get_permalink(absint($map['create'])) : '';
        $base_url = !empty($map['feed']) ? get_permalink(absint($map['feed'])) : get_permalink();

        ob_start();
        ?>
        <main class="srl-shell">
            <?php echo SVW_Helpers::nav(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <header class="srl-head">
                <div>
                    <span class="srl-eyebrow">Educational short-video discovery</span>
                    <h1>Reels</h1>
                    <p>Swipe through verified educational Reels. Every accepted Reel is authoritatively validated at 1–10 minutes.</p>
                </div>
                <?php if ($create_url && SVW_Helpers::can_submit()) : ?>
                    <a class="srl-button" href="<?php echo esc_url($create_url); ?>">Create Reel</a>
                <?php endif; ?>
            </header>

            <nav class="srl-sort" aria-label="Reel sorting">
                <a <?php echo 'trending' === $sort ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('reel_sort', 'trending', $base_url)); ?>">Trending</a>
                <a <?php echo 'latest' === $sort ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('reel_sort', 'latest', $base_url)); ?>">Latest</a>
            </nav>

            <p class="srl-live" role="status" aria-live="polite"></p>

            <?php if ($query->have_posts()) : ?>
                <div class="srl-feed" tabindex="0" aria-label="Reels feed. Use Up and Down arrow keys to move between Reels.">
                    <?php while ($query->have_posts()) : $query->the_post();
                        $id = get_the_ID();
                        $author = (int) get_post_field('post_author', $id);
                        $duration = absint(SVW_Helpers::meta($id, 'duration'));
                        ?>
                        <article class="srl-reel" data-reel="<?php echo absint($id); ?>" data-duration="<?php echo absint($duration); ?>" tabindex="-1" aria-label="<?php echo esc_attr(get_the_title($id)); ?>">
                            <div class="srl-player">
                                <?php echo $this->embed($id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                            <div class="srl-overlay">
                                <span><?php echo esc_html(SVW_Helpers::category($id)); ?></span>
                                <h2><?php echo esc_html(get_the_title($id)); ?></h2>
                                <p><?php echo esc_html(get_the_excerpt($id)); ?></p>
                                <p class="srl-byline"><strong><?php echo esc_html(get_the_author_meta('display_name', $author)); ?></strong> · <?php echo esc_html($this->author_badge($author)); ?> · <?php echo esc_html(SVW_Helpers::duration($duration)); ?></p>
                                <?php echo SVW_Interactions::buttons($id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                <a class="srl-open" href="<?php echo esc_url(get_permalink($id)); ?>">Open permanent Reel page</a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <?php echo $this->pagination($page, (int) $query->max_num_pages, $sort, $base_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php else : ?>
                <section class="srl-empty" role="status">
                    <h2>No published Reels found</h2>
                    <p>Approved educational Reels will appear here after publication.</p>
                </section>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        </main>
        <?php
        return ob_get_clean();
    }

    private function pagination($page, $max_pages, $sort, $base_url) {
        if ($max_pages <= 1) {
            return '';
        }
        $output = '<nav class="srl-pagination" aria-label="Reels pages">';
        if ($page > 1) {
            $output .= '<a href="' . esc_url(add_query_arg(array('reel_sort' => $sort, 'reel_page' => $page - 1), $base_url)) . '">Previous</a>';
        }
        $output .= '<span>Page ' . absint($page) . ' of ' . absint($max_pages) . '</span>';
        if ($page < $max_pages) {
            $output .= '<a href="' . esc_url(add_query_arg(array('reel_sort' => $sort, 'reel_page' => $page + 1), $base_url)) . '">Next</a>';
        }
        return $output . '</nav>';
    }

    private function author_badge($user_id) {
        if (SVW_Helpers::founder($user_id)) {
            return 'Verified Founder';
        }
        if (SVW_Helpers::doctor($user_id)) {
            return 'Verified Doctor';
        }
        if (user_can($user_id, 'manage_reels') || user_can($user_id, 'manage_video_wall')) {
            return 'Authorized Administrator';
        }
        return 'Authorized Publisher';
    }

    private function embed($id) {
        $source = (string) SVW_Helpers::meta($id, 'source');
        $url = (string) SVW_Helpers::meta($id, 'video_url');
        $title = get_the_title($id);

        if ('youtube' === $source) {
            $video_id = $this->youtube_id($url);
            if ($video_id) {
                $origin = rawurlencode(home_url('/'));
                return '<iframe class="srl-remote-player" data-provider="youtube" src="https://www.youtube-nocookie.com/embed/' . esc_attr($video_id) . '?enablejsapi=1&amp;playsinline=1&amp;rel=0&amp;origin=' . esc_attr($origin) . '" title="' . esc_attr($title) . '" loading="lazy" allow="accelerometer; autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>';
            }
        }

        if ('vimeo' === $source) {
            $video_id = $this->vimeo_id($url);
            if ($video_id) {
                return '<iframe class="srl-remote-player" data-provider="vimeo" src="https://player.vimeo.com/video/' . esc_attr($video_id) . '?api=1&amp;autopause=0&amp;muted=1" title="' . esc_attr($title) . '" loading="lazy" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
            }
        }

        if ('local' === $source) {
            $attachment = absint(SVW_Helpers::meta($id, 'attachment_id'));
            $src = $attachment ? wp_get_attachment_url($attachment) : '';
            if ($src) {
                return wp_video_shortcode(array('src' => $src, 'preload' => 'metadata', 'class' => 'srl-local-player'));
            }
        }

        return '<p class="srl-error">This Reel media source is unavailable.</p>';
    }

    public function form() {
        if (!is_user_logged_in()) {
            return '<div class="srl-note"><a href="' . esc_url(wp_login_url(get_permalink())) . '">Log in to submit a Reel.</a></div>';
        }
        if (!SVW_Helpers::can_submit()) {
            return '<div class="srl-note">Only the Founder, authorized administrators, and verified doctors may submit Reels.</div>';
        }

        $submitted = isset($_GET['submitted']) && '1' === sanitize_key(wp_unslash($_GET['submitted']));
        ob_start();
        ?>
        <main class="srl-shell">
            <header class="srl-head srl-head-simple">
                <div>
                    <span class="srl-eyebrow">Verified educational publishing</span>
                    <h1>Create Reel</h1>
                    <p>Duration is verified from authoritative media metadata. Accepted duration: 60–600 seconds.</p>
                </div>
            </header>
            <?php if ($submitted) : ?><div class="srl-success" role="status">Your Reel was submitted successfully.</div><?php endif; ?>
            <form class="srl-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="srl_submit">
                <?php wp_nonce_field('srl_submit', 'srl_nonce'); ?>
                <label>Reel title<input name="title" maxlength="180" required></label>
                <label>Short caption<textarea name="caption" maxlength="500" required></textarea></label>
                <label>Category<select name="category" required><?php foreach (SVW_Helpers::categories() as $key => $value) : ?><option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option><?php endforeach; ?></select></label>
                <label>Source<select name="source" required><option value="youtube">YouTube</option><option value="vimeo">Vimeo</option><option value="local">Local vertical video</option></select></label>
                <label>Video URL<input type="url" name="video_url" placeholder="https://www.youtube.com/shorts/... or https://vimeo.com/..."><small>YouTube requires a configured API key for authoritative duration verification. Vimeo duration is verified through Vimeo oEmbed.</small></label>
                <label>Local video<input type="file" name="local_video" accept="video/mp4,video/webm,video/ogg"><small>Maximum 200 MB or the lower server limit. Duration is read from the uploaded file.</small></label>
                <label>Cover image<input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" required><small>Maximum 10 MB or the lower server limit.</small></label>
                <label class="srl-wide">On-screen caption / transcript<textarea name="transcript" required></textarea></label>
                <label class="srl-wide">References<textarea name="references" required></textarea></label>
                <label class="srl-wide">Medical safety notice<textarea name="safety" required></textarea></label>
                <label class="srl-check srl-wide"><input type="checkbox" name="copyright" value="1" required> I own or am authorized to publish this Reel.</label>
                <label class="srl-check srl-wide"><input type="checkbox" name="medical" value="1" required> This Reel is educational, avoids cure guarantees and personal prescriptions, and does not delay emergency care.</label>
                <label class="srl-check srl-wide"><input type="checkbox" name="case_anonymized" value="1"> For a Patient Case, all identifying information has been removed.</label>
                <label class="srl-check srl-wide"><input type="checkbox" name="case_consent" value="1"> For a Patient Case, valid publication consent has been obtained and documented.</label>
                <button type="submit"><?php echo 'publish' === SVW_Helpers::initial_status() ? 'Publish Reel' : 'Submit Reel for Review'; ?></button>
            </form>
        </main>
        <?php
        return ob_get_clean();
    }

    public function submit() {
        if (!is_user_logged_in() || !SVW_Helpers::can_submit()) {
            wp_die(esc_html__('Reel publishing is restricted.', 'sabri-reels'), '', array('response' => 403));
        }
        check_admin_referer('srl_submit', 'srl_nonce');
        $this->rate_limit('submit', 10, HOUR_IN_SECONDS);

        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $caption = isset($_POST['caption']) ? sanitize_textarea_field(wp_unslash($_POST['caption'])) : '';
        $category = isset($_POST['category']) ? sanitize_title(wp_unslash($_POST['category'])) : '';
        $source = isset($_POST['source']) ? sanitize_key(wp_unslash($_POST['source'])) : '';
        $url = isset($_POST['video_url']) ? esc_url_raw(wp_unslash($_POST['video_url'])) : '';
        $transcript = isset($_POST['transcript']) ? sanitize_textarea_field(wp_unslash($_POST['transcript'])) : '';
        $references = isset($_POST['references']) ? sanitize_textarea_field(wp_unslash($_POST['references'])) : '';
        $safety = isset($_POST['safety']) ? sanitize_textarea_field(wp_unslash($_POST['safety'])) : '';

        if (!$title || !$caption || !isset(SVW_Helpers::categories()[$category]) || !$transcript || !$references || !$safety || empty($_POST['copyright']) || empty($_POST['medical'])) {
            wp_die(esc_html__('Complete every required Reel field.', 'sabri-reels'), '', array('response' => 400));
        }
        if (!in_array($source, array('youtube', 'vimeo', 'local'), true)) {
            wp_die(esc_html__('Choose an approved Reel source.', 'sabri-reels'), '', array('response' => 400));
        }
        if (in_array($category, array('founder-update', 'platform-news'), true) && !SVW_Helpers::founder() && !current_user_can('manage_reels')) {
            wp_die(esc_html__('That category is reserved for official publishing.', 'sabri-reels'), '', array('response' => 403));
        }

        $public_text = $title . ' ' . $caption . ' ' . $transcript . ' ' . $references . ' ' . $safety;
        if ($this->contains_non_english_script($public_text)) {
            wp_die(esc_html__('This release accepts American English public content only.', 'sabri-reels'), '', array('response' => 400));
        }

        $is_patient_case = 'patient-cases' === $category;
        if ($is_patient_case) {
            if (empty($_POST['case_anonymized']) || empty($_POST['case_consent'])) {
                wp_die(esc_html__('Patient Cases require separate anonymization and consent confirmations.', 'sabri-reels'), '', array('response' => 400));
            }
            if ($this->contains_obvious_patient_identifier($public_text)) {
                wp_die(esc_html__('Possible patient-identifying information was detected. Remove names, email addresses, phone numbers, and identity numbers before submission.', 'sabri-reels'), '', array('response' => 400));
            }
        }

        if (empty($_FILES['thumbnail']['tmp_name'])) {
            wp_die(esc_html__('A cover image is required.', 'sabri-reels'), '', array('response' => 400));
        }
        $thumb_limit = min(10 * MB_IN_BYTES, wp_max_upload_size());
        if (!empty($_FILES['thumbnail']['size']) && (int) $_FILES['thumbnail']['size'] > $thumb_limit) {
            wp_die(esc_html__('The cover image exceeds the allowed size.', 'sabri-reels'), '', array('response' => 400));
        }

        $duration = 0;
        if ('youtube' === $source || 'vimeo' === $source) {
            if (!$this->valid_remote_url($source, $url)) {
                wp_die(esc_html__('Provide a valid HTTPS URL from the selected approved provider.', 'sabri-reels'), '', array('response' => 400));
            }
            $duration = $this->remote_duration($source, $url);
            if (is_wp_error($duration)) {
                wp_die(esc_html($duration->get_error_message()), '', array('response' => 400));
            }
        } else {
            $limit = min(200 * MB_IN_BYTES, wp_max_upload_size());
            if (empty($_FILES['local_video']['tmp_name']) || empty($_FILES['local_video']['size']) || (int) $_FILES['local_video']['size'] > $limit) {
                wp_die(esc_html__('A valid local video within the server upload limit is required.', 'sabri-reels'), '', array('response' => 400));
            }
        }

        $status = SVW_Helpers::initial_status();
        if ($is_patient_case && !current_user_can('manage_reels')) {
            $status = 'pending';
        }

        $post_id = wp_insert_post(
            array(
                'post_type' => SVW_Helpers::TYPE,
                'post_status' => $status,
                'post_title' => $title,
                'post_excerpt' => $caption,
                'post_content' => $caption,
                'post_author' => get_current_user_id(),
                'comment_status' => 'open',
            ),
            true
        );
        if (is_wp_error($post_id)) {
            wp_die(esc_html__('The Reel could not be created.', 'sabri-reels'), '', array('response' => 500));
        }

        $uploaded = array();
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        if ('local' === $source) {
            $video_id = media_handle_upload(
                'local_video',
                $post_id,
                array(),
                array('test_form' => false, 'mimes' => array('mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogv' => 'video/ogg'))
            );
            if (is_wp_error($video_id)) {
                $this->cleanup_failed_submission($post_id, $uploaded);
                wp_die(esc_html__('The local video upload failed.', 'sabri-reels'), '', array('response' => 400));
            }
            $uploaded[] = (int) $video_id;
            $duration = $this->local_duration((int) $video_id);
            if (is_wp_error($duration)) {
                $this->cleanup_failed_submission($post_id, $uploaded);
                wp_die(esc_html($duration->get_error_message()), '', array('response' => 400));
            }
            update_post_meta($post_id, '_svw_attachment_id', (int) $video_id);
        }

        if ($duration < self::MIN_DURATION || $duration > self::MAX_DURATION) {
            $this->cleanup_failed_submission($post_id, $uploaded);
            wp_die(esc_html__('A Reel must be between 60 and 600 seconds according to authoritative media metadata.', 'sabri-reels'), '', array('response' => 400));
        }

        $thumbnail_id = media_handle_upload(
            'thumbnail',
            $post_id,
            array(),
            array('test_form' => false, 'mimes' => array('jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'))
        );
        if (is_wp_error($thumbnail_id)) {
            $this->cleanup_failed_submission($post_id, $uploaded);
            wp_die(esc_html__('The required cover image upload failed.', 'sabri-reels'), '', array('response' => 400));
        }
        $uploaded[] = (int) $thumbnail_id;
        if (!wp_attachment_is_image((int) $thumbnail_id) || !set_post_thumbnail($post_id, (int) $thumbnail_id)) {
            $this->cleanup_failed_submission($post_id, $uploaded);
            wp_die(esc_html__('The uploaded cover file is not a valid image.', 'sabri-reels'), '', array('response' => 400));
        }

        wp_set_object_terms($post_id, $category, SVW_Helpers::TAX);
        $metadata = array(
            'is_reel' => 1,
            'source' => $source,
            'video_url' => 'local' === $source ? '' : $url,
            'duration' => (int) $duration,
            'duration_verified' => 1,
            'duration_verified_at' => current_time('mysql', true),
            'transcript' => $transcript,
            'references' => $references,
            'safety' => $safety,
            'views' => 0,
            'likes' => 0,
            'dislikes' => 0,
            'score' => 0,
        );
        foreach ($metadata as $key => $value) {
            update_post_meta($post_id, '_svw_' . $key, $value);
        }
        if ($is_patient_case) {
            update_post_meta(
                $post_id,
                '_srl_case_attestation',
                array(
                    'anonymized' => 1,
                    'consent' => 1,
                    'user_id' => get_current_user_id(),
                    'recorded_at' => current_time('mysql', true),
                )
            );
        }

        $map = (array) get_option('srl_page_map', array());
        $destination = !empty($map['create']) ? get_permalink(absint($map['create'])) : home_url('/');
        wp_safe_redirect(add_query_arg('submitted', '1', $destination));
        exit;
    }

    private function cleanup_failed_submission($post_id, $attachment_ids) {
        foreach (array_unique(array_map('absint', (array) $attachment_ids)) as $attachment_id) {
            if ($attachment_id) {
                wp_delete_attachment($attachment_id, true);
            }
        }
        if ($post_id) {
            wp_delete_post($post_id, true);
        }
    }

    private function local_duration($attachment_id) {
        $file = get_attached_file($attachment_id);
        if (!$file || !is_readable($file)) {
            return new WP_Error('srl_media_unreadable', __('The uploaded video could not be read for duration verification.', 'sabri-reels'));
        }
        $metadata = wp_read_video_metadata($file);
        $duration = !empty($metadata['length']) ? (int) round((float) $metadata['length']) : 0;
        if (!$duration) {
            return new WP_Error('srl_duration_missing', __('The uploaded video has no readable authoritative duration metadata.', 'sabri-reels'));
        }
        return $duration;
    }

    private function valid_remote_url($source, $url) {
        if (!$url || 'https' !== strtolower((string) wp_parse_url($url, PHP_URL_SCHEME))) {
            return false;
        }
        return 'youtube' === $source ? (bool) $this->youtube_id($url) : (bool) $this->vimeo_id($url);
    }

    private function youtube_id($url) {
        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        if (!in_array($host, array('youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be'), true)) {
            return '';
        }
        $path = trim((string) wp_parse_url($url, PHP_URL_PATH), '/');
        if ('youtu.be' === $host && preg_match('/^([A-Za-z0-9_-]{6,20})/', $path, $match)) {
            return $match[1];
        }
        if (preg_match('~^(?:shorts|embed)/([A-Za-z0-9_-]{6,20})~', $path, $match)) {
            return $match[1];
        }
        parse_str((string) wp_parse_url($url, PHP_URL_QUERY), $query);
        return !empty($query['v']) && preg_match('/^[A-Za-z0-9_-]{6,20}$/', $query['v']) ? $query['v'] : '';
    }

    private function vimeo_id($url) {
        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        if (!in_array($host, array('vimeo.com', 'www.vimeo.com', 'player.vimeo.com'), true)) {
            return '';
        }
        $path = (string) wp_parse_url($url, PHP_URL_PATH);
        return preg_match('~(?:/video)?/([0-9]+)(?:$|/)~', $path, $match) ? $match[1] : '';
    }

    private function remote_duration($source, $url) {
        $filtered = apply_filters('srl_remote_duration_seconds', 0, $source, $url);
        if (is_numeric($filtered) && (int) $filtered > 0) {
            return (int) $filtered;
        }

        if ('vimeo' === $source) {
            $response = wp_safe_remote_get(
                'https://vimeo.com/api/oembed.json?url=' . rawurlencode($url),
                array('timeout' => 12, 'redirection' => 2, 'user-agent' => 'Sabri-Reels/' . SRL_VERSION)
            );
            if (is_wp_error($response) || 200 !== (int) wp_remote_retrieve_response_code($response)) {
                return new WP_Error('srl_vimeo_duration', __('Vimeo duration verification failed. Try again later or use a local upload.', 'sabri-reels'));
            }
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $duration = !empty($body['duration']) ? absint($body['duration']) : 0;
            return $duration ? $duration : new WP_Error('srl_vimeo_duration_missing', __('Vimeo did not return authoritative duration metadata.', 'sabri-reels'));
        }

        $video_id = $this->youtube_id($url);
        $api_key = defined('SRL_YOUTUBE_API_KEY') ? SRL_YOUTUBE_API_KEY : '';
        $api_key = (string) apply_filters('srl_youtube_api_key', $api_key);
        if (!$video_id || !$api_key) {
            return new WP_Error('srl_youtube_key_missing', __('YouTube duration verification requires an administrator-configured YouTube Data API key. No Reel is accepted without authoritative duration verification.', 'sabri-reels'));
        }
        $endpoint = add_query_arg(
            array('part' => 'contentDetails', 'id' => $video_id, 'key' => $api_key),
            'https://www.googleapis.com/youtube/v3/videos'
        );
        $response = wp_safe_remote_get($endpoint, array('timeout' => 12, 'redirection' => 0, 'user-agent' => 'Sabri-Reels/' . SRL_VERSION));
        if (is_wp_error($response) || 200 !== (int) wp_remote_retrieve_response_code($response)) {
            return new WP_Error('srl_youtube_duration', __('YouTube duration verification failed. Check the API configuration or use a local upload.', 'sabri-reels'));
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $iso = !empty($body['items'][0]['contentDetails']['duration']) ? (string) $body['items'][0]['contentDetails']['duration'] : '';
        $duration = $this->iso_duration_seconds($iso);
        return $duration ? $duration : new WP_Error('srl_youtube_duration_missing', __('YouTube did not return authoritative duration metadata.', 'sabri-reels'));
    }

    private function iso_duration_seconds($value) {
        if (!preg_match('/^P(?:(\d+)D)?T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/', $value, $match)) {
            return 0;
        }
        return absint(isset($match[1]) ? $match[1] : 0) * DAY_IN_SECONDS
            + absint(isset($match[2]) ? $match[2] : 0) * HOUR_IN_SECONDS
            + absint(isset($match[3]) ? $match[3] : 0) * MINUTE_IN_SECONDS
            + absint(isset($match[4]) ? $match[4] : 0);
    }

    private function contains_non_english_script($text) {
        return (bool) preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{0900}-\x{097F}\x{0400}-\x{04FF}\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}]/u', $text);
    }

    private function contains_obvious_patient_identifier($text) {
        $patterns = array(
            '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i',
            '/(?<!\d)(?:\+?\d[\d\s().-]{8,}\d)(?!\d)/',
            '/(?<!\d)\d{5}[- ]?\d{7}[- ]?\d(?!\d)/',
            '/\b(?:passport|national id|cnic|identity number)\s*[:#-]?\s*[A-Z0-9-]{5,}\b/i',
        );
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        return false;
    }

    public function progress() {
        check_ajax_referer('srl_progress', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Log in first.'), 401);
        }
        $this->rate_limit('progress', 180, MINUTE_IN_SECONDS, true);

        $reel_id = isset($_POST['reel']) ? absint($_POST['reel']) : 0;
        $progress = isset($_POST['progress']) ? absint($_POST['progress']) : 0;
        $replay = !empty($_POST['replay']);

        if (SVW_Helpers::TYPE !== get_post_type($reel_id)
            || 'publish' !== get_post_status($reel_id)
            || '1' !== (string) SVW_Helpers::meta($reel_id, 'is_reel', '0')) {
            wp_send_json_error(array('message' => 'Published Reel not found.'), 404);
        }

        $duration = absint(SVW_Helpers::meta($reel_id, 'duration'));
        if ($duration < self::MIN_DURATION || $duration > self::MAX_DURATION) {
            wp_send_json_error(array('message' => 'Reel duration is invalid.'), 409);
        }
        $progress = min($duration, $progress);
        $completed = $progress >= (int) ceil($duration * 0.9) ? 1 : 0;

        global $wpdb;
        $table = $wpdb->prefix . 'srl_history';
        $user_id = get_current_user_id();
        $existing = $wpdb->get_row(
            $wpdb->prepare("SELECT id,progress,completed,replays FROM {$table} WHERE user_id=%d AND reel_id=%d", $user_id, $reel_id),
            ARRAY_A
        );
        $now = current_time('mysql', true);

        if ($existing) {
            $replays = absint($existing['replays']);
            if ($replay && !empty($existing['completed'])) {
                $replays++;
            }
            $ok = $wpdb->update(
                $table,
                array(
                    'progress' => $progress,
                    'completed' => $completed,
                    'replays' => $replays,
                    'updated_at' => $now,
                ),
                array('id' => absint($existing['id'])),
                array('%d', '%d', '%d', '%s'),
                array('%d')
            );
        } else {
            $ok = $wpdb->insert(
                $table,
                array(
                    'user_id' => $user_id,
                    'reel_id' => $reel_id,
                    'progress' => $progress,
                    'completed' => $completed,
                    'replays' => 0,
                    'updated_at' => $now,
                ),
                array('%d', '%d', '%d', '%d', '%d', '%s')
            );
        }

        if (false === $ok) {
            wp_send_json_error(array('message' => 'Viewing progress could not be saved.'), 500);
        }
        wp_send_json_success(array('progress' => $progress, 'completed' => (bool) $completed));
    }

    private function rate_limit($action, $limit, $window, $ajax = false) {
        $user_id = get_current_user_id();
        $key = 'srl_' . sanitize_key($action) . '_' . $user_id;
        $count = absint(get_transient($key));
        if ($count >= $limit) {
            if ($ajax) {
                wp_send_json_error(array('message' => 'Please wait before trying again.'), 429);
            }
            wp_die(esc_html__('Please wait before trying again.', 'sabri-reels'), '', array('response' => 429));
        }
        set_transient($key, $count + 1, $window);
    }

    public function saved() {
        return $this->list_page('Saved Reels', 'saved');
    }

    public function history() {
        return $this->list_page('Recently Watched Reels', 'history');
    }

    private function list_page($title, $type) {
        if (!is_user_logged_in()) {
            return '<div class="srl-note"><a href="' . esc_url(wp_login_url(get_permalink())) . '">Log in to view this private page.</a></div>';
        }

        global $wpdb;
        if ('saved' === $type) {
            $ids = $wpdb->get_col(
                $wpdb->prepare("SELECT video_id FROM {$wpdb->prefix}svw_saves WHERE user_id=%d ORDER BY updated_at DESC", get_current_user_id())
            );
        } else {
            $ids = $wpdb->get_col(
                $wpdb->prepare("SELECT reel_id FROM {$wpdb->prefix}srl_history WHERE user_id=%d ORDER BY updated_at DESC", get_current_user_id())
            );
        }
        $ids = array_values(array_unique(array_map('absint', (array) $ids)));
        $items = get_posts(
            array(
                'post_type' => SVW_Helpers::TYPE,
                'post_status' => 'publish',
                'post__in' => $ids ? $ids : array(0),
                'orderby' => 'post__in',
                'posts_per_page' => 100,
                'meta_key' => '_svw_is_reel',
                'meta_value' => '1',
            )
        );

        ob_start();
        ?><main class="srl-shell srl-list"><h1><?php echo esc_html($title); ?></h1><?php
        if (!$items) {
            echo '<div class="srl-empty"><p>No Reels are available in this private list.</p></div>';
        } else {
            echo '<ol>';
            foreach ($items as $item) {
                echo '<li><a href="' . esc_url(get_permalink($item)) . '">' . esc_html($item->post_title) . '</a></li>';
            }
            echo '</ol>';
        }
        ?></main><?php
        return ob_get_clean();
    }

    public function menu() {
        add_menu_page('Reels Management', 'Reels Management', 'manage_reels', 'reels-management', array($this, 'admin'), 'dashicons-format-video', 31);
    }

    public function admin() {
        if (!current_user_can('manage_reels')) {
            wp_die(esc_html__('Access denied.', 'sabri-reels'), '', array('response' => 403));
        }
        $items = get_posts(
            array(
                'post_type' => SVW_Helpers::TYPE,
                'post_status' => array('pending', 'publish', 'draft', 'private'),
                'posts_per_page' => 200,
                'meta_key' => '_svw_is_reel',
                'meta_value' => '1',
                'orderby' => 'date',
                'order' => 'DESC',
            )
        );
        ?>
        <div class="wrap">
            <h1>Reels Management</h1>
            <table class="widefat striped">
                <thead><tr><th>Reel</th><th>Author</th><th>Status</th><th>Review action</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item) : ?>
                    <tr>
                        <td><a href="<?php echo esc_url(get_edit_post_link($item->ID)); ?>"><?php echo esc_html($item->post_title); ?></a></td>
                        <td><?php echo esc_html(get_the_author_meta('display_name', $item->post_author)); ?></td>
                        <td><?php echo esc_html(get_post_status($item)); ?></td>
                        <td>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="srl_review">
                                <input type="hidden" name="id" value="<?php echo absint($item->ID); ?>">
                                <?php wp_nonce_field('srl_review_' . $item->ID); ?>
                                <select name="status" required><option value="publish">Publish</option><option value="draft">Reject to draft</option><option value="private">Hide privately</option></select>
                                <label class="screen-reader-text" for="srl-note-<?php echo absint($item->ID); ?>">Review note</label>
                                <textarea id="srl-note-<?php echo absint($item->ID); ?>" name="review_note" maxlength="1000" placeholder="Required review note" required></textarea>
                                <button class="button button-primary">Apply</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function review() {
        if (!current_user_can('manage_reels')) {
            wp_die(esc_html__('Access denied.', 'sabri-reels'), '', array('response' => 403));
        }
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        check_admin_referer('srl_review_' . $id);
        $status = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';
        $note = isset($_POST['review_note']) ? sanitize_textarea_field(wp_unslash($_POST['review_note'])) : '';

        if (SVW_Helpers::TYPE !== get_post_type($id) || '1' !== (string) SVW_Helpers::meta($id, 'is_reel', '0')) {
            wp_die(esc_html__('The selected object is not a Reel.', 'sabri-reels'), '', array('response' => 400));
        }
        if (!in_array($status, array('publish', 'draft', 'private'), true) || !trim($note)) {
            wp_die(esc_html__('Choose a valid status and provide a review note.', 'sabri-reels'), '', array('response' => 400));
        }

        $old_status = get_post_status($id);
        $result = wp_update_post(array('ID' => $id, 'post_status' => $status), true);
        if (is_wp_error($result)) {
            wp_die(esc_html__('The moderation change could not be saved.', 'sabri-reels'), '', array('response' => 500));
        }

        $log = get_post_meta($id, '_srl_review_log', true);
        $log = is_array($log) ? $log : array();
        $log[] = array(
            'from' => $old_status,
            'to' => $status,
            'note' => $note,
            'reviewer_id' => get_current_user_id(),
            'reviewed_at' => current_time('mysql', true),
        );
        if (count($log) > 50) {
            $log = array_slice($log, -50);
        }
        update_post_meta($id, '_srl_review_log', $log);
        update_post_meta($id, '_srl_last_review_note', $note);

        $author = get_userdata((int) get_post_field('post_author', $id));
        if ($author && is_email($author->user_email)) {
            wp_mail(
                $author->user_email,
                sprintf('Reel review: %s', get_the_title($id)),
                "Status: {$status}\n\nReviewer note:\n{$note}\n\n" . get_permalink($id)
            );
        }

        wp_safe_redirect(admin_url('admin.php?page=reels-management&reviewed=1'));
        exit;
    }

    public function assets() {
        global $post;
        $map = (array) get_option('srl_page_map', array());
        $page_match = $post instanceof WP_Post && in_array((int) $post->ID, array_map('absint', $map), true);
        if (!$page_match) {
            return;
        }

        wp_enqueue_style('srl', SRL_URL . 'assets/reels.css', array(), SRL_VERSION);
        wp_enqueue_script('srl', SRL_URL . 'assets/reels.js', array(), SRL_VERSION, true);
        wp_localize_script(
            'srl',
            'srlData',
            array(
                'url' => admin_url('admin-ajax.php'),
                'progressNonce' => wp_create_nonce('srl_progress'),
                'interactionNonce' => wp_create_nonce('svw_action'),
                'loggedIn' => is_user_logged_in(),
                'messages' => array(
                    'saved' => 'Action saved.',
                    'error' => 'The action could not be completed.',
                    'report' => 'Report received.',
                ),
            )
        );
    }

    public function private_response_headers() {
        $map = (array) get_option('srl_page_map', array());
        foreach (array('create', 'saved', 'history') as $key) {
            if (!empty($map[$key]) && is_page(absint($map[$key]))) {
                nocache_headers();
                header('X-Robots-Tag: noindex, noarchive, nofollow', true);
                return;
            }
        }
    }

    public function robots($robots) {
        $map = (array) get_option('srl_page_map', array());
        foreach (array('create', 'saved', 'history') as $key) {
            if (!empty($map[$key]) && is_page(absint($map[$key]))) {
                $robots['noindex'] = true;
                $robots['noarchive'] = true;
                $robots['nofollow'] = true;
            }
        }
        return $robots;
    }

    public function privacy_exporters($exporters) {
        $exporters['sabri-reels'] = array(
            'exporter_friendly_name' => __('Reels viewing history', 'sabri-reels'),
            'callback' => array($this, 'privacy_export'),
        );
        return $exporters;
    }

    public function privacy_export($email, $page = 1) {
        $user = get_user_by('email', $email);
        if (!$user) {
            return array('data' => array(), 'done' => true);
        }
        global $wpdb;
        $per_page = 100;
        $offset = max(0, (absint($page) - 1) * $per_page);
        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT reel_id,progress,completed,replays,updated_at FROM {$wpdb->prefix}srl_history WHERE user_id=%d ORDER BY id ASC LIMIT %d OFFSET %d", $user->ID, $per_page, $offset)
        );
        $data = array();
        foreach ($rows as $row) {
            $data[] = array(
                'group_id' => 'sabri-reels-history',
                'group_label' => __('Reels viewing history', 'sabri-reels'),
                'item_id' => 'reel-history-' . absint($row->reel_id),
                'data' => array(
                    array('name' => 'Reel', 'value' => get_the_title($row->reel_id)),
                    array('name' => 'Progress in seconds', 'value' => absint($row->progress)),
                    array('name' => 'Completed', 'value' => $row->completed ? 'Yes' : 'No'),
                    array('name' => 'Replays', 'value' => absint($row->replays)),
                    array('name' => 'Last viewed', 'value' => $row->updated_at),
                ),
            );
        }
        return array('data' => $data, 'done' => count($rows) < $per_page);
    }

    public function privacy_erasers($erasers) {
        $erasers['sabri-reels'] = array(
            'eraser_friendly_name' => __('Reels viewing history', 'sabri-reels'),
            'callback' => array($this, 'privacy_erase'),
        );
        return $erasers;
    }

    public function privacy_erase($email, $page = 1) {
        $user = get_user_by('email', $email);
        if (!$user) {
            return array('items_removed' => false, 'items_retained' => false, 'messages' => array(), 'done' => true);
        }
        global $wpdb;
        $removed = false !== $wpdb->delete($wpdb->prefix . 'srl_history', array('user_id' => $user->ID), array('%d'));
        return array('items_removed' => $removed, 'items_retained' => false, 'messages' => array(), 'done' => true);
    }
}
