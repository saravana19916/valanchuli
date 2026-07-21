<?php get_header(); ?>

<style>
    .tab-btn {
    border-radius: 10px;
    font-weight: 600;
    padding: 12px;
}

.tab-btn.active {
    background-color: #000;
    color: #fff;
}

.earning-card {
    background: #fff;
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.status-btn {
    background: #e0e0e0;
    color: #333;
    font-size: 13px;
    padding: 6px 14px;
    border-radius: 20px;
    white-space: nowrap;
}


</style>

<h4 class="py-5 fw-bold m-0 text-center">Earning History</h4>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">

            <!-- Tabs -->
            <div class="d-flex gap-2 mb-4">
                <button class="btn btn-outline-dark w-50 tab-btn active">
                    இந்த மாத சம்பாதியம்
                </button>
                <button class="btn btn-dark w-50 tab-btn">
                    முந்தைய சம்பாதியம்
                </button>
            </div>

            <!-- Earning Card -->
            <div class="earning-card mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-1">₹ 77.76</h4>
                    <span class="status-btn">கொடுத்த</span>
                </div>
                <p class="mb-1 text-muted">
                    Transaction ID: #HSBCN52025120699525212
                </p>
                <small class="text-muted">01/11/2025 - 01/12/2025</small>
            </div>

            <div class="earning-card mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-1">₹ 122.22</h4>
                    <span class="status-btn">கொடுத்த</span>
                </div>
                <p class="mb-1 text-muted">
                    Transaction ID: #HSBCN52025110791194541
                </p>
                <small class="text-muted">01/10/2025 - 01/11/2025</small>
            </div>

            <div class="earning-card mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-1">₹ 142.38</h4>
                    <span class="status-btn">கொடுத்த</span>
                </div>
                <p class="mb-1 text-muted">
                    Transaction ID: #HSBCN52025100782259407
                </p>
                <small class="text-muted">01/09/2025 - 01/10/2025</small>
            </div>

            <div class="earning-card mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-1">₹ 218.52</h4>
                    <span class="status-btn">கொடுத்த</span>
                </div>
                <p class="mb-1 text-muted">
                    Transaction ID: #HSBCN52025090773784315
                </p>
                <small class="text-muted">01/08/2025 - 01/09/2025</small>
            </div>

        </div>
    </div>
</div>

<?php get_footer(); ?>
