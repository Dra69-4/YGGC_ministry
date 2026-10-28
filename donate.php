<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/payment.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();
$campaign = get_campaign_goal($pdo);
$manual_momo_currencies = MANUAL_MOMO_NUMBER !== ''
    ? array_values(array_intersect(['USD', 'UGX'], MANUAL_MOMO_CURRENCIES))
    : [];
$manual_bank_currencies = MANUAL_BANK_DETAILS !== ''
    ? array_values(array_intersect(['USD', 'UGX'], MANUAL_BANK_CURRENCIES))
    : [];
$available_currencies = array_unique(array_merge($manual_momo_currencies, $manual_bank_currencies));

$page_title   = 'Donate';
$page_description = 'Donate to YGGC to support children and families in Isingiro with education, basic needs, clothing, safe water and Christian teaching.';
$current_page = 'donate';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container white-container">
    <span class="breadcrumb">Donate</span>
    <h1>Give children and families a helping hand.</h1>
    <p>Your donation helps YGGC support children and families in Isingiro with school fees, scholastic materials, clothing, basic needs, safe water and Bible teaching.</p>
  </div>
</section>

<?php if ($campaign): ?>
<section class="section-tight">
  <div class="container">
    <div class="campaign-card reveal">
      <div class="campaign-head">
        <h3><?= e($campaign['title']) ?></h3>
        <span class="campaign-percent"><?= (int) $campaign['percent'] ?>%</span>
      </div>
      <div class="thermometer-track">
        <div class="thermometer-fill thermo-w-<?= thermo_step((float) $campaign['percent']) ?>"></div>
      </div>
      <div class="campaign-figures">
        <span><strong><?= format_money((float) $campaign['raised'], $campaign['currency']) ?></strong> raised</span>
        <span>Goal: <strong><?= format_money((float) $campaign['goal_amount'], $campaign['currency']) ?></strong></span>
        <?php if ($campaign['deadline']): ?><span>By <strong><?= (new DateTime($campaign['deadline']))->format('j M Y') ?></strong></span><?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container two-col two-col-top">
    <div class="reveal">
      <h2>Choose what you would like to support</h2>
      <p>Every gift contributes to YGGC's work with children, youth and families in Isingiro. Select a focus area when you make your donation.</p>
      <ul class="stack-list-spaced">
        <li><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> School fees and scholastic materials</li>
        <li><i class="fa-solid fa-shirt" aria-hidden="true"></i> Clothing and basic needs for children and families</li>
        <li><i class="fa-solid fa-droplet" aria-hidden="true"></i> Safe water initiatives for communities</li>
        <li><i class="fa-solid fa-book-bible" aria-hidden="true"></i> Bible teaching for children and youth</li>
      </ul>

      <div class="trust-strip">
        <div class="trust-item"><i class="fa-solid fa-lock-open"></i><span>Every gift is logged securely and matched to a reference number</span></div>
        <div class="trust-item"><i class="fa-solid fa-check"></i><span>Confirmed by our finance team before it's marked complete</span></div>
        <div class="trust-item"><i class="fa-solid fa-receipt"></i><span>Request a receipt anytime by emailing us your reference</span></div>
      </div>

      <p class="mt-20">Prefer to give directly?</p>
      <?php if ($manual_momo_currencies || $manual_bank_currencies): ?>
        <p class="text-muted-note">
          <?php if ($manual_momo_currencies): ?><i class="fa-solid fa-mobile-retro"></i> <?= e(MANUAL_MOMO_NUMBER) ?><br><?php endif; ?>
          <?php if ($manual_bank_currencies): ?><i class="fa-solid fa-building-columns"></i> <?= e(MANUAL_BANK_DETAILS) ?><?php endif; ?>
        </p>
      <?php else: ?>
        <p class="text-muted-note">Online donations are not available yet. Please <a href="<?= SITE_URL ?>/contact.php">contact the ministry</a> to arrange a donation.</p>
      <?php endif; ?>
    </div>

    <form id="donateForm" class="form-card reveal" action="<?= SITE_URL ?>/api/donate.php" method="POST">
      <div class="alert is-hidden" id="donateResult"></div>

      <div class="form-field full amount-field">
        <label>How often would you like to give?</label>
        <div class="frequency-toggle">
          <button type="button" class="selected" data-frequency="one_time">One-Time Gift</button>
          <button type="button" data-frequency="monthly">Monthly<span class="freq-sub">Become a recurring partner</span></button>
        </div>
        <input type="hidden" id="frequency" name="frequency" value="one_time">
      </div>

      <div class="form-field full amount-field">
        <label id="amountChoicesLabel">Choose an amount (USD)</label>
        <div class="amount-choices">
          <button type="button" data-amount="20">$20</button>
          <button type="button" data-amount="50">$50</button>
          <button type="button" data-amount="100">$100</button>
          <button type="button" data-amount="350">$350</button>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-field">
          <label for="amount">Amount</label>
          <input type="number" id="amount" name="amount" min="5" max="9999999999.99" step="any" required>
        </div>
        <div class="form-field">
          <label for="currency">Currency</label>
          <select id="currency" name="currency">
            <option value="USD" <?= in_array('USD', $available_currencies, true) ? '' : 'disabled' ?>>USD — US Dollar</option>
            <option value="UGX" <?= in_array('UGX', $available_currencies, true) ? '' : 'disabled' ?>>UGX — Ugandan Shilling</option>
          </select>
        </div>
        <div class="form-field">
          <label for="fund">Give Towards</label>
          <select id="fund" name="fund">
            <option>General Fund</option>
            <option>School Fees and Scholastic Materials</option>
            <option>Clothing and Basic Needs</option>
            <option>Safe Water</option>
            <option>Bible and School Ministries</option>
          </select>
        </div>
        <div class="form-field">
          <label for="payment_method">Payment Method</label>
          <select id="payment_method" name="payment_method" required>
            <option value="" selected disabled>Choose a payment method</option>
            <option value="mobile_money" data-currencies="<?= e(implode(',', $manual_momo_currencies)) ?>" <?= !$manual_momo_currencies ? 'disabled' : '' ?>>Mobile Money<?= !$manual_momo_currencies ? ' (not configured)' : '' ?></option>
            <option value="bank_transfer" data-currencies="<?= e(implode(',', $manual_bank_currencies)) ?>" <?= !$manual_bank_currencies ? 'disabled' : '' ?>>Bank Transfer<?= !$manual_bank_currencies ? ' (not configured)' : '' ?></option>
          </select>
        </div>
        <div class="form-field">
          <label for="donor_name">Full Name</label>
          <input type="text" id="donor_name" name="donor_name" required>
        </div>
        <div class="form-field">
          <label for="donor_email">Email Address</label>
          <input type="email" id="donor_email" name="donor_email" required>
        </div>
        <div class="form-field">
          <label for="donor_phone">Phone Number</label>
          <input type="tel" id="donor_phone" name="donor_phone" placeholder="Optional">
        </div>
        <div class="form-field full">
          <label for="message">Message (optional)</label>
          <textarea id="message" name="message" placeholder="A word of encouragement or a prayer request..."></textarea>
        </div>
        <div class="form-field full checkbox-row">
          <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1" class="checkbox-input">
          <label for="is_anonymous">Give anonymously</label>
        </div>
        <div class="form-field full">
          <button type="submit" class="btn btn-primary btn-block" id="donateSubmitBtn" <?= !$available_currencies ? 'disabled' : '' ?>>Give Now</button>
          <p class="field-hint">Your donation is recorded as pending. The ministry will confirm it after verifying the payment.</p>
        </div>
      </div>
    </form>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="kicker">Other ways to give</span>
        <h2>Give in the way that fits you best</h2>
      </div>
    </div>
    <div class="grid">
      <div class="card-ministry reveal">
        <span class="icon"><i class="fa-solid fa-handshake fa-lg"></i></span>
        <h3>Monthly Partnership</h3>
        <p>A monthly gift helps provide steady support for education, essential needs, safe water and Christian teaching.</p>
      </div>
      <div class="card-ministry reveal">
        <span class="icon"><i class="fa-solid fa-church fa-lg"></i></span>
        <h3>Corporate &amp; Church Partnership</h3>
        <p>Churches, businesses and organisations can partner with YGGC to support children and families in Isingiro.</p>
      </div>
      <div class="card-ministry reveal">
        <span class="icon"><i class="fa-solid fa-gift fa-lg"></i></span>
        <h3>In-Kind Giving</h3>
        <p>Clothing, scholastic materials and other useful goods can also help meet family needs. <a href="<?= SITE_URL ?>/contact.php">Contact us</a> to discuss current needs before arranging a donation.</p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
