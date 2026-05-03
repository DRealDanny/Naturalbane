<?php
require_once 'config.php';
check_auth();

$jsonPath = '../content.json';
if (!file_exists($jsonPath)) {
    die("Error: content.json not found in root.");
}

$cms = json_decode(file_get_contents($jsonPath), true);
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Update WhatsApp Link
    if (isset($_POST['update_link'])) {
        $cms['config']['whatsapp_link'] = $_POST['link'];
        file_put_contents($jsonPath, json_encode($cms, JSON_PRETTY_PRINT));
        $status = "Link updated successfully.";
    }

    // 2. Update Single Image (Story, Profile, etc)
    if (isset($_POST['update_img'])) {
        $key = $_POST['img_key'];
        $filename = $key . "_" . time() . "." . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $target = "../assets/" . $filename;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            if (isset($cms['images'][$key]) && file_exists("../" . $cms['images'][$key])) { @unlink("../" . $cms['images'][$key]); }
            $cms['images'][$key] = "assets/" . $filename;
            file_put_contents($jsonPath, json_encode($cms, JSON_PRETTY_PRINT));
            $status = "Image replaced.";
        }
    }

    // 3. Update Grid Image (Cases or Testimonials)
    if (isset($_POST['update_grid'])) {
        $grid = $_POST['grid_name'];
        $index = $_POST['grid_index'];
        $filename = $grid . "_" . $index . "_" . time() . "." . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $target = "../assets/" . $filename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            if (isset($cms['grids'][$grid][$index]) && file_exists("../" . $cms['grids'][$grid][$index])) {
                @unlink("../" . $cms['grids'][$grid][$index]);
            }
            $cms['grids'][$grid][$index] = "assets/" . $filename;
            file_put_contents($jsonPath, json_encode($cms, JSON_PRETTY_PRINT));
            $status = "Grid item updated.";
        }
    }
}

// Safety function for previews to prevent path errors
function get_img($cms, $type, $key, $index = null) {
    if ($type === 'single') {
        $path = $cms['images'][$key] ?? '';
    } else {
        $path = $cms['grids'][$key][$index] ?? '';
    }
    return (!empty($path) && file_exists("../" . $path)) ? "../" . $path : "https://via.placeholder.com/150?text=No+Image";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CMS Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lora:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Lora', serif; background: #F7F5F2; display: flex; height: 100vh; color: #1a1a1a; }
        
        .sidebar { width: 260px; background: #1a1a1a; color: #fff; display: flex; flex-direction: column; padding: 40px 0; }
        .nav-item { padding: 16px 30px; cursor: pointer; color: #999; font-size: 14px; border-left: 4px solid transparent; transition: 0.2s; }
        .nav-item:hover, .nav-item.active { color: #fff; background: #222; border-left-color: #C8102E; }
        
        .main { flex: 1; padding: 60px; overflow-y: auto; }
        h1 { font-family: 'Playfair Display', serif; font-size: 32px; margin-bottom: 30px; }
        .card { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); max-width: 900px; }
        
        .tab { display: none; }
        .tab.active { display: block; }

        h2 { font-family: 'Playfair Display', serif; font-size: 22px; margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .field-group { margin-bottom: 40px; }
        label { display: block; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; color: #666; }
        
        input[type="text"] { width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 4px; margin-bottom: 15px; font-family: 'Lora', serif; }
        .btn { background: #C8102E; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 13px; }
        
        .preview-box { width: 120px; height: 80px; background: #f9f9f9; border: 1px solid #eee; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .preview-box img { max-width: 100%; max-height: 100%; object-fit: cover; }

        .grid-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; }
        .status { background: #e6ffed; color: #2d5a27; padding: 15px; border-radius: 4px; margin-bottom: 25px; border: 1px solid #c3e6cb; font-size: 14px; }
        .logout { margin-top: auto; padding: 20px 30px; color: #C8102E; text-decoration: none; font-weight: 600; font-size: 14px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="nav-item active" onclick="showTab('link')">WhatsApp Link</div>
    <div class="nav-item" onclick="showTab('story')">Story Images</div>
    <div class="nav-item" onclick="showTab('author')">Author Profile</div>
    <div class="nav-item" onclick="showTab('cases')">Case Studies</div>
    <div class="nav-item" onclick="showTab('testimonials')">Testimonials</div>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="main">
    <h1>Dashboard</h1>
    <?php if($status): ?><div class="status"><?php echo $status; ?></div><?php endif; ?>

    <div class="card">
        <!-- Tab: Link -->
        <div id="link" class="tab active">
            <h2>WhatsApp Settings</h2>
            <form method="POST">
                <label>Current Group Link</label>
                <input type="text" name="link" value="<?php echo htmlspecialchars($cms['config']['whatsapp_link']); ?>">
                <button type="submit" name="update_link" class="btn">Update Link</button>
            </form>
        </div>

        <!-- Tab: Story -->
        <div id="story" class="tab">
            <h2>Story Images</h2>
            <?php foreach(['endoscopy', 'cancer_surgery_1', 'cancer_surgery_2'] as $k): ?>
                <div class="field-group">
                    <label><?php echo str_replace('_', ' ', $k); ?></label>
                    <div class="preview-box"><img src="<?php echo get_img($cms, 'single', $k); ?>"></div>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="img_key" value="<?php echo $k; ?>">
                        <input type="file" name="file" required>
                        <button type="submit" name="update_img" class="btn">Replace</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Tab: Author -->
        <div id="author" class="tab">
            <h2>Profile Management</h2>
            <div class="field-group">
                <label>Michael Toyin Headshot</label>
                <div class="preview-box" style="border-radius: 50%; width: 100px; height: 100px;">
                    <img src="<?php echo get_img($cms, 'single', 'michael_toyin'); ?>" style="border-radius: 50%;">
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="img_key" value="michael_toyin">
                    <input type="file" name="file" required>
                    <button type="submit" name="update_img" class="btn">Update Photo</button>
                </form>
            </div>
        </div>

        <!-- Tab: Cases -->
        <div id="cases" class="tab">
            <h2>Case Studies</h2>
            <div class="grid-container">
                <?php foreach($cms['grids']['cases'] as $i => $p): ?>
                    <div class="field-group">
                        <label>Slot <?php echo $i + 1; ?></label>
                        <div class="preview-box"><img src="<?php echo get_img($cms, 'grid', 'cases', $i); ?>"></div>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="grid_name" value="cases">
                            <input type="hidden" name="grid_index" value="<?php echo $i; ?>">
                            <input type="file" name="file" required>
                            <button type="submit" name="update_grid" class="btn">Update</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tab: Testimonials -->
        <div id="testimonials" class="tab">
            <h2>Testimonials</h2>
            <div class="grid-container">
                <?php foreach($cms['grids']['testimonials'] as $i => $p): ?>
                    <div class="field-group">
                        <label>Testimonial <?php echo $i + 1; ?></label>
                        <div class="preview-box"><img src="<?php echo get_img($cms, 'grid', 'testimonials', $i); ?>"></div>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="grid_name" value="testimonials">
                            <input type="hidden" name="grid_index" value="<?php echo $i; ?>">
                            <input type="file" name="file" required>
                            <button type="submit" name="update_grid" class="btn">Update</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function showTab(id) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        event.currentTarget.classList.add('active');
    }
</script>
</body>
</html>