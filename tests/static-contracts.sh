#!/usr/bin/env bash
set -euo pipefail

plugin='reels/includes/class-srl-plugin.php'
bootstrap='reels/reels.php'
readme='reels/readme.txt'
js='reels/assets/reels.js'

grep -Fq 'Version: 0.2.0' "$bootstrap"
grep -Fq "define('SRL_VERSION', '0.2.0')" "$bootstrap"
grep -Fq 'Stable tag: 0.2.0' "$readme"
grep -Fq 'dependency_ready' "$plugin"
grep -Fq "update_option('srl_db_version'" "$plugin"
grep -Fq 'UNIQUE KEY user_reel' "$plugin"
grep -Fq 'KEY updated_at' "$plugin"
grep -Fq 'wp_read_video_metadata' "$plugin"
grep -Fq 'srl_remote_duration_seconds' "$plugin"
grep -Fq 'youtube/v3/videos' "$plugin"
grep -Fq 'vimeo.com/api/oembed.json' "$plugin"
grep -Fq 'duration_verified' "$plugin"
! grep -Fq 'name="duration"' "$plugin"
grep -Fq 'cleanup_failed_submission' "$plugin"
grep -Fq 'wp_attachment_is_image' "$plugin"
grep -Fq 'A cover image is required.' "$plugin"
grep -Fq "action: 'svw_action'" "$js"
grep -Fq "action', 'srl_progress'" "$js"
grep -Fq "'orderby' => 'post__in'" "$plugin"
grep -Fq 'wp_privacy_personal_data_exporters' "$plugin"
grep -Fq 'wp_privacy_personal_data_erasers' "$plugin"
grep -Fq 'nocache_headers' "$plugin"
grep -Fq 'review_note' "$plugin"
grep -Fq '_srl_review_log' "$plugin"
grep -Fq 'contains_obvious_patient_identifier' "$plugin"
grep -Fq 'case_anonymized' "$plugin"
grep -Fq 'case_consent' "$plugin"
grep -Fq 'prefers-reduced-motion' reels/assets/reels.css
grep -Fq "event.key === 'ArrowDown'" "$js"
grep -Fq 'posts_per_page' "$plugin"
grep -Fq 'No published Reels found' "$plugin"

printf 'Static corrective contracts: PASS\n'
