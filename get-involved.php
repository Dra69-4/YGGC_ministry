<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_title   = 'Get Involved';
$page_description = 'Volunteer or partner with YGGC to support children and families in Isingiro.';
$current_page = 'get-involved';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container white-container">
    <span class="breadcrumb">Get Involved</span>
    <h1>Stand with children and families.</h1>
    <p>Give your time, skills, resources or support to education, essential needs, safe water and Christian teaching in Isingiro.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="grid reveal">
      <div class="card-ministry">
        <span class="icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></span>
        <h3>Education Support</h3>
        <p>Help children access school fees, scholastic materials and encouragement to continue learning.</p>
      </div>
      <div class="card-ministry">
        <span class="icon"><i class="fa-solid fa-book-bible" aria-hidden="true"></i></span>
        <h3>School and Faith Ministry</h3>
        <p>Support Bible teaching and faith activities for children and young people in schools and communities.</p>
      </div>
      <div class="card-ministry">
        <span class="icon"><i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i></span>
        <h3>Family and Community Support</h3>
        <p>Help identify families with children who need support, or contribute clothing and other essential items.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="two-col">
      <div class="reveal">
        <h2>Tell us how you'd like to help</h2>
        <p>Share your interests, skills or the kind of support you can offer. Our team will follow up with next steps.</p>
      </div>
      <form id="volunteerForm" class="form-card reveal" action="<?= SITE_URL ?>/api/message.php" method="POST" data-async="true">
        <input type="hidden" name="source" value="volunteer">
        <div class="alert form-result is-hidden"></div>
        <div class="form-grid">
          <div class="form-field">
            <label for="vname">Full Name</label>
            <input type="text" id="vname" name="name" required>
          </div>
          <div class="form-field">
            <label for="vemail">Email Address</label>
            <input type="email" id="vemail" name="email" required>
          </div>
          <div class="form-field">
            <label for="vphone">Phone Number</label>
            <input type="tel" id="vphone" name="phone">
          </div>
          <div class="form-field">
            <label for="vsubject">Area of Interest</label>
            <select id="vsubject" name="subject" required>
              <option value="">Choose one</option>
              <option>Education Support</option>
              <option>School and Faith Ministry</option>
              <option>Family and Community Support</option>
              <option>Safe Water Support</option>
              <option>Donations and Partnerships</option>
              <option>Other</option>
            </select>
          </div>
          <div class="form-field full">
            <label for="vmessage">Tell us a bit about yourself</label>
            <textarea id="vmessage" name="message" required placeholder="Tell us what kind of support you can offer..."></textarea>
          </div>
          <div class="form-field full">
            <button type="submit" class="btn btn-primary btn-block">Submit Interest</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
