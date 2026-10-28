<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$page_title   = 'About Us';
$page_description = 'Learn how YGGC supports children and families in Isingiro through education, essential needs, safe water and Christian teaching.';
$current_page = 'about';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container white-container">
    <span class="breadcrumb">About Us</span>
    <h1>Care that helps children and families thrive.</h1>
    <p>YGGC serves communities in Isingiro through practical support, education and faith in Christ.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="reveal">
      <span class="kicker">Our purpose</span>
      <h2>Responding to practical needs while nurturing faith.</h2>
      <p>Young Generation Growing In Christ (YGGC) is a Christian charity serving children, young people and families in Isingiro, Uganda. We identify families with children who need support and work to connect them with help through donations and community activities.</p>
      <p>Our support includes school fees and scholastic materials, clothing and other basic needs. We also work to provide safe water for communities and teach children and youth the Bible and faith in Christ through school ministries.</p>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="grid">
      <div class="card-ministry reveal">
        <span class="icon"><i class="fa-solid fa-bullseye fa-lg" aria-hidden="true"></i></span>
        <h3>Our Mission</h3>
        <p>To support children and families in need with education, essential items and safe water, while sharing the Bible and faith in Christ with children and youth.</p>
      </div>
      <div class="card-ministry reveal">
        <span class="icon"><i class="fa-solid fa-eye fa-lg" aria-hidden="true"></i></span>
        <h3>Our Vision</h3>
        <p>Children, young people and families in Isingiro with greater access to learning, basic needs, safe water and the hope of Christ.</p>
      </div>
      <div class="card-ministry reveal">
        <span class="icon"><i class="fa-solid fa-handshake fa-lg" aria-hidden="true"></i></span>
        <h3>How We Serve</h3>
        <p>We listen to families, identify needs, and use donated funds and goods to support children and communities with care and respect.</p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="kicker">Our work</span>
        <h2>Support for everyday needs and lasting hope</h2>
      </div>
    </div>
    <div class="grid">
      <div class="card-ministry reveal">
        <h3>Education</h3>
        <p>We help children with school fees and scholastic materials so that financial hardship is less likely to keep them from learning.</p>
      </div>
      <div class="card-ministry reveal">
        <h3>Family and Community Support</h3>
        <p>We identify families with children in need and provide clothing, basic necessities and safe-water support where donations allow.</p>
      </div>
      <div class="card-ministry reveal">
        <h3>Faith and School Ministries</h3>
        <p>We teach children and youth about the Bible and faith in Christ through school ministries and community activities.</p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
