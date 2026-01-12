<?php get_header(); ?>

<style>
    .earning-card {
    border-top: 8px solid #0b6b6f;
    border-radius: 16px;
}

.calendar-icon {
    font-size: 22px;
}

.payment-btn {
    background-color: #3b82f6;
    border-radius: 12px;
    font-weight: 500;
}

.bank-card {
    border: 3px solid #3b82f6;
    border-radius: 18px;
}

.bank-card .form-control {
    border-radius: 10px;
    padding: 12px;
}

.update-btn {
    background-color: #3b82f6;
    border-radius: 12px;
    padding: 12px;
    font-weight: 600;
}


</style>

<h4 class="py-5 fw-bold m-0 text-center">Month Earning</h4>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-12">

            <!-- Earnings Card -->
            <div class="card earning-card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-semibold mb-0">Current Month Earnings</h6>
                        <span class="calendar-icon">📅</span>
                    </div>

                    <h2 class="fw-bold mt-3">₹ 15,250</h2>

                    <a href="#" class="btn btn-primary w-100 mt-3 payment-btn">
                        Payment History ⏱
                    </a>
                </div>
            </div>

            <!-- Bank Details -->
            <div class="card bank-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Bank Details</h6>

                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Account Holder Name">
                    </div>

                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Bank Name">
                    </div>

                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Account Number">
                    </div>

                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="IFSC Code">
                    </div>

                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Pan Number">
                    </div>

                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Mobile Number">
                    </div>

                    <button class="btn btn-primary w-100 update-btn">
                        Update Bank Details
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<?php get_footer(); ?>
