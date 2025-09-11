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

<header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-gray-200 bg-white px-10 py-3">
  <div class="flex items-center gap-4 text-gray-900">
    <a href="<?= $base ?>/index.php" class="size-8 text-rose-500 inline-flex items-center justify-center">
      <img src="<?= $base ?>/assets/cantik_logo.png" alt="Cantik Homemade" class="h-8 w-auto" />
    </a>
    <h2 class="text-gray-900 text-xl font-bold leading-tight tracking-[-0.015em]">Cantik</h2>
  </div>
  <div class="flex flex-1 items-center justify-end gap-4">
    <nav class="hidden md:flex items-center gap-2">
      <?php $dashActive = $activeClass(["$base/index.php", "$base/"]); ?>
      <a class="rounded-full px-4 py-2 text-sm font-medium <?= $dashActive ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/index.php">Dashboard</a>
      
      <?php $poActive = $activeClass(["$base/po_details/"]); ?>
      <div class="relative group">
        <a class="rounded-full px-4 py-2 text-sm font-medium <?= $poActive ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/po_details/list.php">
          Purchase Orders
          <span class="material-symbols-outlined text-sm ml-1">expand_more</span>
        </a>
        <div class="absolute top-full left-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
          <a href="<?= $base ?>/po_details/list.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">View All POs</a>
          <?php if (hasPermission('add_po_details')): ?>
          <a href="<?= $base ?>/po_details/add.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Add New PO</a>
          <?php endif; ?>
        </div>
      </div>
      
      <?php $invActive = $activeClass(["$base/invoices/"]); ?>
      <div class="relative group">
        <a class="rounded-full px-4 py-2 text-sm font-medium <?= $invActive ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/invoices/list.php">
          Invoices
          <span class="material-symbols-outlined text-sm ml-1">expand_more</span>
        </a>
        <div class="absolute top-full left-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
          <a href="<?= $base ?>/invoices/list.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">View All Invoices</a>
          <?php if (hasPermission('add_invoices')): ?>
          <a href="<?= $base ?>/invoices/add.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Add New Invoice</a>
          <?php endif; ?>
        </div>
      </div>
      
      <?php $outActive = $activeClass(["$base/outsourcing/"]); ?>
      <div class="relative group">
        <a class="rounded-full px-4 py-2 text-sm font-medium <?= $outActive ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/outsourcing/list.php">
          Outsourcing
          <span class="material-symbols-outlined text-sm ml-1">expand_more</span>
        </a>
        <div class="absolute top-full left-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
          <a href="<?= $base ?>/outsourcing/list.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">View All Records</a>
          <?php if (hasPermission('add_outsourcing')): ?>
          <a href="<?= $base ?>/outsourcing/add.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Add New Record</a>
          <?php endif; ?>
        </div>
      </div>
      
      <?php $soActive = $activeClass(["$base/so_form.php"]); ?>
      <a class="rounded-full px-4 py-2 text-sm font-medium <?= $soActive ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/so_form.php">Reports</a>
      
      <?php $trackerActive = $activeClass(["$base/Tracker%20Updates/"]); ?>
      <div class="relative group">
        <a class="rounded-full px-4 py-2 text-sm font-medium <?= $trackerActive ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/Tracker%20Updates/index.php">
          Tracker Updates
          <span class="material-symbols-outlined text-sm ml-1">expand_more</span>
        </a>
        <div class="absolute top-full left-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
          <a href="<?= $base ?>/Tracker%20Updates/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">View All Tasks</a>
          <a href="<?= $base ?>/Tracker%20Updates/add.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Add New Task</a>
        </div>
      </div>
    </nav>
    <div class="flex items-center gap-2">
      <div class="relative">
        <button id="userMenuButton" class="flex size-10 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500" aria-haspopup="true" aria-expanded="false">
          <?php 
          try {
            $user = getCurrentUser();
            if ($user && isset($user['profile_picture']) && $user['profile_picture'] && file_exists($user['profile_picture'])): 
          ?>
            <img src="<?= htmlspecialchars($user['profile_picture']) ?>" 
                 alt="Profile" 
                 class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
          <?php else: ?>
            <span class="material-symbols-outlined text-2xl"> account_circle </span>
          <?php 
            endif;
          } catch (Exception $e) {
          ?>
            <span class="material-symbols-outlined text-2xl"> account_circle </span>
          <?php } ?>
        </button>
        <div id="userMenu" class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl border border-gray-200 bg-white py-2 shadow-lg hidden z-50" role="menu" aria-labelledby="userMenuButton">
          <div class="px-4 py-2 border-b border-gray-100">
            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['username']) ?> <?= htmlspecialchars($_SESSION['last_name'] ?? '') ?></p>
            <p class="text-xs text-gray-500 capitalize"><?= htmlspecialchars($_SESSION['role'] ?? 'employee') ?></p>
            <?php if (isset($_SESSION['department'])): ?>
            <p class="text-xs text-gray-500"><?= htmlspecialchars($_SESSION['department']) ?></p>
            <?php endif; ?>
          </div>
          <a href="<?= $base ?>/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
            <span class="material-symbols-outlined text-sm mr-2">person</span>My Profile
          </a>
          <a href="<?= $base ?>/upload_profile_image.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
            <span class="material-symbols-outlined text-sm mr-2">photo_camera</span>Upload Photo
          </a>
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <a href="<?= $base ?>/admin/users.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
            <span class="material-symbols-outlined text-sm mr-2">admin_panel_settings</span>Manage Users
          </a>
          <a href="<?= $base ?>/admin/audit_log.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
            <span class="material-symbols-outlined text-sm mr-2">history</span>Audit Log
          </a>
          <?php endif; ?>
          <a href="<?= $base ?>/1Login_signuppage/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
            <span class="material-symbols-outlined text-sm mr-2">logout</span>Logout
          </a>
        </div>
      </div>
    </div>
  </div>
</header>

<script>
  (function(){
    const btn = document.getElementById('userMenuButton');
    const menu = document.getElementById('userMenu');
    if (!btn || !menu) return;
    function closeMenu(){ menu.classList.add('hidden'); btn.setAttribute('aria-expanded','false'); }
    function toggleMenu(){ menu.classList.toggle('hidden'); btn.setAttribute('aria-expanded', menu.classList.contains('hidden') ? 'false' : 'true'); }
    btn.addEventListener('click', function(e){ e.stopPropagation(); toggleMenu(); });
    document.addEventListener('click', function(e){ if (!menu.contains(e.target) && e.target !== btn) { closeMenu(); } });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeMenu(); });
  })();
</script>