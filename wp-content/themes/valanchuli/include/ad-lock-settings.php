<?php

add_action('admin_init', function () {
    register_setting(
        'ad_lock_settings_group',
        'ad_lock_time_seconds',
        [
            'type' => 'integer',
            'default' => 15,
            'sanitize_callback' => 'absint'
        ]
    );
});

add_action('admin_menu', function () {
    add_menu_page(
        'Ad Lock Settings',
        'Ad Lock',       
        'manage_options',
        'ad-lock-settings',         
        'render_ad_lock_page',      
        'dashicons-lock',           
        58                          
    );
});


function render_ad_lock_page() {
    ?>
    <div class="wrap">
        <h1>🔒 Ad Lock Settings</h1>

        <form method="post" action="options.php">
            <?php
                settings_fields('ad_lock_settings_group');
                $time = get_option('ad_lock_time_seconds', 15);
            ?>

            <table class="form-table">
                <tr>
                    <th scope="row">Ad Lock Time (seconds)</th>
                    <td>
                        <input type="number"
                               name="ad_lock_time_seconds"
                               value="<?php echo esc_attr($time); ?>"
                               min="5"
                               max="300"
                               class="regular-text" />
                    </td>
                </tr>
            </table>

            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}

// $ad_lock_time = (int) get_option('ad_lock_time_seconds', 15);

// unlock
// <div id="ad-lock-overlay" class="ad-lock-overlay">
//     <div class="ad-lock-box text-center">
//         <p>🔒 This story is locked</p>
//         <p>Please wait <span id="ad-lock-timer">echo $lock_time;</span> seconds</p>

//         <!-- Ad code here -->
//         <div class="my-3">
//             <!-- Google / custom ad -->
//         </div>

//         <button id="unlock-story" class="btn btn-primary d-none">
//             Unlock Story
//         </button>
//     </div>
// </div>

// countdown script
//  <script>
// document.addEventListener('DOMContentLoaded', function () {
//     let seconds = <?php echo (int) get_option('ad_lock_time_seconds', 15);;
//     const timerEl = document.getElementById('ad-lock-timer');
//     const unlockBtn = document.getElementById('unlock-story');

//     const interval = setInterval(() => {
//         seconds--;
//         timerEl.textContent = seconds;

//         if (seconds <= 0) {
//             clearInterval(interval);
//             unlockBtn.classList.remove('d-none');
//         }
//     }, 1000);

//     unlockBtn.addEventListener('click', () => {
//         document.getElementById('ad-lock-overlay').style.display = 'none';
//         document.getElementById('story-content').style.display = 'block';
//     });
// });
// </script>

