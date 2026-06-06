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

<?php $is_logged_in = is_user_logged_in(); ?>
<script>
    // ✅ Define isLoggedIn variable
    var isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

    const CoinPurchaseAjax = {
        ajaxUrl: <?php echo json_encode(admin_url('admin-ajax.php')); ?>,
        nonce: <?php echo json_encode(wp_create_nonce('coin_purchase_nonce')); ?>
    };

    async function createCoinOrder(amount, coins) {
        const res = await fetch(CoinPurchaseAjax.ajaxUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'create_coin_order',
                nonce: CoinPurchaseAjax.nonce,
                coin: coins,
                price: amount
            })
        });
        const text = await res.text();
        const data = JSON.parse(text);
        if (!data.success) throw new Error(data.data?.message || 'Order create failed');
        return data.data; // { order_id, amount, currency }
    }

    function paymentProcess(amount, coins) {
        (async () => {
            const order = await createCoinOrder(amount, coins);

            var options = {
                key: RazorpayConfig.key,
                amount: order.amount,      // paise
                currency: order.currency,  // INR
                name: "Buy Keys",
                description: coins + " Keys",
                order_id: order.order_id, 
                handler: function (response) {
                    // ✅ Do NOT credit keys here. Webhook will credit on payment.captured.
                    alert('Payment received! Keys will be added shortly.');
                    window.location.href = "<?php echo esc_url(site_url('/wallet')); ?>";
                },
                modal: {
                    ondismiss: function () {
                        // No need to save cancelled here; webhook will get failed only if payment actually fails.
                    }
                }
            };

            var rzp = new Razorpay(options);

            rzp.on('payment.failed', function () {
                alert('Payment failed.');
                // ✅ No DB credit/save needed here; webhook will mark failed (payment.failed event)
            });

            rzp.open();
        })().catch((e) => {
            console.error(e);
            alert(e.message || 'Unable to start payment');
        });
    }

    function redirectToLogin() {
        window.location.href = "<?php echo site_url('/login'); ?>?redirect_to=" + encodeURIComponent(window.location.href);
    }
</script>
