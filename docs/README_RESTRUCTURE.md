This repository is being restructured to the following layout:

public/
  index.php
  so_form.php
  assets/
    cantik_logo.png
    style.css
    script.js
app/
  config/
    db.php
    auth.php
  shared/
    nav.php
    nav.css
  modules/
    po_details/
      add.php, edit.php, delete.php, list.php, get_po.php
    invoices/
      add.php, edit.php, delete.php, list.php
    outsourcing/
      add.php, edit.php, delete.php, list.php
    tracker/
      index.php, add.php, edit.php, view.php, delete.php
    admin/
      users.php, audit_log.php
  user/
    profile.php
    upload_profile_image.php
  auth/
    login.php
    logout.php
database/
  po_management.sql
  user_roles_setup.sql
storage/
  uploads/
    profile_images/
docs/
  ROLE_SYSTEM_README.md

This commit seeds the directory tree and will be followed by file moves/updates.

admin pass - admin@123.com
pass-:12345

