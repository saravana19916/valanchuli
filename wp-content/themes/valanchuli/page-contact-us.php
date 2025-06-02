<?php
    get_header();
?>

<div class="container min-h-screen flex items-center justify-center my-4">
    <div class="row d-flex justify-content-center align-items-center p-2 p-lg-5">
        <div class="row">
            <h5 class="text-center text-primary-color fw-bold"> கேள்விகள் உள்ளதா?.. </h5>
        </div>

        <div class="col-lg-6 col-12">
            <div class="d-flex justify-content-center align-items-center">
                <img src="<?php echo get_template_directory_uri() . '/images/contact.png'; ?>" alt="Login Image" style="width: 50%;" />
            </div>
            <div class="d-flex justify-content-center align-items-center mt-5">
                <p class="text-primary-color fs-14px fw-bold">உங்கள் சந்தேகங்கள் கேள்விகளுக்கு தொடர்பு கொள்ள வேண்டிய மின்னஞ்சல்</p>
            </div>
            <div class="d-flex justify-content-center align-items-center mt-3">
                <p class="d-flex justify-content-center align-items-center text-primary-color fs-16px mb-0 text-center px-5 py-4 login-shadow"><i class="fas fa-envelope fa-2x text-primary-color me-3"></i>contact@valanchuli.com</p>
            </div>
        </div>
        
        <div class="col-lg-6 col-12 mt-3 mt-lg-0 p-3 p-lg-5 bg-white">
            <h5 class="text-primary-color fw-bold mt-5 bottom-border"> Contact Us &nbsp;&nbsp;<i class="fas fa-envelope-open-text text-primary-color"></i> </h5>
            <p class="text-primary-color fs-13px mt-3">உங்கள் ஆர்வத்திற்கு நன்றி. தயவுசெய்து படிவத்தை நிரப்பவும், உங்கள் கோரிக்கை குறித்து நாங்கள் உடனடியாக உங்களிடம் பதிலளிப்போம்.</p>
            <div class="d-flex justify-content-center align-items-center">
                <form id="contactForm" class="py-3 rounded w-100" method="POST">
                    <div id="formResponse"></div>
                    <div class="mb-3">
                        <div class="login-form-group contact-name">
                            <input type="text" class="form-control height login-input text-primary-color" id="name" name="name" placeholder="Name *">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="login-form-group contact-email">
                            <input type="text" class="form-control height login-input text-primary-color" id="email" name="email" placeholder="Email *">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="login-form-group contact-subject">
                            <input type="text" class="form-control height login-input text-primary-color" id="subject" name="subject" placeholder="Subject *">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="login-form-group contact-message">
                            <textarea class="form-control login-input text-primary-color" id="message" name="message" rows="4" placeholder="Message *"></textarea>
                        </div>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>

<script>
    jQuery(document).ready(function($) {
        $('#contactForm').submit(function(event) {
            event.preventDefault();

            $('.error-message').remove();

            let formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: formData + '&action=send_contact_mail',
                success: function(response) {
                    if (typeof response.data === 'object') {
                        $.each(response.data, function (field, message) {
                            $('.contact-' + field).after('<p class="text-danger error-message small mt-2">' + message + '</p>');
                        });
                    }

                    if (response.status === 'success') {
                        $('#formResponse').html('<div class="alert alert-success">' + response.message + '</div>');
                        $('#contactForm')[0].reset();
                    }
                },
                error: function(response) {
                    $('#formResponse').html('<div class="alert alert-danger">' + response.data + '</div>');
                }
            });
        });
    });
</script>