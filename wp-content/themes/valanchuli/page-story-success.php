<?php
    get_header();

    $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
?>

<div class="container col-6 my-4 d-flex flex-column justify-content-center align-items-center text-center" style="min-height: 80vh;">
    <i class="fa-solid fa-circle-check fa-4x text-success mb-3"></i>
    <?php if ($status === 'other') : ?>
        <div class="alert alert-success">
            அருமை! உங்கள் படைப்பு வெற்றிகரமாக பதிவேற்றப்பட்டது! உங்கள்
            தனித்துவமான படைப்பை சரிபார்க்கவும், உங்கள் நட்பு வட்டத்துடன்
            பகிரவும் 'எனது பக்கம்' ஐகானை கிளிக் செய்யுங்கள்!
            <a href="write" class="alert-link">Click Here</a> to continue writing
        </div>
    <?php elseif ($status === 'series') : ?>
        <div class="alert alert-success">
            வாழ்த்துக்கள் ! உங்கள் தொடர்கதையின் தலைப்பு வெற்றிகரமாக
            பதிவேற்றப்பட்டது! உங்கள் தொடர்கதையில் முதல் அத்தியாயத்தை
            பதிவிட 'தலைப்பு' பகுதியின் கீழ் அத்தியாயம் - 1 (அ) எபிசோட் - 1 என
            நிரப்பி 'தொடர்கதை' பகுதியின் கீழ் உங்கள் தொடர்கதையின்
            தலைப்பைத் தேர்ந்தெடுத்து முதல் அத்தியாயத்தை இப்போதே
            தொடங்குங்கள்!
            <a href="write" class="alert-link">Click Here</a> to continue writing
        </div>
     <?php elseif ($status === 'draft') : ?>
        <div class="alert alert-success">
            உங்கள் படைப்பு வெற்றிகரமாக draft-ல் பதிவேற்றப்பட்டது!
            <a href="write" class="alert-link">Click Here</a> to continue writing
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
