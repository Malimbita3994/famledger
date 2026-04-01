<!DOCTYPE html>
<html lang="en">
<head>

	<title>FamLedger – Family accounting & private wealth ledger</title>
	<!--

    FamLedger marketing landing page — family accounting, wallets, assets, and governance in one place.

    -->
     <meta charset="UTF-8">
     <meta http-equiv="X-UA-Compatible" content="IE=Edge">
     <meta name="description" content="FamLedger is family accounting software: record income and expenses by wallet, reconcile balances, track properties and projects, and produce clear reports for owners and advisors—all in one private ledger.">
     <meta name="keywords" content="FamLedger, family accounting, family ledger, household finance, family office, property accounting, wallet tracking, family wealth">
     <meta name="author" content="FamLedger">
     <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

     @include('partials.famledger-favicon')

     <link rel="preload" href="{{ asset('metronic/assets/css/tooplate-style.css') }}" as="style">
     <link rel="preload" href="{{ asset('images/background.png') }}" as="image">
     <link rel="preload" href="{{ asset('images/logo.png') }}" as="image">
     {{-- Hero visual: load early so Edge/Chromium does not defer LCP via lazy-load intervention --}}
     <link rel="preload" href="{{ asset('images/hero-wealth-growth.png') }}" as="image" fetchpriority="high">

     <link rel="stylesheet" href="{{ asset('metronic/assets/css/bootstrap.min.css') }}">
     <link rel="stylesheet" href="{{ asset('metronic/assets/css/owl.carousel.css') }}">
     <link rel="stylesheet" href="{{ asset('metronic/assets/css/owl.theme.default.min.css') }}">
     <link rel="stylesheet" href="{{ asset('metronic/assets/css/font-awesome.min.css') }}">

     {{-- Same-origin Quill avoids Edge “Tracking Prevention blocked access to storage” on third-party CDNs --}}
     <link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
     <link rel="stylesheet" href="{{ asset('css/famledger-contact-form-modal.css') }}">

     <!-- MAIN CSS -->
     <link rel="stylesheet" href="{{ asset('metronic/assets/css/tooplate-style.css') }}">

    <style>
     @keyframes blink {
       0%, 100% { opacity: 1; }
       50% { opacity: 0; }
     }

     .animate-blink {
       animation: blink 1s infinite;
     }

     .hero-cta {
       padding: 10px 24px;
            border-radius: 999px;
       transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
       display: inline-block;
     }

     .hero-cta:hover {
       transform: translateY(-2px);
       box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
     }

     .hero-cta-secondary {
       background-color: transparent;
       border: 1px solid #ffffff;
     }

     .hero-cta-secondary:hover {
       background-color: #ffffff;
       color: #009EF7 !important;
       border-color: #ffffff;
     }

     /* Accounting-focused marketing hero */
     .famledger-hero-marketing .home-info h3 {
       font-size: 0.95rem;
       letter-spacing: 0.06em;
       text-transform: uppercase;
       color: rgba(255, 255, 255, 0.88);
       margin-bottom: 12px;
     }
     .famledger-hero-visual {
       text-align: center;
     }
     .famledger-hero-visual-card {
       display: inline-block;
       max-width: 520px;
       margin: 0 auto;
       padding: 12px;
       border-radius: 12px;
       background: rgba(255, 255, 255, 0.08);
       border: 1px solid rgba(255, 255, 255, 0.2);
       box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25);
       animation: famledger-hero-pulse-glow 2.8s ease-in-out infinite;
     }
     @keyframes famledger-hero-pulse-glow {
       0%, 100% {
         box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25), 0 0 0 0 rgba(0, 158, 247, 0);
         border-color: rgba(255, 255, 255, 0.2);
       }
       50% {
         box-shadow: 0 28px 70px rgba(0, 0, 0, 0.3), 0 0 36px 6px rgba(0, 158, 247, 0.35);
         border-color: rgba(255, 255, 255, 0.35);
       }
     }
     .famledger-hero-pulse-img-wrap {
       border-radius: 8px;
       overflow: hidden;
       line-height: 0;
     }
     .famledger-hero-pulse-img-wrap img {
       border-radius: 8px;
       animation: famledger-hero-pulse-scale 2.8s ease-in-out infinite;
     }
     @keyframes famledger-hero-pulse-scale {
       0%, 100% { transform: scale(1); }
       50% { transform: scale(1.035); }
     }
     @media (prefers-reduced-motion: reduce) {
       .famledger-hero-visual-card {
         animation: none;
         box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25);
       }
       .famledger-hero-pulse-img-wrap img {
         animation: none;
       }
     }
     @media (max-width: 991px) {
       .famledger-hero-visual {
         margin-top: 28px;
       }
     }

     #home > .container > .row.famledger-hero-row {
       display: flex;
       flex-wrap: wrap;
       align-items: center;
     }

     /* Scroll reveal animations */
     .scroll-reveal {
       opacity: 0;
       transform: translateY(28px);
       transition: opacity 0.6s ease-out, transform 0.6s ease-out;
     }
     .scroll-reveal.scroll-reveal-visible {
       opacity: 1;
       transform: translateY(0);
     }
     .scroll-reveal-delay-1 { transition-delay: 0.1s; }
     .scroll-reveal-delay-2 { transition-delay: 0.2s; }
     .scroll-reveal-delay-3 { transition-delay: 0.3s; }
     .scroll-reveal-delay-4 { transition-delay: 0.4s; }

     /* Contact heading animation */
     @keyframes contactGlow {
       0%   { letter-spacing: 0.02em; text-shadow: 0 0 0 rgba(0, 158, 247, 0.0); }
       50%  { letter-spacing: 0.09em; text-shadow: 0 0 18px rgba(0, 158, 247, 0.55); }
       100% { letter-spacing: 0.02em; text-shadow: 0 0 0 rgba(0, 158, 247, 0.0); }
     }

     .contact-title-animated {
       animation: contactGlow 3s ease-in-out infinite;
       text-transform: uppercase;
       font-size: 1.6rem;
       letter-spacing: 0.08em;
     }

     /* Footer — professional dark bar, brand accent stripe */
     .famledger-landing #footer {
       position: relative;
       background: linear-gradient(180deg, #10151c 0%, #0a0d12 100%);
       color: rgba(226, 232, 240, 0.92);
       text-align: left;
       border-top: 1px solid rgba(148, 163, 184, 0.1);
       box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
       padding-top: 0;
       padding-bottom: 0;
     }
     .famledger-landing #footer::before {
       content: "";
       position: absolute;
       left: 0;
       right: 0;
       top: 0;
       height: 3px;
       background: linear-gradient(90deg, #009ef7, #38bdf8, #22c55e);
       pointer-events: none;
     }
     .famledger-landing #footer .container {
       padding-top: clamp(1.65rem, 3.8vw, 2.35rem);
       padding-bottom: clamp(1.65rem, 3.8vw, 2.35rem);
     }
     .famledger-landing #footer .famledger-footer-row {
       display: flex;
       flex-wrap: wrap;
       align-items: center;
       justify-content: space-between;
       margin-left: 0;
       margin-right: 0;
     }
     .famledger-landing #footer .famledger-footer-row > [class*="col-"] {
       float: none;
     }
     .famledger-landing #footer .famledger-footer-copy {
       margin-bottom: 0;
     }
     .famledger-landing #footer .famledger-footer-tagline {
       margin: 0;
       max-width: 36rem;
       font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
       font-size: clamp(0.8125rem, 0.2vw + 0.78rem, 0.9375rem);
       line-height: 1.65;
       letter-spacing: 0.01em;
       color: rgba(203, 213, 225, 0.95);
       -webkit-font-smoothing: antialiased;
     }
     .famledger-landing #footer .famledger-footer-brand {
       color: #f8fafc;
       font-weight: 600;
       letter-spacing: -0.02em;
     }
     .famledger-landing #footer .famledger-footer-social {
       margin-bottom: 0;
     }
     .famledger-landing #footer .famledger-footer-social-list {
       display: flex;
       flex-wrap: wrap;
       align-items: center;
       justify-content: flex-end;
       gap: 10px;
       list-style: none;
       margin: 0;
       padding: 0;
     }
     .famledger-landing #footer .famledger-footer-social-list li {
       display: block;
       margin: 0;
       padding: 0;
     }
     .famledger-landing #footer .famledger-footer-social-list li a {
       display: inline-flex;
       align-items: center;
       justify-content: center;
       width: 40px;
       height: 40px;
       border-radius: 999px;
       border: 1px solid rgba(255, 255, 255, 0.14);
       background: rgba(255, 255, 255, 0.05);
       color: rgba(248, 250, 252, 0.92) !important;
       font-size: 17px;
       line-height: 1;
       text-decoration: none;
       transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
       box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
     }
     .famledger-landing #footer .famledger-footer-social-list li a:hover,
     .famledger-landing #footer .famledger-footer-social-list li a:focus {
       background: rgba(0, 158, 247, 0.12);
       border-color: rgba(56, 189, 248, 0.45);
       color: #ffffff !important;
       transform: translateY(-2px);
       box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
       outline: none;
     }
     .famledger-landing #footer .famledger-footer-social-list li a:focus-visible {
       outline: 2px solid rgba(56, 189, 248, 0.8);
       outline-offset: 2px;
     }
     @media (max-width: 767px) {
       .famledger-landing #footer .famledger-footer-row {
         flex-direction: column;
         align-items: stretch;
         gap: 1.15rem;
       }
       .famledger-landing #footer .famledger-footer-copy {
         text-align: center;
       }
       .famledger-landing #footer .famledger-footer-tagline {
         margin-left: auto;
         margin-right: auto;
         max-width: 28rem;
       }
       .famledger-landing #footer .famledger-footer-social-list {
         justify-content: center;
       }
     }
     @media (min-width: 768px) {
       .famledger-landing #footer .famledger-footer-copy {
         text-align: left;
       }
       .famledger-landing #footer .famledger-footer-social {
         text-align: right;
       }
     }
     @media (prefers-reduced-motion: reduce) {
       .famledger-landing #footer .famledger-footer-social-list li a {
         transition: none;
       }
       .famledger-landing #footer .famledger-footer-social-list li a:hover,
       .famledger-landing #footer .famledger-footer-social-list li a:focus {
         transform: none;
       }
     }

     /* Contact success: no third-party modal (avoids full-page dim / stuck backdrops) */
     .famledger-contact-success-banner {
       position: fixed;
       top: 16px;
       right: 16px;
       left: 16px;
       max-width: 420px;
       margin-left: auto;
       z-index: 100000;
       display: flex;
       align-items: flex-start;
       gap: 12px;
       padding: 14px 16px;
       border-radius: 12px;
       border: 1px solid #d1fae5;
       background: #fff;
       box-shadow: 0 20px 50px -12px rgba(15, 23, 42, 0.2);
       font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
       font-size: 0.9375rem;
       font-weight: 600;
       color: #0f172a;
       animation: famledger-banner-in 0.35s ease-out;
     }
     @keyframes famledger-banner-in {
       from { opacity: 0; transform: translateY(-8px); }
       to { opacity: 1; transform: translateY(0); }
     }
     .famledger-contact-success-banner .famledger-contact-success-dismiss {
       flex-shrink: 0;
       margin-left: auto;
       border: none;
       background: linear-gradient(135deg, #059669, #22c55e);
       color: #fff;
       font-weight: 600;
       font-size: 0.75rem;
       padding: 6px 14px;
       border-radius: 999px;
       cursor: pointer;
     }
     .famledger-contact-success-banner .famledger-contact-success-dismiss:hover {
       filter: brightness(1.05);
     }

     /* Landing FAQ — full width within .container */
     .famledger-landing #faq .section-title h1 {
       font-weight: 700;
       font-size: clamp(1.5rem, 2.5vw + 0.9rem, 2.55rem);
       line-height: 1.2;
     }
     .famledger-landing #faq .landing-faq-layout {
       margin-top: 8px;
     }
     .famledger-landing #faq .landing-faq-sidebar-inner {
       position: sticky;
       top: 76px;
       padding: 14px 16px 18px;
       border-radius: 12px;
       border: 1px solid #e2e8f0;
       background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
       box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
     }
     .famledger-landing #faq .landing-faq-nav-title {
       font-size: 0.8125rem;
       font-weight: 700;
       letter-spacing: 0.06em;
       text-transform: uppercase;
       color: #64748b;
       margin: 0 0 12px;
     }
     .famledger-landing #faq .landing-faq-nav ul {
       list-style: none;
       padding: 0;
       margin: 0;
     }
     .famledger-landing #faq .landing-faq-nav li + li {
       margin-top: 6px;
     }
     .famledger-landing #faq .landing-faq-nav a {
       display: block;
       font-size: 1rem;
       font-weight: 600;
       color: #334155;
       text-decoration: none;
       padding: 8px 10px;
       border-radius: 8px;
       line-height: 1.4;
       word-break: break-word;
     }
     .famledger-landing #faq .landing-faq-nav a:hover,
     .famledger-landing #faq .landing-faq-nav a:focus {
       color: #009ef7;
       background: rgba(0, 158, 247, 0.08);
       outline: none;
     }
     .famledger-landing #faq .landing-faq-nav a.is-landing-faq-active {
       color: #009ef7;
       background: rgba(0, 158, 247, 0.12);
     }
     .famledger-landing #faq .landing-faq-group-block {
       scroll-margin-top: 88px;
     }
     .famledger-landing #faq .landing-faq-group-title {
       font-size: clamp(1.2rem, 1.05rem + 0.55vw, 1.45rem);
       font-weight: 700;
       color: #0f172a;
       margin: 28px 0 16px;
       padding-bottom: 8px;
       border-bottom: 2px solid #e2e8f0;
       line-height: 1.25;
     }
     .famledger-landing #faq .landing-faq-group-block:first-of-type .landing-faq-group-title {
       margin-top: 0;
     }
     @media (max-width: 991px) {
       .famledger-landing #faq .landing-faq-sidebar {
         margin-bottom: 20px;
       }
       .famledger-landing #faq .landing-faq-sidebar-inner {
         position: static;
       }
       .famledger-landing #faq .landing-faq-nav ul {
         display: flex;
         flex-wrap: wrap;
         gap: 8px;
       }
       .famledger-landing #faq .landing-faq-nav li + li {
         margin-top: 0;
       }
       .famledger-landing #faq .landing-faq-nav a {
         font-size: 0.9375rem;
         padding: 10px 14px;
         border: 1px solid #e2e8f0;
         background: #fff;
       }
     }
     .famledger-landing #faq .panel-group {
       width: 100%;
       max-width: none;
       margin-left: 0;
       margin-right: 0;
     }
     .famledger-landing #faq .panel-group .panel {
       border-radius: 12px;
       border: 1px solid #e2e8f0;
       box-shadow: 0 4px 22px rgba(15, 23, 42, 0.07);
       margin-bottom: 14px;
       overflow: hidden;
     }
     .famledger-landing #faq .panel-heading {
       background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
       border: none;
       padding: 0;
       border-radius: 0;
     }
     .famledger-landing #faq .panel-title {
       margin: 0;
       font-size: clamp(1.0625rem, 0.98rem + 0.4vw, 1.1875rem);
     }
     .famledger-landing #faq .panel-title a {
       display: flex;
       align-items: center;
       justify-content: space-between;
       gap: 14px;
       font-weight: 600;
       color: #0f172a;
       text-decoration: none;
       padding: 18px 20px;
       line-height: 1.45;
       word-break: break-word;
       overflow-wrap: anywhere;
       font-size: inherit;
     }
     .famledger-landing #faq .panel-title a:hover,
     .famledger-landing #faq .panel-title a:focus {
       color: #009ef7;
       background: rgba(0, 158, 247, 0.06);
       outline: none;
     }
     .famledger-landing #faq .panel-title a::after {
       content: "";
       flex-shrink: 0;
       width: 10px;
       height: 10px;
       border-right: 2px solid #64748b;
       border-bottom: 2px solid #64748b;
       transform: rotate(225deg);
       margin-top: -4px;
       transition: transform 0.25s ease, border-color 0.2s ease;
     }
     .famledger-landing #faq .panel-title a.collapsed::after {
       transform: rotate(45deg);
       margin-top: 0;
     }
     .famledger-landing #faq .panel-title a:hover::after,
     .famledger-landing #faq .panel-title a:focus::after {
       border-color: #009ef7;
     }
     .famledger-landing #faq .panel-body {
       font-size: 1.0625rem;
       line-height: 1.75;
       color: #334155;
       border-top: 1px solid #e2e8f0;
       padding: 20px 22px 22px;
       background: #fff;
     }
     /* Dynamic FAQ (rich text from admin) */
     .landing-faq-q p { display: inline; margin: 0; }
     .landing-faq-q h1, .landing-faq-q h2, .landing-faq-q h3, .landing-faq-q h4 {
       display: inline;
       font-size: inherit;
       font-weight: 600;
       margin: 0;
     }
     .landing-faq-a p { margin: 0 0 0.75em; }
     .landing-faq-a p:last-child { margin-bottom: 0; }
     .landing-faq-a ul, .landing-faq-a ol { margin: 0.5em 0 0.75em 1.25em; padding-left: 1em; }
     .landing-faq-a a { color: #009EF7; text-decoration: underline; }
     .landing-faq-a blockquote {
       margin: 0.75em 0;
       padding-left: 1em;
       border-left: 3px solid #e0e0e0;
       font-style: italic;
     }

     /* FAQ section motion (respects prefers-reduced-motion below) */
     @keyframes landing-faq-group-in {
       from {
         opacity: 0;
         transform: translateY(16px);
       }
       to {
         opacity: 1;
         transform: translateY(0);
       }
     }
     @keyframes landing-faq-nav-item-in {
       from {
         opacity: 0;
         transform: translateX(-12px);
       }
       to {
         opacity: 1;
         transform: translateX(0);
       }
     }
     @keyframes landing-faq-panel-in {
       from {
         opacity: 0;
         transform: translateY(12px);
       }
       to {
         opacity: 1;
         transform: translateY(0);
       }
     }
     .famledger-landing #faq .landing-faq-section-heading h1 {
       position: relative;
     }
     .famledger-landing #faq .landing-faq-section-heading h1::after {
       content: "";
       display: block;
       height: 3px;
       width: 0;
       max-width: 6.5rem;
       margin-top: 14px;
       border-radius: 3px;
       background: linear-gradient(90deg, #009ef7, rgba(0, 158, 247, 0.25));
       transition: width 0.9s cubic-bezier(0.22, 1, 0.36, 1);
     }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-section-heading h1::after {
       width: 100%;
     }
     .famledger-landing #faq .landing-faq-sidebar-inner {
       transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
     }
     .famledger-landing #faq .scroll-reveal:not(.scroll-reveal-visible) .landing-faq-sidebar-inner {
       transform: translateX(-18px);
     }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-sidebar-inner {
       transform: translateX(0);
     }
     .famledger-landing #faq .landing-faq-main {
       transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1) 0.06s;
     }
     .famledger-landing #faq .scroll-reveal:not(.scroll-reveal-visible) .landing-faq-main {
       transform: translateY(22px);
     }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-main {
       transform: translateY(0);
     }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-nav li {
       animation: landing-faq-nav-item-in 0.48s cubic-bezier(0.22, 1, 0.36, 1) both;
     }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-nav li:nth-child(1) { animation-delay: 0.08s; }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-nav li:nth-child(2) { animation-delay: 0.14s; }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-nav li:nth-child(3) { animation-delay: 0.2s; }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-nav li:nth-child(4) { animation-delay: 0.26s; }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-nav li:nth-child(5) { animation-delay: 0.32s; }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-nav li:nth-child(6) { animation-delay: 0.38s; }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-nav li:nth-child(7) { animation-delay: 0.44s; }
     .famledger-landing #faq .scroll-reveal-visible .landing-faq-nav li:nth-child(8) { animation-delay: 0.5s; }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) {
       animation: landing-faq-group-in 0.48s cubic-bezier(0.22, 1, 0.36, 1) both;
     }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .landing-faq-group-title {
       animation: landing-faq-panel-in 0.42s cubic-bezier(0.22, 1, 0.36, 1) both;
     }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel:nth-child(2) { animation: landing-faq-panel-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: 0.04s; }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel:nth-child(3) { animation: landing-faq-panel-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: 0.08s; }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel:nth-child(4) { animation: landing-faq-panel-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: 0.12s; }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel:nth-child(5) { animation: landing-faq-panel-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: 0.16s; }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel:nth-child(6) { animation: landing-faq-panel-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: 0.2s; }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel:nth-child(7) { animation: landing-faq-panel-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: 0.24s; }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel:nth-child(8) { animation: landing-faq-panel-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: 0.28s; }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel:nth-child(9) { animation: landing-faq-panel-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: 0.32s; }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel:nth-child(10) { animation: landing-faq-panel-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: 0.36s; }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel:nth-child(11) { animation: landing-faq-panel-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: 0.4s; }
     .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel:nth-child(12) { animation: landing-faq-panel-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: 0.44s; }
     .famledger-landing #faq .panel-group .panel {
       transition: box-shadow 0.28s ease, transform 0.28s ease, border-color 0.28s ease;
     }
     .famledger-landing #faq .panel-group .panel:hover {
       transform: translateY(-2px);
       box-shadow: 0 10px 32px rgba(15, 23, 42, 0.1);
       border-color: #cbd5e1;
     }
     .famledger-landing #faq .landing-faq-nav a {
       transition: color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
     }
     .famledger-landing #faq .landing-faq-nav a:hover,
     .famledger-landing #faq .landing-faq-nav a:focus {
       transform: translateX(2px);
     }
     @media (max-width: 991px) {
       .famledger-landing #faq .landing-faq-nav a:hover,
       .famledger-landing #faq .landing-faq-nav a:focus {
         transform: none;
       }
     }
     @media (prefers-reduced-motion: reduce) {
       .famledger-landing #faq .landing-faq-section-heading h1::after {
         transition: none;
         width: 100%;
       }
       .famledger-landing #faq .landing-faq-sidebar-inner,
       .famledger-landing #faq .landing-faq-main {
         transition: none !important;
         transform: none !important;
       }
       .famledger-landing #faq .scroll-reveal:not(.scroll-reveal-visible) .landing-faq-sidebar-inner,
       .famledger-landing #faq .scroll-reveal:not(.scroll-reveal-visible) .landing-faq-main {
         transform: none !important;
       }
       .famledger-landing #faq .scroll-reveal-visible .landing-faq-nav li,
       .famledger-landing #faq [data-landing-faq-group]:not([hidden]),
       .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .landing-faq-group-title,
       .famledger-landing #faq [data-landing-faq-group]:not([hidden]) .panel {
         animation: none !important;
       }
       .famledger-landing #faq .panel-group .panel {
         transition: none !important;
       }
       .famledger-landing #faq .panel-group .panel:hover {
         transform: none !important;
       }
       .famledger-landing #faq .landing-faq-nav a {
         transition: color 0.15s ease, background-color 0.15s ease;
       }
     }

     /* Published contact support (from admin, same source as in-app notifications settings) */
     #contact .landing-support-options {
       margin-bottom: 40px;
     }
     #contact .landing-support-options h2 {
       font-weight: 600;
       margin-bottom: 24px;
       color: #333;
     }
     #contact .landing-support-card {
       border-radius: 6px;
       border-color: #e0e0e0;
       box-shadow: none;
       margin-bottom: 16px;
     }
     #contact .landing-support-card .panel-body {
       line-height: 1.65;
       color: #555;
       padding: 22px 24px;
     }
     #contact .landing-support-card h3 {
       margin-top: 0;
       margin-bottom: 12px;
       font-weight: 600;
       color: #222;
       font-size: 1.25rem;
     }
     .landing-support-body p { margin: 0 0 0.75em; }
     .landing-support-body p:last-child { margin-bottom: 0; }
     .landing-support-body ul, .landing-support-body ol { margin: 0.5em 0 0.75em 1.25em; padding-left: 1em; }
     .landing-support-body a { color: #009EF7; text-decoration: underline; }
     .landing-support-body blockquote {
       margin: 0.75em 0;
       padding-left: 1em;
       border-left: 3px solid #e0e0e0;
       font-style: italic;
     }
     #contact .landing-support-link {
       margin-top: 18px;
       margin-bottom: 0;
     }
     #contact .landing-support-link-url {
       display: block;
       margin-top: 10px;
       word-break: break-all;
       font-size: 0.95em;
       color: #009EF7;
     }

     /* Contact modal (Bootstrap 3) — support CTA with #contact opens this instead of navigating */
     #landingContactModal .modal-content {
       border-radius: 10px;
       border: 1px solid #e0e0e0;
       box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
     }
     #landingContactModal .modal-header {
       border-bottom: 1px solid #eee;
       padding: 18px 22px;
     }
     #landingContactModal .modal-title {
       font-weight: 600;
       color: #222;
     }
     #landingContactModal .modal-body {
       padding: 20px 22px 8px;
     }
     #landingContactModal .modal-body .form-group {
       margin-bottom: 16px;
     }
     #landingContactModal .modal-body label {
       font-weight: 600;
       color: #444;
       font-size: 13px;
       margin-bottom: 6px;
     }
     #landingContactModal .modal-footer {
       border-top: 1px solid #eee;
       padding: 14px 22px 18px;
     }
     #landingContactModal .modal-footer .section-btn {
       border: 0;
     }
     button.landing-support-modal-btn.section-btn {
       font-family: inherit;
       cursor: pointer;
     }
     .landing-support-modal-hint {
       margin-top: 12px;
       margin-bottom: 0;
       font-size: 13px;
       color: #777;
     }

     /* Single contact area: hero heading + typing, then support cards or fallback CTA */
     #contact .landing-contact-hero {
       margin-bottom: 8px;
     }
     #contact .landing-contact-hero h1 {
       margin-bottom: 0.25rem;
     }

     /* About section — FamLedger (overrides Tooplate #about .section-title text-align:center) */
     #about.famledger-about {
       font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
       -webkit-font-smoothing: antialiased;
       -moz-osx-font-smoothing: grayscale;
       text-rendering: optimizeLegibility;
       -webkit-text-size-adjust: 100%;
     }
     @keyframes famledger-about-intro-pulse {
       0%, 100% {
         box-shadow: 0 6px 32px rgba(15, 23, 42, 0.08);
         border-left-color: #009EF7;
       }
       50% {
         box-shadow: 0 10px 44px rgba(15, 23, 42, 0.12), 0 0 36px 6px rgba(0, 158, 247, 0.22);
         border-left-color: #38bdf8;
       }
     }
     @keyframes famledger-about-pillar-pulse {
       0%, 100% {
         box-shadow: 0 10px 36px rgba(15, 23, 42, 0.08);
       }
       50% {
         box-shadow: 0 14px 48px rgba(15, 23, 42, 0.12), 0 0 24px 4px rgba(0, 158, 247, 0.12);
       }
     }
     #about.famledger-about .famledger-about-intro {
       margin-bottom: 48px;
     }
     #about.famledger-about .famledger-about-intro .section-title {
       margin-bottom: 0;
       padding-bottom: 0;
       text-align: left;
     }
     #about.famledger-about .famledger-about-intro-panel {
       width: 100%;
       max-width: min(72rem, 100%);
       margin-left: auto;
       margin-right: auto;
       padding: clamp(1.65rem, 4.5vw, 3.25rem) clamp(1.5rem, 4vw, 3.25rem);
       background: linear-gradient(165deg, #f8fafc 0%, #f1f5f9 55%, #eef2f7 100%);
       border: 1px solid #e2e8f0;
       border-radius: 16px;
       border-left-width: 5px;
       border-left-style: solid;
       border-left-color: #009EF7;
       box-shadow: 0 6px 32px rgba(15, 23, 42, 0.08);
       animation: famledger-about-intro-pulse 3s ease-in-out infinite;
     }
     /* Staggered text reveal (activated by .famledger-about-panel--visible on panel) */
     #about.famledger-about .famledger-about-intro-panel > * {
       opacity: 0;
       transform: translateY(20px);
     }
     #about.famledger-about .famledger-about-intro-panel.famledger-about-panel--visible > * {
       opacity: 1;
       transform: translateY(0);
     }
     #about.famledger-about .famledger-about-intro-panel.famledger-about-panel--visible > *:nth-child(1) {
       transition: opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1), transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
       transition-delay: 0.06s;
     }
     #about.famledger-about .famledger-about-intro-panel.famledger-about-panel--visible > *:nth-child(2) {
       transition: opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1), transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
       transition-delay: 0.18s;
     }
     #about.famledger-about .famledger-about-intro-panel.famledger-about-panel--visible > *:nth-child(3) {
       transition: opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1), transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
       transition-delay: 0.32s;
     }
     #about.famledger-about .famledger-about-intro-panel.famledger-about-panel--visible > *:nth-child(4) {
       transition: opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1), transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
       transition-delay: 0.46s;
     }
     @media (prefers-reduced-motion: reduce) {
       #about.famledger-about .famledger-about-intro-panel > * {
         opacity: 1 !important;
         transform: none !important;
         transition: none !important;
       }
       #about.famledger-about .famledger-about-intro-panel,
       #about.famledger-about .famledger-about-card {
         animation: none !important;
       }
       #about.famledger-about .famledger-about-intro-panel {
         box-shadow: 0 6px 32px rgba(15, 23, 42, 0.08);
         border-left-color: #009EF7;
       }
       #about.famledger-about .famledger-about-card {
         box-shadow: 0 10px 36px rgba(15, 23, 42, 0.08);
         transition: none !important;
       }
       #about.famledger-about .famledger-about-card:hover {
         transform: none !important;
         box-shadow: 0 10px 36px rgba(15, 23, 42, 0.08) !important;
         border-color: rgba(0, 0, 0, 0.06) !important;
         border-left-color: #009ef7 !important;
       }
       #about.famledger-about .famledger-about-card__media img {
         transition: none !important;
         transform: none !important;
       }
     }
     #about.famledger-about .famledger-about-kicker {
       margin: 0 0 0.75rem;
       font-size: 0.9375rem;
       font-weight: 700;
       letter-spacing: 0.11em;
       text-transform: uppercase;
       color: #009EF7;
     }
     #about.famledger-about .famledger-about-intro h1 {
       margin: 0 0 1.25rem;
       font-size: clamp(1.85rem, 3.2vw, 2.75rem);
       font-weight: 800;
       letter-spacing: -0.03em;
       line-height: 1.15;
       color: #0f172a;
       text-wrap: balance;
     }
     #about.famledger-about .famledger-about-lead {
       margin: 0 0 1.2rem;
       max-width: none;
       font-size: clamp(1.125rem, 1.65vw, 1.3125rem);
       line-height: 1.75;
       color: #1e293b;
       text-align: left;
       font-weight: 400;
     }
     #about.famledger-about .famledger-about-lead:last-child {
       margin-bottom: 0;
     }
     #about.famledger-about .famledger-about-pillars {
       display: flex;
       flex-wrap: wrap;
       align-items: stretch;
     }
     #about.famledger-about .famledger-about-pillars > [class*="col-"] {
       display: flex;
       align-items: stretch;
       padding-left: 15px;
       padding-right: 15px;
       margin-bottom: 24px;
     }
     #about.famledger-about .famledger-about-card {
       background: #fff;
       border-radius: 12px;
       overflow: hidden;
       box-shadow: 0 10px 36px rgba(15, 23, 42, 0.08);
       border: 1px solid rgba(0, 0, 0, 0.06);
       width: 100%;
       min-height: 0;
       height: 100%;
       display: flex;
       flex-direction: column;
       text-align: left;
       outline: none;
       cursor: default;
       animation: famledger-about-pillar-pulse 3.2s ease-in-out infinite;
       transition:
         transform 0.45s cubic-bezier(0.22, 1, 0.36, 1),
         box-shadow 0.45s cubic-bezier(0.22, 1, 0.36, 1),
         border-color 0.35s ease;
     }
     @media (hover: hover) and (pointer: fine) {
       #about.famledger-about .famledger-about-card:hover {
         animation: none;
         transform: translateY(-8px);
         box-shadow: 0 22px 55px -10px rgba(15, 23, 42, 0.2), 0 0 0 1px rgba(0, 158, 247, 0.12), 0 12px 40px rgba(0, 158, 247, 0.1);
         border-color: rgba(0, 158, 247, 0.22);
         border-left-color: #009ef7;
       }
       #about.famledger-about .famledger-about-card:hover .famledger-about-card__media img {
         transform: scale(1.06);
       }
     }
     #about.famledger-about .famledger-about-pillars > [class*="col-"]:nth-child(1) .famledger-about-card {
       animation-delay: 0s;
     }
     #about.famledger-about .famledger-about-pillars > [class*="col-"]:nth-child(2) .famledger-about-card {
       animation-delay: 0.35s;
     }
     #about.famledger-about .famledger-about-pillars > [class*="col-"]:nth-child(3) .famledger-about-card {
       animation-delay: 0.7s;
     }
     /* Fixed image band: portrait/landscape sources can’t stretch card heights */
     #about.famledger-about .famledger-about-card__media {
       position: relative;
       width: 100%;
       height: clamp(200px, 28vw, 280px);
       line-height: 0;
       background: #f4f4f5;
       overflow: hidden;
       flex-shrink: 0;
     }
     #about.famledger-about .famledger-about-card__media img {
       position: absolute;
       left: 0;
       top: 0;
       width: 100%;
       height: 100%;
       object-fit: cover;
       object-position: center center;
       display: block;
       transform: scale(1);
       transform-origin: center center;
       transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
     }
     #about.famledger-about .famledger-about-card__body {
       padding: 26px 24px 30px;
       flex: 1;
       display: flex;
       flex-direction: column;
     }
     #about.famledger-about .famledger-about-card__body h2 {
       margin: 0 0 10px;
       font-size: clamp(1.35rem, 1.9vw, 1.55rem);
       font-weight: 700;
       color: #0f172a;
       line-height: 1.28;
       letter-spacing: -0.02em;
       text-wrap: balance;
     }
     #about.famledger-about .famledger-about-card__role {
       margin: 0 0 14px;
       font-size: 0.9375rem;
       font-weight: 700;
       letter-spacing: 0.07em;
       text-transform: uppercase;
       color: #009EF7;
     }
     #about.famledger-about .famledger-about-card__text {
       margin: 0;
       font-size: clamp(1.0625rem, 0.9rem + 0.55vw, 1.2rem);
       line-height: 1.72;
       color: #334155;
       flex: 1;
     }

     /* --- Landing page responsiveness (all breakpoints) --- */
     .famledger-landing {
       overflow-x: clip;
     }
     @supports not (overflow: clip) {
       .famledger-landing {
         overflow-x: hidden;
       }
     }
     .famledger-landing .container {
       padding-left: max(18px, env(safe-area-inset-left, 0px));
       padding-right: max(18px, env(safe-area-inset-right, 0px));
     }
     @media (max-width: 767px) {
       .famledger-landing .container {
         padding-left: max(20px, 4.5vw, env(safe-area-inset-left, 0px));
         padding-right: max(20px, 4.5vw, env(safe-area-inset-right, 0px));
       }
     }
     @media (max-width: 380px) {
       .famledger-landing .container {
         padding-left: max(16px, 4vw, env(safe-area-inset-left, 0px));
         padding-right: max(16px, 4vw, env(safe-area-inset-right, 0px));
       }
     }
     .famledger-landing img {
       max-width: 100%;
       height: auto;
     }
     .famledger-landing .row > [class*="col-"] {
       min-width: 0;
     }
     .famledger-hero-row > [class*="col-"] {
       min-width: 0;
     }
     .famledger-hero-marketing .home-info {
       max-width: 100%;
     }
     .famledger-hero-marketing .home-info h2 {
       font-size: clamp(0.95rem, 3.6vw, 1.65rem);
       line-height: 1.35;
       word-break: break-word;
       overflow-wrap: anywhere;
       hyphens: auto;
     }
     .famledger-hero-visual-card {
       width: 100%;
       max-width: min(520px, 100%);
       box-sizing: border-box;
     }
     .famledger-hero-pulse-img-wrap img.img-responsive {
       width: 100%;
     }
     .famledger-landing .section-title h1 {
       font-size: clamp(1.2rem, 4vw, 2.25rem);
       line-height: 1.2;
       word-break: break-word;
       overflow-wrap: anywhere;
       padding: 0 6px;
       box-sizing: border-box;
     }
     .famledger-landing .section-title p {
       max-width: 100%;
       box-sizing: border-box;
       padding: 0 10px;
       overflow-wrap: anywhere;
     }
     @keyframes famledger-feature-head-pulse {
       0%, 100% {
         box-shadow: 0 6px 28px rgba(15, 23, 42, 0.07);
       }
       50% {
         box-shadow: 0 10px 40px rgba(15, 23, 42, 0.1), 0 0 36px 8px rgba(14, 165, 233, 0.11);
       }
     }
     #feature .famledger-feature-head-wrap {
       padding-bottom: 8px;
       margin-bottom: 8px;
     }
     #feature .famledger-feature-head-wrap .section-title {
       padding-bottom: 0;
       margin-bottom: 0;
     }
     #feature .famledger-feature-head-panel {
       position: relative;
       width: 100%;
       max-width: min(72rem, 100%);
       margin: 0 auto;
       padding: clamp(1.5rem, 3.5vw, 2.5rem) clamp(1.35rem, 3vw, 2.75rem);
       padding-top: calc(clamp(1.5rem, 3.5vw, 2.5rem) + 2px);
       background: linear-gradient(195deg, #f0f9ff 0%, #ffffff 38%, #f8fafc 100%);
       border: 1px solid rgba(14, 165, 233, 0.14);
       border-radius: 16px;
       box-shadow: 0 6px 28px rgba(15, 23, 42, 0.07);
       text-align: left;
       animation: famledger-feature-head-pulse 3.4s ease-in-out infinite;
       transition:
         transform 0.4s cubic-bezier(0.22, 1, 0.36, 1),
         box-shadow 0.4s ease,
         border-color 0.35s ease;
     }
     #feature .famledger-feature-head-panel::before {
       content: "";
       position: absolute;
       top: 0;
       left: 0;
       right: 0;
       height: 4px;
       border-radius: 15px 15px 0 0;
       background: linear-gradient(90deg, #009ef7 0%, #22d3ee 45%, #0ea5e9 100%);
       transition: filter 0.35s ease, opacity 0.35s ease;
       pointer-events: none;
     }
     @media (hover: hover) and (pointer: fine) {
       #feature .famledger-feature-head-panel:hover {
         animation: none;
         transform: translateY(-4px);
         box-shadow: 0 18px 52px -10px rgba(15, 23, 42, 0.16), 0 0 0 1px rgba(14, 165, 233, 0.18), 0 10px 36px rgba(14, 165, 233, 0.08);
         border-color: rgba(14, 165, 233, 0.22);
       }
       #feature .famledger-feature-head-panel:hover::before {
         filter: brightness(1.08) saturate(1.05);
       }
     }
     #feature .famledger-feature-head-panel .famledger-feature-reveal-line {
       opacity: 0;
       transform: translateY(16px);
     }
     #feature .famledger-feature-head-panel.famledger-feature-head-panel--visible .famledger-feature-reveal-line {
       opacity: 1;
       transform: translateY(0);
     }
     #feature .famledger-feature-head-panel.famledger-feature-head-panel--visible .famledger-feature-reveal-line:nth-child(1) {
       transition: opacity 0.65s cubic-bezier(0.22, 1, 0.36, 1), transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
       transition-delay: 0.05s;
     }
     #feature .famledger-feature-head-panel.famledger-feature-head-panel--visible .famledger-feature-head-grid > .famledger-feature-reveal-line:nth-child(1) {
       transition: opacity 0.65s cubic-bezier(0.22, 1, 0.36, 1), transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
       transition-delay: 0.16s;
     }
     #feature .famledger-feature-head-panel.famledger-feature-head-panel--visible .famledger-feature-head-grid > .famledger-feature-reveal-line:nth-child(2) {
       transition: opacity 0.65s cubic-bezier(0.22, 1, 0.36, 1), transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
       transition-delay: 0.28s;
     }
     #feature .famledger-feature-kicker {
       margin: 0 0 1rem;
       font-size: 0.875rem;
       font-weight: 700;
       letter-spacing: 0.11em;
       text-transform: uppercase;
       color: #009EF7;
     }
     #feature .famledger-feature-head-grid {
       display: grid;
       gap: 1.25rem 2.25rem;
       align-items: start;
     }
     @media (min-width: 768px) {
       #feature .famledger-feature-head-grid {
         grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
       }
     }
     #feature .famledger-feature-head-main h1 {
       margin: 0;
       font-size: clamp(1.65rem, 3.2vw, 2.5rem);
       font-weight: 800;
       letter-spacing: -0.03em;
       line-height: 1.15;
       color: #0f172a;
       padding: 0;
     }
     #feature .famledger-feature-head-copy p {
       margin: 0;
       font-size: clamp(1.08rem, 1.5vw, 1.22rem);
       line-height: 1.72;
       color: #334155;
       padding: 0;
       max-width: none;
     }
     @media (prefers-reduced-motion: reduce) {
       #feature .famledger-feature-head-panel {
         animation: none !important;
         transition: none !important;
         box-shadow: 0 6px 28px rgba(15, 23, 42, 0.07) !important;
         border-color: rgba(14, 165, 233, 0.14) !important;
       }
       #feature .famledger-feature-head-panel::before {
         filter: none !important;
         transition: none !important;
       }
       #feature .famledger-feature-head-panel:hover::before {
         filter: none !important;
       }
       #feature .famledger-feature-head-panel:hover {
         transform: none !important;
         box-shadow: 0 6px 28px rgba(15, 23, 42, 0.07) !important;
         border-color: rgba(14, 165, 233, 0.14) !important;
       }
       #feature .famledger-feature-head-panel .famledger-feature-reveal-line,
       #feature .famledger-feature-head-panel.famledger-feature-head-panel--visible .famledger-feature-reveal-line {
         opacity: 1 !important;
         transform: none !important;
         transition: none !important;
       }
     }
     #feature .tab-pane p,
     #feature .tab-pane-item h2 {
       word-break: break-word;
       overflow-wrap: anywhere;
     }
     .landing-faq-a,
     .landing-support-body {
       overflow-wrap: anywhere;
       word-break: break-word;
     }
     .landing-faq-a table,
     .landing-support-body table {
       display: block;
       max-width: 100%;
       overflow-x: auto;
       -webkit-overflow-scrolling: touch;
     }
     /* Testimonials — FamLedger: legible type, no fixed vh dead space, card + dots */
     .famledger-landing #testimonial .testimonial-info {
       font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
       -webkit-font-smoothing: antialiased;
       -moz-osx-font-smoothing: grayscale;
       display: flex;
       flex-direction: column;
       justify-content: center;
       height: auto;
       min-height: 0;
       box-sizing: border-box;
       background: linear-gradient(168deg, #1c1c22 0%, #121214 55%, #0e0e10 100%);
       border-left: 1px solid rgba(255, 255, 255, 0.06);
       padding: clamp(1.6rem, 4.2vw, 3.25rem) clamp(1.1rem, 3.2vw, 2.75rem);
     }
     .famledger-landing #testimonial .testimonial-info .section-title {
       padding-bottom: 0.35rem;
       margin-bottom: 0;
       text-align: left;
     }
     .famledger-landing #testimonial .testimonial-info .section-title h1 {
       margin: 0;
       font-size: clamp(1.35rem, 0.55rem + 3.2vw, 1.9rem);
       font-weight: 700;
       line-height: 1.22;
       letter-spacing: -0.02em;
       color: #f8fafc;
     }
     .famledger-landing #testimonial .owl-carousel {
       margin-top: 0.35rem;
       padding: clamp(1.1rem, 2.8vw, 1.45rem) clamp(1rem, 2.5vw, 1.35rem) 0.5rem;
       background: rgba(255, 255, 255, 0.045);
       border: 1px solid rgba(255, 255, 255, 0.1);
       border-radius: 14px;
       box-shadow: 0 18px 48px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.06);
     }
     @@supports (backdrop-filter: blur(12px)) {
       .famledger-landing #testimonial .owl-carousel {
         backdrop-filter: blur(12px);
         -webkit-backdrop-filter: blur(12px);
       }
     }
     .famledger-landing #testimonial .owl-carousel .item {
       padding-left: 2px;
       padding-right: 2px;
     }
     .famledger-landing #testimonial .testimonial-info .item h3 {
       font-size: clamp(1.0625rem, 0.88rem + 1.15vw, 1.375rem);
       font-weight: 500;
       font-style: italic;
       line-height: 1.72;
       letter-spacing: 0.01em;
       color: rgba(248, 250, 252, 0.94);
       word-break: break-word;
       overflow-wrap: anywhere;
       padding: 0;
       margin: 0 0 1rem;
       box-sizing: border-box;
     }
     .famledger-landing #testimonial .testimonial-item {
       margin: 0;
       display: flex;
       align-items: center;
       gap: 12px;
       flex-wrap: wrap;
     }
     .famledger-landing #testimonial .testimonial-item img {
       width: 52px;
       height: 52px;
       margin-right: 0;
       border-width: 2px;
       border-color: rgba(255, 255, 255, 0.35);
       flex-shrink: 0;
     }
     .famledger-landing #testimonial .testimonial-item h4 {
       margin: 0;
       font-size: clamp(0.9375rem, 0.82rem + 0.55vw, 1.0625rem);
       font-weight: 600;
       line-height: 1.35;
       color: rgba(226, 232, 240, 0.95);
       display: block;
       vertical-align: unset;
     }
     .famledger-landing #testimonial .owl-theme .owl-nav.disabled + .owl-dots {
       margin-top: 0.85rem;
       margin-bottom: 0;
       padding-bottom: 0.15rem;
     }
     .famledger-landing #testimonial .owl-theme .owl-dots .owl-dot span {
       width: 11px;
       height: 11px;
       margin: 5px 9px;
       background: rgba(255, 255, 255, 0.28);
       transition: background 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
     }
     .famledger-landing #testimonial .owl-theme .owl-dots .owl-dot:hover span {
       background: rgba(255, 255, 255, 0.45);
     }
     .famledger-landing #testimonial .owl-theme .owl-dots .owl-dot.active span {
       background: linear-gradient(135deg, #22c55e, #29ca8e);
       box-shadow: 0 0 0 3px rgba(41, 202, 142, 0.35), 0 0 16px rgba(34, 197, 94, 0.35);
       transform: scale(1.12);
     }
     @media (max-width: 991px) {
       .famledger-landing #testimonial .testimonial-info,
       .famledger-landing #testimonial .testimonial-image {
         height: auto !important;
         min-height: 0 !important;
       }
       .famledger-landing #testimonial .testimonial-image {
         min-height: clamp(200px, 42vw, 300px);
         max-height: min(48vh, 360px);
       }
       .famledger-landing #testimonial .testimonial-info {
         justify-content: flex-start;
         padding-bottom: clamp(1.25rem, 3.5vw, 1.75rem);
       }
     }
     @media (min-width: 992px) {
       .famledger-landing #testimonial .testimonial-info {
         min-height: min(65vh, 640px);
       }
       .famledger-landing #testimonial .testimonial-image {
         height: min(65vh, 640px);
         min-height: min(65vh, 640px);
       }
     }
     @media (prefers-reduced-motion: reduce) {
       .famledger-landing #testimonial .owl-theme .owl-dots .owl-dot span,
       .famledger-landing #testimonial .owl-theme .owl-dots .owl-dot.active span {
         transition: none;
         transform: none;
       }
     }
     #contact .landing-contact-hero h1 {
       font-size: clamp(1.2rem, 4.2vw, 2rem);
       padding: 0 8px;
     }
     #contact .landing-contact-hero h2 {
       word-break: break-word;
       overflow-wrap: anywhere;
       max-width: 100%;
       margin-left: auto;
       margin-right: auto;
       padding: 0 8px;
       box-sizing: border-box;
     }
     #contact .landing-support-card .panel-body {
       padding: 16px 14px;
     }
     @media (min-width: 768px) {
       #contact .landing-support-card .panel-body {
         padding: 22px 24px;
       }
     }
     .navbar-toggle {
       min-width: 44px;
       min-height: 44px;
       margin-top: 8px;
       margin-bottom: 8px;
     }
     @media (max-width: 767px) {
       /* DOM order is toggle then brand; reverse so logo stays left, hamburger right */
       .famledger-landing .custom-navbar .navbar-header {
         display: flex;
         flex-direction: row-reverse;
         align-items: center;
         justify-content: space-between;
         float: none;
         width: 100%;
         margin-left: 0;
         margin-right: 0;
       }
       .famledger-landing .custom-navbar .navbar-header .navbar-brand,
       .famledger-landing .custom-navbar .navbar-header .navbar-toggle {
         float: none;
       }
       .famledger-landing .custom-navbar .navbar-brand {
         padding-left: 0;
         margin-left: 0;
         flex: 1;
         min-width: 0;
         max-width: none;
         text-align: left;
       }
       .famledger-landing .custom-navbar .navbar-toggle {
         margin-right: 0;
         flex-shrink: 0;
       }
     }
     @media (max-width: 991px) {
       #testimonial > .container > .row {
         display: flex;
         flex-wrap: wrap;
       }
       #testimonial .famledger-testimonial-copy {
         order: 1;
         flex: 0 0 100%;
         max-width: 100%;
       }
       #testimonial .famledger-testimonial-visual {
         order: 2;
         flex: 0 0 100%;
         max-width: 100%;
       }
     }
     @media (max-width: 767px) {
       #about.famledger-about .famledger-about-card__media {
         height: clamp(180px, 48vw, 260px);
       }
       #about.famledger-about .famledger-about-intro {
         margin-bottom: 32px;
       }
       #about.famledger-about .famledger-about-intro-panel {
         border-radius: 12px;
         padding: 1.4rem 1.15rem;
         max-width: 100%;
       }
       #about.famledger-about .famledger-about-intro h1 {
         font-size: clamp(1.5rem, 0.35rem + 4.2vw, 1.95rem);
         font-weight: 700;
         letter-spacing: -0.022em;
         line-height: 1.18;
         margin-bottom: 1rem;
       }
       #about.famledger-about .famledger-about-kicker {
         font-size: clamp(0.8125rem, 0.2rem + 2.5vw, 0.875rem);
         letter-spacing: 0.065em;
         margin-bottom: 0.65rem;
       }
       #about.famledger-about .famledger-about-lead {
         font-size: clamp(1.0625rem, 0.88rem + 1.1vw, 1.25rem);
         line-height: 1.78;
         font-weight: 400;
         color: #334155;
       }
       #about.famledger-about .famledger-about-card {
         border-left: 3px solid #009ef7;
       }
       #about.famledger-about .famledger-about-card__body {
         padding: 20px 18px 24px;
       }
       #about.famledger-about .famledger-about-card__body h2 {
         font-size: clamp(1.28rem, 0.55rem + 3.2vw, 1.48rem);
         font-weight: 700;
         letter-spacing: -0.018em;
         line-height: 1.25;
       }
       #about.famledger-about .famledger-about-card__role {
         font-size: clamp(0.8125rem, 0.2rem + 2.2vw, 0.875rem);
         letter-spacing: 0.055em;
         margin-bottom: 12px;
       }
       #about.famledger-about .famledger-about-card__text {
         font-size: clamp(1.0625rem, 0.92rem + 0.85vw, 1.125rem);
         line-height: 1.78;
         color: #334155;
       }
       #about.famledger-about .famledger-about-pillars > [class*="col-"] {
         margin-bottom: 20px;
       }
       .navbar-brand {
         max-width: calc(100% - 56px);
         white-space: normal;
         line-height: 1.2;
         padding-top: 12px;
         padding-bottom: 12px;
         height: auto;
       }
       .navbar-nav > li > a {
         padding-top: 12px;
         padding-bottom: 12px;
         line-height: 1.35;
       }
       #feature .famledger-feature-head-panel {
         border-radius: 12px;
         padding: 1.35rem 1.15rem;
         padding-top: calc(1.35rem + 2px);
       }
       #feature .famledger-feature-head-panel::before {
         border-radius: 11px 11px 0 0;
       }
       #feature .famledger-feature-head-main h1 {
         font-size: clamp(1.35rem, 4.8vw + 0.4rem, 2.5rem);
       }
       #feature .famledger-feature-head-copy p {
         font-size: clamp(0.98rem, 3.2vw + 0.35rem, 1.22rem);
         line-height: 1.65;
       }
       #feature .famledger-feature-kicker {
         font-size: 0.8125rem;
         margin-bottom: 0.75rem;
       }
       .famledger-feature-tabs > li > a {
         font-size: 12px;
         padding: 8px 9px;
       }
       .hero-cta,
       .section-btn.hero-cta,
       .section-btn.pricing-btn {
         min-height: 44px;
         display: inline-flex;
         align-items: center;
         justify-content: center;
       }
       #home .home-info {
         text-align: center;
       }
       .famledger-hero-cta-row {
         justify-content: center;
       }
       .famledger-contact-success-banner {
         left: max(8px, env(safe-area-inset-left, 0px));
         right: max(8px, env(safe-area-inset-right, 0px));
         max-width: none;
         top: max(12px, env(safe-area-inset-top, 0px));
       }
       #landingContactModal .modal-dialog {
         margin: 10px auto;
         width: auto;
         max-width: calc(100vw - 20px);
       }
       #landingContactModal .modal-header,
       #landingContactModal .modal-body,
       #landingContactModal .modal-footer {
         padding-left: 14px;
         padding-right: 14px;
       }
       #landingContactModal .modal-footer {
         flex-direction: column;
         align-items: stretch;
       }
       #landingContactModal .modal-footer .section-btn {
         width: 100%;
         justify-content: center;
       }
     }
     @media (max-width: 480px) {
       .famledger-feature-tabs {
         display: flex;
         flex-wrap: wrap;
         width: 100%;
       }
       .famledger-feature-tabs > li {
         float: none !important;
         flex: 1 1 auto;
         min-width: 30%;
         text-align: center;
       }
       .famledger-feature-tabs > li > a {
         font-size: 11px;
         padding: 8px 6px;
         margin-right: 0 !important;
       }
     }
     @media (max-width: 380px) {
       .famledger-feature-tabs > li {
         flex: 1 1 100%;
         min-width: 100%;
       }
       .famledger-feature-tabs > li > a {
         font-size: 12px;
         padding: 10px 12px;
       }
     }

    </style>

</head>
<body class="famledger-landing">

     @if (session()->has('contact_success_toast'))
     <div id="famledger-contact-success-banner" class="famledger-contact-success-banner" role="status" aria-live="polite">
          <span>{{ session('contact_success_toast') }}</span>
          <button type="button" class="famledger-contact-success-dismiss">{{ __('Great, thanks') }}</button>
     </div>
     @endif

     <!-- PRE LOADER -->
     <section class="preloader">
          <div class="spinner">

               <span class="spinner-rotate"></span>
               
            </div>
     </section>


     <!-- MENU -->
     <section class="navbar custom-navbar navbar-fixed-top" role="navigation">
          <div class="container">

               <div class="navbar-header">
                    <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                         <span class="icon icon-bar"></span>
                         <span class="icon icon-bar"></span>
                         <span class="icon icon-bar"></span>
                    </button>

                    <!-- LOGO + SYSTEM NAME -->
                    <a href="#home" class="navbar-brand">
                         <img src="{{ asset('images/logo.png') }}" alt="FamLedger logo" style="height:32px; margin-right:8px; display:inline-block; vertical-align:middle;">
                         <span style="color:#009EF7; font-weight:700;">FamLedger</span>
                </a>
            </div>

               <!-- MENU LINKS -->
               <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                         <li><a href="#home" class="smoothScroll">Home</a></li>
                         <li><a href="#feature" class="smoothScroll">Accounting &amp; features</a></li>
                         <li><a href="#about" class="smoothScroll">About us</a></li>
                         <li><a href="#faq" class="smoothScroll">FAQ</a></li>
                         {{-- Pricing hidden for now --}}
                         <li><a href="#contact" class="smoothScroll">Contact</a></li>
                    </ul>
               </div>

        </div>
     </section>

    <!-- HOME -->
    <section id="home" data-stellar-background-ratio="0.5" style="background-image: url('{{ asset('images/background.png') }}'); background-size: cover; background-position: center;">
         <div class="overlay"></div>
          <div class="container">
               <div class="row famledger-hero-row">

                   <div class="col-xs-12 col-sm-12 col-md-6 famledger-hero-marketing">
                         <div class="home-info">
                              <h3>{{ __('Family accounting & private ledger') }}</h3>
                              <h2 style="font-weight:700; line-height:1.25;">
                                   <span id="typing-text" style="color:#009EF7;"></span>
                                   <span class="border-r-2 ml-1 animate-blink" style="border-color:#009EF7;">|</span>
                              </h2>
                              <div class="famledger-hero-cta-row" style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap;">
                                   <a href="{{ route('register') }}" class="section-btn hero-cta">{{ __('Start family accounting') }}</a>
                                   <a href="{{ route('login') }}" class="section-btn hero-cta hero-cta-secondary">{{ __('Sign in') }}</a>
                              </div>
                         </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-6 famledger-hero-visual">
                         <div class="famledger-hero-visual-card">
                              <div class="famledger-hero-pulse-img-wrap">
                                   <img src="{{ asset('images/hero-wealth-growth.png') }}" class="img-responsive" alt="{{ __('Wealth growing over time — savings and investment concept') }}" width="800" height="450" loading="eager" fetchpriority="high" decoding="async">
                              </div>
                         </div>
                    </div>

               </div>
                        </div>
     </section>


     <!-- FEATURE -->
     <section id="feature" data-stellar-background-ratio="0.5">
          <div class="container">
               <div class="row">

                    <div class="col-md-12 col-sm-12 scroll-reveal famledger-feature-head-wrap">
                         <div class="section-title">
                              <div id="famledger-feature-head-panel" class="famledger-feature-head-panel" aria-labelledby="feature-heading">
                                   <p class="famledger-feature-kicker famledger-feature-reveal-line">{{ __('What you get') }}</p>
                                   <div class="famledger-feature-head-grid">
                                        <div class="famledger-feature-reveal-line famledger-feature-head-main">
                                             <h1 id="feature-heading">{{ __('Accounting your family can trust') }}</h1>
                                        </div>
                                        <div class="famledger-feature-reveal-line famledger-feature-head-copy">
                                             <p>{{ __('FamLedger is built around a simple idea: treat household and family-office money with the same discipline as a small business—without losing the flexibility families need.') }}</p>
                                        </div>
                                   </div>
                              </div>
                        </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-6 scroll-reveal scroll-reveal-delay-1">
                         <ul class="nav nav-tabs famledger-feature-tabs" role="tablist">
                              <li class="active"><a href="#tab01" aria-controls="tab01" role="tab" data-toggle="tab">{{ __('Books & cash') }}</a></li>

                              <li><a href="#tab02" aria-controls="tab02" role="tab" data-toggle="tab">{{ __('Assets & projects') }}</a></li>

                              <li><a href="#tab03" aria-controls="tab03" role="tab" data-toggle="tab">{{ __('People & control') }}</a></li>
                         </ul>

                         <div class="tab-content">
                              <div class="tab-pane active" id="tab01" role="tabpanel">
                                   <div class="tab-pane-item">
                                        <h2>{{ __('Wallet-based ledger') }}</h2>
                                        <p>{{ __('Record income and expenses against real wallets—bank accounts, mobile money, petty cash—so balances reconcile to life, not to a single vague “household” bucket.') }}</p>
                                   </div>
                                   <div class="tab-pane-item">
                                        <h2>{{ __('Reports for decisions (and advisors)') }}</h2>
                                        <p>{{ __('Income, expense, cash-flow, and budget views by category, project, and period give you numbers you can explain to co-owners, accountants, or trustees in one sitting.') }}</p>
                                   </div>
                              </div>


                              <div class="tab-pane" id="tab02" role="tabpanel">
                                   <div class="tab-pane-item">
                                        <h2>{{ __('Properties on the same books') }}</h2>
                                        <p>{{ __('Plots, homes, vehicles, and other assets live in the ledger with ownership, status, valuation history, and linked costs—so wealth isn’t scattered across spreadsheets.') }}</p>
                                   </div>
                                   <div class="tab-pane-item">
                                        <h2>{{ __('Projects with budgets and actuals') }}</h2>
                                        <p>{{ __('Plan funding, track spending against budget for construction, education, or investments, and carry completed work into long-term assets when you’re ready.') }}</p>
                                   </div>
                                   <div class="tab-pane-item">
                                        <h2>{{ __('Documents where they belong') }}</h2>
                                        <p>{{ __('Attach invoices, deeds, and agreements to the right property or project instead of losing them in chat threads and inboxes.') }}</p>
                </div>
                        </div>

                              <div class="tab-pane" id="tab03" role="tabpanel">
                                   <div class="tab-pane-item">
                                        <h2>{{ __('Roles that match real families') }}</h2>
                                        <p>{{ __('Owners and co-owners keep full control; other members or staff can get read-only or limited access—so helpers can support you without seeing everything.') }}</p>
                            </div>
                                   <div class="tab-pane-item">
                                        <h2>{{ __('Permissions and audit trail') }}</h2>
                                        <p>{{ __('Fine-grained permissions, confirmations where it matters, and a clear trail of who changed what—so family accounting stays intentional and defensible.') }}</p>
                            </div>
                            </div>
                        </div>

                            </div>

                    <div class="col-xs-12 col-sm-12 col-md-6 scroll-reveal scroll-reveal-delay-2">
                         <div class="feature-image">
                              <img src="{{ asset('images/feature-mockup.png') }}" class="img-responsive" alt="{{ __('FamLedger family accounting workspace') }}" loading="eager" decoding="async">
                        </div>
                    </div>

                </div>
            </div>
        </section>


     <!-- ABOUT -->
     <section id="about" class="famledger-about" data-stellar-background-ratio="0.5" aria-labelledby="about-heading">
          <div class="container">
               <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 scroll-reveal famledger-about-intro">
                         <div class="section-title">
                              <div id="famledger-about-intro-panel" class="famledger-about-intro-panel">
                                   <p class="famledger-about-kicker">{{ __('Why we exist') }}</p>
                                   <h1 id="about-heading">{{ __('About FamLedger') }}</h1>
                                   <p class="famledger-about-lead">
                                        {{ __('Families run serious money; school fees, construction, rentals, investments often split across spreadsheets, mobile money, inboxes, and group chats. That fragmentation makes it hard to see the full picture or agree on the numbers.') }}
                                   </p>
                                   <p class="famledger-about-lead">
                                        {{ __('FamLedger gives you one private ledger: wallet-level accounting, assets and projects on the same books, and reports you can trust in family meetings or with an advisor without enterprise complexity or bloat.') }}
                                   </p>
                              </div>
                         </div>
                    </div>
               </div>

               <div class="row famledger-about-pillars">
                    <div class="col-xs-12 col-sm-12 col-md-4 scroll-reveal scroll-reveal-delay-1">
                         <article class="famledger-about-card">
                              <div class="famledger-about-card__media">
                                   <img src="{{ asset('images/founder.jpg') }}" class="img-responsive" alt="{{ __('Family planning finances together') }}" decoding="async">
                              </div>
                              <div class="famledger-about-card__body">
                                   <h2>{{ __('Grounded in real households') }}</h2>
                                   <p class="famledger-about-card__role">{{ __('Families first') }}</p>
                                   <p class="famledger-about-card__text">{{ __('The product grew from families who needed one place for wallets, projects, and property, so every contribution and every asset has a clear trail, not a lost spreadsheet or chat thread.') }}</p>
                              </div>
                         </article>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-4 scroll-reveal scroll-reveal-delay-2">
                         <article class="famledger-about-card">
                              <div class="famledger-about-card__media">
                                   <img src="{{ asset('images/testimonial-image.jpg') }}" class="img-responsive" alt="{{ __('Working with advisors and accountants') }}" decoding="async">
                              </div>
                              <div class="famledger-about-card__body">
                                   <h2>{{ __('Validated by professionals') }}</h2>
                                   <p class="famledger-about-card__role">{{ __('Advisors & accountants') }}</p>
                                   <p class="famledger-about-card__text">{{ __('Accountants and family, office style advisors helped stress-test categories, balances, and reports; so the numbers work in real reviews, not only on a marketing slide.') }}</p>
                              </div>
                         </article>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-4 scroll-reveal scroll-reveal-delay-3">
                         <article class="famledger-about-card">
                              <div class="famledger-about-card__media">
                                   <img src="{{ asset('images/team-image3.jpg') }}" class="img-responsive" alt="{{ __('FamLedger product and engineering team') }}" decoding="async">
                              </div>
                              <div class="famledger-about-card__body">
                                   <h2>{{ __('Built for the long term') }}</h2>
                                   <p class="famledger-about-card__role">{{ __('Product & engineering') }}</p>
                                   <p class="famledger-about-card__text">{{ __('We prioritize privacy, disciplined accounting, and a system your family can rely on for years iterating with feedback while keeping the core ledger trustworthy.') }}</p>
                              </div>
                         </article>
                    </div>
               </div>
          </div>
     </section>


     <!-- TESTIMONIAL -->
     <section id="testimonial" data-stellar-background-ratio="0.5">
          <div class="container">
               <div class="row">

                    <div class="col-xs-12 col-md-6 col-sm-12 scroll-reveal famledger-testimonial-visual">
                         <div class="testimonial-image"></div>
                    </div>

                    <div class="col-xs-12 col-md-6 col-sm-12 scroll-reveal scroll-reveal-delay-1 famledger-testimonial-copy">
                         <div class="testimonial-info">
                              
                              <div class="section-title">
                                   <h1>{{ __('What families say about the ledger') }}</h1>
                              </div>

                              <div class="owl-carousel owl-theme">
                                   <div class="item">
                                        <h3>{{ __('Our wallets, projects, and rentals finally sit in one accounting view. When we meet, we talk from the same numbers, not from three different notebooks.') }}</h3>
                                        <div class="testimonial-item">
                                             <img src="{{ asset('images/tst-image1.jpg') }}" class="img-responsive" alt="Michael" decoding="async">
                                             <h4>{{ __('Family owner – Dar es Salaam') }}</h4>
                                        </div>
                                   </div>

                                   <div class="item">
                                        <h3>{{ __('We can show who funded each project and how actual spend compares to budget. That alone changed how we plan the next build.') }}</h3>
                                        <div class="testimonial-item">
                                             <img src="{{ asset('images/tst-image2.jpg') }}" class="img-responsive" alt="Sofia" decoding="async">
                                             <h4>{{ __('Co-owner – Arusha') }}</h4>
                                        </div>
                                   </div>

                                   <div class="item">
                                        <h3>{{ __('As an advisor, I care that categories and reports are consistent. FamLedger gives families structure without turning them into accountants.') }}</h3>
                                        <div class="testimonial-item">
                                             <img src="{{ asset('images/tst-image3.jpg') }}" class="img-responsive" alt="Monica" decoding="async">
                                             <h4>{{ __('Family office advisor') }}</h4>
                                        </div>
                                   </div>
                              </div>

                        </div>
                    </div>
                    
                </div>
            </div>
        </section>


    <!-- PRICING (temporarily hidden) -->
    <section id="pricing" data-stellar-background-ratio="0.5" style="display:none;">
          <div class="container">
               <div class="row">

                    <div class="col-md-12 col-sm-12">
                         <div class="section-title">
                              <h1>Simple FamLedger pricing</h1>
                         </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                         <div class="pricing-thumb">
                             <div class="pricing-title">
                                  <h2>Starter Family</h2>
                             </div>
                             <div class="pricing-info">
                                   <p>Up to 3 family members</p>
                                   <p>Wallets & basic reports</p>
                                   <p>Property and projects tracking</p>
                                   <p>Email support</p>
                             </div>
                             <div class="pricing-bottom">
                                   <span class="pricing-dollar">$9/mo</span>
                                   <a href="{{ route('register') }}" class="section-btn pricing-btn">Start with Starter</a>
                             </div>
                         </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                         <div class="pricing-thumb">
                             <div class="pricing-title">
                                  <h2>Growing Family</h2>
                             </div>
                             <div class="pricing-info">
                                   <p>Up to 10 family members</p>
                                   <p>Advanced reports & exports</p>
                                   <p>Property maintenance & documents</p>
                                   <p>Priority support</p>
                             </div>
                             <div class="pricing-bottom">
                                   <span class="pricing-dollar">$19/mo</span>
                                   <a href="{{ route('register') }}" class="section-btn pricing-btn">Choose Growing</a>
                    </div>
                </div>
                        </div>

                    <div class="col-md-4 col-sm-6">
                         <div class="pricing-thumb">
                             <div class="pricing-title">
                                  <h2>Family Office</h2>
                            </div>
                             <div class="pricing-info">
                                   <p>Unlimited members & families</p>
                                   <p>Custom roles and permissions</p>
                                   <p>Dedicated success manager</p>
                                   <p>Audit‑ready reporting</p>
                            </div>
                             <div class="pricing-bottom">
                                   <span class="pricing-dollar">Talk to us</span>
                                   <a href="#contact" class="section-btn pricing-btn">Book a demo</a>
                            </div>
                        </div>
                    </div>
                    
               </div>
          </div>
     </section>   


     <!-- FAQ -->
     <section id="faq" data-stellar-background-ratio="0.5">
          <div class="container">
               <div class="row">
                    <div class="col-md-12 col-sm-12 scroll-reveal">
                         <div class="section-title landing-faq-section-heading">
                              <h1>Frequently Asked Questions</h1>
                         </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-12 scroll-reveal scroll-reveal-delay-1">
                         @if ($landingFaqs->isEmpty())
                              <p class="text-center" style="padding: 24px 12px; color: #666; line-height: 1.6;">
                                   {{ __('No frequently asked questions are published yet. Please check back later, or reach out via the contact section below.') }}
                              </p>
                         @else
                              <div class="row landing-faq-layout">
                                   <aside class="col-xs-12 col-md-3 landing-faq-sidebar" aria-label="{{ __('FAQ topic navigation') }}">
                                        <div class="landing-faq-sidebar-inner">
                                             <nav class="landing-faq-nav">
                                                  <p class="landing-faq-nav-title">{{ __('FAQ') }}</p>
                                                  <ul>
                                                       @foreach ($landingFaqGroups as $groupKey => $_groupFaqs)
                                                            @php
                                                                 $navLabel = $groupKey !== '' ? $groupKey : __('General');
                                                                 $navAnchor = 'faq-'.$loop->iteration;
                                                            @endphp
                                                            <li>
                                                                 <a
                                                                      href="#{{ $navAnchor }}"
                                                                      class="landing-faq-tab"
                                                                      data-landing-faq-target="{{ $navAnchor }}"
                                                                      aria-controls="{{ $navAnchor }}"
                                                                 >{{ $navLabel }}</a>
                                                            </li>
                                                       @endforeach
                                                  </ul>
                                             </nav>
                                        </div>
                                   </aside>
                                   <div class="col-xs-12 col-md-9 landing-faq-main">
                                        <div class="panel-group" id="faq-accordion" role="tablist" aria-multiselectable="true">
                                             @php $landingFaqFirstOpen = true; @endphp
                                             @foreach ($landingFaqGroups as $groupKey => $groupFaqs)
                                                  @php
                                                       $groupTitle = $groupKey !== '' ? $groupKey : __('General');
                                                       $groupAnchor = 'faq-'.$loop->iteration;
                                                  @endphp
                                                  <div
                                                       id="{{ $groupAnchor }}"
                                                       class="landing-faq-group-block"
                                                       data-landing-faq-group
                                                  >
                                                       <h2 class="landing-faq-group-title">{{ $groupTitle }}</h2>
                                                       @foreach ($groupFaqs as $faq)
                                                            @php
                                                                 $openThis = $landingFaqFirstOpen;
                                                                 $landingFaqFirstOpen = false;
                                                            @endphp
                                                            <div class="panel panel-default">
                                                                 <div class="panel-heading" role="tab" id="faqHeading{{ $faq->id }}">
                                                                      <h4 class="panel-title">
                                                                           <a
                                                                                role="button"
                                                                                data-toggle="collapse"
                                                                                data-parent="#faq-accordion"
                                                                                href="#faqCollapse{{ $faq->id }}"
                                                                                class="{{ $openThis ? '' : 'collapsed' }}"
                                                                                aria-expanded="{{ $openThis ? 'true' : 'false' }}"
                                                                                aria-controls="faqCollapse{{ $faq->id }}"
                                                                           >
                                                                                <span class="landing-faq-q">{!! Purify::config('notification_faq')->clean($faq->question) !!}</span>
                                                                           </a>
                                                                      </h4>
                                                                 </div>
                                                                 <div
                                                                      id="faqCollapse{{ $faq->id }}"
                                                                      class="panel-collapse collapse{{ $openThis ? ' in' : '' }}"
                                                                      role="tabpanel"
                                                                      aria-labelledby="faqHeading{{ $faq->id }}"
                                                                 >
                                                                      <div class="panel-body landing-faq-a">
                                                                           {!! Purify::config('notification_faq')->clean($faq->answer) !!}
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       @endforeach
                                                  </div>
                                             @endforeach
                                        </div>
                                   </div>
                              </div>
                         @endif
                    </div>
               </div>
          </div>
     </section>


     <!-- CONTACT (single area: hero + optional support cards; form only in modal) -->
     <section id="contact" data-stellar-background-ratio="0.5">
          <div class="container">
               <div class="row">

                    <div class="col-xs-12 col-sm-12 col-md-offset-1 col-md-10 scroll-reveal">
                         <div class="section-title landing-contact-hero text-center">
                              <h1>{{ __('Talk to us about family accounting') }}</h1>
                              <h2 style="font-weight:600; line-height:1.4; margin-top:0.4rem; font-size: clamp(1rem, 2.5vw, 1.35rem);">
                                   <span id="contact-typing-text" style="color:#009EF7;"></span>
                                   <span class="border-r-2 ml-1 animate-blink" style="border-color:#009EF7;">|</span>
                              </h2>
                         </div>

                         @if ($landingSupportContacts->isNotEmpty())
                              <div class="landing-support-options scroll-reveal scroll-reveal-delay-1" style="margin-top: 24px;">
                                   @foreach ($landingSupportContacts as $supportContact)
                                        @php
                                             $cUrl = $supportContact->link_url ? trim((string) $supportContact->link_url) : '';
                                             $cFragment = '';
                                             if ($cUrl !== '') {
                                                 if (str_starts_with($cUrl, '#')) {
                                                     $cFragment = ltrim($cUrl, '#');
                                                 } else {
                                                     $cFragment = (string) (parse_url($cUrl, PHP_URL_FRAGMENT) ?? '');
                                                 }
                                             }
                                             $supportLinkOpensModal = ($cFragment === 'contact');
                                             $cOpenNew = $cUrl !== '' && ! $supportLinkOpensModal && \Illuminate\Support\Str::startsWith($cUrl, ['http://', 'https://', 'mailto:']);
                                        @endphp
                                        <div class="panel panel-default landing-support-card">
                                             <div class="panel-body">
                                                  <h3>{{ $supportContact->title }}</h3>
                                                  <div class="landing-support-body">
                                                       {!! Purify::config('notification_faq')->clean($supportContact->body) !!}
                                                  </div>
                                                  @if ($cUrl !== '')
                                                       <div class="landing-support-link">
                                                            @if ($supportLinkOpensModal)
                                                                 <button
                                                                      type="button"
                                                                      class="section-btn hero-cta landing-support-modal-btn"
                                                                      style="display: inline-block;"
                                                                      data-toggle="modal"
                                                                      data-target="#landingContactModal"
                                                                 >
                                                                      {{ $supportContact->link_label ?: __('Contact support') }}
                                                                 </button>
                                                                 <p class="landing-support-modal-hint">{{ __('Click to Open a Contact form.') }}</p>
                                                            @else
                                                                 <a
                                                                      href="{{ $cUrl }}"
                                                                      class="section-btn hero-cta"
                                                                      style="display: inline-block;"
                                                                      @if ($cOpenNew) target="_blank" rel="noopener noreferrer" @endif
                                                                 >
                                                                      {{ $supportContact->link_label ?: __('Open link') }}
                                                                 </a>
                                                                 <a
                                                                      href="{{ $cUrl }}"
                                                                      class="landing-support-link-url"
                                                                      @if ($cOpenNew) target="_blank" rel="noopener noreferrer" @endif
                                                                 >
                                                                      {{ $cUrl }}
                                                                 </a>
                                                            @endif
                                                       </div>
                                                  @endif
                                             </div>
                                        </div>
                                   @endforeach
                              </div>
                         @else
                              <div class="text-center scroll-reveal scroll-reveal-delay-1" style="padding: 12px 12px 48px;">
                                   <p style="max-width: 560px; margin: 0 auto 24px; color: #666; line-height: 1.65;">
                                        {{ __('Questions about pricing, onboarding, or support? Tap below to send us a message—we’ll get back to you soon.') }}
                                   </p>
                                   <button type="button" class="section-btn hero-cta landing-support-modal-btn" data-toggle="modal" data-target="#landingContactModal">
                                        {{ __('Contact us') }}
                                   </button>
                                  <p class="landing-support-modal-hint">{{ __('Click to Opens a contact form.') }}</p>
                              </div>
                         @endif

     
                    </div>

                </div>
            </div>
        </section>

     <x-contact-form-modal
         :captcha-driver="$contactCaptchaDriver"
         :recaptcha-site-key="$recaptchaSiteKey"
         :open-on-load="$errors->any() && old('_contact_form_source') === 'modal'"
     />

     <!-- FOOTER -->
     <footer id="footer" role="contentinfo" data-stellar-background-ratio="0.5">
          <div class="container">
               <div class="row famledger-footer-row">
                    <div class="col-xs-12 col-md-6 famledger-footer-copy">
                         <p class="famledger-footer-tagline">{{ now()->year }} &copy; <span class="famledger-footer-brand">FamLedger</span> &middot; {{ __('Family accounting & private wealth ledger') }}</p>
                    </div>
                    <div class="col-xs-12 col-md-6 famledger-footer-social">
                         <ul class="social-icon famledger-footer-social-list" aria-label="{{ __('Social media links') }}">
                              <li><a href="#" class="fa fa-facebook-square" aria-label="{{ __('Facebook') }}" rel="noopener noreferrer"></a></li>
                              <li><a href="#" class="fa fa-twitter" aria-label="{{ __('Twitter') }}" rel="noopener noreferrer"></a></li>
                              <li><a href="#" class="fa fa-instagram" aria-label="{{ __('Instagram') }}" rel="noopener noreferrer"></a></li>
                         </ul>
                    </div>
               </div>
          </div>
     </footer>


     <!-- SCRIPTS (defer = non-blocking load, execution order preserved) -->
     <script src="{{ asset('metronic/assets/js/jquery.js') }}" defer></script>
     <script src="{{ asset('metronic/assets/js/bootstrap.min.js') }}" defer></script>
     <script src="{{ asset('metronic/assets/js/jquery.stellar.min.js') }}" defer></script>
     <script src="{{ asset('metronic/assets/js/owl.carousel.min.js') }}" defer></script>
     <script src="{{ asset('metronic/assets/js/smoothscroll.js') }}" defer></script>
     <script src="{{ asset('metronic/assets/js/custom.js') }}" defer></script>
     <script src="{{ asset('vendor/quill/quill.min.js') }}" defer></script>
     <script src="{{ asset('js/famledger-contact-form-modal.js') }}" defer></script>

     @if (filled($recaptchaSiteKey))
     <script>
      window.__recaptchaSiteKey = @json($recaptchaSiteKey);
     </script>
     <script src="https://www.google.com/recaptcha/api.js" async defer></script>
     @endif

     <script>
      document.addEventListener('DOMContentLoaded', function () {
       function famledgerClearBootstrapModalArtifacts() {
        try {
         if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
          window.jQuery('.modal').modal('hide');
          window.jQuery('.modal-backdrop').remove();
          window.jQuery('body').removeClass('modal-open').css('padding-right', '');
         } else {
          document.querySelectorAll('.modal-backdrop').forEach(function (n) {
           n.parentNode && n.parentNode.removeChild(n);
          });
          document.body.classList.remove('modal-open');
          document.body.style.paddingRight = '';
         }
        } catch (e) {}
       }
       function famledgerClearModalBackdropsOnly() {
        try {
         if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
          window.jQuery('.modal-backdrop').remove();
          window.jQuery('body').removeClass('modal-open').css('padding-right', '');
         } else {
          document.querySelectorAll('.modal-backdrop').forEach(function (n) {
           n.parentNode && n.parentNode.removeChild(n);
          });
          document.body.classList.remove('modal-open');
          document.body.style.paddingRight = '';
         }
        } catch (e) {}
       }
       @if ($errors->any() && old('_contact_form_source') === 'modal')
        famledgerClearModalBackdropsOnly();
       @else
        famledgerClearBootstrapModalArtifacts();
       @endif
       var banner = document.getElementById('famledger-contact-success-banner');
       if (banner) {
        var dismiss = banner.querySelector('.famledger-contact-success-dismiss');
        var remove = function () {
         if (banner && banner.parentNode) {
          banner.parentNode.removeChild(banner);
         }
         famledgerClearBootstrapModalArtifacts();
        };
        if (dismiss) {
         dismiss.addEventListener('click', remove);
        }
        window.setTimeout(remove, 9000);
       }
      });
     </script>

     @php
      $famledgerLandingTypingTexts = [
          __('One private ledger for family accounting: wallets, budgets, properties, and projects—with reports you can trust.'),
          __('Stop reconciling family money from memory, SMS, and scattered spreadsheets.'),
          __('Income and expenses by wallet. Assets and debt in the same books. Clear ownership and history.'),
          __('Built for families who outgrew “just track spending” and need real household accounting.'),
          __('From daily cash to generational assets—FamLedger keeps the full picture in one disciplined place.'),
      ];
      $famledgerContactTypingTexts = [
          __('booking a demo for your family\'s accounting setup'),
          __('questions about wallets, reports, and onboarding'),
          __('partnering as an accountant or family office'),
          __('support for your existing FamLedger account'),
      ];
     @endphp
     <script>
    const texts = @json($famledgerLandingTypingTexts);

     let speed = 60;
     let eraseSpeed = 40;
     let delayBetween = 2000;

     let textIndex = 0;
     let charIndex = 0;
     let isDeleting = false;

     const typingElement = document.getElementById("typing-text");

     function typeEffect() {
       if (!typingElement) return;

       const currentText = texts[textIndex];

       if (!isDeleting) {
         typingElement.textContent = currentText.substring(0, charIndex + 1);
         charIndex++;

         if (charIndex === currentText.length) {
           setTimeout(() => { isDeleting = true; }, delayBetween);
         }
       } else {
         typingElement.textContent = currentText.substring(0, charIndex - 1);
         charIndex--;

         if (charIndex === 0) {
           isDeleting = false;
           textIndex = (textIndex + 1) % texts.length;
         }
       }

       setTimeout(typeEffect, isDeleting ? eraseSpeed : speed);
     }

     // Contact section typing
     const contactTexts = @json($famledgerContactTypingTexts);

     let contactSpeed = 60;
     let contactEraseSpeed = 40;
     let contactDelayBetween = 2000;
     let contactTextIndex = 0;
     let contactCharIndex = 0;
     let contactIsDeleting = false;
     const contactTypingElement = document.getElementById("contact-typing-text");

     function contactTypeEffect() {
       if (!contactTypingElement) return;

       const currentText = contactTexts[contactTextIndex];

       if (!contactIsDeleting) {
         contactTypingElement.textContent = currentText.substring(0, contactCharIndex + 1);
         contactCharIndex++;

         if (contactCharIndex === currentText.length) {
           setTimeout(() => { contactIsDeleting = true; }, contactDelayBetween);
         }
       } else {
         contactTypingElement.textContent = currentText.substring(0, contactCharIndex - 1);
         contactCharIndex--;

         if (contactCharIndex === 0) {
           contactIsDeleting = false;
           contactTextIndex = (contactTextIndex + 1) % contactTexts.length;
         }
       }

       setTimeout(contactTypeEffect, contactIsDeleting ? contactEraseSpeed : contactSpeed);
     }

     document.addEventListener("DOMContentLoaded", function() {
       typeEffect();
       contactTypeEffect();

       // Scroll reveal: add .scroll-reveal-visible when element enters viewport
       var revealEls = document.querySelectorAll(".scroll-reveal");
       if (revealEls.length && "IntersectionObserver" in window) {
         var observer = new IntersectionObserver(function(entries) {
           entries.forEach(function(entry) {
             if (entry.isIntersecting) {
               entry.target.classList.add("scroll-reveal-visible");
             }
           });
         }, { rootMargin: "0px 0px -60px 0px", threshold: 0.05 });
         revealEls.forEach(function(el) { observer.observe(el); });
       } else {
         revealEls.forEach(function(el) { el.classList.add("scroll-reveal-visible"); });
       }

       var aboutIntroPanel = document.getElementById("famledger-about-intro-panel");
       if (aboutIntroPanel) {
         if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
           aboutIntroPanel.classList.add("famledger-about-panel--visible");
         } else if ("IntersectionObserver" in window) {
           var aboutPanelObs = new IntersectionObserver(function(entries) {
             entries.forEach(function(entry) {
               if (entry.isIntersecting) {
                 entry.target.classList.add("famledger-about-panel--visible");
                 aboutPanelObs.unobserve(entry.target);
               }
             });
           }, { rootMargin: "0px 0px -10% 0px", threshold: 0.12 });
           aboutPanelObs.observe(aboutIntroPanel);
         } else {
           aboutIntroPanel.classList.add("famledger-about-panel--visible");
         }
       }

       var featureHeadPanel = document.getElementById("famledger-feature-head-panel");
       if (featureHeadPanel) {
         if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
           featureHeadPanel.classList.add("famledger-feature-head-panel--visible");
         } else if ("IntersectionObserver" in window) {
           var featureHeadObs = new IntersectionObserver(function(entries) {
             entries.forEach(function(entry) {
               if (entry.isIntersecting) {
                 entry.target.classList.add("famledger-feature-head-panel--visible");
                 featureHeadObs.unobserve(entry.target);
               }
             });
           }, { rootMargin: "0px 0px -10% 0px", threshold: 0.1 });
           featureHeadObs.observe(featureHeadPanel);
         } else {
           featureHeadPanel.classList.add("famledger-feature-head-panel--visible");
         }
       }

       /* Main nav / logo: localScroll prevents default and does not update the URL by default,
          so the hash could stay e.g. #faq-4 after switching sections. Sync hash here. */
       (function () {
         function landingPathNoHash() {
           return window.location.pathname + window.location.search;
         }
         document.addEventListener(
           "click",
           function (e) {
             var a = e.target.closest && e.target.closest('a[href^="#"]');
             if (!a) return;
             if (!a.classList.contains("smoothScroll") && !a.classList.contains("navbar-brand")) {
               return;
             }
             var href = a.getAttribute("href") || "";
             if (href.length < 2 || href === "#") return;
             if (window.history && window.history.replaceState) {
               window.history.replaceState(null, "", landingPathNoHash() + href);
             }
             window.dispatchEvent(new Event("hashchange"));
           },
           true
         );
       })();

       /* FAQ: show one topic group at a time (sidebar); no-op when only one group */
       (function () {
         var faqLayout = document.querySelector("#faq .landing-faq-layout");
         if (!faqLayout) return;
         var groupBlocks = faqLayout.querySelectorAll("[data-landing-faq-group]");
         if (groupBlocks.length <= 1) return;

         function targetIdFromHash() {
           var raw = window.location.hash.replace(/^#/, "");
           if (!raw) return null;
           var el = document.getElementById(raw);
           if (el && faqLayout.contains(el) && el.hasAttribute("data-landing-faq-group")) {
             return raw;
           }
           /* Legacy URLs: faq-{slug}-{n} (e.g. #faq-support-4) → canonical faq-{n} */
           var legacy = raw.match(/^faq-(.+)-(\d+)$/);
           if (legacy) {
             var canonical = "faq-" + legacy[2];
             el = document.getElementById(canonical);
             if (el && faqLayout.contains(el) && el.hasAttribute("data-landing-faq-group")) {
               if (window.history && window.history.replaceState) {
                 window.history.replaceState(
                   null,
                   "",
                   window.location.pathname + window.location.search + "#" + canonical
                 );
               }
               return canonical;
             }
           }
           return null;
         }

         function setActiveGroup(id) {
           if (!id) return;
           groupBlocks.forEach(function (block) {
             block.hidden = block.id !== id;
           });
           var tabs = faqLayout.querySelectorAll(".landing-faq-tab");
           tabs.forEach(function (tab) {
             var tid = tab.getAttribute("data-landing-faq-target") || "";
             if (!tid && tab.getAttribute("href")) {
               tid = tab.getAttribute("href").replace(/^#/, "");
             }
             var on = tid === id;
             tab.classList.toggle("is-landing-faq-active", on);
             if (on) {
               tab.setAttribute("aria-current", "page");
             } else {
               tab.removeAttribute("aria-current");
             }
           });
         }

         var initialId = targetIdFromHash() || groupBlocks[0].id;
         setActiveGroup(initialId);

         faqLayout.querySelectorAll(".landing-faq-tab").forEach(function (tab) {
           tab.addEventListener("click", function (e) {
             e.preventDefault();
             var id = tab.getAttribute("data-landing-faq-target") || tab.getAttribute("href").replace(/^#/, "");
             if (!id) return;
             setActiveGroup(id);
             var path = window.location.pathname + window.location.search + "#" + id;
             if (window.history && window.history.replaceState) {
               window.history.replaceState(null, "", path);
             } else {
               window.location.hash = "#" + id;
             }
           });
         });

         window.addEventListener("hashchange", function () {
           var id = targetIdFromHash();
           if (id) {
             setActiveGroup(id);
           } else if (window.location.hash === "#faq" || window.location.hash === "") {
             setActiveGroup(groupBlocks[0].id);
           }
         });
       })();

     });
     </script>

</body>
</html>