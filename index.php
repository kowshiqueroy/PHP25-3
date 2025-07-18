<?php
$dir = __DIR__;
$msg = "";

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['newname'], $_POST['type'])) {
        $name = trim($_POST['newname']);
        if ($name !== '') {
            $path = $dir . DIRECTORY_SEPARATOR . $name;
            if ($_POST['type'] === 'folder') {
                mkdir($path);
                $msg = "Folder '$name' created.";
            } elseif ($_POST['type'] === 'file') {
                touch($path);
                $msg = "File '$name' created.";
            }
        }
    }
    if (isset($_POST['rename_from'], $_POST['rename_to'])) {
        $from = $dir . DIRECTORY_SEPARATOR . trim($_POST['rename_from']);
        $to   = $dir . DIRECTORY_SEPARATOR . trim($_POST['rename_to']);
        if (file_exists($from)) {
            rename($from, $to);
            $msg = "Renamed to '" . htmlspecialchars($_POST['rename_to']) . "'";
        }
    }

    //  if (isset($_POST['delete_confirmed'])) {
    //         unlink($dir . DIRECTORY_SEPARATOR . $_POST['delete']);
    //         $msg = "Deleted '" . htmlspecialchars($_POST['delete']) . "'";
    //     } 
    // if (isset($_POST['delete'])) {
       
    //         $msg = "Confirm deletion of '" . htmlspecialchars($_POST['delete']) . "'?";
    //         echo "<script>
    //             if (confirm('$msg')) {
    //                 window.location.href = '/index.php?delete_confirmed=1&delete=" . urlencode($_POST['delete']) . "';
    //             } else {
    //                 window.location.href = '/index.php';
    //             }
    //         </script>";
    //         exit;
        
    // }
}

// Scan and sort
$items = array_diff(scandir($dir), ['.', '..']);
$folders = [];
$filesByType = ['Images'=>[], 'PDFs'=>[], 'Code'=>[], 'Text'=>[], 'Others'=>[]];

foreach ($items as $item) {
    $path = $dir . DIRECTORY_SEPARATOR . $item;
    $modified = filemtime($path);
    if (is_dir($path)) {
        $inner = array_diff(scandir($path), ['.', '..']);
        $folderFiles = [];
        foreach ($inner as $f) {
            $folderFiles[] = [
                'name' => $f,
                'path' => $path . DIRECTORY_SEPARATOR . $f,
                'is_dir' => is_dir($path . DIRECTORY_SEPARATOR . $f)
            ];
        }
        $folders[] = compact('item','path','modified','folderFiles');
    } else {
        $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
        $type = match (true) {
            in_array($ext, ['jpg','jpeg','png','webp','gif','svg']) => 'Images',
            $ext === 'pdf' => 'PDFs',
            in_array($ext, ['php','js','html','css','py']) => 'Code',
            in_array($ext, ['txt','md']) => 'Text',
            default => 'Others'
        };
        $filesByType[$type][] = compact('item','path','modified');
    }
}

usort($folders, fn($a,$b) => $b['modified'] - $a['modified']);
foreach ($filesByType as &$group) {
    usort($group, fn($a,$b) => $b['modified'] - $a['modified']);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>🧠 File Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
  
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<style>
    body {
        background: linear-gradient(to right, #4873f7ff, #df7676ff);
        font-family: 'Segoe UI', sans-serif;
    }

    .card {
        border: none;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
    }

    .folder {
        background: linear-gradient(135deg, #00c9ff, #92fe9d);
        color: #fff;
    }

    .file {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
    }

    .btn {
        font-weight: 500;
        transition: background 0.3s ease;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
    }

    .card-body h6 a {
        color: inherit;
        text-decoration: none;
    }

    .card-body h6 a:hover {
        text-decoration: underline;
    }

    ul.inner-files {
        list-style: none;
        padding-left: 0;
        margin-top: 0.5rem;
        animation: fadeIn 0.4s ease;
    }

    ul.inner-files li {
        font-size: 0.85rem;
        margin: 0.25rem 0;
        color: #fff;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (prefers-color-scheme: dark) {
        body { background-color: #121212; color: #eee; }
        .folder, .file { filter: brightness(1.2); }
    }
</style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container py-5">
    <h2 class="text-center mb-4">🧠 File Manager</h2>

    <?php if ($msg): ?>
        <div class="alert alert-info text-center"><?= $msg ?></div>
    <?php endif ?>

    <!-- Create new -->
    <form method="POST" class="d-flex gap-2 mb-4 justify-content-center">
        <input type="text" name="newname" class="form-control w-25" placeholder="New name..." required />
        <select name="type" class="form-select w-25">
            <option value="file">New File</option>
            <option value="folder">New Folder</option>
        </select>
        <button class="btn btn-success">Create</button>
    </form>

    <!-- Folders -->
    <h4>📂 Folders</h4>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
    <?php foreach ($folders as $folder): ?>
        <div class="col">
            <div class="card folder h-100">
                <div class="card-body">
                    <h6><a href="<?= htmlspecialchars($folder['item']) ?>" class="text-decoration-none"><?= htmlspecialchars($folder['item']) ?></a></h6>
                    <small>Modified: <?= date("Y-m-d H:i:s", $folder['modified']) ?></small><br>
                    <form method="POST" class="d-inline-block mt-2 me-1">
                        <input type="hidden" name="delete" value="<?= htmlspecialchars($folder['item']) ?>" />
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($folder['item']) ?>?')">Delete</button>
                    </form>
                    <form method="POST" class="mt-2">
                        <input type="hidden" name="rename_from" value="<?= htmlspecialchars($folder['item']) ?>" />
                        <input type="text" name="rename_to" class="form-control form-control-sm mb-1" placeholder="New name..." required />
                        <button class="btn btn-sm btn-warning">Rename</button>
                    </form>
                    <?php if (!empty($folder['folderFiles'])): ?>
                    <ul class="inner-files">
                        <?php foreach ($folder['folderFiles'] as $f): ?>
                            <li><i class="bi bi-<?= $f['is_dir'] ? 'folder-fill' : 'file-earmark' ?>"></i>
                                <?= htmlspecialchars($f['name']) ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                    <?php else: ?>
                        <span class="badge badge-warning mt-2">Empty Folder</span>
                    <?php endif ?>

                    <a href="<?= htmlspecialchars($folder['item']) ?>" class="btn btn-sm btn-primary mt-2">Open this</a>
                    
                </div>
            </div>
        </div>
    <?php endforeach ?>
    </div>

    <!-- Files -->
    <?php foreach ($filesByType as $type => $group): ?>
        <?php if (!empty($group)): ?>
        <h4 class="mt-5"><?= $type ?></h4>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
        <?php foreach ($group as $file): ?>
            <div class="col">
                <div class="card file h-100">
                    <div class="card-body">
                        <h6><a href="<?= htmlspecialchars($file['item']) ?>" target="_blank" class="text-decoration-none"><?= htmlspecialchars($file['item']) ?></a></h6>
                        <small>Modified: <?= date("Y-m-d H:i:s", $file['modified']) ?></small><br>
                        <form method="POST" class="d-inline-block ms-2">
                            <input type="hidden" name="delete" value="<?= htmlspecialchars($file['item']) ?>" />
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        <form method="POST" class="mt-2">
                            <input type="hidden" name="rename_from" value="<?= htmlspecialchars($file['item']) ?>" />
                            <input type="text" name="rename_to" class="form-control form-control-sm mb-1" placeholder="New name..." required />
                            <button class="btn btn-sm btn-warning">Rename</button>
                        </form>
                          <a href="<?= htmlspecialchars($folder['item']) ?>" class="btn btn-sm btn-primary mt-2">Open this</a>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
        </div>
        <?php endif ?>
    <?php endforeach ?>
</div>

<script>
    document.querySelectorAll('.card.folder').forEach(card => {
        card.addEventListener('click', () => {
            const ul = card.querySelector('.inner-files');
            if (ul) {
                ul.style.display = (ul.style.display === 'none' || ul.style.display === '') ? 'block' : 'none';
            }
        });
    });
</script>
</body>
</html>
