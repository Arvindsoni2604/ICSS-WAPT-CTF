<?php require_once __DIR__ . '/../src/bootstrap.php'; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ICSS Market | Cybersecurity Training Portal</title>
<meta name="description" content="ICSS cybersecurity training portal with student accounts, a demo shop, and guided OWASP WAPT challenges.">
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header class="top">
  <a class="brand" href="/"><span class="mark">IC</span><div><b>ICSS TRAINING</b><small>WEB SECURITY & LEARNING PORTAL</small></div></a>
  <nav class="main-nav">
    <a href="/shop.php">Demo Shop</a>
    <a href="/challenges.php">WAPT Challenges</a>
    <?php if (logged_in()): ?>
      <a href="/dashboard.php">My Dashboard</a>
      <a href="/logout.php">Sign out</a>
    <?php else: ?>
      <a href="/login.php">Log in</a>
      <a class="nav-cta" href="/signup.php">Create Student Account</a>
    <?php endif; ?>
  </nav>
</header>

<main>
  <section class="clear-hero">
    <div class="clear-hero-copy">
      <p class="eyebrow">INDIAN CYBER SECURITY SOLUTIONS · CLASSROOM LAB</p>
      <h1>Learn web security.<br><span>Practice with purpose.</span></h1>
      <p class="clear-lead">A training portal for students to explore a demo online store, learn web application security, complete guided OWASP challenges, and earn points by submitting flags.</p>
      <div class="clear-actions">
        <?php if (logged_in()): ?>
          <a class="button primary" href="/dashboard.php">Go to my dashboard <span aria-hidden="true">→</span></a>
        <?php else: ?>
          <a class="button primary" href="/signup.php">Create a student account <span aria-hidden="true">→</span></a>
          <a class="button ghost" href="/login.php">Already registered? Log in</a>
        <?php endif; ?>
      </div>
      <p class="fine">Training environment only · Fictional data · No real payments</p>
    </div>
    <aside class="clear-overview">
      <div class="overview-top"><span class="overview-icon">IC</span><div><b>ICSS WAPT LAB</b><small>YOUR LEARNING SPACE</small></div></div>
      <div class="overview-row"><span><b>01</b><span><strong>Create an account</strong><small>Keep your score personal</small></span></span><span class="overview-arrow">→</span></div>
      <div class="overview-row"><span><b>02</b><span><strong>Explore the challenges</strong><small>Learn OWASP Top 10 concepts</small></span></span><span class="overview-arrow">→</span></div>
      <div class="overview-row"><span><b>03</b><span><strong>Submit flags</strong><small>Earn points and track progress</small></span></span><span class="overview-arrow">→</span></div>
    </aside>
  </section>

  <section class="clear-section">
    <div class="clear-section-heading">
      <p class="eyebrow">CHOOSE WHERE TO START</p>
      <h2>Everything you need, in one place.</h2>
      <p>Use the three sections below to get around the portal.</p>
    </div>
    <div class="clear-cards">
      <article class="clear-card">
        <div class="clear-card-top"><span class="clear-number">01</span><span class="clear-label">SHOP</span></div>
        <h3>Demo online shop</h3>
        <p>Browse fictional products, add items to your cart, and try a simulated checkout. No money is charged.</p>
        <a href="/shop.php">Browse the shop <span>→</span></a>
      </article>
      <article class="clear-card clear-card-featured">
        <div class="clear-card-top"><span class="clear-number">02</span><span class="clear-label">LEARN</span></div>
        <h3>WAPT challenges</h3>
        <p>Explore guided scenarios for all ten OWASP Top 10:2025 categories and learn how to report and fix weaknesses.</p>
        <a href="/challenges.php">View challenges <span>→</span></a>
      </article>
      <article class="clear-card">
        <div class="clear-card-top"><span class="clear-number">03</span><span class="clear-label">PROGRESS</span></div>
        <h3>Student dashboard</h3>
        <p>Submit challenge flags, earn points, and see your progress. Sign up to keep your results under your own account.</p>
        <?php if (logged_in()): ?>
          <a href="/dashboard.php">Open my dashboard <span>→</span></a>
        <?php else: ?>
          <a href="/signup.php">Create your account <span>→</span></a>
        <?php endif; ?>
      </article>
    </div>
  </section>

  <section class="clear-notice">
    <div><span class="notice-mark">i</span></div>
    <div><strong>For classroom practice only</strong><p>Use this portal only for authorized training. Products and orders are fictional, checkout does not process payments, and challenge work should stay within the assigned lab.</p></div>
  </section>
</main>
<footer><span>ICSS Training Portal</span><span>Cybersecurity education · Classroom use</span></footer>
</body>
</html>
