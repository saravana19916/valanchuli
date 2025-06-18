<?php
/* Template Name: Profile */
get_header();

if (is_user_logged_in()) :
    $current_user = wp_get_current_user();

    // Get existing photo
    $photo_id = get_user_meta($current_user->ID, 'profile_photo', true);
    $photo_url = $photo_id ? wp_get_attachment_url($photo_id) : '';
    ?>
    <h1>சுயவிவரம் திருத்த</h1>
    <div id="registerMessage" class="mt-3"></div>

    <form id="profile-form" method="POST" enctype="multipart/form-data">
        <?php wp_nonce_field('update_profile_nonce'); ?>
        
        <div>
            <label>Username (can't be changed)</label>
            <input id="username" name="username" type="text" disabled value="<?php echo esc_attr($current_user->user_login); ?>"> 
        </div>

        <div>
            <label>Email</label>
            <input id="email" name="email" type="email" value="<?php echo esc_attr($current_user->user_email); ?>"> 
        </div>

        <div>
            <label>First Name</label>
            <input id="firstname" name="firstname" type="text" value="<?php echo esc_attr(get_user_meta($current_user->ID,'first_name',true)); ?>"> 
        </div>

        <div>
            <label>Last Name</label>
            <input id="lastname" name="lastname" type="text" value="<?php echo esc_attr(get_user_meta($current_user->ID,'last_name',true)); ?>"> 
        </div>

        <div>
            <label>Profile photo</label>
            <input id="profile_photo" name="profile_photo" type="file" accept="image/*">
            <?php if ($photo_url) : ?>
                <img src="<?php echo esc_url($photo_url); ?>" alt="Profile photo" style="max-width:100px;height:auto;margin-top:10px">
            <?php endif; ?>
        </div>

        <br>
        <input type="submit" value="Save">
    </form>

    <?php
else :
    echo "<p>சுயவிவரத்தை திருத்த உள்நுழைய வேண்டும்.</p>";
endif;

get_footer();
?>

<script>
    document.getElementById('profile-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'update_profile');

    fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('registerMessage').innerHTML = '<div class="alert alert-success">Profile updated successfully</div>';
        } else {
            document.getElementById('registerMessage').innerHTML = '<div class="alert alert-danger">' + data.data + '</div>';
        }
    });
});

</script>

