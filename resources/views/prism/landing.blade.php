<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PRISM – Batangas State University</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    *,
    *::before,
    *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    :root {
      --red: #8b0000;
      --red-mid: #9b0000;
      --red-light: #b30b0b;
      --white: #ffffff;
      --bg: #f5efef;
      --text-dark: #111111;
      --text-muted: #555555;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      background: var(--bg);
      overflow-x: hidden;
    }

    /* ── NAVBAR ── */
    header {
      height: 85px;
      width: 100%;
      background: var(--white);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 50px;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 999;
      box-shadow: 0 2px 15px rgba(0, 0, 0, .07);
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .logo img {
      width: 68px;
      height: 68px;
      border-radius: 50%;
      object-fit: cover;
    }

    .logo-fallback {
      width: 68px;
      height: 68px;
      border-radius: 50%;
      background: var(--red);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 26px;
      color: white;
    }

    .logo-text h3 {
      font-size: 14px;
      font-weight: 700;
      color: var(--text-dark);
      line-height: 1.2;
    }

    .logo-text p {
      font-size: 11.5px;
      color: var(--red-light);
      font-weight: 600;
    }

    nav {
      display: flex;
      gap: 32px;
    }

    nav a {
      text-decoration: none;
      color: var(--text-dark);
      font-size: 13.5px;
      font-weight: 500;
      transition: color .2s;
    }

    nav a:hover {
      color: var(--red);
    }

    .sign-btn {
      background: var(--red);
      color: var(--white);
      padding: 10px 20px;
      border-radius: 7px;
      text-decoration: none;
      font-size: 13.5px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background .2s, transform .15s;
    }

    .sign-btn:hover {
      background: #6e0000;
      transform: translateY(-1px);
    }

    /* ── HERO ── */
    .hero {
      margin-top: 85px;
      height: 620px;
      position: relative;
      overflow: hidden;
    }

    .slide {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-position: center;
      opacity: 0;
      transition: opacity .9s ease;
    }

    .slide.active {
      opacity: 1;
    }

    .slide-1 {
      background-image: url('images/bgbsu.jpg'), linear-gradient(135deg, #c0392b, #922b21);
    }

    .slide-2 {
      background-image: url('images/bgbsu2.jpg'), linear-gradient(135deg, #1a237e, #283593);
    }

    .slide-3 {
      background-image: url('images/bgbsu3.jpg'), linear-gradient(135deg, #004d40, #00695c);
    }

    .slide-4 {
      background-image: url('images/bgbsu4.jpg'), linear-gradient(135deg, #4a148c, #6a1b9a);
    }

    .slide-5 {
      background-image: url('images/bgbsu5.jpg'), linear-gradient(135deg, #e65100, #bf360c);
    }

    .hero-overlay {
      position: absolute;
      inset: 0;
      z-index: 2;
      pointer-events: none;
    }

    .hero-overlay::before {
      content: "";
      position: absolute;
      left: -100px;
      top: 0;
      width: 700px;
      height: 100%;
      background: linear-gradient(105deg, #7d0000 60%, #b30b0b 100%);
      border-radius: 0 40% 38% 0/0 50% 50% 0;
      opacity: .92;
    }

    .hero-content {
      position: absolute;
      left: 65px;
      top: 50%;
      transform: translateY(-50%);
      z-index: 4;
      width: 430px;
      color: var(--white);
    }

    .hero-content h1 {
      font-size: 36px;
      font-weight: 800;
      line-height: 1.3;
      margin-bottom: 18px;
      letter-spacing: -.3px;
    }

    .hero-content h1 span {
      white-space: nowrap;
      display: block;
    }

    .hero-content p {
      font-size: 14px;
      line-height: 1.85;
      margin-bottom: 28px;
      opacity: .92;
    }

    .hero-btn {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      background: var(--white);
      color: var(--red);
      padding: 13px 24px;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 700;
      font-size: 13.5px;
      transition: transform .2s, box-shadow .2s;
    }

    .hero-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 22px rgba(0, 0, 0, .18);
    }

    .hero-btn-icon {
      background: var(--red);
      color: var(--white);
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
    }

    .carousel-dots {
      position: absolute;
      bottom: 28px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 5;
      display: flex;
      gap: 10px;
    }

    .dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--white);
      opacity: .4;
      cursor: pointer;
      border: none;
      padding: 0;
      transition: opacity .3s, transform .3s;
    }

    .dot.active {
      opacity: 1;
      transform: scale(1.3);
    }

    /* ── HERO FEATURES STRIP ── */
    .features {
      width: 86%;
      margin: -55px auto 0;
      background: var(--white);
      padding: 28px;
      border-radius: 16px;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 22px;
      position: relative;
      z-index: 5;
      box-shadow: 0 12px 30px rgba(0, 0, 0, .09);
    }

    .feature-box {
      text-align: center;
      padding: 28px 20px;
      border-radius: 13px;
      background: #fafafa;
      border: 1px solid #ececec;
      transition: transform .25s, box-shadow .25s;
    }

    .feature-box:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 25px rgba(139, 0, 0, .1);
    }

    .feature-icon {
      width: 52px;
      height: 52px;
      margin: 0 auto 16px;
      display: block;
      color: var(--red);
    }

    .feature-box h3 {
      font-size: 14.5px;
      font-weight: 700;
      color: var(--red);
      margin-bottom: 9px;
    }

    .feature-box p {
      font-size: 12px;
      color: var(--text-muted);
      line-height: 1.6;
    }

    /* ── ABOUT ── */
    .about {
      margin-top: 70px;
      background: var(--red-mid);
      padding: 90px 70px;
    }

    .about-title {
      text-align: center;
      color: var(--white);
      font-size: 52px;
      font-weight: 800;
      margin-bottom: 60px;
      letter-spacing: 2px;
    }

    .about-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 50px;
    }

    .about-text {
      flex: 1;
      color: var(--white);
    }

    .about-text h2 {
      font-size: 34px;
      font-weight: 700;
      margin-bottom: 20px;
    }

    .about-text p {
      font-size: 15px;
      line-height: 2;
      opacity: .9;
    }

    .about-image {
      width: 500px;
      height: 330px;
      background: rgba(255, 255, 255, .15);
      border-radius: 12px;
      border: 2px solid rgba(255, 255, 255, .25);
      flex-shrink: 0;
    }

    /* ── SECTION LABEL ── */
    .section-label {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 14px;
    }

    .section-label::before {
      content: "";
      display: block;
      width: 32px;
      height: 3px;
      background: var(--red);
      border-radius: 2px;
    }

    .section-label span {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 2px;
      color: var(--red);
      text-transform: uppercase;
    }

    /* ── MODULES ── */
    .modules {
      padding: 90px 7%;
      background: var(--white);
    }

    .modules-header {
      margin-bottom: 50px;
    }

    .modules-header h2 {
      font-size: 38px;
      font-weight: 800;
      color: var(--text-dark);
      line-height: 1.2;
      margin-bottom: 12px;
    }

    .modules-header p {
      font-size: 14px;
      color: var(--text-muted);
      max-width: 480px;
      line-height: 1.8;
    }

    .modules-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }

    .module-card {
      border: 1.5px solid #e8e0e0;
      border-radius: 14px;
      padding: 30px 26px;
      background: #fff;
      opacity: 0;
      transform: translateY(30px);
      transition: box-shadow .3s, border-color .3s;
    }

    .module-card.visible {
      opacity: 1;
      transform: translateY(0);
      transition: opacity .5s ease, transform .5s ease, box-shadow .3s, border-color .3s;
    }

    .module-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 14px 35px rgba(139, 0, 0, .1);
      border-color: var(--red);
    }

    .module-icon-wrap {
      width: 48px;
      height: 48px;
      background: #fdf0f0;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 18px;
    }

    .module-icon-wrap svg {
      width: 26px;
      height: 26px;
      color: var(--red);
    }

    .module-card h4 {
      font-size: 15px;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 10px;
    }

    .module-card p {
      font-size: 12.5px;
      color: var(--text-muted);
      line-height: 1.75;
    }

    /* ── USER ROLES / FLIP CARDS ── */
    .user-roles {
      padding: 90px 7%;
      background: var(--bg);
    }

    .user-roles-header {
      margin-bottom: 55px;
    }

    .user-roles-header h2 {
      font-size: 38px;
      font-weight: 800;
      color: var(--text-dark);
      line-height: 1.2;
      margin-bottom: 12px;
    }

    .user-roles-header p {
      font-size: 14px;
      color: var(--text-muted);
      max-width: 500px;
      line-height: 1.8;
    }

    .roles-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 20px;
    }

    .flip-card {
      height: 220px;
      perspective: 1000px;
      cursor: pointer;
      opacity: 0;
      transform: translateY(25px);
    }

    .flip-card.visible {
      opacity: 1;
      transform: translateY(0);
      transition: opacity .5s ease, transform .5s ease;
    }

    .flip-card-inner {
      position: relative;
      width: 100%;
      height: 100%;
      transform-style: preserve-3d;
      transition: transform .6s cubic-bezier(.4, 0, .2, 1);
    }

    .flip-card.flipped .flip-card-inner {
      transform: rotateY(180deg);
    }

    .flip-front,
    .flip-back {
      position: absolute;
      inset: 0;
      border-radius: 14px;
      backface-visibility: hidden;
      -webkit-backface-visibility: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 24px 18px;
      text-align: center;
    }

    .flip-front {
      background: var(--white);
      border: 1.5px solid #e8e0e0;
      box-shadow: 0 4px 15px rgba(0, 0, 0, .06);
    }

    .flip-front-icon {
      width: 56px;
      height: 56px;
      background: #fdf0f0;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 14px;
    }

    .flip-front-icon svg {
      width: 28px;
      height: 28px;
      color: var(--red);
    }

    .flip-front h4 {
      font-size: 13.5px;
      font-weight: 700;
      color: var(--text-dark);
      line-height: 1.3;
      margin-bottom: 6px;
    }

    .flip-hint {
      font-size: 10px;
      color: #bbb;
      display: flex;
      align-items: center;
      gap: 4px;
      margin-top: 4px;
    }

    .flip-back {
      background: var(--red);
      transform: rotateY(180deg);
    }

    .flip-back h4 {
      font-size: 14px;
      font-weight: 700;
      color: var(--white);
      margin-bottom: 10px;
    }

    .flip-back p {
      font-size: 11.5px;
      color: rgba(255, 255, 255, .88);
      line-height: 1.7;
    }

    /* ── WORKFLOW ── */
    .workflow {
      padding: 90px 7%;
      background: var(--white);
    }

    .workflow-header {
      margin-bottom: 60px;
    }

    .workflow-header h2 {
      font-size: 38px;
      font-weight: 800;
      color: var(--text-dark);
      line-height: 1.2;
      margin-bottom: 12px;
    }

    .workflow-header p {
      font-size: 14px;
      color: var(--text-muted);
      max-width: 500px;
      line-height: 1.8;
    }

    .workflow-steps {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      position: relative;
      gap: 0;
    }

    .workflow-steps::before {
      content: "";
      position: absolute;
      top: 28px;
      left: 28px;
      right: 28px;
      height: 3px;
      background: #e8e0e0;
      z-index: 0;
    }

    .workflow-line-fill {
      position: absolute;
      top: 28px;
      left: 28px;
      height: 3px;
      width: 0;
      background: var(--red);
      z-index: 1;
      transition: width 1.4s ease;
      border-radius: 2px;
    }

    .workflow-line-fill.animated {
      width: calc(100% - 56px);
    }

    .step {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      position: relative;
      z-index: 2;
      opacity: 0;
      transform: translateY(20px);
    }

    .step.visible {
      opacity: 1;
      transform: translateY(0);
      transition: opacity .5s ease, transform .5s ease;
    }

    .step-num {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: var(--white);
      border: 3px solid #e8e0e0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      font-weight: 800;
      color: var(--text-muted);
      margin-bottom: 14px;
      transition: background .4s, border-color .4s, color .4s, transform .3s;
    }

    .step.active-step .step-num,
    .step:hover .step-num {
      background: var(--red);
      border-color: var(--red);
      color: white;
      transform: scale(1.1);
    }

    .step-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-dark);
      line-height: 1.4;
    }

    .step-sub {
      font-size: 11px;
      color: var(--text-muted);
      margin-top: 4px;
    }

    /* ── CTA ── */
    .cta {
      background: linear-gradient(110deg, #4a0000 0%, #7a0000 40%, #9b1010 100%);
      padding: 80px 7%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 40px;
      position: relative;
      overflow: hidden;
    }

    /* PRISM watermark — very large, sits behind content on the right */
    .cta::after {
      content: "PRISM";
      position: absolute;
      right: -40px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 260px;
      font-weight: 900;
      letter-spacing: -8px;
      color: rgba(180, 20, 20, .35);
      pointer-events: none;
      user-select: none;
      line-height: 1;
      font-family: 'Poppins', sans-serif;
    }

    .cta-text {
      position: relative;
      z-index: 1;
    }

    .cta-text h2 {
      font-size: 34px;
      font-weight: 800;
      color: var(--white);
      margin-bottom: 14px;
      line-height: 1.2;
    }

    .cta-text p {
      font-size: 14px;
      color: rgba(255, 255, 255, .75);
      max-width: 460px;
      line-height: 1.85;
    }

    .cta-btn {
      position: relative;
      z-index: 1;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: var(--white);
      color: var(--red);
      padding: 15px 32px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 700;
      white-space: nowrap;
      flex-shrink: 0;
      box-shadow: 0 4px 20px rgba(0, 0, 0, .2);
      transition: transform .2s, box-shadow .2s;
    }

    .cta-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 28px rgba(0, 0, 0, .3);
    }

    /* ── FOOTER ── */
    footer {
      background: #0d0101;
      padding: 60px 7% 28px;
      color: rgba(255, 255, 255, .55);
    }

    .footer-top {
      display: grid;
      grid-template-columns: 1fr auto auto;
      gap: 80px;
      padding-bottom: 40px;
      border-bottom: 1px solid rgba(255, 255, 255, .08);
      margin-bottom: 24px;
    }

    .footer-brand .brand-name {
      font-size: 22px;
      font-weight: 900;
      color: var(--white);
      letter-spacing: 2px;
    }

    .footer-brand p {
      font-size: 12.5px;
      line-height: 2;
      max-width: 280px;
      margin-top: 10px;
    }

    .footer-col h5 {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      color: #c9a84c;
      margin-bottom: 20px;
    }

    .footer-col a {
      display: block;
      text-decoration: none;
      color: rgba(255, 255, 255, .55);
      font-size: 13.5px;
      margin-bottom: 12px;
      transition: color .2s;
    }

    .footer-col a:hover {
      color: var(--white);
    }

    .footer-bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .footer-bottom p {
      font-size: 12px;
      color: rgba(255, 255, 255, .35);
    }

    .footer-tagline {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      color: #c9a84c;
    }

    /* ── RESPONSIVE ── */
    @media(max-width:991px) {
      nav {
        display: none;
      }

      .hero-content {
        width: 85%;
        left: 30px;
      }

      .hero-content h1 {
        font-size: 28px;
      }

      .features {
        grid-template-columns: 1fr 1fr;
      }

      .about-content {
        flex-direction: column;
      }

      .about-image {
        width: 100%;
      }

      .modules-grid {
        grid-template-columns: 1fr 1fr;
      }

      .roles-grid {
        grid-template-columns: repeat(3, 1fr);
      }

      .workflow-steps {
        flex-wrap: wrap;
        gap: 20px;
      }

      .workflow-steps::before,
      .workflow-line-fill {
        display: none;
      }

      .cta {
        flex-direction: column;
        text-align: center;
      }

      .footer-top {
        grid-template-columns: 1fr;
        gap: 30px;
      }
    }

    @media(max-width:600px) {
      header {
        padding: 0 20px;
      }

      .features {
        grid-template-columns: 1fr;
      }

      .hero-content h1 {
        font-size: 22px;
      }

      .about {
        padding: 60px 30px;
      }

      .about-title {
        font-size: 38px;
      }

      .modules-grid,
      .roles-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>

  <!-- ── HEADER ── -->
  <header>
    <div class="logo">
      <img src="images/bsulogo.png" alt="BSU Logo"
        onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
      <div class="logo-fallback" style="display:none;">🎓</div>
      <div class="logo-text">
        <h3>BATANGAS STATE UNIVERSITY</h3>
        <p>The National Engineering University</p>
      </div>
    </div>
    <nav>
      <a href="#">Home</a>
      <a href="#features">Features</a>
      <a href="#roles">Users</a>
      <a href="#workflow">Workflow</a>
      <a href="#about">About</a>
      <a href="#">Contact</a>
    </nav>
    <a href="{{ route('login') }}" class="sign-btn">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
      </svg>
      Sign In
    </a>
  </header>

  <!-- ── HERO ── -->
  <section class="hero">
    <div class="slide slide-1 active"></div>
    <div class="slide slide-2"></div>
    <div class="slide slide-3"></div>
    <div class="slide slide-4"></div>
    <div class="slide slide-5"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>
        <span>Smart Procurement.</span>
        <span>Transparent Governance.</span>
        <span>Better Education.</span>
      </h1>
      <p>An integrated, AI-assisted procurement management system for Batangas State University – TNEU ARASOF-Nasugbu Campus.</p>
      <a href="{{ route('login') }}" class="hero-btn">
        GET STARTED
        <span class="hero-btn-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
            <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z" />
          </svg>
        </span>
      </a>
    </div>
    <div class="carousel-dots">
      <button class="dot active" data-index="0"></button>
      <button class="dot" data-index="1"></button>
      <button class="dot" data-index="2"></button>
      <button class="dot" data-index="3"></button>
      <button class="dot" data-index="4"></button>
    </div>
  </section>

  <!-- ── HERO FEATURE STRIP ── -->
  <section class="features">
    <div class="feature-box">
      <svg class="feature-icon" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <path d="M32 6 L54 16 L54 32 C54 44 44 54 32 58 C20 54 10 44 10 32 L10 16 Z" />
        <path d="M22 32 L29 39 L42 26" />
      </svg>
      <h3>Transparent</h3>
      <p>Full visibility in every step of the procurement process.</p>
    </div>
    <div class="feature-box">
      <svg class="feature-icon" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10 40 C20 28 28 36 32 30 C36 24 44 32 54 24" />
        <path d="M20 44 C26 34 30 40 32 36 C34 32 38 38 44 30" />
        <circle cx="16" cy="20" r="6" />
        <circle cx="48" cy="20" r="6" />
        <line x1="22" y1="20" x2="42" y2="20" />
      </svg>
      <h3>Collaborative</h3>
      <p>Strong stakeholder participation across all units.</p>
    </div>
    <div class="feature-box">
      <svg class="feature-icon" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <rect x="12" y="6" width="34" height="44" rx="3" />
        <path d="M20 20 L36 20" />
        <path d="M20 28 L36 28" />
        <path d="M20 36 L28 36" />
        <circle cx="42" cy="46" r="10" />
        <path d="M38 46 L41 49 L46 43" />
      </svg>
      <h3>Efficient</h3>
      <p>Streamlined and optimized procurement workflow.</p>
    </div>
    <div class="feature-box">
      <svg class="feature-icon" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <rect x="14" y="28" width="36" height="28" rx="4" />
        <path d="M22 28 L22 20 C22 12 42 12 42 20 L42 28" />
        <circle cx="32" cy="42" r="4" />
        <line x1="32" y1="46" x2="32" y2="50" />
      </svg>
      <h3>Secure</h3>
      <p>Reliable, protected, and auditable records.</p>
    </div>
  </section>

  <!-- ── ABOUT ── -->
  <section class="about" id="about">
    <h1 class="about-title">ABOUT</h1>
    <div class="about-content">
      <div class="about-text">
        <h2>What is PRISM?</h2>
        <p>PRISM is an integrated procurement management platform that helps Batangas State University streamline procurement activities while ensuring transparency, efficiency, accountability, and security throughout the entire procurement lifecycle.</p>
      </div>
      <div class="about-image"></div>
    </div>
  </section>

  <!-- ── CORE FEATURES / MODULES ── -->
  <section class="modules" id="features">
    <div class="modules-header">
      <div class="section-label"><span>Core Features</span></div>
      <h2>Campus Procurement<br>Support Modules</h2>
      <p>Purpose-built tools that streamline every stage of the procurement lifecycle — from budget planning to final monitoring.</p>
    </div>
    <div class="modules-grid">

      <div class="module-card">
        <div class="module-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="3" width="20" height="14" rx="2" />
            <path d="M8 21h8M12 17v4" />
          </svg>
        </div>
        <h4>Budget Deliberation</h4>
        <p>Organize proposed items, justifications, and office budget requirements in a structured, reviewable format.</p>
      </div>

      <div class="module-card">
        <div class="module-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" />
            <path d="M11 8v6M8 11h6" />
          </svg>
        </div>
        <h4>AI-Assisted Market Scoping</h4>
        <p>Gather supplier price references intelligently for budget validation and comprehensive documentation.</p>
      </div>

      <div class="module-card">
        <div class="module-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <path d="M9 15l2 2 4-4" />
          </svg>
        </div>
        <h4>Budget Proposal Approval</h4>
        <p>Route proposals through Finance and Chancellor review with a transparent, trackable workflow.</p>
      </div>

      <div class="module-card">
        <div class="module-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 11l3 3L22 4" />
            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
          </svg>
        </div>
        <h4>APP Consolidation</h4>
        <p>Prepare consolidated Annual Procurement Plan records seamlessly from all approved budget proposals.</p>
      </div>

      <div class="module-card">
        <div class="module-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3" />
            <path d="M12 2v4M12 18v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M2 12h4M18 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83" />
          </svg>
        </div>
        <h4>Procurement Mode Recommendation</h4>
        <p>Support procurement mode review using item category and estimated cost data for full compliance.</p>
      </div>

      <div class="module-card">
        <div class="module-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
          </svg>
        </div>
        <h4>Purchase Request Tracking</h4>
        <p>Monitor PR submission, status movement, and procurement remarks in real-time dashboards.</p>
      </div>

    </div>
  </section>

  <!-- ── USER ROLES — FLIP CARDS ── -->
  <section class="user-roles" id="roles">
    <div class="user-roles-header">
      <div class="section-label"><span>User Roles</span></div>
      <h2>Designed for Campus<br>Procurement Coordination</h2>
      <p>Each role has tailored access and capabilities to keep the procurement process efficient and transparent. <strong>Click a card</strong> to see what each role does.</p>
    </div>
    <div class="roles-grid">

      <div class="flip-card" onclick="this.classList.toggle('flipped')">
        <div class="flip-card-inner">
          <div class="flip-front">
            <div class="flip-front-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                <polyline points="9 22 9 12 15 12 15 22" />
              </svg>
            </div>
            <h4>Office Head / Dean</h4>
            <div class="flip-hint">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12l7 7 7-7" />
              </svg>
              Click to learn more
            </div>
          </div>
          <div class="flip-back">
            <h4>Office Head / Dean</h4>
            <p>Provides budget proposals, market references, and purchase requests for their office units.</p>
          </div>
        </div>
      </div>

      <div class="flip-card" onclick="this.classList.toggle('flipped')">
        <div class="flip-card-inner">
          <div class="flip-front">
            <div class="flip-front-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="5" width="20" height="14" rx="2" />
                <line x1="2" y1="10" x2="22" y2="10" />
              </svg>
            </div>
            <h4>Finance Office</h4>
            <div class="flip-hint">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12l7 7 7-7" />
              </svg>
              Click to learn more
            </div>
          </div>
          <div class="flip-back">
            <h4>Finance Office</h4>
            <p>Reviews budget availability and consolidates approved procurement plans across all offices.</p>
          </div>
        </div>
      </div>

      <div class="flip-card" onclick="this.classList.toggle('flipped')">
        <div class="flip-card-inner">
          <div class="flip-front">
            <div class="flip-front-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                <line x1="3" y1="6" x2="21" y2="6" />
                <path d="M16 10a4 4 0 01-8 0" />
              </svg>
            </div>
            <h4>Procurement Office</h4>
            <div class="flip-hint">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12l7 7 7-7" />
              </svg>
              Click to learn more
            </div>
          </div>
          <div class="flip-back">
            <h4>Procurement Office</h4>
            <p>Tracks purchase requests, status updates, and procurement progress end-to-end in real time.</p>
          </div>
        </div>
      </div>

      <div class="flip-card" onclick="this.classList.toggle('flipped')">
        <div class="flip-card-inner">
          <div class="flip-front">
            <div class="flip-front-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="5" />
                <path d="M3 21v-2a7 7 0 0114 0v2" />
                <path d="M16 3.13a4 4 0 010 7.75" />
              </svg>
            </div>
            <h4>Chancellor</h4>
            <div class="flip-hint">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12l7 7 7-7" />
              </svg>
              Click to learn more
            </div>
          </div>
          <div class="flip-back">
            <h4>Chancellor</h4>
            <p>Reviews campus-level approvals, reports, and monitoring dashboards for full oversight.</p>
          </div>
        </div>
      </div>

      <div class="flip-card" onclick="this.classList.toggle('flipped')">
        <div class="flip-card-inner">
          <div class="flip-front">
            <div class="flip-front-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 16 12 13 21 9 3 6 12 2 12" />
              </svg>
            </div>
            <h4>Vice Chancellor</h4>
            <div class="flip-hint">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12l7 7 7-7" />
              </svg>
              Click to learn more
            </div>
          </div>
          <div class="flip-back">
            <h4>Vice Chancellor</h4>
            <p>Monitors division performance and office procurement status across all departments.</p>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ── WORKFLOW ── -->
  <section class="workflow" id="workflow">
    <div class="workflow-header">
      <div class="section-label"><span>Procurement Workflow</span></div>
      <h2>From Proposal Preparation<br>to Analytics</h2>
      <p>A structured, end-to-end process that ensures every procurement activity is documented, approved, and monitored.</p>
    </div>
    <div class="workflow-steps">
      <div class="workflow-line-fill" id="workflowLine"></div>
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-label">Budget<br>Proposal</div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-label">Market<br>Scoping</div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-label">Approval<br>Workflow</div>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <div class="step-label">APP<br>Consolidation</div>
      </div>
      <div class="step">
        <div class="step-num">5</div>
        <div class="step-label">Purchase<br>Request</div>
      </div>
      <div class="step">
        <div class="step-num">6</div>
        <div class="step-label">Procurement<br>Tracking</div>
      </div>
      <div class="step">
        <div class="step-num">7</div>
        <div class="step-label">Dashboard<br>&amp; Analytics</div>
      </div>
    </div>
  </section>

  <!-- ── CTA ── -->
  <section class="cta">
    <div class="cta-text">
      <h2>Ready to streamline campus procurement?</h2>
      <p>Join Batangas State University TNEU ARASOF-Nasugbu Campus in transforming how procurement is planned, tracked, and reported.</p>
    </div>
    <a href="{{ route('login') }}" class="cta-btn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
      </svg>
      Login to PRISM
    </a>
  </section>

  <!-- ── FOOTER ── -->
  <footer>
    <div class="footer-top">
      <div class="footer-brand">
        <div class="brand-name">PRISM</div>
        <p>Batangas State University<br>TNEU ARASOF-Nasugbu Campus<br>College of Information and Computing Sciences</p>
      </div>
      <div class="footer-col">
        <h5>System</h5>
        <a href="#">About PRISM</a>
        <a href="#">Features</a>
        <a href="#">Workflow</a>
      </div>
      <div class="footer-col">
        <h5>Access</h5>
        <a href="#">Login</a>
        <a href="#">User Roles</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 PRISM Prototype. All rights reserved.</p>
      <span class="footer-tagline">Leading Innovations, Transforming Lives</span>
    </div>
  </footer>

  <script>
    /* ── CAROUSEL ── */
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let current = 0,
      timer;

    function goTo(i) {
      slides[current].classList.remove('active');
      dots[current].classList.remove('active');
      current = (i + slides.length) % slides.length;
      slides[current].classList.add('active');
      dots[current].classList.add('active');
    }

    function startAuto() {
      clearInterval(timer);
      timer = setInterval(() => goTo(current + 1), 4000);
    }
    dots.forEach(d => d.addEventListener('click', () => {
      goTo(+d.dataset.index);
      startAuto();
    }));
    startAuto();

    /* ── SCROLL ANIMATIONS ── */
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          // stagger children
          if (el.classList.contains('modules-grid') || el.classList.contains('roles-grid')) {
            [...el.children].forEach((child, i) => {
              setTimeout(() => child.classList.add('visible'), i * 120);
            });
          } else if (el.classList.contains('workflow-steps')) {
            [...el.querySelectorAll('.step')].forEach((step, i) => {
              setTimeout(() => step.classList.add('visible'), i * 130);
            });
            setTimeout(() => {
              document.getElementById('workflowLine').classList.add('animated');
            }, 300);
          } else {
            el.classList.add('visible');
          }
          observer.unobserve(el);
        }
      });
    }, {
      threshold: 0.15
    });

    document.querySelectorAll('.modules-grid, .roles-grid, .workflow-steps').forEach(el => observer.observe(el));

    /* ── WORKFLOW STEP HOVER HIGHLIGHT ── */
    document.querySelectorAll('.step').forEach(step => {
      step.addEventListener('mouseenter', () => {
        document.querySelectorAll('.step').forEach(s => s.classList.remove('active-step'));
        step.classList.add('active-step');
      });
      step.addEventListener('mouseleave', () => step.classList.remove('active-step'));
    });
  </script>

</body>

</html>