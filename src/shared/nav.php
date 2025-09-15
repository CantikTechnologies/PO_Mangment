<?php
// Robust project base path resolver (works from any depth)
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/../../') ?: ''), '/');
$base = str_replace($docRoot, '', $projectRoot);
if ($base === '' || $base[0] !== '/') { $base = '/' . ltrim($base, '/'); }

// Active route helpers
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$current_file = basename($_SERVER['SCRIPT_NAME'] ?? '');
$current_dir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$isReportsPage = ($current_file === 'so_form.php');

$activeClass = function(array $needles) use ($request_uri, $current_file, $current_dir): string {
  foreach ($needles as $needle) {
    if ($current_file === $needle) { return 'active'; }
    if ($needle !== '' && strpos($current_dir, $needle) !== false) { return 'active'; }
    if ($needle !== '' && strpos($request_uri, $needle) !== false) { return 'active'; }
  }
  return '';
};
?>

<header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-gray-200 bg-white px-10 py-3">
  <div class="flex items-center gap-3 text-gray-900">
    <a href="<?= $base ?>/index.php" aria-label="Cantik Homemade" class="inline-flex items-center justify-center">
      <span class="inline-flex p-[2px] rounded-full bg-gradient-to-br from-rose-500 via-fuchsia-500 to-indigo-500 shadow-sm ring-1 ring-rose-200/50">
        <span class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-white">
          <img src="<?= $base ?>/assets/cantik_logo.png" alt="Cantik Homemade" class="h-7 w-7 object-contain"/>
        </span>
      </span>
    </a>
    <div class="leading-tight">
      <div class="text-xl font-extrabold tracking-[-0.02em] bg-gradient-to-r from-gray-900 via-rose-700 to-fuchsia-600 bg-clip-text text-transparent">Cantik</div>
      <div class="text-[11px] uppercase tracking-[0.18em] text-gray-500">PO Management</div>
    </div>
  </div>
  <div class="flex flex-1 items-center justify-end gap-4">
    <nav class="hidden md:flex items-center gap-2">
      <?php 
        // Dashboard should be active only for the project's root index.php
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $isRoot = ($scriptName === ($base . '/index.php'));
      ?>
      <a class="rounded-full px-4 py-2 text-sm font-medium <?= $isRoot ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/index.php">Dashboard</a>
      
      <?php $poActive = !$isReportsPage && $activeClass(["po_details"]); ?>
      <div class="relative group">
        <a class="rounded-full px-4 py-2 text-sm font-medium <?= $poActive ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/src/Modules/po_details/list.php">
          Purchase Orders
          <span class="material-symbols-outlined text-sm ml-1">expand_more</span>
        </a>
        <div class="absolute top-full left-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
          <a href="<?= $base ?>/src/Modules/po_details/list.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">View All POs</a>
          <?php if (hasPermission('add_po_details')): ?>
          <a href="<?= $base ?>/src/Modules/po_details/add.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Add New PO</a>
          <?php endif; ?>
        </div>
      </div>
      
      <?php $invActive = !$isReportsPage && $activeClass(["invoices"]); ?>
      <div class="relative group">
        <a class="rounded-full px-4 py-2 text-sm font-medium <?= $invActive ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/src/Modules/invoices/list.php">
          Invoices
          <span class="material-symbols-outlined text-sm ml-1">expand_more</span>
        </a>
        <div class="absolute top-full left-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
          <a href="<?= $base ?>/src/Modules/invoices/list.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">View All Invoices</a>
          <?php if (hasPermission('add_invoices')): ?>
          <a href="<?= $base ?>/src/Modules/invoices/add.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Add New Invoice</a>
          <?php endif; ?>
        </div>
      </div>
      
      <?php $outActive = !$isReportsPage && $activeClass(["outsourcing"]); ?>
      <div class="relative group">
        <a class="rounded-full px-4 py-2 text-sm font-medium <?= $outActive ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/src/Modules/outsourcing/list.php">
          Outsourcing
          <span class="material-symbols-outlined text-sm ml-1">expand_more</span>
        </a>
        <div class="absolute top-full left-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
          <a href="<?= $base ?>/src/Modules/outsourcing/list.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">View All Records</a>
          <?php if (hasPermission('add_outsourcing')): ?>
          <a href="<?= $base ?>/src/Modules/outsourcing/add.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Add New Record</a>
          <?php endif; ?>
        </div>
      </div>
      
      <?php $soActive = $isReportsPage || $activeClass(["so_form.php"]); ?>
      <a class="rounded-full px-4 py-2 text-sm font-medium <?= $soActive ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/so_form.php">Reports</a>
      
      <?php $trackerActive = !$isReportsPage && $activeClass(["Tracker"]); ?>
      <div class="relative group">
          <a class="rounded-full px-4 py-2 text-sm font-medium <?= $trackerActive ? 'text-rose-600 bg-rose-50' : 'text-gray-700 hover:bg-gray-100' ?>" href="<?= $base ?>/src/Modules/Tracker/index.php">
          Tracker Updates
          <span class="material-symbols-outlined text-sm ml-1">expand_more</span>
        </a>
        <div class="absolute top-full left-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
          <a href="<?= $base ?>/src/Modules/Tracker/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">View All Tasks</a>
          <a href="<?= $base ?>/src/Modules/Tracker/add.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Add New Task</a>
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
          <a href="<?= $base ?>/src/Modules/User/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
            <span class="material-symbols-outlined text-sm mr-2">person</span>My Profile
          </a>
          <a href="<?= $base ?>/src/Modules/User/upload_profile_image.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
            <span class="material-symbols-outlined text-sm mr-2">photo_camera</span>Upload Photo
          </a>
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <a href="<?= $base ?>/src/Modules/admin/users.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
            <span class="material-symbols-outlined text-sm mr-2">admin_panel_settings</span>Manage Users
          </a>
          <a href="<?= $base ?>/src/Modules/admin/audit_log.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
            <span class="material-symbols-outlined text-sm mr-2">history</span>Audit Log
          </a>
          <?php endif; ?>
          <a href="<?= $base ?>/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
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