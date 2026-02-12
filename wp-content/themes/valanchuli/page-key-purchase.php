<?php get_header(); ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-9">

            <h3 class="text-center mb-5 fw-bold" style="color:#2366a8;">Top-up Key Packages</h3>

            <div class="row g-4 justify-content-center">
                <?php $coin_prices = get_option('coin_pack_prices_setting', []); ?>
                <?php if(!empty($coin_prices)): ?>
                    <?php foreach ($coin_prices as $coin_price): ?>
                        <div class="col-6 col-md-4 col-lg-4 d-flex flex-column align-items-center">
                            <div class="key-pack-card position-relative text-center mb-4" style="width:150px;">
                                <img src="<?php echo get_template_directory_uri() . '/images/key-purchase.png'; ?>" alt="<?php echo esc_attr($coin_price['coin']); ?> Keys" class="img-fluid" style="width:150px;">
                                <div class="key-pack-overlay position-absolute translate-middle w-100" style="top:94%; left:58%;">
                                    <h5 style="color:#2366a8;font-weight:900;"><?php echo esc_html($coin_price['coin']); ?> KEYS</h5>
                                    <h5 style="color:#c0392b;font-weight:900;">₹<?php echo esc_html($coin_price['price']); ?></h5>
                                </div>
                            </div>
                            <div class="w-100 d-flex justify-content-center mt-4" style="margin-left: 15px;">
                                <button class="btn purchase-btn rounded-pill px-4 btn-sm"
                                        style="background: #3B86BD; color: #fff; font-weight: bold;"
                                        onclick="if(!isLoggedIn){redirectToLogin();return false;} paymentProcess(<?php echo esc_attr($coin_price['price']); ?>, <?php echo esc_attr($coin_price['coin']); ?>)">
                                    PURCHASE
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-danger">⚠️ காயின் பேக் அமைப்புகள் இல்லை</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php get_footer(); ?>

<script>
    function paymentProcess(amount, coins) {

        var options = {
            "key": RazorpayConfig.key,
            "amount": amount * 100,
            "currency": "INR",
            "name": "Buy Keys",
            "description": amount + " Keys",
            "handler": function (response){
                // AJAX to save coin purchase
                saveCoinPurchase(response.razorpay_payment_id, 'success', amount, coins);
            }
        };
        var rzp = new Razorpay(options);
        rzp.on('payment.failed', function (response){
            saveCoinPurchase(response.error.metadata.payment_id || '', 'failed', amount, coins);
        });
        rzp.open();
    }

    function saveCoinPurchase(payment_id, payment_status, amount, coins) {
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'save_coin_purchase',
                coin: coins,
                price: amount,
                payment_id: payment_id,
                payment_method: 'razorpay',
                payment_status: payment_status
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success && payment_status === 'success') {
                alert('Purchase successful! Keys will be added to your wallet.');
                <?php
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
                    if ($redirect === 'wallet') {
                        $redirect_url = site_url('/wallet');
                    } elseif (filter_var($redirect, FILTER_VALIDATE_URL)) {
                        $redirect_url = $redirect;
                    } else {
                        $redirect_url = site_url('/wallet');
                    }
                ?>
                window.location.href = "<?php echo esc_url($redirect_url); ?>";
            } else if(payment_status === 'failed') {
                alert('Payment failed or cancelled.');
            } else {
                alert('Purchase failed!');
            }
        });
    }

    function redirectToLogin() {
        window.location.href = "<?php echo site_url('/login'); ?>?redirect_to=" + encodeURIComponent(window.location.href);
    }
</script>

<?php $is_logged_in = is_user_logged_in(); ?>
<script>
    var isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
</script>
