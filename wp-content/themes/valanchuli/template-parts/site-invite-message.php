<?php $message = $args['message'] ?? ''; ?>
<div class="my-4">
    <div class="reading-card d-flex align-items-center shadow-sm">
        <div class="card-content d-flex align-items-center flex-grow-1 p-2">
            <div class="icon-box me-3">
                <i class="fa-solid fa-book-open"></i>
            </div>

            <div>
                <p class="mb-2 fw-semibold text-dark tamil-text fs-14px site-invite-message">
                    <?php echo wp_kses_post($message); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.reading-card {
    background: #e6f4e8;
    border-radius: 14px;
    overflow: hidden;
}

/* Left Icon */
.icon-box {
    width: 48px;
    height: 48px;
    background: #e0f0e5;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-box i {
    font-size: 22px;
    color: #4b7c63;
}

/* Tamil text */
.tamil-text {
    font-size: 16px;
    line-height: 1.5;
}

/* Badge */
.subscribe-badge {
    background: #7fb69a;
    color: #fff;
    padding: 6px 14px;
    font-size: 13px;
}

/* Right Arrow */
.arrow-box {
    width: 70px;
    background: #1e73be;
    color: #fff;
    font-size: 24px;
}

/* Shadow enhancement */
.reading-card {
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}

.site-invite-message a {
    color: red;
}
</style>