<?php
require_once 'config.php';
check_auth();

$jsonPath = '../content.json';
if (!file_exists($jsonPath)) {
    die("Error: content.json not found in root.");
}

$cms = json_decode(file_get_contents($jsonPath), true);

function delete_old_asset($relativePath) {
    if (empty($relativePath)) return true;
    $fullPath = '../' . $relativePath;
    if (!file_exists($fullPath)) return true;
    return unlink($fullPath);
}

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {
    header('Content-Type: application/json');

    $cms = json_decode(file_get_contents($jsonPath), true);

    if (isset($_POST['update_link'])) {
        $cms['config']['whatsapp_link'] = $_POST['link'];
        file_put_contents($jsonPath, json_encode($cms, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'msg' => 'WhatsApp link updated.']);
        exit;
    }

    if (isset($_POST['update_img'])) {
        $key     = $_POST['img_key'];
        $ext     = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'msg' => 'Invalid file type. Use JPG, PNG, or WEBP.']);
            exit;
        }

        $filename = $key . '_' . time() . '.' . $ext;
        $target   = '../assets/' . $filename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $oldPath    = $cms['images'][$key] ?? '';
            $deleteOk   = delete_old_asset($oldPath);
            $deleteNote = $deleteOk ? '' : ' (old file could not be removed — check folder permissions)';

            $cms['images'][$key] = 'assets/' . $filename;
            file_put_contents($jsonPath, json_encode($cms, JSON_PRETTY_PRINT));

            echo json_encode([
                'success'  => true,
                'msg'      => 'Image replaced.' . $deleteNote,
                'new_path' => '../assets/' . $filename,
            ]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Upload failed. Check that the assets folder is writable.']);
        }
        exit;
    }

    if (isset($_POST['update_grid'])) {
        $grid  = $_POST['grid_name'];
        $index = (int) $_POST['grid_index'];
        $ext   = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        $filename = $grid . '_' . $index . '_' . time() . '.' . $ext;
        $target   = '../assets/' . $filename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $oldPath    = $cms['grids'][$grid][$index] ?? '';
            $deleteOk   = delete_old_asset($oldPath);
            $deleteNote = $deleteOk ? '' : ' (old file could not be removed — check folder permissions)';

            $cms['grids'][$grid][$index] = 'assets/' . $filename;
            file_put_contents($jsonPath, json_encode($cms, JSON_PRETTY_PRINT));

            echo json_encode([
                'success'  => true,
                'msg'      => 'Image updated.' . $deleteNote,
                'new_path' => '../assets/' . $filename,
            ]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Upload failed. Check that the assets folder is writable.']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'msg' => 'Unknown action.']);
    exit;
}

function get_img($cms, $type, $key, $index = null) {
    $path = ($type === 'single') ? ($cms['images'][$key] ?? '') : ($cms['grids'][$key][$index] ?? '');
    return (!empty($path) && file_exists('../' . $path))
        ? '../' . $path
        : 'https://via.placeholder.com/150?text=No+Image';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/naturalbane-icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lora:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Lora', serif; background: #F7F5F2; display: flex; height: 100vh; color: #1a1a1a; }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            background: #1a1a1a;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 40px 0;
            flex-shrink: 0;
        }
        .nav-item {
            padding: 16px 30px;
            cursor: pointer;
            color: #999;
            font-size: 14px;
            border-left: 4px solid transparent;
            transition: 0.2s;
            user-select: none;
        }
        .nav-item:hover, .nav-item.active { color: #fff; background: #222; border-left-color: #C8102E; }
        .logout { margin-top: auto; padding: 20px 30px; color: #C8102E; text-decoration: none; font-weight: 600; font-size: 14px; }

        /* ── Main ── */
        .main { flex: 1; padding: 60px; overflow-y: auto; }
        h1 { font-family: 'Playfair Display', serif; font-size: 32px; margin-bottom: 30px; }
        .card { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); max-width: 900px; }

        /* ── Tabs ── */
        .tab { display: none; }
        .tab.active { display: block; }

        /* ── Form elements ── */
        h2 { font-family: 'Playfair Display', serif; font-size: 22px; margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .field-group { margin-bottom: 40px; }
        label { display: block; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; color: #666; }
        input[type="text"] { width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 4px; margin-bottom: 15px; font-family: 'Lora', serif; font-size: 14px; }
        input[type="text"]:focus { outline: none; border-color: #C8102E; }
        .section-divider { border: none; border-top: 1px solid #eee; margin: 0 0 40px; }

        /* ── Buttons ── */
        .btn {
            background: #C8102E; color: #fff; border: none; padding: 10px 20px;
            border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 13px;
            font-family: 'Lora', serif; transition: opacity 0.2s, transform 0.1s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn:hover { opacity: 0.88; }
        .btn:active { transform: scale(0.97); }
        .btn.loading { opacity: 0.6; pointer-events: none; }
        .btn .spinner { width: 13px; height: 13px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; display: none; }
        .btn.loading .spinner { display: block; }
        .btn.loading .btn-label { opacity: 0.7; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Preview boxes ── */
        .preview-box { width: 120px; height: 80px; background: #f9f9f9; border: 1px solid #eee; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 4px; transition: opacity 0.3s; }
        .preview-box img { max-width: 100%; max-height: 100%; object-fit: cover; }
        .preview-box.wide { width: 200px; height: 120px; }
        .preview-box.round { border-radius: 50%; width: 100px; height: 100px; }
        .preview-box.round img { border-radius: 50%; }
        .preview-box.updating { animation: pulse 1s ease infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }

        /* ── Grid ── */
        .grid-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; }

        /* ── Toast ── */
        #toast-container { position: fixed; bottom: 28px; right: 28px; z-index: 9999; display: flex; flex-direction: column-reverse; gap: 10px; pointer-events: none; }
        .toast { min-width: 260px; max-width: 380px; background: #1a1a1a; color: #fff; border-radius: 6px; padding: 14px 18px; font-size: 14px; display: flex; align-items: center; gap: 12px; box-shadow: 0 6px 24px rgba(0,0,0,0.22); pointer-events: all; animation: toastIn 0.3s cubic-bezier(0.34,1.56,0.64,1) forwards; overflow: hidden; position: relative; }
        .toast.success { border-left: 4px solid #25c36a; }
        .toast.error   { border-left: 4px solid #C8102E; }
        .toast.warn    { border-left: 4px solid #f0a500; }
        .toast-icon { font-size: 18px; flex-shrink: 0; }
        .toast-msg  { flex: 1; line-height: 1.4; }
        .toast-bar { position: absolute; bottom: 0; left: 0; height: 3px; background: rgba(255,255,255,0.25); animation: toastBar 3.5s linear forwards; }
        .toast.success .toast-bar { background: #25c36a; }
        .toast.error   .toast-bar { background: #C8102E; }
        .toast.warn    .toast-bar { background: #f0a500; }
        .toast.leaving { animation: toastOut 0.25s ease forwards; }
        @keyframes toastIn  { from { opacity:0; transform:translateY(16px) scale(0.96); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { from { opacity:1; transform:translateY(0); max-height:100px; } to { opacity:0; transform:translateY(8px); max-height:0; padding:0; margin:0; } }
        @keyframes toastBar { from { width:100%; } to { width:0%; } }

        /* ══════════════════════════════════════════
           RESPONSIVE STYLES
        ══════════════════════════════════════════ */

        /* ── Hamburger button ── */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 1100;
            background: #1a1a1a;
            border: none;
            border-radius: 6px;
            width: 42px;
            height: 42px;
            cursor: pointer;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 0;
            transition: background 0.2s;
        }
        .menu-toggle:hover { background: #333; }
        .menu-toggle span {
            display: block;
            width: 20px;
            height: 2px;
            background: #fff;
            border-radius: 2px;
            transition: transform 0.3s, opacity 0.3s;
            transform-origin: center;
        }
        /* Animate hamburger → X when sidebar is open */
        .menu-toggle.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .menu-toggle.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .menu-toggle.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* Dim overlay behind sidebar on mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 900;
            backdrop-filter: blur(2px);
        }
        .sidebar-overlay.active { display: block; }

        /* ── Tablet (≤ 1024px) ── */
        @media (max-width: 1024px) {
            .sidebar { width: 220px; }
            .main { padding: 40px 30px; }
            .grid-container { grid-template-columns: 1fr; }
        }

        /* ── Mobile (≤ 768px) ── */
        @media (max-width: 768px) {
            body { display: block; }

            .menu-toggle { display: flex; }

            /* Sidebar slides in from the left */
            .sidebar {
                position: fixed;
                top: 0;
                left: -280px;
                width: 260px;
                height: 100%;
                z-index: 1000;
                transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                padding-top: 64px;
                overflow-y: auto;
            }
            .sidebar.open { left: 0; }

            .main {
                padding: 72px 18px 48px;
                min-height: 100vh;
            }

            h1 { font-size: 22px; margin-bottom: 20px; }

            .card { padding: 20px 16px; border-radius: 6px; }

            h2 { font-size: 17px; margin-bottom: 18px; }

            .field-group { margin-bottom: 28px; }

            .grid-container { grid-template-columns: 1fr; gap: 20px; }

            /* Full-width previews on mobile so they're easier to see */
            .preview-box { width: 100%; height: 120px; }
            .preview-box.wide { width: 100%; height: 150px; }
            .preview-box.round { width: 90px; height: 90px; border-radius: 50%; }

            /* Prevent iOS auto-zoom on input focus */
            input[type="text"],
            input[type="file"] { font-size: 16px; }

            .btn { width: 100%; justify-content: center; padding: 12px 20px; }

            /* Toast sits higher so it clears the browser nav bar */
            #toast-container { bottom: 80px; right: 16px; left: 16px; }
            .toast { min-width: unset; max-width: 100%; }
        }
    </style>
</head>
<body>

<!-- Hamburger button (mobile only) -->
<button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- Dim overlay (mobile only) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="nav-item active" onclick="showTab('link', this)">WhatsApp</div>
    <div class="nav-item" onclick="showTab('story', this)">Story Images</div>
    <div class="nav-item" onclick="showTab('author', this)">Author Profile</div>
    <div class="nav-item" onclick="showTab('cases', this)">Case Studies</div>
    <div class="nav-item" onclick="showTab('testimonials', this)">Testimonials</div>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="main">
    <h1>Dashboard</h1>

    <div class="card">

        <!-- ── WhatsApp Tab ── -->
        <div id="link" class="tab active">
            <h2>WhatsApp Settings</h2>

            <div class="field-group">
                <label>WhatsApp Group Link</label>
                <form data-ajax>
                    <input type="hidden" name="update_link" value="1">
                    <input type="text" name="link" value="<?php echo htmlspecialchars($cms['config']['whatsapp_link']); ?>">
                    <button type="submit" class="btn">
                        <span class="spinner"></span>
                        <span class="btn-label">Update Link</span>
                    </button>
                </form>
            </div>

            <hr class="section-divider">

            <div class="field-group">
                <label>Free WhatsApp Class Image</label>
                <div class="preview-box wide" id="preview-class_preview">
                    <img src="<?php echo get_img($cms, 'single', 'class_preview'); ?>">
                </div>
                <form data-ajax data-preview="#preview-class_preview img">
                    <input type="hidden" name="update_img" value="1">
                    <input type="hidden" name="img_key" value="class_preview">
                    <input type="file" name="file" accept="image/*" required>
                    <button type="submit" class="btn" style="margin-top:10px;">
                        <span class="spinner"></span>
                        <span class="btn-label">Replace Image</span>
                    </button>
                </form>
            </div>

            <hr class="section-divider">

            <div class="field-group">
                <label>Program Mockup / Review Image</label>
                <div class="preview-box wide" id="preview-program_mockup">
                    <img src="<?php echo get_img($cms, 'single', 'program_mockup'); ?>">
                </div>
                <form data-ajax data-preview="#preview-program_mockup img">
                    <input type="hidden" name="update_img" value="1">
                    <input type="hidden" name="img_key" value="program_mockup">
                    <input type="file" name="file" accept="image/*" required>
                    <button type="submit" class="btn" style="margin-top:10px;">
                        <span class="spinner"></span>
                        <span class="btn-label">Replace Image</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- ── Story Images Tab ── -->
        <div id="story" class="tab">
            <h2>Story Images</h2>
            <?php
                $storyImages = [
                    'endoscopy' => 'Endoscopy Photo',
                    'surgery_1' => 'Cancer of the Stomach',
                    'surgery_2' => 'Spread of Cancer',
                ];
            ?>
            <?php foreach($storyImages as $k => $label): ?>
                <div class="field-group">
                    <label><?php echo $label; ?></label>
                    <div class="preview-box" id="preview-<?php echo $k; ?>">
                        <img src="<?php echo get_img($cms, 'single', $k); ?>">
                    </div>
                    <form data-ajax data-preview="#preview-<?php echo $k; ?> img">
                        <input type="hidden" name="update_img" value="1">
                        <input type="hidden" name="img_key" value="<?php echo $k; ?>">
                        <input type="file" name="file" accept="image/*" required>
                        <button type="submit" class="btn">
                            <span class="spinner"></span>
                            <span class="btn-label">Replace</span>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Author Profile Tab ── -->
        <div id="author" class="tab">
            <h2>Profile Management</h2>
            <div class="field-group">
                <label>Michael Toyin Headshot</label>
                <div class="preview-box round" id="preview-michael_toyin">
                    <img src="<?php echo get_img($cms, 'single', 'michael_toyin'); ?>">
                </div>
                <form data-ajax data-preview="#preview-michael_toyin img">
                    <input type="hidden" name="update_img" value="1">
                    <input type="hidden" name="img_key" value="michael_toyin">
                    <input type="file" name="file" accept="image/*" required>
                    <button type="submit" class="btn">
                        <span class="spinner"></span>
                        <span class="btn-label">Update Photo</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- ── Case Studies Tab ── -->
        <div id="cases" class="tab">
            <h2>Case Studies</h2>
            <div class="grid-container">
                <?php foreach($cms['grids']['cases'] as $i => $p): ?>
                    <div class="field-group">
                        <label>Slot <?php echo $i + 1; ?></label>
                        <div class="preview-box" id="preview-case-<?php echo $i; ?>">
                            <img src="<?php echo get_img($cms, 'grid', 'cases', $i); ?>">
                        </div>
                        <form data-ajax data-preview="#preview-case-<?php echo $i; ?> img">
                            <input type="hidden" name="update_grid" value="1">
                            <input type="hidden" name="grid_name" value="cases">
                            <input type="hidden" name="grid_index" value="<?php echo $i; ?>">
                            <input type="file" name="file" accept="image/*" required>
                            <button type="submit" class="btn">
                                <span class="spinner"></span>
                                <span class="btn-label">Update</span>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── Testimonials Tab ── -->
        <div id="testimonials" class="tab">
            <h2>Testimonials</h2>
            <div class="grid-container">
                <?php foreach($cms['grids']['testimonials'] as $i => $p): ?>
                    <div class="field-group">
                        <label>Testimonial <?php echo $i + 1; ?></label>
                        <div class="preview-box" id="preview-testimonial-<?php echo $i; ?>">
                            <img src="<?php echo get_img($cms, 'grid', 'testimonials', $i); ?>">
                        </div>
                        <form data-ajax data-preview="#preview-testimonial-<?php echo $i; ?> img">
                            <input type="hidden" name="update_grid" value="1">
                            <input type="hidden" name="grid_name" value="testimonials">
                            <input type="hidden" name="grid_index" value="<?php echo $i; ?>">
                            <input type="file" name="file" accept="image/*" required>
                            <button type="submit" class="btn">
                                <span class="spinner"></span>
                                <span class="btn-label">Update</span>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div><!-- /.card -->
</div><!-- /.main -->

<div id="toast-container"></div>

<script>
    // ── Tab switching ──
    function showTab(id, clickedNav) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        clickedNav.classList.add('active');

        // Auto-close sidebar on mobile after picking a tab
        if (window.innerWidth <= 768) toggleSidebar();
    }

    // ── Mobile sidebar toggle ──
    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const toggle   = document.getElementById('menuToggle');
        const overlay  = document.getElementById('sidebarOverlay');
        const isOpen   = sidebar.classList.contains('open');

        sidebar.classList.toggle('open', !isOpen);
        toggle.classList.toggle('open', !isOpen);
        overlay.classList.toggle('active', !isOpen);

        // Lock body scroll when sidebar is open
        document.body.style.overflow = isOpen ? '' : 'hidden';
    }

    // ── Toast ──
    function showToast(msg, type) {
        const icons     = { success: '✓', error: '✕', warn: '⚠' };
        const container = document.getElementById('toast-container');
        const toast     = document.createElement('div');

        toast.className = 'toast ' + type;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || '✓'}</span>
            <span class="toast-msg">${msg}</span>
            <div class="toast-bar"></div>
        `;
        container.appendChild(toast);

        const remove = () => {
            toast.classList.add('leaving');
            toast.addEventListener('animationend', () => toast.remove(), { once: true });
        };
        setTimeout(remove, 3500);
    }

    // ── AJAX form handler ──
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn        = this.querySelector('.btn');
            const previewSel = this.dataset.preview;
            const previewImg = previewSel ? document.querySelector(previewSel) : null;
            const previewBox = previewImg ? previewImg.closest('.preview-box') : null;

            btn.classList.add('loading');
            if (previewBox) previewBox.classList.add('updating');

            try {
                const res  = await fetch('dashboard.php', {
                    method:  'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body:    new FormData(this),
                });
                const data = await res.json();

                if (data.new_path && previewImg) {
                    previewImg.src = data.new_path + '?t=' + Date.now();
                }

                const hasWarning = data.success && data.msg.includes('could not be removed');
                const toastType  = !data.success ? 'error' : hasWarning ? 'warn' : 'success';
                showToast(data.msg, toastType);

                const fileInput = this.querySelector('input[type="file"]');
                if (fileInput) fileInput.value = '';

            } catch (err) {
                showToast('Connection error. Please try again.', 'error');
            } finally {
                btn.classList.remove('loading');
                if (previewBox) previewBox.classList.remove('updating');
            }
        });
    });
</script>

</body>
</html>