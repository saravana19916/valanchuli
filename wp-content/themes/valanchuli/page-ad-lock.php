<?php
get_header();


$episode_id = intval($_GET['episode_id'] ?? 0);
$episode_number = intval($_GET['episode_number'] ?? 0);
$parent_id = intval($_GET['parent_id'] ?? 0);

// Check if already unlocked for this user
$user_id = get_current_user_id();
if ($user_id && is_episode_unlocked($user_id, $episode_id)) {
    ?>
    <script>
        window.location.replace("<?php echo get_permalink($episode_id); ?>");
    </script>
    <?php
    exit;
}

// Fetch ad lock config for this episode (implement this function as needed)
$ad_lock = get_ads_lock_for_episode($parent_id, $episode_number);
$total_seconds = isset($ad_lock['ads_time_sec']) ? intval($ad_lock['ads_time_sec']) : 0;
$ads_content = isset($ad_lock['ads_content']) ? $ad_lock['ads_content'] : '';
?>
<div class="ad-lock-container" style="max-width:600px;margin:40px auto;padding:24px;background:#fff;border-radius:12px;box-shadow:0 2px 16px rgba(0,0,0,0.08); border: 2px solid #005d67">
    <!-- <h3 class="mb-4 text-center fw-bold" style="text-align:center;">Watch Ad to Unlock</h3> -->
    <p class="mb-4 text-center" style="font-size:1.1rem;color:#555;text-align:center;">எபிசோட் தயார் ஆகிறது… சில விநாடிகள் காத்திருக்கவும்</p>
    <div class="text-center mb-3">
        <div class="timer-circle" style="position:relative; width:80px; height:80px; margin:0 auto;">
            <svg id="circleTimerSVG" width="80" height="80" viewBox="0 0 180 180" style="display:block;">
                <!-- Background circle -->
                <circle cx="90" cy="90" r="75" fill="#fff" />
                <!-- Progress arc (dynamic) -->
                <path id="circleProgressFill" fill="#005d67ad" fill-opacity="0.55" stroke="none"/>
                <!-- Glowing border -->
                <circle cx="90" cy="90" r="75" fill="none" stroke="#005d67ad" stroke-width="12" filter="url(#glow)" />
                <defs>
                    <filter id="glow" x="-40%" y="-40%" width="180%" height="180%">
                        <feGaussianBlur stdDeviation="8" result="coloredBlur"/>
                        <feMerge>
                            <feMergeNode in="coloredBlur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>
            </svg>
            <div id="circleTimerNumber"
                 style="position:absolute;top:4px;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:bold;color:#222;">
                <?php echo sprintf('%02d', $total_seconds); ?>
            </div>
        </div>
    </div>
    <p class="my-4 text-center" style="font-size:1.1rem;color:#555;text-align:center;">Timer முடிந்ததும் இந்த பக்கம் தானாக மூடப்படும்</p>
    <div class="ad-lock-content mb-4" style="padding:15px;">
        <?php echo do_shortcode($ads_content); ?>
    </div>
</div>
<script>
(function(){
    var total = <?php echo $total_seconds; ?>;
    var seconds = total;
    var timerNum = document.getElementById('circleTimerNumber');
    var circleProgress = document.getElementById('circleProgressFill');

    function getCirclePath(cx, cy, r, percent) {
        // percent: 1.0 = full, 0.0 = empty
        if (percent <= 0) return '';
        if (percent >= 1) percent = 0.9999; // avoid full circle SVG bug

        var startAngle = -Math.PI / 2;
        var endAngle = startAngle + percent * 2 * Math.PI;

        var x1 = cx + r * Math.cos(startAngle);
        var y1 = cx + r * Math.sin(startAngle);
        var x2 = cx + r * Math.cos(endAngle);
        var y2 = cx + r * Math.sin(endAngle);

        var largeArcFlag = percent > 0.5 ? 1 : 0;

        return [
            'M', cx, cy,
            'L', x1, y1,
            'A', r, r, 0, largeArcFlag, 1, x2, y2,
            'Z'
        ].join(' ');
    }

    function updateTimer() {
        timerNum.textContent = seconds;
        var percent = seconds / total;
        circleProgress.setAttribute('d', getCirclePath(90, 90, 75, percent));
    }

    updateTimer();

    var interval = null;
    var timerRunning = true;

    function startTimer() {
        if (interval) return;
        interval = setInterval(function() {
            if (seconds <= 0) {
                clearInterval(interval);
                interval = null;
                // Unlock logic
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        action: 'unlock_episode_with_ad',
                        episode_id: <?php echo $episode_id; ?>,
                        parent_id: <?php echo $parent_id; ?>
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Episode unlocked!');
                        window.location.href = "<?php echo get_permalink($episode_id); ?>";
                    } else {
                        alert(data.data && data.data.message ? data.data.message : 'Unlock failed!');
                    }
                });
                return;
            }
            seconds--;
            updateTimer();
        }, 1000);
    }

    function stopTimer() {
        if (interval) {
            clearInterval(interval);
            interval = null;
        }
    }

    // Start timer initially
    startTimer();

    // Pause/resume timer on visibility change
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopTimer();
        } else {
            startTimer();
        }
    });

    // After inserting ads_content into the DOM:
    document.querySelectorAll('.ad-lock-content script').forEach(function(oldScript) {
        var newScript = document.createElement('script');
        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
        newScript.text = oldScript.text;
        oldScript.parentNode.replaceChild(newScript, oldScript);
    });

    // After ads_content is rendered
    document.querySelectorAll('.ad-lock-content video').forEach(function(video) {
        video.muted = false; // Start muted
        video.volume = 0.05; // Low volume (0.0 to 1.0)
        video.autoplay = true;
        video.controls = true; // Show controls so user can adjust volume
        // Try to play (some browsers require user interaction)
        video.play().catch(function(){});
    });
})();
</script>
<style>
@media (max-width: 600px) {
    .ad-lock-container {
        margin: 20px !important;
    }
}
</style>

<?php
function is_episode_unlocked($user_id, $episode_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'user_episode_unlocks';
    return $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE user_id = %d AND episode_id = %d",
        $user_id, $episode_id
    )) > 0;
}

get_footer();