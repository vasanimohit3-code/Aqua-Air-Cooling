<?php
$topbar_phone = '+91 6354911971';
$topbar_email = 'aquaircooling@gmail.com';

if (isset($conn) && $conn) {
    $tb_q = mysqli_query($conn, "SELECT phone, email FROM contact_info WHERE id=1");
    if ($tb_q && $tb_row = mysqli_fetch_assoc($tb_q)) {
        if (!empty($tb_row['phone'])) $topbar_phone = $tb_row['phone'];
        if (!empty($tb_row['email'])) $topbar_email = $tb_row['email'];
    }
}
?>
<div class="topbar">
    <div class="topbar-content">

        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $topbar_phone); ?>" class="topbar-item phone-item">
            <i class="fas fa-phone-alt"></i>
            <span><?php echo htmlspecialchars($topbar_phone); ?></span>
        </a>

        <a href="mailto:<?php echo htmlspecialchars($topbar_email); ?>" class="topbar-item email-item">
            <i class="fas fa-envelope"></i>
            <span><?php echo htmlspecialchars($topbar_email); ?></span>
        </a>

    </div>

    <div class="topbar-shine"></div>
</div>

<style>
.topbar {
    position: relative;

    min-height: 44px;

    display: flex;
    align-items: center;
    justify-content: flex-end;

    padding: 0 50px;

    background:
        linear-gradient(
            120deg,
            #020b35,
            #07164f,
            #003b73,
            #020b35
        );

    background-size: 300% 300%;

    animation: topbarGradient 8s ease infinite;

    overflow: hidden;

    border-bottom: 1px solid rgba(56,189,248,0.15);

    box-shadow:
        0 4px 14px rgba(0,0,0,0.10);
}


/* =========================================
   CONTENT
========================================= */

.topbar-content {
    position: relative;
    z-index: 2;

    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-left: auto;

    gap: 18px;
}


/* =========================================
   TOPBAR ITEMS
========================================= */

.topbar-item {

    position: relative;

    display: inline-flex;
    align-items: center;

    gap: 9px;

    padding: 6px 13px;

    border-radius: 20px;

    color: #dbeafe !important;

    text-decoration: none !important;

    font-size: 14px;
    font-weight: 500;

    background: rgba(255,255,255,0.05);

    border: 1px solid rgba(255,255,255,0.08);

    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);

    transition: all 0.35s ease;
}


/* =========================================
   ICON
========================================= */

.topbar-item i {

    font-size: 13px;

    color: #38bdf8;

    transition: all 0.35s ease;
}


/* =========================================
   HOVER
========================================= */

.topbar-item:hover {

    color: #ffffff !important;

    background: rgba(56,189,248,0.14);

    border-color: rgba(56,189,248,0.35);

    transform: translateY(-2px);

    box-shadow:
        0 6px 18px rgba(14,165,233,0.18),
        inset 0 1px 1px rgba(255,255,255,0.08);
}


.topbar-item:hover i {

    transform: scale(1.2);

    color: #7dd3fc;

    filter:
        drop-shadow(0 0 6px rgba(56,189,248,0.8));
}


/* =========================================
   PHONE
========================================= */

.phone-item i {
    color: #60a5fa;
}


/* =========================================
   EMAIL
========================================= */

.email-item i {
    color: #22d3ee;
}


/* =========================================
   MOVING SHINE
========================================= */

.topbar-shine {

    position: absolute;

    top: 0;
    left: -20%;

    width: 20%;
    height: 100%;

    background: linear-gradient(
        110deg,
        transparent,
        rgba(255,255,255,0.10),
        transparent
    );

    transform: skewX(-25deg);

    animation: topbarShine 5s ease-in-out infinite;

    pointer-events: none;
}


/* =========================================
   GRADIENT ANIMATION
========================================= */

@keyframes topbarGradient {

    0% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }

}


/* =========================================
   SHINE ANIMATION
========================================= */

@keyframes topbarShine {

    0% {
        left: -30%;
    }

    55% {
        left: 120%;
    }

    100% {
        left: 120%;
    }

}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 768px) {

    .topbar {
        min-height: 40px;
        padding: 0 10px;
    }

    .topbar-content {
        width: 100%;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .topbar-item {
        padding: 4px 9px;
        font-size: 11px;
        gap: 5px;
    }

    .topbar-item i {
        font-size: 11px;
    }

    .email-item span {
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
}

@media (max-width: 480px) {
    .topbar {
        min-height: 36px;
        padding: 2px 6px;
    }

    .topbar-content {
        gap: 5px;
    }

    .topbar-item {
        padding: 3px 7px;
        font-size: 10.5px;
    }

    .email-item span {
        max-width: 130px;
    }
}
</style>