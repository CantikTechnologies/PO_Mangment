<?php
// Determine active route relative to project root
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
$base = str_replace($docRoot, '', $projectRoot);

$activeClass = function(array $needles) use ($request_uri): string {
  foreach ($needles as $needle) {
    if (strpos($request_uri, $needle) !== false) {
      return 'active';
    }
  }
  return '';
};
?>
<link rel="stylesheet" href="<?= $base ?>/assets/style2.css?v=<?php echo time(); ?>">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<header class="navbar">
  <div class="container">
    <div class="logo">
      <a href="<?= $base ?>/index.php" class="brand">
        <img src="<?= $base ?>/assets/cantik_logo.png" alt="Cantik Homemade Logo">
      </a>
    </div>

    <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
      <i class="fas fa-bars"></i>
    </button>

    <nav class="nav-links" id="nav-links">
      <?php $dashActive = $activeClass(["$base/index.php", "$base/"]); ?>
      <a href="<?= $base ?>/index.php" class="<?= $dashActive ?>" <?= $dashActive ? 'aria-current="page"' : '' ?>>
        <i class="fas fa-chart-line"></i>
        Dashboard
      </a>

      <?php $poActive = $activeClass(["$base/po_details/"]); ?>
      <div class="dropdown">
        <a href="<?= $base ?>/po_details/list.php" class="drop-link <?= $poActive ?>">
          <i class="fas fa-file-text"></i>
          PO Details
          <i class="fas fa-chevron-down"></i>
        </a>
        <div class="dropdown-menu">
          <a href="<?= $base ?>/po_details/list.php">
            <i class="fas fa-list"></i>
            View POs
          </a>
          <a href="<?= $base ?>/po_details/add.php">
            <i class="fas fa-plus"></i>
            Add New PO
          </a>
        </div>
      </div>

      <?php $invActive = $activeClass(["$base/invoices/"]); ?>
      <div class="dropdown">
        <a href="<?= $base ?>/invoices/list.php" class="drop-link <?= $invActive ?>">
          <i class="fas fa-receipt"></i>
          Invoices
          <i class="fas fa-chevron-down"></i>
        </a>
        <div class="dropdown-menu">
          <a href="<?= $base ?>/invoices/list.php">
            <i class="fas fa-list"></i>
            View Invoices
          </a>
          <a href="<?= $base ?>/invoices/add.php">
            <i class="fas fa-plus"></i>
            Add New Invoice
          </a>
        </div>
      </div>

      <?php $outActive = $activeClass(["$base/outsourcing/"]); ?>
      <div class="dropdown">
        <a href="<?= $base ?>/outsourcing/list.php" class="drop-link <?= $outActive ?>">
          <i class="fas fa-users"></i>
          Outsourcing
          <i class="fas fa-chevron-down"></i>
        </a>
        <div class="dropdown-menu">
          <a href="<?= $base ?>/outsourcing/list.php">
            <i class="fas fa-list"></i>
            View Records
          </a>
          <a href="<?= $base ?>/outsourcing/add.php">
            <i class="fas fa-plus"></i>
            Add New Record
          </a>
        </div>
      </div>

      <?php $soActive = $activeClass(["$base/so_form.php"]); ?>
      <a href="<?= $base ?>/so_form.php" class="<?= $soActive ?>" <?= $soActive ? 'aria-current="page"' : '' ?>>
        <i class="fas fa-cog"></i>
        SO Form
      </a>
    </nav>

    <div class="nav-actions">
      <a href="<?= $base ?>/1Login_signuppage/logout.php" class="btn-logout">
        <i class="fas fa-sign-out-alt"></i>
        Logout
      </a>
    </div>
  </div>
</header>

<script>
  // Mobile navigation toggle
  const navToggle = document.getElementById('nav-toggle');
  const navLinks = document.getElementById('nav-links');
  
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
    });
  }

  // Close mobile menu when clicking outside
  document.addEventListener('click', (e) => {
    if (!navToggle.contains(e.target) && !navLinks.contains(e.target)) {
      navLinks.classList.remove('open');
    }
  });
</script>