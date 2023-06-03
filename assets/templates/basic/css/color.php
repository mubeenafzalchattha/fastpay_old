<?php

header("Content-Type:text/css");
$color1 = $_GET['color1']; // Change your Color Here


function checkhexcolor($color1) {
    return preg_match('/^#[a-f0-9]{6}$/i', $color1);
}

if (isset($_GET['color1']) and $_GET['color1'] != '') {
    $color1 = "#" . $_GET['color1'];
}

if (!$color1 or !checkhexcolor($color1)) {
    $color1 = "#336699";
}
?>

a:hover, .section-top-title, .nav-tabs.custom--style .nav-item .nav-link.active, .number-list--style li span, .page-breadcrumb li:first-child::before, .page-breadcrumb li a:hover, .read-btn, .read-btn:hover i, .action-btn, .header .main-menu li.menu_has_children:hover > a::before, .header .main-menu li a:hover, .header .main-menu li a:focus, .header .main-menu li .sub-menu li a:hover, .overview-card__icon i, .testimonial-card__content i, .text--base, .contact-item a:hover, .cookie__wrapper .title, .cookie__wrapper .btn--close, .account-menu .icon .account-submenu li a:hover, .link-btn, .btn-outline--base, file-demo-btn {
color: <?= $color1 ?>;
}

.base--color {
color: <?= $color1 ?> !important;
}

::selection{
background: <?= $color1 ?> !important;
}

.preloader .animated-preloader, .preloader .animated-preloader::before, .modal-footer .btn--primary, ::-webkit-scrollbar-thumb {
background-color: <?= $color1 ?> !important;
}

.section-title.has--border::after, .bling-dot, .bling-dot::before, .bling-dot::after, .nav-tabs.custom--style-two .nav-item .nav-link.active, .pagination li.active a, .pagination li a:hover, .page-item.active .page-link, .scroll-to-top, .btn--base, .border-btn:hover, .read-btn:hover, .read-btn i, .custom-radio label::after, .account-menu .icon i, .bitcoin-form-wrapper .title::after, .choose-card__icon, .testimonial-slider .slick-dots li.slick-active button, .chat-box__thread::-webkit-scrollbar-thumb, .footer-widget .social-links li a:hover, .subscribe-form button, .active, .header .main-menu li .menu-badge, .contact-item .icon, .btn--base:hover, .create-trade-form .input-group-text, .d-widget2 .icon, .btn-list li a.active, .custom-input-field::before, .btn-outline--base:hover {
background-color: <?= $color1 ?>;
}

.base--bg, .bg--base, .profile-thumb .avatar-edit label {
background-color: <?= $color1 ?> !important;
}

.contact-item, .single-message .message-author .thumb {
background-color: <?= $color1 ?>12;
}

.custom-checkbox input:checked + label::after, .btn-list li a.active, .btn-outline--base {
border-color: <?= $color1 ?>;
}

.nav-tabs.custom--style .nav-item .nav-link.active, .nav-tabs.custom--style-two .nav-item .nav-link.active, .pagination li.active a, .pagination li a:hover, .page-item.active .page-link, input:focus, textarea:focus, .nice-select.open, .border-btn:hover, .form-control:focus, .custom-radio input[type=radio]:checked ~ label::before, .testimonial-slider .slick-center .testimonial-card, .footer-widget .social-links li a:hover, .d-widget2, .border--base {
border-color: <?= $color1 ?>;
}

.cookie__wrapper .read-policy {
border: 1px solid <?= $color1 ?>;
}

.choose-card {
border: 2px solid <?= $color1 ?>;
}

.bitcoin-form-wrapper {
border: 3px solid <?= $color1 ?>;
}

.cmn-list li::before {
border-left: 1px solid <?= $color1 ?>;
}

.cmn-list li::before {
border-bottom: 1px solid <?= $color1 ?>;
}

.choose-card:hover {
box-shadow: 0 5px 10px <?= $color1 ?>;
}

.cookie__wrapper {
border-top: 1px solid <?= $color1 ?>50;
}

.tab-menu, .nav-tabs.custom--style-two .nav-item {
background-color: <?= $color1 ?>3b;
}

@media (max-width: 575px) {
.tab-menu .tab-menu-btn:not(.active) {
background-color: <?= $color1 ?>3b;
}
}

@media (max-width: 991px) {
.nav-tabs.custom--style-two .nav-link:not(.active) {
background-color: <?= $color1 ?>3b;
}
}

@media (max-width: 767px) {
.nav-tabs.custom--style-two.nav-tabs--lg .nav-link:not(.active) {
background-color: <?= $color1 ?>3b;
}
}

.single-message.message--right .message-content {
background-color: <?= $color1 ?>0d;
border: 1px solid <?= $color1 ?>30;
}

.currency-loading {
background-color: <?= $color1 ?>38;
}