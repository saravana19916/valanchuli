<?php
    get_header();
?>

<div class="container min-h-screen flex items-center justify-center my-4">
    <div class="row d-flex justify-content-center align-items-center p-2 p-lg-5">
        <div class="col-lg-6 col-12 p-5 bg-white">
            <h4 class="text-primary-color fw-bold bottom-border">About Us</h4>
            <p class="text-primary-color fs-14px mt-4" style="line-height: 1.7rem;">
                வலஞ்சுழி எளிய முறையில் எழுத்தாளர்கள் மற்றும் வாசகர்களை இணைக்க உருவாக்கப்பட்ட தமிழ் தளமாகும். இங்கு எழுத்தாளர்கள் தங்களின் படைப்புகளை இலவசமாக வெளியிட்டு கொள்ளலாம். வாசகர்கள் எந்த தடையும் இன்றி தங்களுக்கு பிடித்த கதைகளை வாசித்து கொள்ளலாம்.
            </p>
        </div>

        <div class="col-lg-6 col-12">
            <img src="<?php echo get_template_directory_uri() . '/images/about.png'; ?>" alt="Login Image" class="img-fluid h-60 w-60 object-fit-cover" />
        </div>
    </div>
</div>


<?php get_footer(); ?>