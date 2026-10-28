<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_title   = 'Contact';
$page_description = 'Contact YGGC about supporting children and families in Isingiro, volunteering, donations or partnerships.';
$current_page = 'contact';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container white-container">
    <span class="breadcrumb">Contact</span>
    <h1>Get in touch with YGGC.</h1>
    <p>Ask about our work with children and families in Isingiro, make a donation, offer support or explore a partnership.</p>
  </div>
</section>

<section class="section">
  <div class="container two-col">
    <div class="reveal">
      <h2>Reach us directly</h2>
      <p><i class="fa-solid fa-location-crosshairs fa-lg"></i> YGGC Ministry, Isingiro, Uganda</p>
      <p><i class="fa-solid fa-phone fa-lg"></i> +256 778 130 025</p>
      <a href=""><p><i class="fa-solid fa-envelope fa-lg"></i> info@yggcministry.com</p></a>
      <p><i class="fa-solid fa-clock fa-lg"></i> Office hours: Mon–Fri, 9:00 AM – 5:00 PM</p>
      <div class="social-row social-row-light">
        <a href=""><i class="fa-brands fa-facebook-f"></i></a>
        <a href=""><i class="fa-brands fa-instagram"></i></a>
        <a href=""><i class="fa-brands fa-whatsapp"></i></a>
        <a href=""><i class="fa-brands fa-x-twitter"></i></a>
      </div>
    </div>

    <form id="contactForm" class="form-card reveal" action="<?= SITE_URL ?>/api/message.php" method="POST" data-async="true">
      <input type="hidden" name="source" value="contact">
      <div class="alert form-result is-hidden"></div>
      <div class="form-grid">
        <div class="form-field">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" required>
        </div>
        <div class="form-field">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div class="form-field">
          <label for="phone">Phone (optional)</label>
          <input type="tel" id="phone" name="phone">
        </div>
        <div class="form-field">
          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" required>
        </div>
        <div class="form-field full">
          <label for="message">Message</label>
          <textarea id="message" name="message" required></textarea>
        </div>
        <div class="form-field full">
          <button type="submit" class="btn btn-primary btn-block">Send Message</button>
        </div>
      </div>
    </form>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="kicker">Partner with us</span>
        <h2>Support children and families in practical ways</h2>
      </div>
    </div>
    <div class="grid">
      <div class="card-ministry reveal">
        <span class="icon"><i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i></span>
        <h3>Financial Donations</h3>
        <p>Contribute towards school fees and scholastic materials, clothing, basic needs, safe water and Bible teaching.</p>
      </div>
      <div class="card-ministry reveal">
        <span class="icon"><i class="fa-solid fa-people-group" aria-hidden="true"></i></span>
        <h3>Organisational Partnerships</h3>
        <p>Churches, businesses and community organisations can partner with YGGC to support children and families in Isingiro.</p>
      </div>
      <div class="card-ministry reveal">
        <span class="icon"><i class="fa-solid fa-box-open" aria-hidden="true"></i></span>
        <h3>Useful Goods and Skills</h3>
        <p>Offer clothing, scholastic materials or other useful goods, or volunteer your skills. <a href="<?= SITE_URL ?>/contact.php">Contact us</a> to discuss what is currently needed.</p>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
