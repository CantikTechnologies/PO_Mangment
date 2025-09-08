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
<link rel="stylesheet" href="<?= $base ?>/shared/nav.css?v=<?php echo time(); ?>">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<header class="navbar">
  <div class="logo">
    <a href="<?= $base ?>/index.php" class="brand">
      <img src="<?= $base ?>/assets/cantik_logo.png" alt="Cantik Homemade Logo" width="120">
    </a>
  </div>

  <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
    <i class="fas fa-bars"></i>
  </button>

  <nav class="nav-links" id="nav-links">
    <?php $dashActive = $activeClass(["$base/index.php"]); ?>
    <a href="<?= $base ?>/index.php" class="<?= $dashActive ?>" <?= $dashActive ? 'aria-current="page"' : '' ?>>Dashboard</a>

    <?php $poActive = $activeClass(["$base/po_details/"]); ?>
    <div class="dropdown">
      <a href="<?= $base ?>/po_details/list.php" class="drop-link <?= $poActive ?>">PO Details</a>
      <div class="dropdown-menu">
        <a href="<?= $base ?>/po_details/list.php">View POs</a>
        <a href="<?= $base ?>/po_details/add.php">Add New PO</a>
      </div>
    </div>

    <?php $invActive = $activeClass(["$base/invoices/"]); ?>
    <div class="dropdown">
      <a href="<?= $base ?>/invoices/list.php" class="drop-link <?= $invActive ?>">Invoices</a>
      <div class="dropdown-menu">
        <a href="<?= $base ?>/invoices/list.php">View Invoices</a>
        <a href="<?= $base ?>/invoices/add.php">Add New Invoice</a>
      </div>
    </div>

    <?php $outActive = $activeClass(["$base/outsourcing/"]); ?>
    <div class="dropdown">
      <a href="<?= $base ?>/outsourcing/list.php" class="drop-link <?= $outActive ?>">Outsourcing</a>
      <div class="dropdown-menu">
        <a href="<?= $base ?>/outsourcing/list.php">View Records</a>
        <a href="<?= $base ?>/outsourcing/add.php">Add New Record</a>
      </div>
    </div>

    <?php $trkActive = $activeClass(["$base/Tracker%20Updates/"]); ?>
    <div class="dropdown">
      <a href="<?= $base ?>/Tracker%20Updates/index.php" class="drop-link <?= $trkActive ?>">Tracker Updates</a>
      <div class="dropdown-menu">
        <a href="<?= $base ?>/Tracker%20Updates/index.php">View Updates</a>
        <a href="<?= $base ?>/Tracker%20Updates/index.php#trackerFormModal" class="nav-open-tracker-modal">Add New Task</a>
      </div>
    </div>

    <?php $soActive = $activeClass(["$base/so_form.php"]); ?>
    <a href="<?= $base ?>/so_form.php" class="<?= $soActive ?>" <?= $soActive ? 'aria-current="page"' : '' ?>>SO Form</a>
  </nav>

  <div class="nav-actions">
    <a href="<?= $base ?>/1Login_signuppage/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</header>

<script>
  const navToggle = document.getElementById('nav-toggle');
  const navLinks = document.getElementById('nav-links');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
    });
  }

  // Open Tracker modal immediately if already on the Tracker Updates page
  document.addEventListener('click', function(e) {
    const link = e.target.closest && e.target.closest('a.nav-open-tracker-modal');
    if (!link) return;
    try {
      const onTrackerPage = /\/Tracker%20Updates\//.test(window.location.pathname);
      if (onTrackerPage) {
        e.preventDefault();
        if (window.location.hash !== '#trackerFormModal') {
          history.replaceState(null, '', '#trackerFormModal');
        }
        const modal = document.getElementById('trackerFormModal');
        if (modal) {
          modal.style.display = 'block';
          document.body.style.overflow = 'hidden';
        }
      }
    } catch (_) {}
  });
</script>
