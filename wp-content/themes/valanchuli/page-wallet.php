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
global $wpdb;
$user_id = get_current_user_id();
$table = $wpdb->prefix . 'user_subscriptions';

// Get the latest active subscription for the user
$now = current_time('mysql');
$subscription = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $table 
         WHERE user_id = %d 
           AND status = 1 
           AND payment_status = 'success'
           AND start_date <= %s 
           AND end_date >= %s
         ORDER BY end_date DESC 
         LIMIT 1",
        $user_id, $now, $now
    )
);

$is_subscribed = !!$subscription;

// Fetch upcoming (queued) plans for the user
$upcoming_plans = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table WHERE user_id = %d AND status = 1 AND payment_status = 'success' AND start_date > %s ORDER BY start_date ASC",
        $user_id,
        current_time('mysql')
    )
);
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
                        <h3 class="mb-0 me-3">
                            <?php echo intval(get_user_meta($user_id, 'wallet_keys', true)); ?> Keys Available
                        </h3>
                        <!-- <span class="wallet-icon">🔑</span> -->
                         <img src="<?php echo get_template_directory_uri(); ?>/images/wallet-key.jpeg"
                                            class="d-block rounded" alt="Default Image" style="width: 60px; height: 80px;">
                    </div>

                    <div class="d-flex gap-3">
                        <a href="<?php echo esc_url(site_url('/key-purchase?redirect=' . get_permalink())); ?>" class="btn btn-primary flex-fill">Buy More Keys</a>
                        <a href="#" class="btn btn-secondary flex-fill" data-bs-toggle="modal" data-bs-target="#keyHistoryModal">Key History</a>
                    </div>
                </div>
            </div>

            <?php if ($is_subscribed): ?>
                <div class="card subscription-card text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="text-uppercase mb-0">My Subscription</h6>
                            <span class="badge bg-success px-3 py-2">Active</span>
                        </div>

                        <h4 class="mt-3 mb-1"><?php echo esc_html($subscription->plan_name); ?></h4>
                        <p class="mb-2">
                            Renews on <?php echo date('M d, Y', strtotime($subscription->end_date)); ?>
                        </p>

                        <div class="d-flex gap-3 mt-3">
                            <a href="#" 
                                class="btn btn-light flex-fill" 
                                id="renewNowBtn"
                                data-plan="<?php echo esc_attr($subscription->plan_name); ?>"
                                data-period="<?php echo esc_attr($subscription->plan_period); ?>"
                                data-amount="<?php echo esc_attr($subscription->plan_amount); ?>">
                                Renew Now
                            </a>
                            <a href="#" class="btn btn-outline-light flex-fill" data-bs-toggle="modal" data-bs-target="#subscriptionHistoryModal">Subscription History</a>
                        </div>
                    </div>
                </div>

                <?php if ($upcoming_plans): ?>
                    <div class="card wallet-card mt-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Upcoming Plans</h6>
                            <?php foreach ($upcoming_plans as $plan): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold"><?php echo esc_html($plan->plan_name); ?></span>
                                        <span class="badge bg-info text-dark">
                                            Starts on <?php echo date('M d, Y', strtotime($plan->start_date)); ?>
                                        </span>
                                    </div>
                                    <div class="text-muted small">
                                        Valid till <?php echo date('M d, Y', strtotime($plan->end_date)); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- ❌ NO SUBSCRIPTION -->
                <div class="card inactive-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h4 class="fw-bold mb-1">Unlock Unlimited Reading!</h4>
                            <span class="inactive-badge">Inactive</span>
                        </div>

                        <p class="mt-3 mb-2">
                            கதைகளுக்கு எதுக்கு பூட்டு? இப்பொழுதே Subscription பண்ணுங்க, அனைத்து எபிசோடையும் திறந்து படிங்க!
                        </p>

                        <div class="d-flex align-items-center gap-3 mt-3">
                            <span class="lock-icon">🔒</span>
                            <a href="subscription"
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

<!-- Key History Modal -->
<div class="modal fade" id="keyHistoryModal" tabindex="-1" aria-labelledby="keyHistoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="keyHistoryModalLabel">Key History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php
        $history = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}coin_purchases WHERE user_id = %d ORDER BY created_at DESC",
                $user_id
            )
        );
        if ($history): ?>
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Date</th>
                <th>Keys</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($history as $row): ?>
                <tr>
                  <td><?php echo date('M d, Y H:i', strtotime($row->created_at)); ?></td>
                  <td><?php echo esc_html($row->coin); ?></td>
                  <td>₹<?php echo esc_html($row->price); ?></td>
                  <td>
                    <?php if ($row->payment_status === 'success'): ?>
                      <span class="badge bg-success">Success</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Failed</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div>No key purchase history found.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Subscription History Modal -->
<div class="modal fade" id="subscriptionHistoryModal" tabindex="-1" aria-labelledby="subscriptionHistoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary-color text-white">
        <h5 class="modal-title fw-bold" id="subscriptionHistoryModalLabel">Subscription History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body m-3">
        <div class="rounded-4 p-3" style="border:3px solid #19706e;">
          <?php
          $subs = $wpdb->get_results(
            $wpdb->prepare(
              "SELECT * FROM {$wpdb->prefix}user_subscriptions WHERE user_id = %d ORDER BY start_date DESC",
              $user_id
            )
          );
          if ($subs):
            $now = current_time('mysql');
            foreach ($subs as $i => $sub):
              $is_active = ($sub->status == 1 && $sub->payment_status == 'success' && $sub->start_date <= $now && $sub->end_date >= $now);
          ?>
            <div class="py-3 px-2 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
              <div>
                <div class="fw-bold fs-5 mb-1"><?php echo esc_html($sub->plan_name); ?></div>
                <div class="text-muted mb-1" style="font-size:1rem;">
                  Valid from <?php echo date('M d, Y', strtotime($sub->start_date)); ?>
                  to <?php echo date('M d, Y', strtotime($sub->end_date)); ?>
                </div>
                <div class="text-secondary small d-flex align-items-center">
                  <i class="fa fa-clock-o me-1"></i>
                  Renewed on <?php echo date('M d, Y', strtotime($sub->start_date)); ?>
                </div>
              </div>
              <div class="text-end">
                <?php if ($is_active): ?>
                  <span class="badge bg-primary-color px-4 py-2 fs-6">Active</span>
                <?php else: ?>
                  <span class="badge bg-price-color text-dark px-4 py-2 fs-6">Paid ₹<?php echo number_format($sub->plan_amount, 2); ?></span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; else: ?>
            <div class="text-center text-muted py-5">No subscription history found.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let selectedPlan = {};

document.getElementById('renewNowBtn').onclick = function(e) {
    e.preventDefault();
    selectedPlan = {
        name: this.dataset.plan,
        period: this.dataset.period,
        amount: this.dataset.amount
    };
    // Open your payment modal (reuse your existing modal)
    var modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
};
</script>

<?php get_footer(); ?>
