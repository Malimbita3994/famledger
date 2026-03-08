<!DOCTYPE html>
<html lang="en">
<head>

	<title>FamLedger</title>
	<!--

    FamLedger marketing landing page

    -->
     <meta charset="UTF-8">
     <meta http-equiv="X-UA-Compatible" content="IE=Edge">
     <meta name="description" content="FamLedger is a private family system for tracking assets, projects, and finances in one secure place.">
     <meta name="keywords" content="FamLedger, family finance, family office, property management, projects, assets">
     <meta name="author" content="FamLedger">
     <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

     <link rel="icon" type="image/png" href="{{ asset('images/Flavicon.png') }}">
     <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/Flavicon.png') }}">
     <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/Flavicon.png') }}">
     <link rel="apple-touch-icon" href="{{ asset('images/Flavicon.png') }}">

     <link rel="preload" href="{{ asset('metronic/assets/css/tooplate-style.css') }}" as="style">
     <link rel="preload" href="{{ asset('images/background.png') }}" as="image">
     <link rel="preload" href="{{ asset('images/logo.png') }}" as="image">

     <link rel="stylesheet" href="{{ asset('metronic/assets/css/bootstrap.min.css') }}">
     <link rel="stylesheet" href="{{ asset('metronic/assets/css/owl.carousel.css') }}">
     <link rel="stylesheet" href="{{ asset('metronic/assets/css/owl.theme.default.min.css') }}">
     <link rel="stylesheet" href="{{ asset('metronic/assets/css/font-awesome.min.css') }}">

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

     /* Landing footer using FamLedger metamorph gradient */
     #footer {
       background: linear-gradient(135deg, #009EF7, #38bdf8, #22c55e);
       color: #ffffff;
     }
     #footer p,
     #footer a,
     #footer .social-icon li a {
       color: #ffffff;
     }
     </style>

</head>
<body>

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
                         <li><a href="#feature" class="smoothScroll">Features</a></li>
                         <li><a href="#about" class="smoothScroll">About us</a></li>
                         {{-- Pricing hidden for now --}}
                         <li><a href="#contact" class="smoothScroll">Contact</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                         <li><a href="#contact">Say hello - <span>info@famledger.com</span></a></li>
                    </ul>
               </div>

          </div>
     </section>

    <!-- HOME -->
    <section id="home" data-stellar-background-ratio="0.5" style="background-image: url('{{ asset('images/background.png') }}'); background-size: cover; background-position: center;">
         <div class="overlay"></div>
          <div class="container">
               <div class="row">

                   <div class="col-md-offset-3 col-md-6 col-sm-12">
                         <div class="home-info">
                              <h3>Private family finance system</h3>
                              <h2 style="font-weight:700; line-height:1.3;">
                                   <span id="typing-text" style="color:#009EF7;"></span>
                                   <span class="border-r-2 ml-1 animate-blink" style="border-color:#009EF7;">|</span>
                              </h2>
                              <div style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap;">
                                   <a href="{{ route('register') }}" class="section-btn hero-cta">Get started with FamLedger</a>
                                   <a href="{{ route('login') }}" class="section-btn hero-cta hero-cta-secondary">Sign in</a>
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

                    <div class="col-md-12 col-sm-12 scroll-reveal">
                         <div class="section-title">
                              <h1>What your family gets</h1>
                         </div>
                    </div>

                    <div class="col-md-6 col-sm-6 scroll-reveal scroll-reveal-delay-1">
                         <ul class="nav nav-tabs" role="tablist">
                              <li class="active"><a href="#tab01" aria-controls="tab01" role="tab" data-toggle="tab">Property</a></li>

                              <li><a href="#tab02" aria-controls="tab02" role="tab" data-toggle="tab">Finance</a></li>

                              <li><a href="#tab03" aria-controls="tab03" role="tab" data-toggle="tab">Access</a></li>
                         </ul>

                         <div class="tab-content">
                              <div class="tab-pane active" id="tab01" role="tabpanel">
                                   <div class="tab-pane-item">
                                        <h2>Track every property</h2>
                                        <p>Record plots, houses, vehicles and other assets in one ledger with clear categories, ownership, status (active, sold, under construction) and valuation history.</p>
                                   </div>
                                   <div class="tab-pane-item">
                                        <h2>Maintenance & documents</h2>
                                        <p>Log repairs and services, set next-due dates and attach invoices, title deeds and other documents directly to the property so nothing is lost in WhatsApp or email.</p>
                                   </div>
                              </div>


                              <div class="tab-pane" id="tab02" role="tabpanel">
                                   <div class="tab-pane-item">
                                        <h2>Wallet‑based finance</h2>
                                        <p>Capture incomes and expenses against real family wallets (bank accounts, mobile money, petty cash) with support for your family currency and clear running balances.</p>
                                   </div>
                                   <div class="tab-pane-item">
                                        <h2>Practical reports</h2>
                                        <p>View income, expense and property reports by category, project and period to answer common questions quickly in family meetings.</p>
                                   </div>
                                   <div class="tab-pane-item">
                                        <h2>Projects as assets</h2>
                                        <p>Plan budgets, track funding and actual spending for family projects (construction, education, investments) and convert completed projects into long‑term properties.</p>
                                   </div>
                              </div>

                              <div class="tab-pane" id="tab03" role="tabpanel">
                                   <div class="tab-pane-item">
                                        <h2>Roles for owners & members</h2>
                                        <p>Give owners and co‑owners full control while offering read-only or limited access to selected family members, assistants or advisors.</p>
                                   </div>
                                   <div class="tab-pane-item">
                                        <h2>Secure permissions</h2>
                                        <p>Use fine-grained permissions, confirmations and a clear separation between family roles and system admin roles so every important change is intentional and traceable.</p>
                                   </div>
                              </div>
                         </div>

                    </div>

                    <div class="col-md-6 col-sm-6 scroll-reveal scroll-reveal-delay-2">
                         <div class="feature-image">
                              <img src="{{ asset('images/feature-mockup.png') }}" class="img-responsive" alt="Thin Laptop" loading="lazy" decoding="async">                             
                         </div>
                    </div>

               </div>
          </div>
     </section>


     <!-- ABOUT -->
     <section id="about" data-stellar-background-ratio="0.5">
          <div class="container">
               <div class="row">

                    <div class="col-md-offset-3 col-md-6 col-sm-12 scroll-reveal">
                         <div class="section-title">
                              <h1>The FamLedger story</h1>
                         </div>
                    </div>

                    <div class="col-md-4 col-sm-4 scroll-reveal scroll-reveal-delay-1">
                         <div class="team-thumb">
                              <img src="{{ asset('images/founder.jpg') }}" class="img-responsive" alt="Japhet Malimbita" loading="lazy" decoding="async">
                              <div class="team-info team-thumb-up">
                                   <h2>Elias Family</h2>
                                   <small>First FamLedger family</small>
                                   <p>We built FamLedger to keep track of real family projects, plots, vehicles and day‑to‑day cash in one place.</p>
                              </div>
                         </div>
                    </div>

                    <div class="col-md-4 col-sm-4 scroll-reveal scroll-reveal-delay-2">
                         <div class="team-thumb">
                              <div class="team-info team-thumb-down">
                                   <h2>Family Office Advisors</h2>
                                   <small>Early supporters</small>
                                   <p>Accountants and advisors helped us shape clear reports that busy family owners can actually use.</p>
                              </div>
                              <img src="{{ asset('images/testimonial-image.jpg') }}" class="img-responsive" alt="Catherine Soft" loading="lazy" decoding="async">
                         </div>
                    </div>

                    <div class="col-md-4 col-sm-4 scroll-reveal scroll-reveal-delay-3">
                         <div class="team-thumb">
                              <img src="{{ asset('images/team-image3.jpg') }}" class="img-responsive" alt="Jack Wilson" loading="lazy" decoding="async">
                              <div class="team-info team-thumb-up">
                                   <h2>FamLedger Team</h2>
                                   <small>Product & Engineering</small>
                                   <p>We are focused on privacy, clarity and long‑term stewardship of family wealth and responsibilities.</p>
                              </div>
                         </div>
                    </div>
                    
               </div>
          </div>
     </section>


     <!-- TESTIMONIAL -->
     <section id="testimonial" data-stellar-background-ratio="0.5">
          <div class="container">
               <div class="row">

                    <div class="col-md-6 col-sm-12 scroll-reveal">
                         <div class="testimonial-image"></div>
                    </div>

                    <div class="col-md-6 col-sm-12 scroll-reveal scroll-reveal-delay-1">
                         <div class="testimonial-info">
                              
                              <div class="section-title">
                                   <h1>What families say</h1>
                              </div>

                              <div class="owl-carousel owl-theme">
                                   <div class="item">
                                        <h3>FamLedger helps our family see wallets, projects and properties in one view. Meetings are shorter and decisions are clearer.</h3>
                                        <div class="testimonial-item">
                                             <img src="{{ asset('images/tst-image1.jpg') }}" class="img-responsive" alt="Michael" loading="lazy" decoding="async">
                                             <h4>Family owner – Dar es Salaam</h4>
                                        </div>
                                   </div>

                                   <div class="item">
                                        <h3>We finally have a simple record of who contributed what to each project and how much value we have built as a family.</h3>
                                        <div class="testimonial-item">
                                             <img src="{{ asset('images/tst-image2.jpg') }}" class="img-responsive" alt="Sofia" loading="lazy" decoding="async">
                                             <h4>Co‑owner – Arusha</h4>
                                        </div>
                                   </div>

                                   <div class="item">
                                        <h3>Before FamLedger, property documents and numbers lived in WhatsApp and email. Now everything sits in one secure system.</h3>
                                        <div class="testimonial-item">
                                             <img src="{{ asset('images/tst-image3.jpg') }}" class="img-responsive" alt="Monica" loading="lazy" decoding="async">
                                             <h4>Family office advisor</h4>
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


     <!-- CONTACT -->
     <section id="contact" data-stellar-background-ratio="0.5">
          <div class="container">
               <div class="row">

                    <div class="col-md-offset-1 col-md-10 col-sm-12 scroll-reveal">
                         @if (session('success'))
                              <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
                         @endif
                         <form id="contact-form" role="form" action="{{ route('contact.store') }}" method="post">
                              @csrf
                              <div class="section-title">
                                   <h1>Talk to the FamLedger team</h1>
                                   <h2 style="font-weight:600; line-height:1.4; margin-top:0.4rem;">
                                        <span id="contact-typing-text" style="color:#009EF7;"></span>
                                        <span class="border-r-2 ml-1 animate-blink" style="border-color:#009EF7;">|</span>
                                   </h2>
                              </div>

                              <div class="col-md-4 col-sm-4">
                                   <input type="text" class="form-control" placeholder="Full name" name="name" value="{{ old('name') }}" required>
                              </div>
                              <div class="col-md-4 col-sm-4">
                                   <input type="email" class="form-control" placeholder="Email address" name="email" value="{{ old('email') }}" required>
                              </div>
                              <div class="col-md-4 col-sm-4">
                                   <input type="submit" class="form-control" name="send message" value="Request a demo">
                              </div>
                              <div class="col-md-12 col-sm-12">
                                   <textarea class="form-control" rows="8" placeholder="Your message" name="message" required>{{ old('message') }}</textarea>
                              </div>
                              @if ($errors->any())
                                   <div class="col-md-12 col-sm-12">
                                        <div class="alert alert-danger">
                                             @foreach ($errors->all() as $err)
                                                  <div>{{ $err }}</div>
                                             @endforeach
                                        </div>
                                   </div>
                              @endif
                         </form>
                    </div>

               </div>
          </div>
     </section>       


     <!-- FOOTER -->
     <footer id="footer" data-stellar-background-ratio="0.5">
          <div class="container">
               <div class="row">

                    <div class="copyright-text col-md-12 col-sm-12">
                         <div class="col-md-6 col-sm-6">
                              <p>{{ now()->year }} &copy; FamLedger &middot; Private family finance system</p>
                         </div>

                         <div class="col-md-6 col-sm-6">
                              <ul class="social-icon">
                                   <li><a href="#" class="fa fa-facebook-square" attr="facebook icon"></a></li>
                                   <li><a href="#" class="fa fa-twitter"></a></li>
                                   <li><a href="#" class="fa fa-instagram"></a></li>
                              </ul>
                         </div>
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

     <script>
    const texts = [
      "FamLedger is a modern family financial management platform that helps families track income, expenses, assets, and projects while providing insights into their overall wealth and financial growth.",
      "Control your family finances before they control you.",
      "Track everything your family owns in one place.",
      "Understand your family's real net worth.",
      "Most families know their expenses but not their wealth. FamLedger shows you the complete financial picture."
    ];

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
     const contactTexts = [
       "booking a demo for your family",
       "questions about pricing and onboarding",
       "ideas to improve FamLedger",
       "support for your existing account"
     ];

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
     });
     </script>

</body>
</html>