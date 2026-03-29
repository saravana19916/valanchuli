<?php

$page_link = get_permalink();
$title     = get_the_title();

$message  = "திகிலும் ருசிக்கும் ஒரு தொடர்கதை....\n\n";
$message .= "கதையை பிரதிலிபி செயலியில் வாசியுங்கள்\n\n";
$message .= get_the_title() . "\n";
$message .= get_permalink() . "\n\n";
$message .= "வாசிக்க கதையின் மேல் க்ளிக் செய்யவும் 👆";

$whatsapp_link = "https://wa.me/?text=" . rawurlencode($message);

$facebook_link = "https://www.facebook.com/sharer/sharer.php?u=" . rawurlencode($page_link);

$twitter_link  = "https://twitter.com/intent/tweet?text=" . rawurlencode($title) . 
                 "&url=" . rawurlencode($page_link);
?>

<?php $page_link = get_permalink(); ?>

<div class="mb-3">
    <label class="form-label fw-semibold">Share Story</label>

    <div class="border rounded p-2 bg-light text-break"
         id="pageLink">
        <?php echo esc_url($page_link); ?>
    </div>

    <div class="mt-2">
        <button type="button" class="btn btn-primary btn-sm" id="copyLinkBtn">
            Copy
        </button>

        <span id="copyMsg" class="text-success mt-2" style="display: none;">
            Copied!
        </span>
    </div>
</div>

<div>
    <h5 class="fw-semibold mt-4 mb-3">Share in Social Media</h5>
    <?php if ( function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) ) { ?>
        <div class="my-3">
            <?php ADDTOANY_SHARE_SAVE_KIT(array('follow'=>true)); ?>
            <!-- <a href="#" onclick="window.open('https://www.com/'); return false;">
   Share to Instagram
</a> -->
            <!-- <a href="https://instagram.com/yourprofile" target="_blank" class="a2a_button_instagram" style="margin-left:8px;">
                <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/instagram.svg" alt="Instagram" style="width:32px;height:32px;vertical-align:middle;">
            </a> -->
        </div>
    <?php } ?>


    <!-- <a href="<?php echo $whatsapp_link; ?>" 
        class="btn btn-success whatsapp-popup">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <a href="<?php echo esc_url($facebook_link); ?>" 
       class="btn btn-primary" 
       target="_blank">
        <i class="fa-brands fa-facebook-f"></i>
    </a>

    <a href="<?php echo esc_url($twitter_link); ?>" 
       class="btn btn-dark" 
       target="_blank">
        <i class="fa-brands fa-x-twitter"></i>
    </a> -->
</div>

<script>
document.getElementById('copyLinkBtn').addEventListener('click', function () {
    const linkText = document.getElementById('pageLink').innerText;

    navigator.clipboard.writeText(linkText).then(() => {
        const msg = document.getElementById('copyMsg');
        msg.style.display = 'block';

        setTimeout(() => {
            msg.style.display = 'none';
        }, 2000);
    });
});
</script>

<script>
// var a2a_config = a2a_config || {};
// a2a_config.templates = {
//     whatsapp: {
//         text: "<?php echo esc_js( get_the_title() . ' - tamil ' . get_permalink() ); ?>"
//     },
//     twitter: {
//         text: "<?php echo esc_js( get_the_title() ); ?>",
//         url: "<?php echo esc_js( get_permalink() ); ?>"
//     }
// };
</script>

