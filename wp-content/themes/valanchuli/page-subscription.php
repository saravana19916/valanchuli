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


</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">

            <h3 class="text-center fw-bold mb-4">Choose Your Plan</h3>

            <?php foreach ($plans as $index => $plan): 
                $collapseId = 'planDetails_' . $index;
            ?>
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
                                <span class="text-decoration-line-through me-2 fs-16px">
                                    ₹<?php echo esc_html($plan['price']); ?>
                                </span>

                                <?php if (!empty($plan['price']) && !empty($plan['offerprice'])):
                                    $discount = round((($plan['price'] - $plan['offerprice']) / $plan['price']) * 100);
                                ?>
                                    <span class="badge bg-success fs-14px">
                                        Save <?php echo $discount; ?>%
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="fw-bold fs-16px">
                                ₹<?php echo esc_html($plan['offerprice']); ?>
                            </p>
                        </div>

                        <div class="text-end">
                            <button class="btn btn-link p-0 plan-icon"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?php echo esc_attr($collapseId); ?>"
                                    aria-expanded="false">
                                <i class="fa-solid fa-list fa-sm" style="color:#005d67;"></i>
                            </button>
                            <br>
                            <button class="btn btn-primary btn-sm mt-2">
                                Subscribe Now
                            </button>
                        </div>
                    </div>
                </div>

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

<?php get_footer(); ?>
