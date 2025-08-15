<?php
if (!defined('ABSPATH')) exit;

/**
 * AVDB All In One Packages
 * * QUAN TRỌNG: 
 * 1. 'slug' PHẢI là tên "sạch", không có '-main'. Slug này phải khớp với slug trong manifest.json của bạn.
 * 2. 'download_url' có thể trỏ đến file ZIP của Release hoặc của Branch. Plugin sẽ xử lý được cả hai.
 */
$avdb_webkit_packages = [
  [
    'slug' => 'adult-api-crawler-for-wp-script',
    'name' => 'Crawler For Wp-script',
    'type' => 'plugin',
    'description' => 'Crawler for wp-script ver 2.0.0',
    'download_url' => 'https://github.com/Avdbapi-1/adult-api-crawler-for-wp-script/releases/download/V2.1.0/adult-api-crawler-for-wp-script.zip',
    'image' => 'https://help.avdbapi.com/image/wpscript.jpg',
    'group' => 'official',
    'note' => 'Hot'
  ],
  [
    'slug' => 'adult-api-crawler-for-eroz-theme',
    'name' => 'Crawler for theme Eroz',
    'type' => 'plugin',
    'description' => 'Crawler for eroz theme ver 2.0.0',
    'download_url' => 'https://github.com/Avdbapi-1/adult-api-crawler-for-eroz-theme/releases/download/V2.1.0/adult-api-crawler-for-eroz-theme.zip',
    'image' => 'https://help.avdbapi.com/image/eroz.jpg',
    'group' => 'official',
    'note' => 'Recommended'
  ],
  [
    'slug' => 'crawl-avdbapi-vidmov',
    'name' => 'Crawler for theme Vidmov',
    'type' => 'plugin',
    'description' => 'Crawler for vidmov theme ver 2.0.0',
    'download_url' => 'https://github.com/Avdbapi-1/crawl-avdbapi-vidmov/releases/download/V2.4.0/crawl-avdbapi-vidmov.zip',
    'image' => 'https://help.avdbapi.com/image/vidmov.jpg',
    'group' => 'official',
    'note' => ''
  ],
  [
    'slug' => 'Theme-Eroz-WP',
    'name' => 'Eroz Theme',
    'type' => 'theme',
    'description' => 'Eroz Theme',
    'download_url' => 'https://github.com/Avdbapi-1/Theme-Eroz-WP/archive/refs/heads/main.zip',
    'image' => 'https://help.avdbapi.com/image/eroz.jpg',
    'group' => 'official',
    'note' => 'Recommended'
  ],
  [
    'slug' => 'theme-vidmov-wordpress',
    'name' => 'Theme Vidmov Wordpress',
    'type' => 'theme',
    'description' => 'Theme Vidmov Wordpress',
    'download_url' => 'https://github.com/Avdbapi-1/theme-vidmov-wordpress/archive/refs/heads/main.zip',
    'image' => 'https://help.avdbapi.com/image/vidmov.jpg',
    'group' => 'official',
    'note' => 'Hot'
  ],
  [
    'slug' => 'theme-vidmov-wordpress-childtheme',
    'name' => 'Theme child Vidmov',
    'type' => 'theme',
    'description' => 'Vidmov Child',
    'download_url' => 'https://github.com/Avdbapi-1/theme-vidmov-wordpress-childtheme/archive/refs/heads/main.zip',
    'image' => 'https://help.avdbapi.com/image/vidmov.jpg',
    'group' => 'official',
    'note' => 'required'
  ],
  [
    'slug' => 'wp-script-core',
    'name' => 'Wp-script',
    'type' => 'plugin',
    'description' => 'Wp-script official',
    'download_url' => 'https://wp-script-products.s3.us-east-2.amazonaws.com/wp-script-core.zip',
    'image' => 'https://help.avdbapi.com/image/wpscript.jpg',
    'group' => 'thirdparty',
    'note' => 'Hot'
  ]
];


$avdbwki_current_plugin = plugin_basename(__FILE__);
$avdbwki_plugin_slug = dirname($avdbwki_current_plugin);
$avdbwki_plugin_file = $avdbwki_current_plugin;
$avdbwki_self_update_url = 'https://help.avdbapi.com/manifest.json';

// --- CORE LOGIC ---

function avdbwki_get_manifest_data($force_refresh = false) {
    global $avdbwki_self_update_url;
    $cache_key = 'avdbwki_manifest_data';
    $cached_data = get_transient($cache_key);
    if (!$force_refresh && $cached_data !== false) return $cached_data;
    $plugin_info = get_plugin_data(__FILE__);
    $response = wp_remote_get($avdbwki_self_update_url, ['timeout' => 15, 'headers' => ['User-Agent' => 'AVDB-All-In-One/' . ($plugin_info['Version'] ?? '1.0.0')]]);
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) return false;
    $json = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($json)) return false;
    set_transient($cache_key, $json, 3600);
    return $json;
}

// --- SELF-UPDATE LOGIC FOR THE PLUGIN ITSELF ---

add_filter('pre_set_site_transient_update_plugins', 'avdbwki_check_for_self_update');
add_filter('plugins_api', 'avdbwki_plugin_info', 20, 3);
add_action('upgrader_process_complete', 'avdbwki_after_self_update', 10, 2);

function avdbwki_check_for_self_update($transient) {
    global $avdbwki_plugin_file;
    if (empty($transient->checked)) return $transient;
    $plugin_data = get_plugin_data(__FILE__);
    $current_version = $plugin_data['Version'] ?: '1.0.0';
    $json = avdbwki_get_manifest_data();
    if (!$json) return $transient;
    $data = null;
    foreach ($json as $item) {
        if (($item['slug'] ?? '') === 'avdb-all-in-one') {
            $data = $item;
            break;
        }
    }
    if (!$data || empty($data['version'])) return $transient;
    if (version_compare($current_version, $data['version'], '<')) {
        $transient->response[$avdbwki_plugin_file] = (object) [
            'slug' => dirname($avdbwki_plugin_file),
            'plugin' => $avdbwki_plugin_file,
            'new_version' => $data['version'],
            'url' => $data['homepage'] ?? '',
            'package' => $data['download_url'] ?? '',
            'tested' => $data['tested'] ?? get_bloginfo('version'),
        ];
    }
    return $transient;
}

function avdbwki_plugin_info($result, $action, $args) {
    global $avdbwki_plugin_slug;
    if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== $avdbwki_plugin_slug) {
        return $result;
    }
    $json = avdbwki_get_manifest_data();
    if (!$json) return $result;
    $data = null;
    foreach ($json as $item) {
        if (($item['slug'] ?? '') === 'avdb-all-in-one') {
            $data = $item;
            break;
        }
    }
    if (!$data) return $result;
    return (object) [
        'name' => $data['name'] ?? 'AVDB All In One',
        'slug' => $avdbwki_plugin_slug,
        'version' => $data['version'] ?? '',
        'author' => 'AVDB Team',
        'homepage' => $data['homepage'] ?? '',
        'sections' => [
            'description' => $data['description'] ?? 'Plugin for managing AVDB packages installation.',
            'changelog' => $data['changelog'] ?? 'New update available.',
        ],
        'download_link' => $data['download_url'] ?? '',
        'tested' => $data['tested'] ?? get_bloginfo('version'),
    ];
}

function avdbwki_after_self_update($upgrader, $hook_extra) {
    global $avdbwki_plugin_file;
    if (isset($hook_extra['action']) && $hook_extra['action'] === 'update' && $hook_extra['type'] === 'plugin' ) {
        if (isset($hook_extra['plugins']) && in_array($avdbwki_plugin_file, $hook_extra['plugins'])) {
             wp_clean_plugins_cache();
             delete_transient('avdbwki_manifest_data');
        }
    }
}


// --- SMART DETECTION FUNCTIONS ---

function avdbwki_get_installed_path($pkg, $allPlugins = null, $allThemes = null) {
    static $path_cache = [];
    $cache_key = $pkg['slug'];
    if (isset($path_cache[$cache_key])) return $path_cache[$cache_key];

    if ($allPlugins === null) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; $allPlugins = get_plugins(); }
    if ($allThemes === null) { require_once ABSPATH . 'wp-admin/includes/theme.php'; $allThemes = wp_get_themes(); }

    $type = strtolower($pkg['type']);
    $canonical_slug = strtolower($pkg['slug']);
    $possible_slugs = [$canonical_slug, $canonical_slug . '-main', $canonical_slug . '-master'];

    if ($type === 'plugin') {
        foreach ($allPlugins as $path => $data) {
            $pluginFolder = strtolower(dirname($path));
            if (in_array($pluginFolder, $possible_slugs)) {
                $path_cache[$cache_key] = $path;
                return $path;
            }
        }
    } elseif ($type === 'theme') {
        foreach ($allThemes as $stylesheet => $theme) {
            $themeFolder = strtolower($stylesheet);
            if (in_array($themeFolder, $possible_slugs)) {
                $path_cache[$cache_key] = $stylesheet;
                return $stylesheet;
            }
        }
    }
    $path_cache[$cache_key] = '';
    return '';
}

function avdbwki_is_package_installed($pkg, $plugins = null, $themes = null) {
    return !empty(avdbwki_get_installed_path($pkg, $plugins, $themes));
}

function avdbwki_get_installed_version($pkg, $allPlugins = null, $allThemes = null) {
    static $version_cache = [];
    $cache_key = $pkg['slug'] . '_version';
    if (isset($version_cache[$cache_key])) return $version_cache[$cache_key];
    
    $path = avdbwki_get_installed_path($pkg, $allPlugins, $allThemes);
    if (empty($path)) {
        $version_cache[$cache_key] = '';
        return '';
    }

    if ($allPlugins === null) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; $allPlugins = get_plugins(); }
    if ($allThemes === null) { require_once ABSPATH . 'wp-admin/includes/theme.php'; $allThemes = wp_get_themes(); }

    $type = strtolower($pkg['type']);
    $version = '';

    if ($type === 'plugin') {
        if (isset($allPlugins[$path])) {
            $version = $allPlugins[$path]['Version'] ?? '';
        }
    } elseif ($type === 'theme') {
        if (isset($allThemes[$path])) {
            $version = $allThemes[$path]->get('Version') ?: '';
        }
    }
    
    $version_cache[$cache_key] = $version;
    return $version;
}

// --- ADMIN PAGE & UI ---

add_action('admin_menu', function() {
    add_menu_page('AVDB All In One', 'AVDB All In One', 'manage_options', 'avdb-all-in-one', 'avdb_webkit_installer_page', 'dashicons-admin-generic', 3);
});

function avdb_webkit_installer_page() {
    global $avdb_webkit_packages;
    $official = array_filter($avdb_webkit_packages, function($p){return $p['group']==='official';});
    $thirdparty = array_filter($avdb_webkit_packages, function($p){return $p['group']==='thirdparty';});
    wp_cache_delete('plugins', 'plugins');
    $allPlugins = get_plugins();
    $allThemes = wp_get_themes();
    ?>
    <style>
    .avdbwki-wrap * { box-sizing: border-box; }
    .avdbwki-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; font-size: 14px; line-height: 1.5; color: #111827; }
    .avdbwki-wrap .avdbwki-title { font-size: 24px !important; line-height: 1.25 !important; font-weight: 700 !important; margin: 0 0 10px 0 !important; color: #111827 !important; }
    .avdbwki-wrap .avdbwki-grid { display: flex; flex-wrap: wrap; gap: 28px; margin-top: 20px; }
    .avdbwki-card { --card-bg: #ffffff; --card-accent: #7c3aed; --card-text: #1e293b; --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); width: 220px; min-height: 420px; background: var(--card-bg); border-radius: 20px; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: var(--card-shadow); border: 1px solid rgba(255, 255, 255, 0.2); display: flex; flex-direction: column; justify-content: flex-start; }
    .avdbwki-card__shine { position: absolute; inset: 0; background: linear-gradient(120deg,rgba(255,255,255,0) 40%,rgba(255,255,255,0.8) 50%,rgba(255,255,255,0) 60%); opacity: 0; transition: opacity 0.3s ease; }
    .avdbwki-card__glow { position: absolute; inset: -10px; background: radial-gradient(circle at 50% 0%,rgba(124,58,237,0.3) 0%,rgba(124,58,237,0) 70%); opacity: 0; transition: opacity 0.5s ease; }
    .avdbwki-card__content { padding: 1.25em; height: 100%; display: flex; flex-direction: column; gap: 0.75em; position: relative; z-index: 2; }
    .avdbwki-card__badge { position: absolute; top: 12px; right: 12px; background: #10b981; color: #fff; padding: 0.25em 0.5em; border-radius: 999px; font-size: 0.7em; font-weight: 600; transform: scale(0); opacity: 0; transition: all 0.3s ease; }
    .avdbwki-card__note { position: absolute; top: 12px; left: 12px; background: #3b82f6; color: #fff; padding: 0.25em 0.5em; border-radius: 999px; font-size: 0.7em; font-weight: 600; z-index: 3; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .avdbwki-card__note.avdbwki-note-recommended { background: #10b981; }
    .avdbwki-card__note.avdbwki-note-hot { background: #ef4444; }
    .avdbwki-card__note.avdbwki-note-new { background: #8b5cf6; }
    .avdbwki-card__note.avdbwki-note-popular { background: #f59e0b; }
    .avdbwki-card__note.avdbwki-note-required { background: #dc2626; }
    .avdbwki-card__image { width: 100%; height: 160px; flex-shrink: 0; background: linear-gradient(45deg, #a78bfa, #8b5cf6); border-radius: 12px; transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); position: relative; overflow: hidden; cursor: zoom-in; }
    .avdbwki-card__image::after { content: ""; position: absolute; inset: 0; background: radial-gradient(circle at 30% 30%,rgba(255,255,255,0.1) 0%,transparent 30%),repeating-linear-gradient(45deg,rgba(139,92,246,0.1) 0px,rgba(139,92,246,0.1) 2px,transparent 2px,transparent 4px); opacity: 0.5; pointer-events: none; }
    .avdbwki-card__text { display: flex; flex-direction: column; gap: 0.25em; flex-grow: 1; }
    .avdbwki-card__title { color: var(--card-text); font-size: 20px !important; line-height: 1.3 !important; margin: 0 !important; font-weight: 800 !important; transition: all 0.3s ease; }
    .avdbwki-card__description { color: var(--card-text); font-size: 14px !important; line-height: 1.5 !important; margin: 0 !important; opacity: 0.8; transition: all 0.3s ease; }
    .avdbwki-install-btn { display: block; width: 100%; text-align: center; border-radius: 12px; padding: 12px 16px; font-weight: 800; font-size: 18px; border: none !important; cursor: pointer; transition: all .2s ease; background: #3b82f6; color: #fff; }
    .avdbwki-install-btn.is-working { background: #cbd5e1; color: #111827; cursor: wait; }
    .avdbwki-install-btn.is-installed { background: #22c55e; color: #fff; cursor: default; }
    .avdbwki-install-btn:not(.is-installed):not(.is-working):hover { background: #2563eb; transform: translateY(-1px); }
    .avdbwki-card__bottom { margin-top: auto; }
    .avdbwki-card:hover { transform: translateY(-10px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04); border-color: rgba(124,58,237,0.2); }
    .avdbwki-card:hover .avdbwki-card__shine { opacity: 1; animation: avdbwki-shine 3s infinite; }
    .avdbwki-card:hover .avdbwki-card__glow { opacity: 1; }
    .avdbwki-card:hover .avdbwki-card__badge { transform: scale(1); opacity: 1; z-index: 1; }
    .avdbwki-card:hover .avdbwki-card__image { transform: translateY(-5px) scale(1.03); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    .avdbwki-card:hover .avdbwki-card__title { color: var(--card-accent); }
    .avdbwki-card:hover .avdbwki-card__description { opacity: 1; }
    .avdbwki-card:active { transform: translateY(-5px) scale(0.98); }
    @keyframes avdbwki-shine { 0% { background-position: -100% 0; } 100% { background-position: 200% 0; } }
    .avdbwki-card__image img { width: 100% !important; height: 100% !important; object-fit: cover; border-radius: 12px; display: block; }
    .avdbwki-section-title { font-size: 20px !important; font-weight: 800 !important; margin: 32px 0 12px 0 !important; border-left: 5px solid; padding-left: 10px; }
    .avdbwki-section-title.official { color: #22c55e; border-color: #22c55e; } .avdbwki-section-title.thirdparty { color: #ef4444; border-color: #ef4444; }
    .avdbwki-note { font-size: 13px; color: #ef4444; margin-bottom: 18px; }
    .avdbwki-meta { font-size: 12px; color: #6b7280; }
    .avdbwki-status { font-weight: 700; }
    .avdbwki-status.ok { color: #16a34a; } .avdbwki-status.warn { color: #d97706; } .avdbwki-status.update { color: #dc2626; }
    .avdbwki-btn-line { display: flex; gap: 10px; }
    .avdbwki-update-btn { visibility: hidden; flex-shrink: 0; padding: 10px 14px; border-radius: 10px; background: #f59e0b; font-weight: 800; border: none; cursor: pointer; color: #111; }
    .avdbwki-update-btn.visible { visibility: visible; }
    .avdbwki-update-btn[disabled] { opacity: .6; cursor: wait; }
    .avdbwki-update-btn:not([disabled]):hover { background: #d97706; }
    .avdbwki-find-btn { margin: 10px 0 0 0; display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; font-weight:700; border:none; cursor:pointer; }
    .avdbwki-find-btn[disabled] { opacity:.7; cursor: wait; }
    .avdbwki-find-btn { background: #0ea5e9; color:#fff; }
    .avdbwki-find-btn:not([disabled]):hover { background: #0284c7; }
    .avdbwki-loading { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.3); border-radius: 50%; border-top-color: #fff; animation: avdbwki-spin 1s linear infinite; }
    @keyframes avdbwki-spin { to { transform: rotate(360deg); } }
    .avdbwki-alert { padding: 12px 16px; border-radius: 8px; margin: 16px 0; }
    .avdbwki-alert.success { background: #dcfce7; color: #166534; }
    .avdbwki-alert.error { background: #fef2f2; color: #dc2626; }
    .avdbwki-alert.warning { background: #fefce8; color: #ca8a04; }
    </style>
    <div class="wrap avdbwki-wrap">
        <h1 class="avdbwki-title">AVDB All In One</h1>
        <button type="button" id="avdbwki-find-updates" class="avdbwki-find-btn"><span class="text">Check for Updates</span></button>
        <button type="button" id="avdbwki-clear-cache" class="avdbwki-find-btn" style="background: #f59e0b;"><span class="text">Clear Cache</span></button>
        <div id="avdbwki-result"></div>
        <div class="avdbwki-section-title official">AVDB Official Packages</div>
        <div class="avdbwki-grid">
        <?php foreach ($official as $pkg):
            $is_installed = avdbwki_is_package_installed($pkg, $allPlugins, $allThemes);
            $installed_version = avdbwki_get_installed_version($pkg, $allPlugins, $allThemes);
        ?>
            <div class="avdbwki-card" data-slug="<?php echo esc_attr($pkg['slug']); ?>">
                <div class="avdbwki-card__shine"></div><div class="avdbwki-card__glow"></div>
                <?php if (!empty($pkg['note'])): ?><div class="avdbwki-card__note avdbwki-note-<?php echo strtolower(htmlspecialchars($pkg['note'])); ?>"><?php echo htmlspecialchars($pkg['note']); ?></div><?php endif; ?>
              <div class="avdbwki-card__content">
                <div class="avdbwki-card__badge"><?php echo strtoupper(htmlspecialchars($pkg['type'])); ?></div>
                <div class="avdbwki-card__image"><?php if (!empty($pkg['image'])): ?><img src="<?php echo esc_url($pkg['image']); ?>" alt="<?php echo esc_attr($pkg['name']); ?>"><?php endif; ?></div>
                <div class="avdbwki-card__text">
                  <p class="avdbwki-card__title"><?php echo htmlspecialchars($pkg['name']); ?></p>
                  <p class="avdbwki-card__description"><?php echo htmlspecialchars($pkg['description']); ?></p>
                  <p class="avdbwki-meta">Installed: <span class="avdbwki-installed-version"><?php echo $installed_version ? htmlspecialchars($installed_version) : '—'; ?></span></p>
                  <p class="avdbwki-meta">Latest: <span class="avdbwki-latest-version">—</span></p>
                  <p class="avdbwki-status <?php echo $is_installed ? 'ok' : 'warn'; ?>"><?php echo $is_installed ? 'Installed' : 'Not installed'; ?></p>
                </div>
                <div class="avdbwki-card__bottom">
                  <div class="avdbwki-btn-line">
                    <button type="button" class="avdbwki-install-btn <?php echo $is_installed ? 'is-installed' : ''; ?>" data-slug="<?php echo esc_attr($pkg['slug']); ?>"><?php echo $is_installed ? 'Installed' : 'Install'; ?></button>
                    <button type="button" class="avdbwki-update-btn" data-slug="<?php echo esc_attr($pkg['slug']); ?>">Update</button>
                  </div>
                </div>
              </div>
            </div>
        <?php endforeach; ?>
        </div>
        <?php if(count($thirdparty)): ?>
        <div class="avdbwki-section-title thirdparty">Third-party Packages</div>
        <div class="avdbwki-note">We do not guarantee the safety of third-party packages. Please use at your own risk.</div>
        <div class="avdbwki-grid">
        <?php foreach ($thirdparty as $pkg):
            $is_installed = avdbwki_is_package_installed($pkg, $allPlugins, $allThemes);
            $installed_version = avdbwki_get_installed_version($pkg, $allPlugins, $allThemes);
        ?>
            <div class="avdbwki-card" data-slug="<?php echo esc_attr($pkg['slug']); ?>">
                <div class="avdbwki-card__shine"></div><div class="avdbwki-card__glow"></div>
                <?php if (!empty($pkg['note'])): ?><div class="avdbwki-card__note avdbwki-note-<?php echo strtolower(htmlspecialchars($pkg['note'])); ?>"><?php echo htmlspecialchars($pkg['note']); ?></div><?php endif; ?>
              <div class="avdbwki-card__content">
                <div class="avdbwki-card__badge"><?php echo strtoupper(htmlspecialchars($pkg['type'])); ?></div>
                <div class="avdbwki-card__image"><?php if (!empty($pkg['image'])): ?><img src="<?php echo esc_url($pkg['image']); ?>" alt="<?php echo esc_attr($pkg['name']); ?>"><?php endif; ?></div>
                <div class="avdbwki-card__text">
                  <p class="avdbwki-card__title"><?php echo htmlspecialchars($pkg['name']); ?></p>
                  <p class="avdbwki-card__description"><?php echo htmlspecialchars($pkg['description']); ?></p>
                  <p class="avdbwki-meta">Installed: <span class="avdbwki-installed-version"><?php echo $installed_version ? htmlspecialchars($installed_version) : '—'; ?></span></p>
                  <p class="avdbwki-meta">Latest: <span class="avdbwki-latest-version">—</span></p>
                  <p class="avdbwki-status <?php echo $is_installed ? 'ok' : 'warn'; ?>"><?php echo $is_installed ? 'Installed' : 'Not installed'; ?></p>
                </div>
                <div class="avdbwki-card__bottom">
                  <div class="avdbwki-btn-line">
                    <button type="button" class="avdbwki-install-btn <?php echo $is_installed ? 'is-installed' : ''; ?>" data-slug="<?php echo esc_attr($pkg['slug']); ?>"><?php echo $is_installed ? 'Installed' : 'Install'; ?></button>
                    <button type="button" class="avdbwki-update-btn" data-slug="<?php echo esc_attr($pkg['slug']); ?>">Update</button>
                  </div>
                </div>
              </div>
            </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div id="avdbwki-image-modal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(30,41,59,0.7);align-items:center;justify-content:center;">
      <div style="position:relative;max-width:96vw;max-height:96vh;display:flex;align-items:center;justify-content:center;">
        <img id="avdbwki-image-modal-img" src="" alt="Zoom" style="max-width:96vw;max-height:96vh;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.25);background:#fff;">
        <button id="avdbwki-image-modal-close" style="position:absolute;top:8px;right:8px;background:#fff;border:none;border-radius:50%;width:36px;height:36px;box-shadow:0 2px 8px rgba(0,0,0,0.15);font-size:22px;cursor:pointer;">&times;</button>
      </div>
    </div>

    <script>
    jQuery(document).ready(function($){
        // --- Setup Functions ---
        function showMessage(msg, type='success'){ var c=type==='error'?'error':(type==='warning'?'warning':'success'); $('#avdbwki-result').html('<div class="avdbwki-alert '+c+'">'+msg+'</div>').show(); if(type==='success') setTimeout(function(){$('#avdbwki-result').fadeOut(500,function(){$(this).empty()})},5000)}
        function compareVersions(v1,v2){ if(!v1||!v2||v1==='—'||v2==='—')return 0;var p1=v1.replace('v','').split('.').map(n=>parseInt(n)||0),p2=v2.replace('v','').split('.').map(n=>parseInt(n)||0);for(var i=0;i<Math.max(p1.length,p2.length);i++){var a=p1[i]||0,b=p2[i]||0;if(a>b)return 1;if(a<b)return -1}return 0}
        
        // --- Event Handlers ---
        $('.avdbwki-install-btn').on('click',function(){var t=$(this);if(t.hasClass('is-installed')||t.hasClass('is-working'))return;var s=t.data('slug'),n=t.closest('.avdbwki-card').find('.avdbwki-card__title').text();t.addClass('is-working').html('<span class="avdbwki-loading"></span> Installing...');$('#avdbwki-result').hide();$.post(ajaxurl,{action:'avdb_webkit_install',slug:s,_wpnonce:'<?php echo wp_create_nonce("avdb_install_nonce"); ?>'}).done(function(r){if(r.success){showMessage(r.data.message||'Success!','success');setTimeout(function(){location.reload()},2e3)}else{t.removeClass('is-working').text('Install');showMessage('Install failed: '+(r.data||'Unknown error'),'error')}}).fail(function(){t.removeClass('is-working').text('Install');showMessage('Install failed: Network error','error')})});
        $('#avdbwki-find-updates').on('click',function(){var t=$(this);if(t.prop('disabled'))return;t.prop('disabled',!0).find('.text').text('Checking...');$('#avdbwki-result').hide();$.post(ajaxurl,{action:'avdbwki_find_updates',_wpnonce:'<?php echo wp_create_nonce("avdb_update_nonce"); ?>'}).done(function(r){if(!r.success){showMessage('Update check failed: '+(r.data||'Server error'),'error');return}var s=r.data||{},u=0;$('.avdbwki-card').each(function(){var e=$(this),a=e.data('slug'),i=s[a];if(!i)return;if(i.latest_version)e.find('.avdbwki-latest-version').text(i.latest_version);var n=e.find('.avdbwki-installed-version').text().trim(),l=i.latest_version,d=e.find('.avdbwki-status'),c=e.find('.avdbwki-update-btn');if(i.installed&&l&&n!=='—'){if(compareVersions(n,l)<0){d.removeClass('ok warn').addClass('update').text('Update: v'+l);c.addClass('visible').data('version',l);u++}else{d.removeClass('warn update').addClass('ok').text('Up to date');c.removeClass('visible')}}else if(!i.installed){d.removeClass('ok update').addClass('warn').text('Not installed');c.removeClass('visible')}});showMessage(u>0?u+' package(s) have updates.':'All packages are up to date.',u>0?'warning':'success')}).fail(function(){showMessage('Update check failed: Network error','error')}).always(function(){t.prop('disabled',!1).find('.text').text('Check for Updates')})});
        $('#avdbwki-clear-cache').on('click',function(){var t=$(this);if(t.prop('disabled'))return;t.prop('disabled',!0).find('.text').text('Clearing...');$.post(ajaxurl,{action:'avdbwki_clear_cache',_wpnonce:'<?php echo wp_create_nonce("avdb_update_nonce"); ?>'}).done(function(r){showMessage(r.success?'Cache cleared.':'Failed to clear cache.',r.success?'success':'error')}).fail(function(){showMessage('Failed to clear cache: Network error','error')}).always(function(){t.prop('disabled',!1).find('.text').text('Clear Cache')})});
        $('.avdbwki-grid').on('click','.avdbwki-update-btn.visible:not([disabled])',function(){var t=$(this),s=t.data('slug'),e=t.closest('.avdbwki-card'),n=e.find('.avdbwki-card__title').text(),o=t.data('version');if(!confirm('Update '+n+' to version '+o+'?'))return;t.prop('disabled',!0).html('<span class="avdbwki-loading"></span>');$('#avdbwki-result').hide();$.post(ajaxurl,{action:'avdbwki_update_package',slug:s,_wpnonce:'<?php echo wp_create_nonce("avdb_update_nonce"); ?>'}).done(function(r){if(r.success){showMessage(n+' updated. '+(r.data.message||''),'success');setTimeout(function(){location.reload()},2e3)}else{t.prop('disabled',!1).text('Update');showMessage('Update failed: '+(r.data||'Unknown error'),'error')}}).fail(function(){t.prop('disabled',!1).text('Update');showMessage('Update failed: Network error','error')})});

        // ADDED BACK: Image Zoom Modal Javascript
        $('.avdbwki-card__image').on('click', function(e){
            var img = $(this).find('img');
            if(!img.length) return;
            $('#avdbwki-image-modal-img').attr('src', img.attr('src'));
            $('#avdbwki-image-modal').css({display:'flex', opacity:0}).animate({opacity:1}, 150);
        });
        $('#avdbwki-image-modal, #avdbwki-image-modal-close').on('click', function(e){
            // Close only if the background or the close button is clicked
            if(e.target === this || $(e.target).is('#avdbwki-image-modal-close')) {
                $('#avdbwki-image-modal').animate({opacity:0}, 100, function(){ $(this).css('display', 'none'); });
            }
        });
        $('#avdbwki-image-modal-img').on('click', function(e){ e.stopPropagation(); }); // Prevent modal from closing when image is clicked
    });
    </script>
    <?php
}

// --- AJAX HANDLERS ---

add_action('wp_ajax_avdb_webkit_install', function() {
    if (!current_user_can('manage_options') || !check_ajax_referer('avdb_install_nonce', '_wpnonce', false)) wp_send_json_error('Security check failed');
    
    $slug = sanitize_text_field($_POST['slug'] ?? '');
    if (empty($slug)) wp_send_json_error('No package selected');
    
    global $avdb_webkit_packages;
    $pkg = null;
    foreach ($avdb_webkit_packages as $p) { if ($p['slug'] === $slug) { $pkg = $p; break; } }
    if (!$pkg) wp_send_json_error('Package not found');
    
    if (avdbwki_is_package_installed($pkg)) wp_send_json_error($pkg['name'] . ' is already installed.');
    
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    if (!WP_Filesystem()) wp_send_json_error('Cannot access filesystem');
    
    $url = $pkg['download_url'];
    $type = strtolower($pkg['type']);
    $skin = new WP_Ajax_Upgrader_Skin();
    $upgrader = null;

    try {
        if ($type === 'plugin') {
            $upgrader = new Plugin_Upgrader($skin);
        } elseif ($type === 'theme') {
            $upgrader = new Theme_Upgrader($skin);
        } else {
            wp_send_json_error('Unknown package type');
        }
        
        $result = $upgrader->install($url);
        
        if (is_wp_error($result) || $result === false) {
             $error_message = $skin->get_error_messages() ? implode('; ', $skin->get_error_messages()) : 'Installation failed.';
             if(is_wp_error($result)) $error_message = $result->get_error_message();
             wp_send_json_error($error_message);
        }

        if ($type === 'plugin') {
            $destination_name = $skin->result['destination_name'] ?? null;
            if ($destination_name) {
                wp_clean_plugins_cache(true);
                $all_plugins = get_plugins();
                $plugin_file = null;
                foreach ($all_plugins as $file => $details) {
                    if (strpos($file, $destination_name . '/') === 0) {
                        $plugin_file = $file;
                        break;
                    }
                }

                if ($plugin_file) {
                    $activation = activate_plugin($plugin_file);
                    if (is_wp_error($activation)) {
                        wp_send_json_success(['message' => $pkg['name'] . ' installed but failed to activate: ' . $activation->get_error_message()]);
                    } else {
                        wp_send_json_success(['message' => $pkg['name'] . ' installed and activated.']);
                    }
                } else {
                    wp_send_json_success(['message' => $pkg['name'] . ' installed, but could not find its main file to auto-activate.']);
                }
            } else {
                 wp_send_json_success(['message' => $pkg['name'] . ' installed, but could not be auto-activated.']);
            }
        } else {
            wp_clean_themes_cache(true);
            wp_send_json_success(['message' => $pkg['name'] . ' theme installed successfully.']);
        }
    } catch (Exception $e) { wp_send_json_error('Exception: ' . $e->getMessage()); }
});

add_action('wp_ajax_avdbwki_find_updates', function() {
    if (!current_user_can('manage_options') || !check_ajax_referer('avdb_update_nonce', '_wpnonce', false)) wp_send_json_error('Security check failed');
    global $avdb_webkit_packages;
    $json = avdbwki_get_manifest_data(true);
    if (!$json) wp_send_json_error('Cannot connect to update server');
    $manifest_map = [];
    foreach ($json as $item) { if (!empty($item['slug'])) $manifest_map[$item['slug']] = $item; }
    wp_cache_delete('plugins', 'plugins');
    $update_info = [];
    foreach ($avdb_webkit_packages as $pkg) {
        $slug = $pkg['slug'];
        $manifest_entry = $manifest_map[$slug] ?? null;
        $installed_version = avdbwki_get_installed_version($pkg);
        $update_info[$slug] = [
            'installed' => !empty($installed_version),
            'current_version' => $installed_version ?: '',
            'latest_version' => $manifest_entry['version'] ?? '',
        ];
    }
    wp_send_json_success($update_info);
});

add_action('wp_ajax_avdbwki_update_package', function() {
    if (!current_user_can('manage_options') || !check_ajax_referer('avdb_update_nonce', '_wpnonce', false)) wp_send_json_error('Security check failed');
    $slug = sanitize_text_field($_POST['slug'] ?? '');
    if (!$slug) wp_send_json_error('Missing slug');
    global $avdb_webkit_packages;
    $target_package = null;
    foreach ($avdb_webkit_packages as $pkg) { if ($pkg['slug'] === $slug) { $target_package = $pkg; break; } }
    if (!$target_package) wp_send_json_error('Package definition not found');
    $json = avdbwki_get_manifest_data(true);
    if (!$json) wp_send_json_error('Cannot connect to update server');
    $manifest_entry = null;
    foreach ($json as $item) { if (($item['slug'] ?? '') === $slug) { $manifest_entry = $item; break; } }
    if (!$manifest_entry || empty($manifest_entry['download_url'])) wp_send_json_error('Package not in manifest or missing URL');
    
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    if (!WP_Filesystem()) wp_send_json_error('Cannot access filesystem');
    
    $current_path = avdbwki_get_installed_path($target_package);
    if (!$current_path) wp_send_json_error('Package not installed or path not found.');
    
    $skin = new WP_Ajax_Upgrader_Skin();
    try {
        if (strtolower($target_package['type']) === 'plugin') $upgrader = new Plugin_Upgrader($skin);
        else $upgrader = new Theme_Upgrader($skin);

        $result = $upgrader->install($manifest_entry['download_url'], ['overwrite_package' => true]);

        if (is_wp_error($result) || $result === false) {
             $error_message = $skin->get_error_messages() ? implode('; ', $skin->get_error_messages()) : 'Update failed. Check file permissions.';
             if(is_wp_error($result)) $error_message = $result->get_error_message();
             wp_send_json_error($error_message);
        }
        
        wp_clean_plugins_cache(true);
        wp_clean_themes_cache(true);
        $new_version = avdbwki_get_installed_version($target_package);
        wp_send_json_success(['message' => 'Updated to version ' . ($new_version ?: $manifest_entry['version'])]);
    } catch (Exception $e) { wp_send_json_error('Exception: ' . $e->getMessage()); }
});

add_action('wp_ajax_avdbwki_clear_cache', function() {
    if (!current_user_can('manage_options') || !check_ajax_referer('avdb_update_nonce', '_wpnonce', false)) wp_send_json_error('Security check failed');
    delete_transient('avdbwki_manifest_data');
    wp_send_json_success('Cache cleared.');
});