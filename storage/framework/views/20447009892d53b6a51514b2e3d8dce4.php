<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PRISM – Batangas State University</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after {
      margin: 0; padding: 0; box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    :root {
      --red:      #8B0000;
      --red-dark: #6a0000;
      --white:    #ffffff;
      --bg:       #f9f2f2;
      --text:     #1a1a1a;
      --muted:    #666666;
      --border:   #ede0e0;
      --rose:     #fdf0f0;
    }
    html { scroll-behavior: smooth; }
    body { background: var(--white); color: var(--text); overflow-x: hidden; }

    /* ── NAVBAR ── */
    header {
      position: fixed; top: 0; left: 0; width: 100%; z-index: 1000;
      height: 72px;
      background: transparent;
      border-bottom: 1px solid transparent;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 48px;
      transition: background .3s, box-shadow .3s, border-color .3s;
    }
    header.scrolled {
      background: rgba(255,255,255,0.98);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      box-shadow: 0 2px 20px rgba(139,0,0,.09);
      border-bottom: 1px solid var(--border);
    }

    .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .nav-logo-icon { width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nav-logo-icon img { width: 54px; height: 54px; object-fit: contain; }
    .nav-logo-name { font-size: 18px; font-weight: 800; color: var(--red); letter-spacing: .5px; transition: color .3s; }
    header.scrolled .nav-logo-name { color: var(--red); }

    .nav-right { display: flex; align-items: center; gap: 36px; }
    nav { display: flex; align-items: center; gap: 32px; }
    nav a {
      text-decoration: none; font-size: 13.5px; font-weight: 500;
      color: #1a1a1a; transition: color .3s;
    }
    nav a:hover { color: var(--red); }
    header.scrolled nav a { color: #1a1a1a; }
    header.scrolled nav a:hover { color: var(--red); }

    .nav-btn {
      display: flex; align-items: center; gap: 7px;
      background: var(--red); color: white;
      padding: 10px 22px; border-radius: 7px;
      text-decoration: none; font-size: 13px; font-weight: 600;
      transition: background .2s, transform .15s;
      white-space: nowrap;
    }
    .nav-btn:hover { background: var(--red-dark); transform: translateY(-1px); }

    /* ── HERO ── */
    .hero {
      position: relative;
      width: 100%;
      height: 100vh;
      min-height: 620px;
      overflow: hidden;
    }
    .hero-bg {
      position: absolute; inset: 0; z-index: 0;
    }
    .hero-bg img {
      width: 100%; height: 100%;
      object-fit: cover; object-position: center;
      display: block;
    }
    .hero-bg::after {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(105deg,
        rgba(255,255,255,0.18) 0%,
        rgba(255,255,255,0.04) 55%,
        rgba(0,0,0,0.08) 100%);
    }

    .hero-glass {
      position: absolute;
      left: 60px; top: 50%;
      transform: translateY(-50%);
      z-index: 5;
      width: 440px;
      background: linear-gradient(135deg, rgba(255,255,255,0.55) 0%, rgba(255,255,255,0.28) 100%);
      backdrop-filter: blur(28px) saturate(180%);
      -webkit-backdrop-filter: blur(28px) saturate(180%);
      border-radius: 28px;
      padding: 50px 50px 46px;
      border-top: 1.5px solid rgba(255,255,255,0.9);
      border-left: 1.5px solid rgba(255,255,255,0.75);
      border-right: 1.5px solid rgba(255,255,255,0.3);
      border-bottom: 1.5px solid rgba(255,255,255,0.2);
      box-shadow:
        0 4px 6px rgba(0,0,0,0.04),
        0 12px 40px rgba(0,0,0,0.14),
        0 32px 80px rgba(0,0,0,0.1),
        inset 0 1px 0 rgba(255,255,255,0.95),
        inset 0 -1px 0 rgba(255,255,255,0.2);
      animation: cardIn .9s cubic-bezier(.22,1,.36,1) both;
      overflow: hidden;
    }
    .hero-glass::before {
      content: '';
      position: absolute; top: 0; left: 0;
      width: 60%; height: 50%;
      background: radial-gradient(ellipse at 20% 10%, rgba(255,255,255,0.45) 0%, rgba(255,255,255,0) 70%);
      pointer-events: none; z-index: 0;
    }
    .hero-glass::after {
      content: '';
      position: absolute; bottom: -20px; right: -20px;
      width: 180px; height: 180px;
      background: radial-gradient(circle, rgba(139,0,0,0.08) 0%, rgba(139,0,0,0) 70%);
      pointer-events: none; z-index: 0;
    }
    .hero-glass > * { position: relative; z-index: 1; }

    @keyframes cardIn {
      from { opacity: 0; transform: translateY(calc(-50% + 40px)) scale(0.97); }
      to   { opacity: 1; transform: translateY(-50%) scale(1); }
    }

    .hero-glass h1 {
      font-size: 38px; font-weight: 900; line-height: 1.1;
      color: #111; margin-bottom: 0;
      text-transform: uppercase; letter-spacing: -.5px;
      text-shadow: 0 1px 2px rgba(255,255,255,0.6);
    }
    .hero-glass h1 .accent {
      color: var(--red);
      text-shadow: 0 2px 12px rgba(139,0,0,0.2);
    }
    .hero-divider {
      width: 48px; height: 3px;
      background: linear-gradient(90deg, var(--red), rgba(139,0,0,0.3));
      border-radius: 2px; margin: 20px 0;
      box-shadow: 0 2px 8px rgba(139,0,0,0.3);
    }
    .hero-glass p {
      font-size: 13.5px; line-height: 1.9;
      color: rgba(30,30,30,0.82); margin-bottom: 30px; font-weight: 400;
    }
    .hero-btns { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .hero-btn-primary {
      display: inline-flex; align-items: center; gap: 8px;
      background: linear-gradient(135deg, var(--red) 0%, #a50000 100%);
      color: white; padding: 13px 26px; border-radius: 10px;
      text-decoration: none; font-size: 13px; font-weight: 700;
      box-shadow: 0 4px 14px rgba(139,0,0,0.4), 0 1px 3px rgba(139,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.15);
      transition: transform .2s, box-shadow .2s, background .2s; letter-spacing: .2px;
    }
    .hero-btn-primary:hover {
      background: linear-gradient(135deg, #9e0000 0%, #7a0000 100%);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(139,0,0,0.45), 0 2px 6px rgba(139,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.15);
    }
    .hero-btn-ghost {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,0.45);
      backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
      border: 1.5px solid rgba(139,0,0,0.25); color: var(--red);
      padding: 12px 22px; border-radius: 10px;
      text-decoration: none; font-size: 13px; font-weight: 600;
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.06);
      transition: background .2s, transform .2s, box-shadow .2s;
    }
    .hero-btn-ghost:hover {
      background: rgba(255,255,255,0.72);
      transform: translateY(-2px);
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 6px 16px rgba(0,0,0,0.1);
    }

    /* ── ABOUT SECTION ── */
    .about {
      position: relative;
      background: white;
      padding: 0;
    }
    .about-bar-top {
      width: 100%; height: 80px;
      background: var(--red-dark);
    }
    .about-bar-bottom {
      width: 100%; height: 80px;
      background: var(--red-dark);
    }
    .about-bg {
      position: relative;
      width: calc(100% - 200px);
      min-height: 630px;
      margin: -28px auto;
      border-radius: 50px;
      overflow: hidden;
      z-index: 2;
      background-image: url('<?php echo e(asset("images/about.png")); ?>');
      background-size: cover;
      background-position: center;
      background-color: #fdf2ec;
    }
    .about-inner {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: flex-start;
    }
    .about-left {
      flex: 0 0 55%;
      padding: 38px 34px 30px 44px;
      display: flex; flex-direction: column;
      justify-content: center;
    }
    .about-left h2 {
      font-size: 40px; font-weight: 800; line-height: 1.18;
      color: #111; margin-bottom: 0;
    }
    .about-left h2 .accent { color: var(--red); }
    .about-divider {
      width: 28px; height: 3px;
      background: var(--red); border-radius: 2px;
      margin: 13px 0 15px;
    }
    .about-left > p {
      font-size: 12px; line-height: 1.8; color: #444;
      max-width: 320px;
    }
    .about-right {
      flex: 0 0 45%;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      align-content: center;
      padding: 26px 26px 26px 0;
      transform: translateY(100px);
    }
    .about-feat {
      background: white;
      border-radius: 16px;
      padding: 26px 20px 22px;
      box-shadow: 0 2px 10px rgba(139,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
      transition: transform .3s, box-shadow .3s;
      display: flex; flex-direction: column; align-items: center; text-align: center;
    }
    .about-feat:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 22px rgba(139,0,0,0.13);
    }
    .about-feat-icon {
      width: 60px; height: 60px;
      border-radius: 50%;
      background: #fdf0f0;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 12px;
    }
    .about-feat-icon svg { width: 26px; height: 26px; color: var(--red); }
    .about-feat-divider {
      width: 24px; height: 2px;
      background: var(--red); border-radius: 2px;
      margin: 0 auto 12px;
    }
    .about-feat h4 { font-size: 15px; font-weight: 700; color: var(--red); margin-bottom: 8px; line-height: 1.3; }
    .about-feat p  { font-size: 12.5px; color: #666; line-height: 1.6; }

    /* ── MODULES ── */
    .modules {
      position: relative;
    
    padding: 70px 56px;

      background-image: url('<?php echo e(asset("images/features.png")); ?>');
      background-size: cover;
      background-position: center;
      background-color: #faf8f8;
      overflow: hidden;
    }

    /* Dot grid — left side */
    .modules-dots-left {
      position: absolute;
      left: 20px;
      top: 50%;
      transform: translateY(-50%);
      display: grid;
      grid-template-columns: repeat(6, 10px);
      gap: 10px;
      z-index: 0;
      opacity: 0.55;
    }
    .modules-dots-left span {
      width: 4px; height: 4px;
      border-radius: 50%;
      background: #c9a8a8;
      display: block;
    }

    
    .modules-head { text-align: center; margin-bottom: 5px; position: relative; z-index: 1; }
    .modules-head h2 { font-size: 36px; font-weight: 800; color: var(--text); line-height: 1.2; margin-bottom: 10px; }
    .modules-head h2 .accent { color: var(--red); }
    .modules-head p { font-size: 15px; color: var(--muted); max-width: 600px; margin: 0 auto; line-height: 1.8; }

    .sec-label {
      text-align: center; margin-bottom: 2px;
      display: flex; align-items: center; justify-content: center; gap: 10px;
      position: relative; z-index: 1;
    }
    .sec-label::before, .sec-label::after {
      content: ''; flex: 1; max-width: 60px; height: 1.5px; background: #ddd;
    }
    .sec-label span {
      font-size: 13px; font-weight: 700; letter-spacing: 2.5px;
      text-transform: uppercase; color: var(--red);
    }

    .modules-grid {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 28px; position: relative; z-index: 1;
    }
    .mod-card {
    padding: 20px;
    background: white;
    border-radius: 18px;
    border: 1px solid rgba(139,0,0,0.08);
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);

    display: flex;
    flex-direction: column;
    justify-content: flex-start;

    min-height: 235px; /* adjust mo 220-250px */
}
    .mod-card.visible {
      opacity: 1; transform: translateY(0);
      transition: opacity .5s ease, transform .5s ease, background .25s, box-shadow .3s, border-color .3s;
    }
    .mod-card:hover { 
      background: var(--rose);
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(139,0,0,0.08);
      border-color: rgba(139,0,0,0.15);
    }

    .mod-num {
      font-size: 12px; font-weight: 700; color: #ccc; letter-spacing: 1.5px;
      margin-bottom: 8px; display: flex; justify-content: flex-end;
    }
    /* Red underline below the number */
    .mod-num-wrap {
      display: flex; flex-direction: column; align-items: flex-end;
      margin-bottom: 6px;
    }
    .mod-num-wrap .mod-num { margin-bottom: 2px; }
    .mod-num-underline {
      width: 22px; height: 2px;
      background: var(--red); border-radius: 2px;
      opacity: 0.5;
    }

    .mod-icon { width: 48px; height: 48px; border-radius: 10px; background: var(--rose); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
    .mod-icon svg { width: 20px; height: 20px; color: var(--red); }
    .mod-card h4 { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
    .mod-card p  { font-size: 14px; color: var(--muted); line-height: 1.6; }

    /* ── WORKFLOW ── */
.workflow{
    padding: 90px 60px;
    background: white;
}

.workflow-head{
    text-align: center;
    margin-bottom: 55px;
}

.workflow-head h2{
    font-size: 36px;       /* same as User Roles */
    line-height: 1.2;
    font-weight: 800;
    color: #071634;
    margin-bottom: 10px;
}

.workflow-head h2 .accent{
    color: var(--red);
}

.workflow-head p{
    font-size: 15px;       /* same as User Roles */
    line-height: 1.8;
    color: #667085;
    max-width: 600px;
    margin: auto;
}
    .workflow-steps { position: relative; display: flex; align-items: flex-start; justify-content: space-between; }
    .workflow-steps::before { content: ''; position: absolute; top: 27px; left: 40px; right: 40px; height: 2px; background: var(--border); z-index: 0; }
    .wf-fill { position: absolute; top: 27px; left: 40px; height: 2px; width: 0; z-index: 1; background: var(--red); transition: width 1.5s ease; }
    .wf-fill.go { width: calc(100% - 80px); }
    .wf-step { flex: 1; display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; z-index: 2; opacity: 0; transform: translateY(16px); }
    .wf-step.vis { opacity: 1; transform: translateY(0); transition: opacity .45s ease, transform .45s ease; }
    .wf-num { width: 54px; height: 54px; border-radius: 50%; background: white; border: 2.5px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 17px; font-weight: 800; color: var(--muted); margin-bottom: 14px; transition: all .3s; }
    .wf-step:hover .wf-num { background: var(--red); border-color: var(--red); color: white; transform: scale(1.1); }
    .wf-label { font-size: 12px; font-weight: 600; color: var(--text); line-height: 1.4; }

    /* ── CTA ── */
    .cta {
      background: linear-gradient(110deg, #3d0000 0%, #7a0000 45%, #9e1111 100%);
      padding: 80px 56px;
      display: flex; align-items: center; justify-content: space-between; gap: 40px;
      position: relative; overflow: hidden;
    }
    .cta::after {
      content: 'PRISM';
      position: absolute; right: -30px; top: 50%; transform: translateY(-50%);
      font-size: 240px; font-weight: 900; letter-spacing: -6px;
      color: rgba(180,20,20,.28); pointer-events: none; user-select: none; line-height: 1;
    }
    .cta-text { position: relative; z-index: 1; }
    .cta-text h2 { font-size: 32px; font-weight: 800; color: white; margin-bottom: 12px; line-height: 1.2; }
    .cta-text p  { font-size: 13.5px; color: rgba(255,255,255,.72); max-width: 460px; line-height: 1.85; }
    .cta-action { position: relative; z-index: 1; flex-shrink: 0; }
    .cta-btn { display: inline-flex; align-items: center; gap: 10px; background: white; color: var(--red); padding: 15px 30px; border-radius: 8px; text-decoration: none; font-size: 13.5px; font-weight: 700; box-shadow: 0 4px 20px rgba(0,0,0,.2); transition: transform .2s, box-shadow .2s; }
    .cta-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,.3); }

    /* ── FOOTER ── */
    footer { background: #0d0101; padding: 60px 56px 28px; }
    .footer-top { display: grid; grid-template-columns: 1fr auto auto; gap: 80px; padding-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,.08); margin-bottom: 24px; }
    .footer-brand .name { font-size: 22px; font-weight: 900; color: white; letter-spacing: 2px; }
    .footer-brand p { font-size: 12px; line-height: 2; color: rgba(255,255,255,.45); max-width: 260px; margin-top: 10px; }
    .footer-col h5 { font-size: 10px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: #c9a84c; margin-bottom: 18px; }
    .footer-col a { display: block; text-decoration: none; color: rgba(255,255,255,.45); font-size: 13px; margin-bottom: 10px; transition: color .2s; }
    .footer-col a:hover { color: white; }
    .footer-bottom { display: flex; justify-content: space-between; align-items: center; }
    .footer-bottom p { font-size: 11.5px; color: rgba(255,255,255,.28); }
    .footer-tagline { font-size: 10.5px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: #c9a84c; }

    /* ── RESPONSIVE ── */
    @media (max-width: 991px) {
      nav { display: none; }
      .hero-glass { left: 30px; right: 30px; width: auto; padding: 36px 32px; }
      .hero-glass h1 { font-size: 30px; }
      .about-bar-top, .about-bar-bottom { height: 44px; }
      .about-bg { width: calc(100% - 32px); margin: -22px auto; border-radius: 20px; }
      .about-inner { flex-direction: column; }
      .about-left { flex: none; width: 100%; padding: 30px 26px 16px; justify-content: flex-start; }
      .about-left h2 { font-size: 24px; }
      .about-left > p { max-width: none; }
      .about-right { flex: none; width: 100%; padding: 4px 26px 30px; grid-template-columns: 1fr 1fr; }
      .modules { padding: 60px 30px; }
      .modules-grid { grid-template-columns: 1fr 1fr; gap: 16px; }
      .modules-dots-left { display: none; }
      .workflow { padding: 60px 30px; }
      .workflow-steps { flex-wrap: wrap; gap: 20px; }
      .workflow-steps::before, .wf-fill { display: none; }
      .wf-step { flex: 0 0 calc(33.3% - 14px); }
      .cta { flex-direction: column; padding: 60px 30px; text-align: center; }
      .footer-top { grid-template-columns: 1fr; gap: 30px; }
      footer { padding: 50px 30px 24px; }
    }
    @media (max-width: 600px) {
      header { padding: 0 20px; }
      .hero-glass { left: 16px; right: 16px; padding: 28px 24px; }
      .hero-glass h1 { font-size: 26px; }
      .about-left h2 { font-size: 21px; }
      .about-right { grid-template-columns: 1fr 1fr; gap: 9px; }
      .about-feat { padding: 13px 10px 11px; }
      .modules-grid { grid-template-columns: 1fr; gap: 14px; }
      .wf-step { flex: 0 0 calc(50% - 10px); }
    }
  </style>
</head>
<body>

<!-- HEADER -->
<header id="hdr">
  <a href="#" class="nav-logo">
    <div class="nav-logo-icon">
      <img src="<?php echo e(asset('images/prism2.png')); ?>" alt="PRISM Logo">
    </div>
    <span class="nav-logo-name">PRISM</span>
  </a>
  <div class="nav-right">
    <nav>
      <a href="#">Home</a>
      <a href="#about">About</a>
      <a href="#features">Features</a>
      <a href="#workflow">Workflow</a>
      <a href="#">Contact</a>
    </nav>
    <a href="<?php echo e(route('login')); ?>" class="nav-btn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
      </svg>
      Sign In
    </a>
  </div>
</header>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg">
    <img src="<?php echo e(asset('images/bsuhero.png')); ?>" alt="Batangas State University ARASOF-Nasugbu">
  </div>
  <div class="hero-glass">
    <h1>TRANSFORMING<br>IDEAS INTO<br><span class="accent">SMART<br>PROCUREMENT</span></h1>
    <div class="hero-divider"></div>
    <p>Designed to simplify procurement workflows through innovation, intelligence, and transparency.</p>
    <div class="hero-btns">
      <a href="<?php echo e(route('login')); ?>" class="hero-btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
        </svg>
        Get Started
      </a>
      <a href="#about" class="hero-btn-ghost">
        Learn More
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </a>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section class="about" id="about">
  <div class="about-bar-top"></div>
  <div class="about-bg">
    <div class="about-inner">
      <div class="about-left">
        <h2>Empowering Smarter<br><span class="accent">Procurement.</span></h2>
        <div class="about-divider"></div>
        <p>PRISM is an AI-assisted platform that simplifies procurement processes, improves transparency, and supports smarter budget decisions for the university.</p>
      </div>
      <div class="about-right">
        <div class="about-feat">
          <div class="about-feat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
            </svg>
          </div>
          <div class="about-feat-divider"></div>
          <h4>Smart Automation</h4>
          <p>Automate workflows and reduce manual tasks.</p>
        </div>
        <div class="about-feat">
          <div class="about-feat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
          </div>
          <div class="about-feat-divider"></div>
          <h4>Transparency</h4>
          <p>Ensure visibility and accountability in every step.</p>
        </div>
        <div class="about-feat">
          <div class="about-feat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="8" r="4"/><path d="M3 21v-1a6 6 0 0 1 6-6h1"/><circle cx="17" cy="16" r="4"/>
            </svg>
          </div>
          <div class="about-feat-divider"></div>
          <h4>Better Collaboration</h4>
          <p>Connect offices and improve coordination.</p>
        </div>
        <div class="about-feat">
          <div class="about-feat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
          </div>
          <div class="about-feat-divider"></div>
          <h4>Data-Driven Decisions</h4>
          <p>Use real-time insights for smarter budget planning.</p>
        </div>
      </div>
    </div>
  </div>
  <div class="about-bar-bottom"></div>
</section>

<!-- MODULES -->
<section class="modules" id="features">
  <!-- Dot grid decoration left -->
  <div class="modules-dots-left" id="dotGridLeft"></div>

  <div class="sec-label"><span>Our Solutions</span></div>
  <div class="modules-head">
    <h2>Campus Procurement<br><span class="accent">Support Modules</span></h2>
    <p>Purpose-built tools that streamline every stage of the procurement lifecycle — from budget planning to final monitoring.</p>
  </div>
  <div class="modules-grid" id="modGrid">
    <div class="mod-card">
      <div class="mod-num-wrap">
        <div class="mod-num">01</div>
        <div class="mod-num-underline"></div>
      </div>
      <div class="mod-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
      <h4>Budget Deliberation</h4>
      <p>Organize proposed items, justifications, and office budget requirements in a structured, reviewable format.</p>
    </div>
    <div class="mod-card">
      <div class="mod-num-wrap">
        <div class="mod-num">02</div>
        <div class="mod-num-underline"></div>
      </div>
      <div class="mod-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35M11 8v6M8 11h6"/></svg></div>
      <h4>AI-Assisted Market Scoping</h4>
      <p>Gather supplier price references intelligently for budget validation and comprehensive documentation.</p>
    </div>
    <div class="mod-card">
      <div class="mod-num-wrap">
        <div class="mod-num">03</div>
        <div class="mod-num-underline"></div>
      </div>
      <div class="mod-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15l2 2 4-4"/></svg></div>
      <h4>Budget Proposal Approval</h4>
      <p>Route proposals through Finance and Chancellor review with a transparent, trackable workflow.</p>
    </div>
    <div class="mod-card">
      <div class="mod-num-wrap">
        <div class="mod-num">04</div>
        <div class="mod-num-underline"></div>
      </div>
      <div class="mod-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/><path d="M9 10l2 2 4-4"/></svg></div>
      <h4>APP Consolidation</h4>
      <p>Prepare consolidated Annual Procurement Plan records seamlessly from all approved budget proposals.</p>
    </div>
    <div class="mod-card">
      <div class="mod-num-wrap">
        <div class="mod-num">05</div>
        <div class="mod-num-underline"></div>
      </div>
      <div class="mod-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></div>
      <h4>Procurement Mode Recommendation</h4>
      <p>Support procurement mode review using item category and estimated cost data for full compliance.</p>
    </div>
    <div class="mod-card">
      <div class="mod-num-wrap">
        <div class="mod-num">06</div>
        <div class="mod-num-underline"></div>
      </div>
      <div class="mod-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
      <h4>Purchase Request Tracking</h4>
      <p>Monitor PR submission, status movement, and procurement remarks in real-time dashboards.</p>
    </div>
  </div>
</section>

<!-- WORKFLOW -->
<section class="workflow" id="workflow">

    <div class="sec-label">
        <span>Procurement Workflow</span>
    </div>

    <div class="workflow-head">
        <h2>
            From Proposal Preparation<br>
            <span class="accent">to Analytics</span>
        </h2>

        <p>
            A structured, end-to-end process that ensures every procurement
            activity is documented, approved, and monitored.
        </p>
    </div>
  <div class="workflow-steps" id="wfSteps">
    <div class="wf-fill" id="wfLine"></div>
    <div class="wf-step"><div class="wf-num">1</div><div class="wf-label">Budget<br>Proposal</div></div>
    <div class="wf-step"><div class="wf-num">2</div><div class="wf-label">Market<br>Scoping</div></div>
    <div class="wf-step"><div class="wf-num">3</div><div class="wf-label">Approval<br>Workflow</div></div>
    <div class="wf-step"><div class="wf-num">4</div><div class="wf-label">APP<br>Consolidation</div></div>
    <div class="wf-step"><div class="wf-num">5</div><div class="wf-label">Purchase<br>Request</div></div>
    <div class="wf-step"><div class="wf-num">6</div><div class="wf-label">Procurement<br>Tracking</div></div>
    <div class="wf-step"><div class="wf-num">7</div><div class="wf-label">Dashboard<br>&amp; Analytics</div></div>
  </div>
</section>

<!-- CTA -->
<section class="cta">
  <div class="cta-text">
    <h2>Ready to streamline campus procurement?</h2>
    <p>Join Batangas State University TNEU ARASOF-Nasugbu Campus in transforming how procurement is planned, tracked, and reported.</p>
  </div>
  <div class="cta-action">
    <a href="<?php echo e(route('login')); ?>" class="cta-btn">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.6 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
      Login to PRISM
    </a>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-top">
    <div class="footer-brand">
      <div class="name">PRISM</div>
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
  /* Navbar scroll */
  window.addEventListener('scroll', () => {
    document.getElementById('hdr').classList.toggle('scrolled', window.scrollY > 10);
  });

  /* Generate dot grid */
  (function() {
    const grid = document.getElementById('dotGridLeft');
    if (!grid) return;
    for (let i = 0; i < 6 * 10; i++) {
      const s = document.createElement('span');
      grid.appendChild(s);
    }
  })();

  /* Intersection observer animations */
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target;
      if (el.id === 'modGrid') {
        [...el.children].forEach((c, i) => setTimeout(() => c.classList.add('visible'), i * 100));
      } else if (el.id === 'wfSteps') {
        [...el.querySelectorAll('.wf-step')].forEach((s, i) => setTimeout(() => s.classList.add('vis'), i * 120));
        setTimeout(() => document.getElementById('wfLine').classList.add('go'), 300);
      }
      io.unobserve(el);
    });
  }, { threshold: 0.12 });

  ['modGrid','wfSteps'].forEach(id => {
    const el = document.getElementById(id);
    if (el) io.observe(el);
  });

  /* Smooth scroll */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const t = document.querySelector(a.getAttribute('href'));
      if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
    });
  });

</script>
</body>
</html><?php /**PATH C:\xampp\htdocs\Prism\resources\views/prism/landing.blade.php ENDPATH**/ ?>