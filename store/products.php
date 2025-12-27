<?php 
require_once 'header.php'; 
checkAuth(['admin', 'staff']);

// --- BACKEND LOGIC ---

// 1. ADD SINGLE PRODUCT
if(isset($_POST['add_product'])) {
    $type = trim($_POST['p_type']);
    $name = trim($_POST['p_name']);
    $unit = trim($_POST['p_unit']);
    
    // Server-side duplicate check
    $stmt = $pdo->prepare("SELECT id FROM products WHERE LOWER(p_type)=LOWER(?) AND LOWER(p_name)=LOWER(?)");
    $stmt->execute([$type, $name]);
    
    if($stmt->rowCount() > 0) {
        echo "<script>alert('Error: This product already exists!');</script>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (p_type, p_name, p_unit) VALUES (?,?,?)");
        $stmt->execute([$type, $name, $unit]);
        echo "<script>alert('Product Added!');</script>";
    }
}

// 2. UPDATE PRODUCT (Edit Logic)
if(isset($_POST['update_product'])) {
    $id = $_POST['edit_id'];
    $name = trim($_POST['edit_name']);
    $type = trim($_POST['edit_type']);
    $unit = trim($_POST['edit_unit']);

    if($id && $name && $type) {
        $stmt = $pdo->prepare("UPDATE products SET p_name=?, p_type=?, p_unit=? WHERE id=?");
        $stmt->execute([$name, $type, $unit, $id]);
        echo "<script>alert('Product Updated Successfully!'); window.location.href='products.php';</script>";
    }
}

// 3. CSV IMPORT (Column Format: Type, Type, Type...)
if(isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
    $filename = $_FILES['csv_file']['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    
    if($ext == 'csv') {
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        
        // Read Headers (Row 1) -> These are the Types
        $headers = fgetcsv($file); 
        
        if($headers !== FALSE) {
            $count = 0;
            // Read Rows (Row 2+) -> These are the Names
            while (($row = fgetcsv($file)) !== FALSE) {
                foreach($row as $index => $prodName) {
                    $prodName = trim($prodName);
                    
                    // Only process if name is not empty
                    if(!empty($prodName)) {
                        // Get Type from header (fallback to 'General')
                        $prodType = isset($headers[$index]) ? trim($headers[$index]) : 'General';
                        if(empty($prodType)) $prodType = 'General';

                        $unit = 'PCS'; // Default Unit

                        // Insert Ignore to skip duplicates
                        $stmt = $pdo->prepare("INSERT IGNORE INTO products (p_type, p_name, p_unit) VALUES (?,?,?)");
                        $stmt->execute([$prodType, $prodName, $unit]);
                        $count++;
                    }
                }
            }
            echo "<script>alert('Import Successful! Added $count products.');</script>";
        }
        fclose($file);
    } else {
        echo "<script>alert('Please upload a valid CSV file.');</script>";
    }
}

// 4. MERGE TYPES
if(isset($_POST['merge_types'])) {
    $source = $_POST['source_type'];
    $target = $_POST['target_type'];
    
    if($source && $target && $source != $target) {
        $pdo->beginTransaction();
        try {
            // Get all products in Source Type
            $stmt = $pdo->prepare("SELECT * FROM products WHERE p_type = ?");
            $stmt->execute([$source]);
            $items = $stmt->fetchAll();
            
            foreach($items as $item) {
                // Check if target already has this product name
                $check = $pdo->prepare("SELECT id FROM products WHERE p_type = ? AND p_name = ?");
                $check->execute([$target, $item['p_name']]);
                $existing = $check->fetch();
                
                if($existing) {
                    // CONFLICT: Target has it. Move transactions to Target Product, Delete Source Product.
                    // (Update 'transactions' table - ensure table name matches your DB)
                    $pdo->prepare("UPDATE transactions SET product_id = ? WHERE product_id = ?")->execute([$existing['id'], $item['id']]);
                    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$item['id']]);
                } else {
                    // NO CONFLICT: Just rename the type
                    $pdo->prepare("UPDATE products SET p_type = ? WHERE id = ?")->execute([$target, $item['id']]);
                }
            }
            $pdo->commit();
            echo "<script>alert('Type Merged Successfully!');</script>";
        } catch(Exception $e) {
            $pdo->rollBack();
            echo "<script>alert('Merge Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// 5. MERGE PRODUCTS (Single)
if(isset($_POST['merge_product_single'])) {
    $s_id = $_POST['source_pid'];
    $t_id = $_POST['target_pid'];
    
    if($s_id != $t_id && !empty($s_id) && !empty($t_id)) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE transactions SET product_id = ? WHERE product_id = ?")->execute([$t_id, $s_id]);
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$s_id]);
            $pdo->commit();
            echo "<script>alert('Product Merged Successfully!');</script>";
        } catch(Exception $e) {
            $pdo->rollBack();
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}
?>

<div class="container">
    <h2>📦 Product Management</h2>

    <div class="tabs" style="margin-bottom:20px; border-bottom: 2px solid #ddd;">
        <button class="tab-btn active" onclick="openTab('list')">Product List</button>
        <button class="tab-btn" onclick="openTab('add')">Add New</button>
        <button class="tab-btn" onclick="openTab('merge')">Merge Tools</button>
        <button class="tab-btn" onclick="openTab('import')">Import CSV</button>
    </div>

    <div id="list" class="tab-content">
        <input type="text" id="filterList" placeholder="Filter products..." onkeyup="filterTable()" style="margin-bottom:10px; padding:10px; width:100%;">
        <div style="max-height:60vh; overflow-y:auto;">
            <table id="prodTable">
                <thead><tr><th>Type</th><th>Name</th><th>Unit</th><th>Action</th></tr></thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM products ORDER BY p_type, p_name");
                    while($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['p_type']) ?></td>
                        <td><?= htmlspecialchars($row['p_name']) ?></td>
                        <td><?= htmlspecialchars($row['p_unit']) ?></td>
                        <td>
                            <button class="btn btn-edit" 
                                data-id="<?= $row['id'] ?>"
                                data-name="<?= htmlspecialchars($row['p_name'], ENT_QUOTES) ?>"
                                data-type="<?= htmlspecialchars($row['p_type'], ENT_QUOTES) ?>"
                                data-unit="<?= htmlspecialchars($row['p_unit'], ENT_QUOTES) ?>"
                                onclick="openEditModal(this)">
                                Edit
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="add" class="tab-content" style="display:none;">
        <div class="form-grid" style="max-width:600px; background:white; padding:20px; border-radius:8px;">
            <h3>Add Single Product</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Product Type</label>
                    <input type="text" name="p_type" id="in_type" list="dl_types" required autocomplete="off">
                    <div id="type_feedback" class="feedback-msg"></div>
                </div>
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="p_name" id="in_name" required autocomplete="off">
                    <div id="name_feedback" class="feedback-msg"></div>
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <input type="text" name="p_unit" value="PCS" required>
                </div>
                <button type="submit" name="add_product" id="btnAdd" class="btn">Create Product</button>
            </form>
        </div>
        <datalist id="dl_types">
            <?php 
            $types = $pdo->query("SELECT DISTINCT p_type FROM products")->fetchAll(PDO::FETCH_COLUMN);
            foreach($types as $t) echo "<option value='$t'>";
            ?>
        </datalist>
    </div>

    <div id="merge" class="tab-content" style="display:none;">
        <div class="form-grid">
            <div style="background:#fff3cd; padding:15px; border-radius:8px; border:1px solid #ffeeba;">
                <h3>📂 Merge Entire Types</h3>
                <p>Moves all products from <b>Type A</b> into <b>Type B</b>.</p>
                <form method="POST">
                    <div class="form-group">
                        <label>Source Type (Remove)</label>
                        <select name="source_type" class="select2-types" style="width:100%">
                            <?php 
                            $stmt = $pdo->query("SELECT DISTINCT p_type FROM products ORDER BY p_type");
                            while($row = $stmt->fetch()):
                            ?>
                            <option value="<?= htmlspecialchars($row['p_type']) ?>"><?= htmlspecialchars($row['p_type']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Target Type (Keep)</label>
                        <select name="target_type" class="select2-types" style="width:100%">
                            <?php 
                            $stmt = $pdo->query("SELECT DISTINCT p_type FROM products ORDER BY p_type");
                            while($row = $stmt->fetch()):
                            ?>
                            <option value="<?= htmlspecialchars($row['p_type']) ?>"><?= htmlspecialchars($row['p_type']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="merge_types" class="btn" style="background:#d97706; margin-top:10px;">Merge Types</button>
                </form>
            </div>

            <div style="background:#dbeafe; padding:15px; border-radius:8px; border:1px solid #bfdbfe;">
                <h3>📦 Merge Products</h3>
                <p>Merges <b>Product A</b> into <b>Product B</b>.</p>
                <form method="POST">
                    <div class="form-group">
                        <label>Source Product (Duplicate)</label>
                        <select name="source_pid" class="select2-prods" style="width:100%">
                            <?php 
                            $stmt = $pdo->query("SELECT id, p_name FROM products ORDER BY p_name");
                            while($row = $stmt->fetch()):
                            ?>
                            <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['p_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Target Product (Correct)</label>
                        <select name="target_pid" class="select2-prods" style="width:100%">
                            <?php 
                            $stmt = $pdo->query("SELECT id, p_name FROM products ORDER BY p_name");
                            while($row = $stmt->fetch()):
                            ?>
                            <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['p_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="merge_product_single" class="btn" style="background:#2563eb; margin-top:10px;">Merge Products</button>
                </form>
            </div>
        </div>
    </div>

    <div id="import" class="tab-content" style="display:none;">
        <div style="background:white; padding:20px; border-radius:8px; max-width:600px;">
            <h3>📥 Bulk Import from CSV</h3>
            <p><strong>Required CSV Format:</strong><br>
            - <b>Row 1:</b> Product Types (Headers)<br>
            - <b>Row 2+:</b> Product Names (in columns under types)<br>
            - Empty cells are ignored.<br>
            - Default Unit: <b>PCS</b>
            </p>
            
            <div style="background:#f3f4f6; padding:10px; font-family:monospace; margin-bottom:15px; overflow-x:auto;">
                Electronics, Grocery, Clothing<br>
                iPhone 13, Rice 5kg, T-Shirt<br>
                Samsung S21, Oil, Jeans<br>
                , Salt, 
            </div>

            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="csv_file" accept=".csv" required style="margin-bottom:15px;">
                <button type="submit" class="btn" style="width:100%">Upload & Import</button>
            </form>
        </div>
    </div>
</div>

<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:25px; border-radius:8px; width:400px; max-width:90%;">
        <h3>Edit Product</h3>
        <form method="POST">
            <input type="hidden" name="edit_id" id="edit_id">
            
            <div class="form-group">
                <label>Type</label>
                <input type="text" name="edit_type" id="edit_type" required>
            </div>
            
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="edit_name" id="edit_name" required>
            </div>
            
            <div class="form-group">
                <label>Unit</label>
                <input type="text" name="edit_unit" id="edit_unit" required>
            </div>
            
            <div style="margin-top:15px; text-align:right;">
                <button type="button" class="btn" onclick="$('#editModal').hide();" style="background:#999;">Cancel</button>
                <button type="submit" name="update_product" class="btn">Update</button>
            </div>
        </form>
    </div>
</div>

<style>
.tab-btn { padding: 10px 20px; border: none; background: #eee; cursor: pointer; font-size: 1rem; }
.tab-btn.active { background: var(--primary); color: white; font-weight: bold; }
.btn-edit { padding: 2px 8px; font-size: 0.8rem; background-color: #6c757d; color: white; }
.feedback-msg { font-size: 0.85rem; margin-top: 5px; min-height: 1.2em; }
.warning { color: #d97706; }
.error { color: #dc2626; }
/* Flexbox for Modal Centering */
#editModal[style*="display: block"] { display: flex !important; } 
</style>

<script>
// Tab Switching
function openTab(id) {
    $('.tab-content').hide();
    $('.tab-btn').removeClass('active');
    $('#'+id).show();
    event.target.classList.add('active');
}

// Open Edit Modal (Safe Method)
function openEditModal(btn) {
    // Read data from attributes
    var id = $(btn).data('id');
    var name = $(btn).data('name');
    var type = $(btn).data('type');
    var unit = $(btn).data('unit');

    // Populate modal
    $('#edit_id').val(id);
    $('#edit_name').val(name);
    $('#edit_type').val(type);
    $('#edit_unit').val(unit);
    
    // Show Modal
    $('#editModal').css('display', 'flex'); 
}

// Client-side Table Filter
function filterTable() {
    var input = document.getElementById("filterList");
    var filter = input.value.toUpperCase();
    var table = document.getElementById("prodTable");
    var tr = table.getElementsByTagName("tr");
    for (var i = 0; i < tr.length; i++) {
        var tdType = tr[i].getElementsByTagName("td")[0];
        var tdName = tr[i].getElementsByTagName("td")[1];
        if (tdType || tdName) {
            var txtValue = (tdType.textContent || tdType.innerText) + " " + (tdName.textContent || tdName.innerText);
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }       
    }
}

// Check Similarity API calls (For Adding New Products)
$('#in_type').on('input', function() {
    var val = $(this).val();
    if(val.length < 2) return;
    $.get('api.php?action=check_type_similarity&q='+val, function(res){
        var data = JSON.parse(res);
        var box = $('#type_feedback');
        if(data.exists) box.html('<span class="error">⚠️ Exact match exists!</span>');
        else if(data.similar.length > 0) box.html('<span class="warning">Did you mean: <b>'+data.similar.join(', ')+'</b>?</span>');
        else box.html('<span style="color:green">New Type</span>');
    });
});

$('#in_name').on('input', function() {
    var type = $('#in_type').val();
    var val = $(this).val();
    if(val.length < 2) return;
    $.get('api.php?action=check_name_similarity&type='+type+'&name='+val, function(res){
        var data = JSON.parse(res);
        var box = $('#name_feedback');
        if(data.exists) {
            box.html('<span class="error">⚠️ This product already exists!</span>');
            $('#btnAdd').prop('disabled', true).css('opacity',0.5);
        } else if(data.similar.length > 0) {
            box.html('<span class="warning">Similar names found: <b>'+data.similar.join(', ')+'</b></span>');
            $('#btnAdd').prop('disabled', false).css('opacity',1);
        } else {
            box.html('<span style="color:green">✓ Available</span>');
            $('#btnAdd').prop('disabled', false).css('opacity',1);
        }
    });
});

// Initialize Select2 with the NEW API actions
$(document).ready(function() {
    // For Merging Types (Needs ID = Type Name)
    $('.select2-types').select2({
        tags: true,
        width: '100%'
    });

    // For Merging Products (Needs ID = Numeric ID)
    $('.select2-prods').select2({
        tags: true,
        width: '100%'
    });
});
</script>

<?php require_once 'footer.php'; ?>