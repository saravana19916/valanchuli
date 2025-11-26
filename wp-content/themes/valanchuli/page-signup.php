<?php
    get_header();
?>

<div class="container min-h-screen flex items-center justify-center my-4">
    <div class="row d-flex justify-content-center align-items-center p-2 p-lg-5 login-shadow">
        <div class="row">
            <h5 class="text-center text-primary-color fw-bold"><i class="fas fa-circle-user text-primary-color"></i> Registration </h5>
        </div>
        <!-- Image Section -->
        <div class="col-lg-6 col-12">
            <img src="<?php echo get_template_directory_uri() . '/images/signup.png'; ?>" alt="Login Image" class="img-fluid h-60 w-55 object-fit-cover" style="width: 90%;" />
        </div>

        <div class="col-lg-6 col-xxl-4 col-12 p-5 bg-white">
            <p class="text-primary-color fs-16px fw-bold">வலஞ்சுழி வலை தளத்திற்கு உங்களை வரவேற்கிறோம் !...</p>
            <p class="mb-4 text-primary-color">Create a free account</p>

            <?php if (isset($wp_error) && is_wp_error($wp_error)) {
                    foreach ($wp_error->get_error_messages() as $message) {
                        echo "<div class='alert alert-danger'>$message</div>";
                    }
                }
            ?>

            <div id="registerMessage" class="mt-3"></div>

            <form id="signup-form">
                <div id="registerMessage" class="mt-3"></div>

                <div class="mb-3">
                    <div class="col-sm-12 input-group login-form-group register-username">
                        <span class="input-group-text login-group-text"><i class="fas fa-user text-primary-color"></i></span>
                        <input type="text" class="form-control login-input tamil-suggestion-input" id="username" name="username" placeholder="Username *">
                    </div>
                    <p class="tamil-suggestion-box mt-2" data-suggestion-for="username" style="display:none;"></p>
                </div>

                <div class="mb-3">
                    <div class="col-sm-12 input-group login-form-group register-email">
                        <span class="input-group-text login-group-text"><i class="fas fa-envelope text-primary-color"></i></span>
                        <input type="text" class="form-control login-input" id="email" name="email" placeholder="Email *">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group login-form-group register-password">
                        <span class="input-group-text login-group-text">
                            <i class="fas fa-lock text-primary-color"></i>
                        </span>
                        <input type="password" class="form-control login-input" id="password" name="password" placeholder="Password *">
                        <span class="input-group-text login-input bg-white" id="togglePassword" style="cursor: pointer;">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="col-sm-12 input-group login-form-group register-firstname">
                        <span class="input-group-text login-group-text"><i class="fas fa-user text-primary-color"></i></span>
                        <input type="text" class="form-control login-input tamil-suggestion-input" id="firstname" name="firstname" placeholder="First Name *">
                    </div>
                    <p class="tamil-suggestion-box mt-2" data-suggestion-for="firstname" style="display:none;"></p>
                </div>

                <div class="mb-4">
                    <div class="col-sm-12 input-group login-form-group register-lastname">
                        <span class="input-group-text login-group-text"><i class="fas fa-user text-primary-color"></i></span>
                        <input type="text" class="form-control login-input tamil-suggestion-input" id="lastname" name="lastname" placeholder="Last Name *">
                    </div>
                    <p class="tamil-suggestion-box mt-2" data-suggestion-for="lastname" style="display:none;"></p>
                </div>

                <div class="mb-4">
                    <div class="col-sm-12 input-group login-form-group">
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="form-control">
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="col-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-floppy-disk me-2"></i> Register
                        </button>
                    </div>
                </div>
                <div class="mb-4">
                    <p> <span class="text-primary-color">Already have an account? </span>
                        <a href="<?php echo site_url('/login'); ?>" class="text-primary-color"><span class="fs-14px fw-bold">Login</span></a>
                    </p>
                </div>

                <div id="canvasContainer"></div>
            </form>
        </div>
    </div>
</div>


<?php get_footer(); ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const passwordField = document.getElementById("password");
        const togglePassword = document.getElementById("togglePassword");
        const togglePasswordIcon = document.getElementById("togglePasswordIcon");

        togglePassword.addEventListener("click", function () {
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);

            // Toggle the icon
            togglePasswordIcon.classList.toggle("fa-eye");
            togglePasswordIcon.classList.toggle("fa-eye-slash");
        });
    });
</script>

<script>
function renderStoryAsImage(text) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext("2d");

    // Canvas width
    const maxWidth = 780;
    const lineHeight = 26;
    const fontStyle = "20px Arial";

    // Setup temporary font for measuring
    ctx.font = fontStyle;

    const words = text.split(" ");
    let line = "";
    let y = 10;

    // 1️⃣ Calculate height FIRST
    for (let i = 0; i < words.length; i++) {
        const testLine = line + words[i] + " ";
        const testWidth = ctx.measureText(testLine).width;
        if (testWidth > maxWidth) {
            line = words[i] + " ";
            y += lineHeight;
        } else {
            line = testLine;
        }
    }
    y += lineHeight;

    // 2️⃣ Now set proper canvas size
    canvas.width = 800;
    canvas.height = y + 10;

    // 3️⃣ Set font again (important)
    ctx.font = fontStyle;
    ctx.fillStyle = "#000";
    ctx.textBaseline = "top";

    // 4️⃣ Draw text again
    let line2 = "";
    let y2 = 10;
    for (let i = 0; i < words.length; i++) {
        const testLine = line2 + words[i] + " ";
        const testWidth = ctx.measureText(testLine).width;
        if (testWidth > maxWidth) {
            ctx.fillText(line2, 10, y2);
            line2 = words[i] + " ";
            y2 += lineHeight;
        } else {
            line2 = testLine;
        }
    }
    ctx.fillText(line2, 10, y2);

    document.getElementById("canvasContainer").innerHTML = "";
    document.getElementById("canvasContainer").appendChild(canvas);
}

// Example text
renderStoryAsImage("இது ஒரு சிறுகதை. இது பிறரால் நகலெடுக்க முடியாது ஏனெனில் இது படம் வடிவத்தில் காட்டப்படுகிறது.");
</script>
