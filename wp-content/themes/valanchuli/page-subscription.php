<?php
    get_header();

    $plans = get_option('plan_details');
?>

<style>
    .plan-card {
    background: #fff;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.plan-icon {
    font-size: 28px;
    text-decoration: none;
}

.plan-details {
    background: #f8f9fa;
    border-radius: 14px;
    padding: 16px;
}

.plan-details ul {
    list-style: none;
    padding-left: 0;
}

.plan-details li {
    padding-left: 26px;
    position: relative;
    margin-bottom: 10px;
}

.plan-details li::before {
    content: "✔";
    color: #22c55e;
    position: absolute;
    left: 0;
    top: 0;
}

.save-badge {
    background: #d1fae5;
    color: #065f46;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 10px;
    margin-left: 6px;
}

.highlight-plan {
    background: linear-gradient(135deg, #f8d9fa 0%, #b7b6fa 100%);
    border: 3px solid #a26edc;
    border-radius: 18px;
    padding: 22px 18px 28px 18px;
    position: relative;
    box-shadow: 0 4px 18px rgba(162,110,220,0.10);
    overflow: hidden;
}

.best-value-badge {
    position: absolute;
    top: 14px;
    right: 14px;
    background: gold;
    color: #333;
    font-weight: bold;
    font-size: 13px;
    border-radius: 50%;
    padding: 10px 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.10);
    border: 2px solid #fff;
    z-index: 2;
}

.save-big {
    color: #22c55e;
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    margin-top: 0.5rem;
    letter-spacing: 1px;
    display: block;
}

.save-percent {
    color: #22c55e;
    font-size: 1.2rem;
    font-weight: bold;
    margin-left: 8px;
}

.eye-circle {
    width: 23px;
    height: 23px;
    background-color: #005d67;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.eye-circle i {
    color: #ffffff;
    font-size: 12px;
}
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">

            <h3 class="text-center fw-bold mb-4">Choose your subscription plan</h3>

            <?php foreach ($plans as $index => $plan): 
                $collapseId = 'planDetails_' . $index;
                $isOneYear = (strtolower($plan['period']) === 'one year' || strtolower($plan['period']) === '1 year');
                $discount = (!empty($plan['price']) && !empty($plan['offerprice']))
                    ? round((($plan['price'] - $plan['offerprice']) / $plan['price']) * 100)
                    : 0;
            ?>
            <?php if($plan['name'] == '') continue; ?>

            <?php if ($isOneYear) : ?>
                <div class="plan-card mb-3 highlight-plan">
                    <div class="d-flex flex-column align-items-end" style="position: absolute; top: 14px; right: 14px; z-index: 2;">
                        <span class="best-value-badge" style="position: static; box-shadow: none; border: none;">BEST VALUE</span>
                        <button class="btn btn-link p-0 plan-icon mt-2"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?php echo esc_attr($collapseId); ?>"
                                aria-expanded="false">
                            <span class="eye-circle">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                        </button>
                    </div>

                    <div class="d-flex flex-column align-items-start">
                        <small class="text-muted fw-semibold">
                            <?php echo esc_html($plan['period']); ?>
                        </small>
                        <h5 class="mb-1 fw-bold">
                            <?php echo esc_html($plan['name']); ?>
                        </h5>
                        <?php if (!empty($plan['price']) && !empty($plan['offerprice'])): ?>
                            <span class="fw-bold fs-4 mb-1">
                                ₹<?php echo esc_html($plan['offerprice']); ?>
                            </span>
                        <?php endif; ?>

                        <div class="mb-1">
                            <span class="<?php echo (!empty($plan['offerprice']) ? 'text-decoration-line-through' : ''); ?> me-2 fs-16px">
                                ₹<?php echo esc_html($plan['price']); ?>
                            </span>
                        </div>

                        <?php if ($discount > 0): ?>
                            <span class="badge bg-danger fs-16px">
                                Save <?php echo $discount; ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="w-100 mt-3 text-center">
                        <?php
                            global $wpdb, $current_user;
                            $user_id = get_current_user_id();
                            $plan_name = $plan['name'];
                            $now = current_time('mysql');

                            // Check if user has an active subscription for this plan
                            $is_active = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM {$wpdb->prefix}user_subscriptions 
                                WHERE user_id = %d AND plan_name = %s AND status = 1 AND payment_status = 'success' 
                                AND start_date <= %s AND end_date >= %s",
                                $user_id, $plan_name, $now, $now
                            ));
                            $button_text = $is_active ? 'Renew Now' : 'Subscribe Now';
                        ?>
                        <button class="btn btn-primary mt-2 rounded-pill px-4 subscribe-btn"
                            data-plan="<?php echo esc_attr($plan['name']); ?>"
                            data-period="<?php echo esc_attr($plan['period']); ?>"
                            data-amount="<?php echo esc_attr(!empty($plan['offerprice']) ? $plan['offerprice'] : $plan['price']); ?>">
                            <?php echo esc_html($button_text); ?>
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="plan-card mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-semibold">
                                <?php echo esc_html($plan['period']); ?>
                            </small>

                            <h5 class="mb-1 fw-bold">
                                <?php echo esc_html($plan['name']); ?>
                            </h5>

                            <div class="d-flex justify-content-center align-items-center mb-1">
                                <span class="<?php echo (!empty($plan['offerprice']) ? 'text-decoration-line-through' : ''); ?> me-2 fs-16px">
                                    ₹<?php echo esc_html($plan['price']); ?>
                                </span>

                                <?php if (!empty($plan['price']) && !empty($plan['offerprice'])):
                                    $discount = round((($plan['price'] - $plan['offerprice']) / $plan['price']) * 100);
                                ?>
                                    <span class="badge bg-danger fs-14px">
                                        Save <?php echo $discount; ?>%
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($plan['price']) && !empty($plan['offerprice'])): ?>
                                <p class="fw-bold fs-16px">
                                    ₹<?php echo esc_html($plan['offerprice']); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="text-end">
                            <?php
                                global $wpdb, $current_user;
                                $user_id = get_current_user_id();
                                $plan_name = $plan['name'];
                                $now = current_time('mysql');

                                // Check if user has an active subscription for this plan
                                $is_active = $wpdb->get_var($wpdb->prepare(
                                    "SELECT COUNT(*) FROM {$wpdb->prefix}user_subscriptions 
                                    WHERE user_id = %d AND plan_name = %s AND status = 1 AND payment_status = 'success' 
                                    AND start_date <= %s AND end_date >= %s",
                                    $user_id, $plan_name, $now, $now
                                ));
                                $button_text = $is_active ? 'Renew Now' : 'Subscribe Now';
                            ?>
                            <button class="btn btn-link p-0 plan-icon"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?php echo esc_attr($collapseId); ?>"
                                    aria-expanded="false">
                                <span class="eye-circle">
                                    <i class="fa-solid fa-eye"></i>
                                </span>
                            </button>
                            <br>
                            <button class="btn btn-primary mt-2 rounded-pill px-4 subscribe-btn"
                                data-plan="<?php echo esc_attr($plan['name']); ?>"
                                data-period="<?php echo esc_attr($plan['period']); ?>"
                                data-amount="<?php echo esc_attr(!empty($plan['offerprice']) ? $plan['offerprice'] : $plan['price']); ?>">
                                <?php echo esc_html($button_text); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

                <!-- PLAN DETAILS -->
                <div class="collapse mb-4" id="<?php echo esc_attr($collapseId); ?>">
                    <div class="plan-details">
                        <?php echo wpautop($plan['description']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
let selectedPlan = {};

document.querySelectorAll('.subscribe-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        var isLoggedIn = <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;
        if (!isLoggedIn) {
            window.location.href = "<?php echo site_url('/login'); ?>?redirect_to=" + encodeURIComponent(window.location.href);
            return;
        }

        let plan = {
            name: this.dataset.plan,
            period: this.dataset.period,
            amount: this.dataset.amount
        };
        var options = {
            "key": RazorpayConfig.key,
            "amount": plan.amount * 100,
            "currency": "INR",
            "name": plan.name,
            "description": plan.period,
            "handler": function (response){
                saveSubscription('razorpay', response.razorpay_payment_id, 'success', plan);
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function (response){
            saveSubscription('razorpay', response.error.metadata.payment_id || '', 'failed', plan);
        });
        rzp1.open();
    });
});

function saveSubscription(method, payment_id, payment_status, plan) {
    const redirectTo = getRedirectTo();
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'save_subscription',
            plan_name: plan.name,
            plan_period: plan.period,
            plan_amount: plan.amount,
            payment_method: method,
            payment_id: payment_id,
            payment_status: payment_status
        })
    })
    .then(res => res.json())
    .then (data => {
        if(data.success && payment_status === 'success') {
            alert('Subscription added successfully!');
            if (redirectTo) {
                window.location.href = redirectTo;
            } else {
                location.reload();
            }
        } else if(payment_status === 'failed') {
            alert('Payment failed or cancelled.');
        } else {
            alert('Subscription failed!');
        }
    });
}

// document.getElementById('paypalBtn').onclick = function() {
//     document.getElementById('paypalBtn').style.display = 'none';
//     var container = document.getElementById('paypal-button-container');
//     container.style.display = 'block';
//     container.innerHTML = "";

//     paypal.Buttons({
//         createOrder: function(data, actions) {
//             return actions.order.create({
//                 purchase_units: [{
//                     amount: {
//                         value: selectedPlan.amount
//                     },
//                     description: selectedPlan.name + " - " + selectedPlan.period
//                 }]
//             });
//         },
//         onApprove: function(data, actions) {
//             return actions.order.capture().then(function(details) {
//                 saveSubscription('paypal', details.id, 'success');
//             });
//         },
//         onCancel: function (data) {
//             saveSubscription('paypal', '', 'failed');
//         },
//         onError: function (err) {
//             saveSubscription('paypal', '', 'failed');
//         }
//     }).render('#paypal-button-container');
// };

function getRedirectTo() {
    const params = new URLSearchParams(window.location.search);
    return params.get('redirect_to');
}
</script>

<?php get_footer(); ?>
