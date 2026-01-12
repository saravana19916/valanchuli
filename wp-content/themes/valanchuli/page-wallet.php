<?php get_header(); ?>

<style>
.wallet-card {
    border: 3px solid #0b6b6f;
    border-radius: 16px;
}

.wallet-icon {
    font-size: 42px;
}

.subscription-card {
    background: linear-gradient(135deg, #0b6b6f, #0a5a5d);
    border-radius: 16px;
}

.inactive-card {
    background: #fbf6e9;
    border: 3px solid #d6b46a;
    border-radius: 16px;
    position: relative;
}

.lock-icon {
    font-size: 42px;
    color: #c7a24c;
}

.inactive-badge {
    background: #e5e5e5;
    color: #333;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 8px;
}

.btn-subscribe {
    background: #e1b55b;
    border: none;
    border-radius: 10px;
    font-weight: 600;
}

.btn-subscribe:hover {
    background: #d4a84d;
}
</style>

<?php
// TEMP condition – replace with real subscription check
$is_subscribed = true;
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <h3 class="text-center fw-bold mb-4">My Wallet</h3>

            <!-- Wallet Card -->
            <div class="card wallet-card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold">My Balance</h5>

                    <div class="d-flex align-items-center my-3">
                        <h3 class="mb-0 me-3">25 Keys Available</h3>
                        <span class="wallet-icon">🔑</span>
                    </div>

                    <div class="d-flex gap-3">
                        <a href="#" class="btn btn-primary flex-fill">Buy More Keys</a>
                        <a href="#" class="btn btn-secondary flex-fill">Key History</a>
                    </div>
                </div>
            </div>

            <?php if ($is_subscribed): ?>

                <!-- ✅ ACTIVE SUBSCRIPTION -->
                <div class="card subscription-card text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="text-uppercase mb-0">My Subscription</h6>
                            <span class="badge bg-success px-3 py-2">Active</span>
                        </div>

                        <h4 class="mt-3 mb-1">Basic</h4>
                        <p class="mb-2">Renews on Jan 26, 2026</p>

                        <div class="d-flex gap-3 mt-3">
                            <a href="#" class="btn btn-light flex-fill">Renew</a>
                            <a href="#" class="btn btn-outline-light flex-fill">More Keys</a>
                        </div>
                    </div>
                </div>

            <?php else: ?>

                <!-- ❌ NO SUBSCRIPTION -->
                <div class="card inactive-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h4 class="fw-bold mb-1">Unlock Unlimited Reading!</h4>
                            <span class="inactive-badge">Inactive</span>
                        </div>

                        <p class="mt-3 mb-2">
                            Subscription plan potta keys illamale
                            ella episodes-aiyum neenga padikalaam.
                        </p>

                        <p class="fw-semibold">
                            Plans start from just ₹199/month
                        </p>

                        <div class="d-flex align-items-center gap-3 mt-3">
                            <span class="lock-icon">🔒</span>
                            <a href="/subscription-plans"
                               class="btn btn-subscribe flex-fill">
                                View Subscription Plan
                            </a>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php get_footer(); ?>
