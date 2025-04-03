<?php
/**
 * Plugin Name: Sticky App Download Banner
 * Description: Adds a sticky banner for app downloads with customizable colors
 * Version: 1.2
 * Author: Yigit Serin
 * Text Domain: sticky-app-banner
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('STICKY_APP_BANNER_VERSION', '1.2');
define('STICKY_APP_BANNER_PATH', plugin_dir_path(__FILE__));
define('STICKY_APP_BANNER_URL', plugin_dir_url(__FILE__));

// Register activation hook
register_activation_hook(__FILE__, 'sticky_app_banner_activate');

function sticky_app_banner_activate() {
    // Set default options if they don't exist
    if (!get_option('sticky_app_banner_options')) {
        $default_options = array(
            'enabled' => 1,
            'position' => 'bottom',
            'app_name' => '',
            'app_description' => '',
            'app_rating' => 5,
            'app_color' => '#fc8c82',
            'download_link' => '',
            'app_icon' => '',
            'button_text' => 'Download Now'
        );
        
        update_option('sticky_app_banner_options', $default_options);
    }
}

// Register admin menu and settings
add_action('admin_menu', 'sticky_app_banner_menu');
add_action('admin_init', 'sticky_app_banner_settings_init');

// Enqueue admin scripts and styles
add_action('admin_enqueue_scripts', 'sticky_app_banner_admin_scripts');

function sticky_app_banner_admin_scripts($hook) {
    // Only load on our settings page
    if ($hook != 'settings_page_sticky-app-banner') {
        return;
    }
    
    // Enqueue color picker
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    // Enqueue media uploader
    wp_enqueue_media();
    
    // Enqueue custom admin script
    wp_enqueue_script(
        'sticky-app-banner-admin', 
        STICKY_APP_BANNER_URL . 'admin.js', 
        array('jquery', 'wp-color-picker', 'media-upload'), 
        STICKY_APP_BANNER_VERSION, 
        true
    );
}

// Add banner HTML and CSS to footer
function sticky_app_banner_footer() {
    // Get options
    $options = get_option('sticky_app_banner_options');
    
    // Check if banner is enabled
    if (empty($options['enabled'])) {
        return;
    }
    
    // Set variables from options
    $app_name = esc_html($options['app_name']);
    $app_description = esc_html($options['app_description']);
    $app_rating = intval($options['app_rating']);
    $main_color = sanitize_hex_color($options['app_color']) ?: '#fc8c82';
    $position = ($options['position'] === 'top') ? 'top' : 'bottom';
    $button_text = !empty($options['button_text']) ? esc_html($options['button_text']) : 'Download Now';
    
    // Get download link
    $download_link = esc_url($options['download_link']);
    
    // Generate rating stars
    $rating_stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $rating_stars .= ($i <= $app_rating) ? '&#9733;' : '&#9734;';
    }
    
    // Get app icon
    $app_icon_url = !empty($options['app_icon']) ? 
                    wp_get_attachment_url($options['app_icon']) : 
                    plugins_url('app-icon-placeholder.jpg', __FILE__);
    
    // Banner HTML structure
    $banner_html = '<div id="sticky-app-banner" class="position-' . esc_attr($position) . '">
        <div class="banner-content">
            <button id="close-banner" aria-label="Close">&times;</button>
            <a href="' . esc_url($download_link) . '" target="_blank" class="app-icon-link">
            <img src="' . esc_url($app_icon_url) . '" alt="' . esc_attr($app_name) . '" class="app-icon">
            </a>
            <div class="banner-info">
                <a href="' . esc_url($download_link) . '" target="_blank" class="text-link">
                    <div class="banner-text">
                        <strong>' . $app_name . '</strong>
                        <span>' . $app_description . '</span>
                        <div class="app-rating">' . $rating_stars . '</div>
                    </div>
                </a>
                <a href="' . esc_url($download_link) . '" target="_blank" class="app-button">' . $button_text . '</a>
            </div>
        </div>
    </div>';

    echo $banner_html;

    // Add inline CSS
    $css = '
    <style>
        #sticky-app-banner {
            position: fixed;
            ' . ($position === 'top' ? 'top: 0;' : 'bottom: 0;') . '
            left: 0;
            width: 100%;
            background-color: #f8f8f8; /* Lighter background */
            color: #333;
            z-index: 9999;
            box-shadow: ' . ($position === 'top' ? '0 2px 10px' : '0 -2px 10px') . ' rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
            font-family: sans-serif;
            border-' . ($position === 'top' ? 'bottom' : 'top') . ': 1px solid #eee;
        }

        #sticky-app-banner.position-top.banner-hidden {
            transform: translateY(-100%);
        }
        
        #sticky-app-banner.position-bottom.banner-hidden {
            transform: translateY(100%);
        }

        .banner-content {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }

        #close-banner {
            position: absolute;
            top: 5px;
            right: 5px;
            background: none;
            border: none;
            color: #aaa;
            font-size: 20px;
            cursor: pointer;
            line-height: 1;
            padding: 5px;
        }
        #close-banner:hover {
            color: #555;
        }

        .app-icon-link {
             display: block; /* Make link behave like a block */
             flex-shrink: 0;
        }

        .app-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            margin-right: 15px;
            display: block; /* Ensure image behaves correctly within link */
            background-color: #eee; /* Placeholder background */
            object-fit: cover; /* Ensure the image covers the area nicely */
        }

        .banner-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-grow: 1;
            gap: 15px; /* Add gap between text and button */
        }

        /* Style for the text link */
        .text-link {
            text-decoration: none;
            color: inherit; /* Inherit color from parent */
            flex-grow: 1; /* Allow text block to take available space */
            min-width: 0; /* Prevent flex item from overflowing */
        }

        .banner-text {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
        }

        .banner-text strong {
            font-size: 15px;
            font-weight: 600;
            color: #000;
        }

        .banner-text span {
            font-size: 13px;
            color: #555;
        }

        .app-rating {
            font-size: 12px;
            color: ' . $main_color . '; /* Use app color for stars */
            margin-top: 2px;
        }

        .app-button {
            display: inline-block;
            background-color: ' . $main_color . ';
            color: white !important; /* Ensure text is white */
            padding: 8px 20px;
            border-radius: 20px; /* Rounded button */
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            transition: background-color 0.2s;
            white-space: nowrap; /* Prevent button text wrapping */
            flex-shrink: 0; /* Prevent button from shrinking */
        }

        .app-button:hover {
            opacity: 0.9;
            color: white !important; /* Ensure text remains white on hover */
        }

        /* Adjustments for smaller screens */
        @media (max-width: 480px) {
            .banner-content {
                padding: 10px;
            }
            .app-icon {
                width: 50px;
                height: 50px;
                margin-right: 10px;
            }
            .banner-text strong {
                font-size: 14px;
            }
            .banner-text span, .app-rating {
                font-size: 12px;
            }
            .app-button {
                padding: 6px 15px;
                font-size: 13px;
            }
             #close-banner {
                font-size: 18px;
                top: 2px;
                right: 2px;
            }
        }
         @media (max-width: 360px) {
             .banner-info {
                 /* Keep flex-direction row but allow wrap */
                 flex-wrap: wrap;
                 gap: 8px;
             }
             .text-link {
                 /* Ensure text block takes full width when wrapped */
                 width: 100%;
                 order: 1; /* Place text block first when wrapped */
             }
             .app-button {
                 order: 2; /* Place button second when wrapped */
                 margin-top: 0; /* Remove previous top margin */
             }
         }
    </style>
    ';

    echo $css;

    // Add JavaScript for banner functionality
    $js = '
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const banner = document.getElementById("sticky-app-banner");
        const closeButton = document.getElementById("close-banner");

        // Check if banner was previously closed
        if (localStorage.getItem("appBannerClosed")) {
            banner.classList.add("banner-hidden");
        }

        // Close button functionality
        if (closeButton && banner) {
            closeButton.addEventListener("click", function(e) {
                e.stopPropagation(); // Prevent event bubbling if needed
                banner.classList.add("banner-hidden");
                localStorage.setItem("appBannerClosed", "true");
            });
        }
    });
    </script>
    ';

    echo $js;
}

// Add the banner to the site
add_action('wp_footer', 'sticky_app_banner_footer');

// Admin menu
function sticky_app_banner_menu() {
    add_options_page(
        'Sticky App Banner Settings',
        'App Banner',
        'manage_options',
        'sticky-app-banner',
        'sticky_app_banner_settings_page'
    );
}

// Register settings
function sticky_app_banner_settings_init() {
    register_setting('sticky_app_banner', 'sticky_app_banner_options');
    
    // General Settings Section
    add_settings_section(
        'sticky_app_banner_general_section',
        'General Settings',
        'sticky_app_banner_general_section_callback',
        'sticky-app-banner'
    );
    
    // App Content Section
    add_settings_section(
        'sticky_app_banner_content_section',
        'Banner Content',
        'sticky_app_banner_content_section_callback',
        'sticky-app-banner'
    );
    
    // Link Settings Section
    add_settings_section(
        'sticky_app_banner_link_section',
        'Link Settings',
        'sticky_app_banner_link_section_callback',
        'sticky-app-banner'
    );
    
    // General Settings Fields
    add_settings_field(
        'sticky_app_banner_enabled',
        'Banner Status',
        'sticky_app_banner_enabled_callback',
        'sticky-app-banner',
        'sticky_app_banner_general_section'
    );
    
    add_settings_field(
        'sticky_app_banner_position',
        'Banner Position',
        'sticky_app_banner_position_callback',
        'sticky-app-banner',
        'sticky_app_banner_general_section'
    );
    
    add_settings_field(
        'sticky_app_banner_color',
        'Banner Color',
        'sticky_app_banner_color_callback',
        'sticky-app-banner',
        'sticky_app_banner_general_section'
    );
    
    // Content Settings Fields
    add_settings_field(
        'sticky_app_banner_app_name',
        'App Name',
        'sticky_app_banner_app_name_callback',
        'sticky-app-banner',
        'sticky_app_banner_content_section'
    );
    
    add_settings_field(
        'sticky_app_banner_app_description',
        'App Description',
        'sticky_app_banner_app_description_callback',
        'sticky-app-banner',
        'sticky_app_banner_content_section'
    );
    
    add_settings_field(
        'sticky_app_banner_app_rating',
        'App Rating',
        'sticky_app_banner_app_rating_callback',
        'sticky-app-banner',
        'sticky_app_banner_content_section'
    );
    
    add_settings_field(
        'sticky_app_banner_app_icon',
        'App Icon',
        'sticky_app_banner_app_icon_callback',
        'sticky-app-banner',
        'sticky_app_banner_content_section'
    );
    
    // Link Settings Fields
    add_settings_field(
        'sticky_app_banner_download_link',
        'Download Link',
        'sticky_app_banner_download_link_callback',
        'sticky-app-banner',
        'sticky_app_banner_link_section'
    );
    
    add_settings_field(
        'sticky_app_banner_button_text',
        'Button Text',
        'sticky_app_banner_button_text_callback',
        'sticky-app-banner',
        'sticky_app_banner_link_section'
    );
}

// Section callbacks
function sticky_app_banner_general_section_callback() {
    echo '<p>Configure the general appearance and behavior settings of the banner.</p>';
}

function sticky_app_banner_content_section_callback() {
    echo '<p>Configure the banner content and visual elements.</p>';
}

function sticky_app_banner_link_section_callback() {
    echo '<p>Configure the download link settings.</p>';
}

// Field callbacks
function sticky_app_banner_enabled_callback() {
    $options = get_option('sticky_app_banner_options');
    $enabled = isset($options['enabled']) ? $options['enabled'] : 1;
    
    echo '<label><input type="checkbox" name="sticky_app_banner_options[enabled]" value="1" ' . checked(1, $enabled, false) . '> Active</label>';
    echo '<p class="description">Check to display the banner on your site.</p>';
}

function sticky_app_banner_position_callback() {
    $options = get_option('sticky_app_banner_options');
    $position = isset($options['position']) ? $options['position'] : 'bottom';
    
    echo '<select name="sticky_app_banner_options[position]">';
    echo '<option value="top" ' . selected('top', $position, false) . '>Top</option>';
    echo '<option value="bottom" ' . selected('bottom', $position, false) . '>Bottom</option>';
    echo '</select>';
    echo '<p class="description">Select the position where the banner will appear on the page.</p>';
}

function sticky_app_banner_color_callback() {
    $options = get_option('sticky_app_banner_options');
    $color = isset($options['app_color']) ? $options['app_color'] : '#fc8c82';
    
    echo '<input type="text" name="sticky_app_banner_options[app_color]" value="' . esc_attr($color) . '" class="color-picker" data-default-color="#fc8c82" />';
    echo '<p class="description">Choose a color for the button and stars.</p>';
}

function sticky_app_banner_app_name_callback() {
    $options = get_option('sticky_app_banner_options');
    $app_name = isset($options['app_name']) ? $options['app_name'] : '';
    
    echo '<input type="text" name="sticky_app_banner_options[app_name]" value="' . esc_attr($app_name) . '" class="regular-text">';
    echo '<p class="description">The app name to be displayed on the banner.</p>';
}

function sticky_app_banner_app_description_callback() {
    $options = get_option('sticky_app_banner_options');
    $app_description = isset($options['app_description']) ? $options['app_description'] : '';
    
    echo '<input type="text" name="sticky_app_banner_options[app_description]" value="' . esc_attr($app_description) . '" class="regular-text">';
    echo '<p class="description">A short description for the app.</p>';
}

function sticky_app_banner_app_rating_callback() {
    $options = get_option('sticky_app_banner_options');
    $app_rating = isset($options['app_rating']) ? $options['app_rating'] : 5;
    
    echo '<select name="sticky_app_banner_options[app_rating]">';
    for ($i = 0; $i <= 5; $i++) {
        echo '<option value="' . $i . '" ' . selected($i, $app_rating, false) . '>' . $i . ' Stars</option>';
    }
    echo '</select>';
    echo '<p class="description">Select the rating for your app (0-5 stars).</p>';
}

function sticky_app_banner_app_icon_callback() {
    $options = get_option('sticky_app_banner_options');
    $app_icon = isset($options['app_icon']) ? $options['app_icon'] : '';
    
    // Get the image if it exists
    $image = $app_icon ? wp_get_attachment_image_src($app_icon, 'thumbnail') : '';
    $image_url = $image ? $image[0] : '';
    
    echo '<div class="image-preview-wrapper">';
    echo '<img id="app-icon-preview" src="' . esc_url($image_url) . '" style="max-width:100px;' . ($image_url ? '' : 'display:none;') . '" />';
    echo '</div>';
    echo '<input id="upload_app_icon_button" type="button" class="button" value="Select Icon" />';
    echo '<input id="remove_app_icon_button" type="button" class="button" value="Remove Icon" ' . ($image_url ? '' : 'style="display:none;"') . ' />';
    echo '<input type="hidden" name="sticky_app_banner_options[app_icon]" id="app_icon_id" value="' . esc_attr($app_icon) . '" />';
    echo '<p class="description">Upload an app icon (recommended size: 128x128px).</p>';
}

function sticky_app_banner_download_link_callback() {
    $options = get_option('sticky_app_banner_options');
    $download_link = isset($options['download_link']) ? $options['download_link'] : '';
    
    echo '<input type="url" name="sticky_app_banner_options[download_link]" value="' . esc_url($download_link) . '" class="regular-text">';
    echo '<p class="description">The full URL of the app download page.</p>';
}

function sticky_app_banner_button_text_callback() {
    $options = get_option('sticky_app_banner_options');
    $button_text = isset($options['button_text']) ? $options['button_text'] : 'Download Now';
    
    echo '<input type="text" name="sticky_app_banner_options[button_text]" value="' . esc_attr($button_text) . '" class="regular-text">';
    echo '<p class="description">The text to display on the download button.</p>';
}

// Settings page
function sticky_app_banner_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Show settings form
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('sticky_app_banner');
            do_settings_sections('sticky-app-banner');
            submit_button('Save Settings');
            ?>
        </form>
        
        <div class="banner-preview" style="margin-top: 30px;">
            <h2>Preview</h2>
            <p>Visit the site to see how the banner will appear or <a href="<?php echo esc_url(home_url()); ?>" target="_blank">click here</a> to preview it.</p>
        </div>
    </div>
    <?php
}
?>
