<?php get_header(); ?>

<?php
global $wpdb;
$user_id = get_current_user_id();
$table = $wpdb->prefix . 'user_bank_details';
$bank_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id));
?>

<style>
.earning-main-bg {
    background: #f7fafc;
    min-height: 100vh;
    padding-bottom: 40px;
}
.earning-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 12px #0001;
    padding: 24px 18px;
    margin-bottom: 24px;
}
.earning-title {
    font-size: 2rem;
    font-weight: 700;
    text-align: center;
    margin-top: 32px;
    margin-bottom: 24px;
}
.bank-btn {
    background: #eaf7f2;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 500;
    color: #005d67;
    width: 100%;
    padding: 12px;
    margin-bottom: 18px;
    cursor: pointer;
    transition: background 0.2s;
}
.bank-btn:hover {
    background: #d0ece7;
}
.earning-amount {
    font-size: 2.2rem;
    font-weight: 700;
    color: #005d67;
    margin-bottom: 8px;
}
.earning-sub {
    color: #888;
    font-size: 1.1rem;
    margin-bottom: 18px;
}
.earning-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f7f7f7;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 12px;
}
.earning-row .icon {
    font-size: 1.5rem;
    margin-right: 8px;
}
.earning-row .label {
    font-size: 1rem;
    font-weight: 500;
    color: #333;
}
.earning-row .value {
    font-size: 1rem;
    font-weight: 600;
    color: #005d67;
}
.earning-history-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-top: 32px;
    margin-bottom: 12px;
}
.earning-history-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 6px #0001;
    padding: 18px 16px;
    margin-bottom: 14px;
}
.earning-history-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.earning-history-row .paid {
    color: #1e7e34;
    font-weight: 600;
    background: #eaf7f2;
    border-radius: 8px;
    padding: 4px 12px;
    font-size: 0.8rem;
}
.earning-history-row .amount {
    font-size: 1.2rem;
    font-weight: 600;
    color: #005d67;
}
.earning-history-row .date {
    color: #888;
    font-size: 1rem;
}
.earning-history-row .txn {
    color: #aaa;
    font-size: 0.95rem;
}
.more-link {
    display: block;
    text-align: center;
    color: #005d67;
    font-weight: 500;
    margin-top: 10px;
    font-size: 1.1rem;
}
.modal-bg {
    position: fixed;
    top:0; left:0; right:0; bottom:0;
    background: rgba(0,0,0,0.18);
    z-index: 1000;
    display: none;
}
.bank-modal {
    background: #fff;
    border-radius: 18px;
    max-width: 420px;
    margin: 60px auto;
    padding: 28px 22px;
    box-shadow: 0 4px 24px #0002;
    position: relative;
}
.bank-modal h2 {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 8px;
    text-align: center;
}
.bank-modal .form-group {
    margin-bottom: 14px;
}
.bank-modal label {
    font-weight: 500;
    color: #333;
    margin-bottom: 4px;
    display: block;
}
.bank-modal input {
    width: 100%;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 10px;
    font-size: 1rem;
    margin-bottom: 2px;
}
.bank-modal .modal-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 18px;
}
.bank-modal .modal-actions button {
    flex: 1;
    margin: 0 4px;
    padding: 10px 0;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    border: none;
    cursor: pointer;
}
.bank-modal .modal-actions .save-btn {
    background: #005d67;
    color: #fff;
}
.bank-modal .close-btn {
    position: absolute;
    top: 12px;
    right: 18px;
    font-size: 1.3rem;
    color: #888;
    cursor: pointer;
}
@media (max-width: 600px) {
    .bank-modal {
        max-height: 80vh;
        overflow-y: auto;
    }
}
.paid.unpaid {
    color: #c00;
    background: #fdeaea;
}
.paid.processing {
    color: #856404;
    background: #fff3cd;
}
.earning-history-row-revenue {
    display: grid;
    grid-template-columns: 194px 1fr 80px;
    align-items: center;
    margin-top: 4px;
    margin-bottom: 4px;
}
.earning-history-row-revenue .label {
    font-size: 1rem;
    font-weight: 500;
    color: #333;
}
.earning-history-row-revenue .amount {
    font-size: 1.1rem;
    font-weight: 600;
    color: #005d67;
}
.earning-history-row-revenue .paid {
    justify-self: end;
}
</style>

<div class="earning-main-bg">
    <div class="container" style="max-width:480px;">
        <div class="earning-title">My Earnings</div>
        <button class="bank-btn" id="openBankModal">Bank Details</button>
        <div class="earning-card">
            <div class="fw-semibold mb-2" style="font-size:1.1rem;">Current Month Key Revenue</div>
            <div class="earning-amount">₹<?php echo (isset(getWriterKeyEarning(get_current_user_id(), date('Y-m-01'), date('Y-m-t'))[0]) ? getWriterKeyEarning(get_current_user_id(), date('Y-m-01'), date('Y-m-t'))[0]['revenue_share'] : 0); ?></div>
            <!-- <div class="earning-row">
                <div style="display:flex;align-items:center;">
                    <span class="icon">🔑</span>
                    <span class="label" style="margin-left:8px;">
                        <?php
                            $key_earnings = getWriterKeyEarning(get_current_user_id(), date('Y-m-01'), date('Y-m-t'));
                            echo isset($key_earnings[0]) ? $key_earnings[0]['keys'] : 0;
                        ?> Key Revenue
                    </span>
                </div>
                <span class="value">
                    ₹<?php
                        $key_earnings = getWriterKeyEarning(get_current_user_id(), date('Y-m-01'), date('Y-m-t'));
                        echo isset($key_earnings[0]) ? $key_earnings[0]['revenue_share'] : 0;
                    ?>
                </span>
            </div>
            <div class="earning-row">
                <div style="display:flex;align-items:center;">
                    <span class="icon">🎁</span>
                    <span class="label" style="margin-left:8px;">Subscription Bonus</span>
                </div>
                <span class="value">
                    ₹<?php echo getWriterSubsctiptionEarning(get_current_user_id(), date('Y-m-01'), date('Y-m-t')); ?>
                </span>
            </div> -->
            <div class="earning-row">
                <div style="display:flex;align-items:center;">
                    <span class="icon" style="vertical-align:middle;">
                        <svg width="24" height="24" viewBox="0 0 32 32">
                            <circle cx="16" cy="16" r="14" fill="#005d67" />
                            <path d="M16 16 L16 2 A14 14 0 0 1 28.12 23.2 Z" fill="#eaf7f2" />
                            <circle cx="16" cy="16" r="14" fill="none" stroke="#005d67" stroke-width="1"/>
                        </svg>
                    </span>
                    <span class="label" style="margin-left:8px;"><b><?php echo get_option('writer_revenue_percentage', 0); ?>%</b> Revenue Share</span>
                </div>
            </div>

            <?php
            $info_board = get_option('revenue_info_board', '');
            if (!empty($info_board)) {
                echo '<div class="earning-info-board" style="margin:24px 0;padding:18px;background:#f9f9f9;border-radius:12px;border:1px solid #e0e0e0;max-height:220px;overflow-y:auto;">';
                echo wpautop($info_board);
                echo '</div>';
            }
            ?>
        </div>

        <?php
        global $wpdb;
        $user_id = get_current_user_id();
        $table = $wpdb->prefix . 'writer_payment_history';

        // Get all payment history for this user, newest first
        $history = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY from_date DESC", $user_id
        ));

        // Group by month and sum revenue
        $monthly_history = [];
        foreach ($history as $row) {
            $month = date('Y-m', strtotime($row->from_date));
            if (!isset($monthly_history[$month])) {
                $monthly_history[$month] = [
                    'from_date' => $row->from_date,
                    'to_date' => $row->to_date,
                    'key' => 0,
                    'subscription' => 0,
                    'status' => [],
                    'transaction_id' => [],
                    'unpaid_reason' => [],
                ];
            }
            if ($row->revenue_type == 'key') {
                $monthly_history[$month]['key'] += $row->revenue_payment;
                $monthly_history[$month]['status']['key'] = $row->payment_status;
                $monthly_history[$month]['transaction_id']['key'] = $row->transaction_id;
                $monthly_history[$month]['unpaid_reason']['key'] = $row->unpaid_reason;
            } elseif ($row->revenue_type == 'subscription') {
                $monthly_history[$month]['subscription'] += $row->revenue_payment;
                $monthly_history[$month]['status']['subscription'] = $row->payment_status;
                $monthly_history[$month]['transaction_id']['subscription'] = $row->transaction_id;
                $monthly_history[$month]['unpaid_reason']['subscription'] = $row->unpaid_reason;
            }
        }
        ?>

        <div class="earning-history-title">Earning History</div>
        <div id="earning-history-list">
            <?php if (empty($monthly_history)): ?>
                <div class="earning-history-card" style="text-align:center; color:#888;">
                    No data found
                </div>
            <?php else: ?>
                <?php
                $months = array_keys($monthly_history);
                rsort($months); // Show latest first
                $show_count = 5;
                $i = 0;
                foreach ($months as $month):
                    $data = $monthly_history[$month];
                    $total = $data['key'] + $data['subscription'];
                ?>
                    <div class="earning-history-card" style="<?= $i >= $show_count ? 'display:none;' : '' ?>">
                        <div class="earning-history-row">
                            <span class="fw-bold">
                                <?php echo date('F Y', strtotime($data['from_date'])); ?> (<?= date('d-m-Y', strtotime($data['from_date'])); ?> to <?= date('d-m-Y', strtotime($data['to_date'])); ?>)
                            </span>
                            <span class="amount">₹<?= number_format($total, 2); ?></span>
                        </div>
                        <div class="earning-history-row earning-history-row-revenue">
                            <span class="label">Key Revenue</span>
                            <span class="amount">₹<?= number_format($data['key'], 2); ?></span>
                            <?php
                            $status_key = strtolower($data['status']['key'] ?? '');
                            $paid_class_key = 'paid';
                            if ($status_key === 'unpaid') $paid_class_key .= ' unpaid';
                            elseif ($status_key === 'processing') $paid_class_key .= ' processing';
                            ?>
                            <span class="<?= $paid_class_key; ?>"><?= esc_html($data['status']['key'] ?? ''); ?></span>
                        </div>
                        <?php if ($status_key === 'paid' && !empty($data['transaction_id']['key'])): ?>
                            <div class="earning-history-row mt-1 mb-2" style="font-size:0.95rem;">
                                <span class="fw-bold">Transaction ID: <?= esc_html($data['transaction_id']['key']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (($status_key === 'unpaid' || $status_key === 'processing') && !empty($data['unpaid_reason']['key'])): ?>
                            <div class="earning-history-row mt-1" style="color:#c00; font-size:0.95rem;">
                                <span>Reason: <?= esc_html($data['unpaid_reason']['key']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="earning-history-row earning-history-row-revenue">
                            <span class="label">Subscription Revenue</span>
                            <span class="amount">₹<?= number_format($data['subscription'], 2); ?></span>
                            <?php
                            $status_sub = strtolower($data['status']['subscription'] ?? '');
                            $paid_class_sub = 'paid';
                            if ($status_sub === 'unpaid') $paid_class_sub .= ' unpaid';
                            elseif ($status_sub === 'processing') $paid_class_sub .= ' processing';
                            ?>
                            <span class="<?= $paid_class_sub; ?>"><?= esc_html($data['status']['subscription'] ?? ''); ?></span>
                        </div>
                        <?php if ($status_sub === 'paid' && !empty($data['transaction_id']['subscription'])): ?>
                            <div class="earning-history-row mt-1 mb-2" style="font-size:0.95rem;">
                                <span class="fw-bold">Transaction ID: <?= esc_html($data['transaction_id']['subscription']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (($status_sub === 'unpaid' || $status_sub === 'processing') && !empty($data['unpaid_reason']['subscription'])): ?>
                            <div class="earning-history-row mt-1" style="color:#c00; font-size:0.95rem;">
                                <span>Reason: <?= esc_html($data['unpaid_reason']['subscription']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php $i++; endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if (!empty($history) && count($history) > $show_count): ?>
            <a href="#" class="more-link" id="showAllHistory">More</a>
            <a href="#" class="more-link" id="showLessHistory" style="display:none;">Less</a>
        <?php endif; ?>

        <script>
        document.getElementById('showAllHistory')?.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('#earning-history-list .earning-history-card').forEach(function(card) {
                card.style.display = 'block';
            });
            this.style.display = 'none';
            document.getElementById('showLessHistory').style.display = 'block';
        });

        document.getElementById('showLessHistory')?.addEventListener('click', function(e) {
            e.preventDefault();
            var showCount = <?php echo (int)$show_count; ?>;
            document.querySelectorAll('#earning-history-list .earning-history-card').forEach(function(card, idx) {
                card.style.display = idx < showCount ? 'block' : 'none';
            });
            this.style.display = 'none';
            document.getElementById('showAllHistory').style.display = 'block';
        });
        </script>
    </div>
</div>

<!-- Bank Details Modal -->
<div class="modal-bg" id="bankModalBg">
    <div class="bank-modal">
        <span class="close-btn" id="closeBankModal">&times;</span>
        <h2>Bank Details</h2>
        <?php if (!$bank_details): ?>
            <div class="fs-14px fw-bold" id="bankSaveNote">வங்கி விவரத்தை பதிவிட்டு save செய்த பிறகு உங்களால் மீண்டும் edit செய்ய முடியாது, அதனால் கவனமாக கையாளவும்.</div>
        <?php endif; ?>
        <form id="bankDetailsForm" class="mt-4">
            <div class="form-group">
                <label>Bank Name <span class="required">*</span></label>
                <input type="text" name="bank_name" value="<?php echo esc_attr($bank_details->bank_name ?? ''); ?>" <?php echo (!empty($bank_details) && is_object($bank_details)) ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label>Account Holder Name <span class="required">*</span></label>
                <input type="text" name="holder_name" value="<?php echo esc_attr($bank_details->holder_name ?? ''); ?>" <?php echo (!empty($bank_details) && is_object($bank_details)) ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label>Account Number <span class="required">*</span></label>
                <input type="text" name="account_number" value="<?php echo esc_attr($bank_details->account_number ?? ''); ?>" <?php echo (!empty($bank_details) && is_object($bank_details)) ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label>IFSC Code <span class="required">*</span></label>
                <input type="text" name="ifsc_code" value="<?php echo esc_attr($bank_details->ifsc_code ?? ''); ?>" <?php echo (!empty($bank_details) && is_object($bank_details)) ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label>PAN Number <span class="required">*</span></label>
                <input type="text" name="pan_number" value="<?php echo esc_attr($bank_details->pan_number ?? ''); ?>" <?php echo (!empty($bank_details) && is_object($bank_details)) ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label>Phone Number <span class="required">*</span></label>
                <input type="text" name="phone_number" value="<?php echo esc_attr($bank_details->phone_number ?? ''); ?>" <?php echo (!empty($bank_details) && is_object($bank_details)) ? 'disabled' : ''; ?>>
            </div>
            <?php if (!$bank_details): ?>
                <div class="modal-actions">
                    <button type="submit" class="save-btn">Save</button>
                </div>
            <?php endif; ?>
            <div class="fs-14px fw-bold" id="bankEditNote" style="<?php echo !$bank_details ? 'display:none;' : ''; ?>">
                உங்கள் Bank details மாற்ற அல்லது திருத்த வேண்டும் என்றால் இங்கே 
                <a href="<?php echo site_url('/contact'); ?>" style="color:#005d67;font-weight:600;text-decoration:underline;">கிளிக்</a> செய்யுங்கள்.
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('openBankModal').onclick = function() {
    document.getElementById('bankModalBg').style.display = 'block';
};
document.getElementById('closeBankModal').onclick = function() {
    document.getElementById('bankModalBg').style.display = 'none';
};
// Optional: handle form submit
document.getElementById('bankDetailsForm').onsubmit = function(e) {
    e.preventDefault();
    var form = e.target;
    var valid = true;
    var fields = ['bank_name', 'holder_name', 'account_number', 'ifsc_code', 'pan_number', 'phone_number'];
    fields.forEach(function(name) {
        var input = form[name];
        if (!input.value.trim()) {
            input.style.borderColor = '#c00';
            valid = false;
        } else {
            input.style.borderColor = '#e0e0e0';
        }
    });
    if (!valid) {
        alert('Please fill all fields.');
        return;
    }
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        credentials: 'same-origin',
        body: new URLSearchParams([
            ['action', 'save_bank_details'],
            ['bank_name', form.bank_name.value],
            ['holder_name', form.holder_name.value],
            ['account_number', form.account_number.value],
            ['ifsc_code', form.ifsc_code.value],
            ['pan_number', form.pan_number.value],
            ['phone_number', form.phone_number.value]
        ])
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Bank details saved!');
            Array.from(form.querySelectorAll('input')).forEach(function(input) {
                input.disabled = true;
            });
            var saveBtn = form.querySelector('.save-btn');
            if (saveBtn) saveBtn.style.display = 'none';
            // Hide the save note and show the edit note
            document.getElementById('bankSaveNote').style.display = 'none';
            document.getElementById('bankEditNote').style.display = 'block';
        } else {
            alert('Failed to save: ' + (data.data || 'Unknown error'));
        }
    });
};
</script>

<?php get_footer(); ?>
