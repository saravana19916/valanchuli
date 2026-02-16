<?php
$episode_id = intval($_GET['episode_id'] ?? 0);
$episode_number = intval($_GET['episode_number'] ?? 0);
$parent_id = intval($_GET['parent_id'] ?? 0);

// Fetch ad lock config for this episode (implement this function as needed)
$ad_lock = get_ads_lock_for_episode($parent_id, $episode_number);

$ads_time_min = isset($ad_lock['ads_time_min']) ? intval($ad_lock['ads_time_min']) : 0;
$ads_time_sec = isset($ad_lock['ads_time_sec']) ? intval($ad_lock['ads_time_sec']) : 0;
$ads_content = isset($ad_lock['ads_content']) ? $ad_lock['ads_content'] : '';
$total_seconds = $ads_time_min * 60 + $ads_time_sec;
?>
<div class="ad-lock-container" style="max-width:600px;margin:40px auto;padding:24px;background:#fff;border-radius:12px;box-shadow:0 2px 16px rgba(0,0,0,0.08);">
    <h3 class="mb-4 text-center">Ad Lock</h3>
    <div class="text-center mb-3">
        <span id="adLockTimer" style="font-size:2rem;font-weight:bold;"><?php echo sprintf('%02d:%02d', $ads_time_min, $ads_time_sec); ?></span>
        <div style="font-size:1rem;color:#888;">Please wait for the timer to finish</div>
    </div>
    <div class="ad-lock-content mb-4" style="border:1px solid #eee;padding:16px;border-radius:8px;">
        <?php echo wp_kses_post($ads_content); ?>
    </div>
</div>
<script>
(function(){
    var seconds = <?php echo $total_seconds; ?>;
    var timerEl = document.getElementById('adLockTimer');
    var interval = setInterval(function() {
        if (seconds <= 0) {
            clearInterval(interval);
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
        var min = Math.floor(seconds / 60);
        var sec = seconds % 60;
        timerEl.textContent = ('0' + min).slice(-2) + ':' + ('0' + sec).slice(-2);
    }, 1000);
})();
</script>