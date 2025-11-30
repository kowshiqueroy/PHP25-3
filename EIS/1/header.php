<?php require_once '../config.php'; ?>
<?php
if(isset($_SESSION['role']) && $_SESSION['role'] != 1){
    header("Location: ../");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $website_name; ?></title>
    <style>
        /* --- 1. MODERN RESET & VARIABLES --- */
        :root {
            /* Palette */
            --primary: #6366f1; /* Indigo */
            --primary-hover: #4f46e5;
            --bg-body: #f3f4f6;
            --bg-surface: #ffffff;
            --text-main: #111827;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --sidebar-bg: #64b864ff; /* Slate 800 */
            --sidebar-text: #ffffffff;
            
            /* Spacing & Shape */
            --radius: 12px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --header-h: 64px;
            --sidebar-w: 260px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            -webkit-font-smoothing: antialiased;
        }

        /* --- 2. LAYOUT --- */
        .app-layout { display: flex; min-height: 100vh; }

        /* Sidebar (Desktop: Fixed, Mobile: Off-canvas) */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex; flex-direction: column;
            position: fixed; inset: 0 auto 0 0;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
        }
        .logo-area {
            height: var(--header-h); display: flex; align-items: center; padding: 0 1.5rem;
            color: yellow; font-weight: 700; font-size: 1.25rem; letter-spacing: -0.025em;
            border-bottom: 2px solid rgba(255,255,255,0.1);
        }
        .nav-menu { padding: 1.5rem 1rem; flex: 1; }
        .nav-item {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1rem; margin-bottom: 0.25rem;
            border-radius: 8px; color: var(--sidebar-text);
            text-decoration: none; font-weight: 500; transition: 0.2s;
        }
        .nav-item:hover { background: rgba(248, 12, 12, 0.05); color: white; }
        .nav-item.active { background: var(--primary); color: white; box-shadow: var(--shadow-md); }
        
        /* Main Content Wrapper */
        .main-wrapper { flex: 1; display: flex; flex-direction: column; margin-left: 0; width: 100%; transition: margin 0.3s; }
        
        /* Header */
        .top-header {
            height: var(--header-h); background: var(--bg-surface);
            border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem; position: sticky; top: 0; z-index: 40;
            background-color: red;
            color: white;
        }
        .mobile-toggle { display: block; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: white; }
        
        /* Avatar */
        .user-profile { display: flex; align-items: center; gap: 10px; }
        .avatar { width: 15px; height: 15px; border-radius: 50%; background: linear-gradient(135deg, #1ca30aff, #a4db24ff); }

        /* Dashboard Content */
        .content-area { padding: 2rem; max-width: 1400px; margin: 0 auto; width: 100%; }
        .page-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-main); }

        /* --- 3. COMPONENTS --- */

        /* Modern Cards */
        .card {
            background: var(--bg-surface); border-radius: var(--radius);
            box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);
            padding: 1.5rem; margin-bottom: 1.5rem;
        }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { display: flex; flex-direction: column; gap: 0.5rem; }
        .stat-title { font-size: 0.875rem; color: var(--text-secondary); font-weight: 500; }
        .stat-value { font-size: 1.875rem; font-weight: 700; color: var(--text-main); }
        .stat-trend { font-size: 0.875rem; font-weight: 500; display: inline-flex; align-items: center; }
        .trend-up { color: #10b981; } /* Green */

        /* Inputs & Buttons */
        .input-group { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        .form-control {
            width: 100%; padding: 0.625rem 0.875rem; border-radius: 8px;
            border: 1px solid var(--border-color); background: #f9fafb;
            font-size: 0.95rem; transition: 0.2s; outline: none;
        }
        .form-control:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
        
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.625rem 1.25rem; border-radius: 8px; font-weight: 600;
            font-size: 0.95rem; border: none; cursor: pointer; transition: 0.2s;
        }
        .btn-primary { background: var(--primary); color: white; box-shadow: 0 2px 5px rgba(99, 102, 241, 0.3); }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-ghost { background: transparent; color: var(--text-secondary); }
        .btn-ghost:hover { background: #f3f4f6; color: var(--text-main); }

        /* Modern Table */
        .table-responsive { overflow-x: auto; border-radius: 8px; border: 1px solid var(--border-color); }
        .data-table { width: 100%; border-collapse: collapse; background: white; }
        .data-table th { 
            text-align: left; padding: 1rem 1.5rem; 
            background: #f9fafb; color: var(--text-secondary); 
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;
        }
        .data-table td { padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); font-size: 0.9rem; color: var(--text-main); }
        .data-table tr:hover td { background: #f8fafc; }

        /* Status Badge */
        .badge { padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }

        /* --- 4. RESPONSIVE BEHAVIOR --- */
        @media (min-width: 1024px) {
            .sidebar { transform: translateX(0); box-shadow: none; }
            .main-wrapper { margin-left: var(--sidebar-w); width: calc(100% - var(--sidebar-w)); }
            .mobile-toggle { display: none; }
            .input-group { grid-template-columns: 1fr 1fr 1fr; align-items: end; }
        }

        /* --- 5. PRINT LOGIC (The Critical Part) --- */
        @media print {
            /* 1. Hide Everything Global */
            body * { visibility: hidden; height: 0; overflow: hidden; }
            
            /* 2. Reset Body Background */
            body { background: white; }

            /* 3. Reveal ONLY the .printable-content and its children */
            .printable-content, .printable-content * { 
                visibility: visible; 
                height: auto; 
                overflow: visible; 
                color: black; /* Force pure black for printers */
            }

            /* 4. Position the printable content at the very top */
            .printable-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 0;
                border: none;
                box-shadow: none;
            }

            /* 5. Hide specific "no-print" elements inside the printable area (like buttons) */
            .no-print { display: none !important; }
            
            /* 6. Table Tweaks for Print */
            .data-table th { background-color: #f3f3f3 !important; -webkit-print-color-adjust: exact; }
            .badge { border: 1px solid #ccc; }
        }

        /* --- CUSTOM SEARCHABLE SELECT CSS --- */
.custom-select-wrapper {
    position: relative;
}

.searchable-dropdown {
    position: relative;
}

.dropdown-arrow {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.7rem;
    color: var(--text-secondary);
    pointer-events: none; /* Let clicks pass through to input */
}

.dropdown-options {
    display: none; /* Hidden by default */
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    margin-top: 5px;
    max-height: 200px;
    overflow-y: auto;
    box-shadow: var(--shadow-md);
    z-index: 100; /* Ensure it floats above other cards */
}

.dropdown-options.show {
    display: block;
}

.option-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    font-size: 0.95rem;
    border-bottom: 1px solid #f3f4f6;
    color: var(--text-main);
}

.option-item:hover {
    background-color: #eff6ff; /* Light Indigo */
    color: var(--primary);
}

.option-item:last-child {
    border-bottom: none;
}

/* Scrollbar styling for the dropdown */
.dropdown-options::-webkit-scrollbar {
    width: 6px;
}
.dropdown-options::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 4px;
}
    </style>
</head>
<body>

    <div class="app-layout">
        
        <aside class="sidebar" id="sidebar">
            <div class="logo-area">
                ⚡ <?php echo $website_name; ?>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item active"><span>📊</span> Dashboard</a>
                <a href="route.php" class="nav-item"><span>🚛</span> Route</a>
                 <a href="shop.php" class="nav-item"><span>🏬</span> Shop</a>
                  <a href="item.php" class="nav-item"><span>📦</span> Item</a>
                <a href="order.php" class="nav-item"><span>🛒</span> Order</a>
                <a href="list.php" class="nav-item"><span>📋</span> List</a>


            </nav>
            <div style="padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1)">
                <div style="font-size: 0.8rem; opacity: 0.7; text-align: center"><a href="../logout.php" style="color: inherit; text-decoration: none;">Logout [<?php echo $_SESSION['role'] ?>]</a></div>
            </div>
        </aside>

        <div class="main-wrapper">
            
            <header class="top-header">
                <button class="mobile-toggle" id="menuBtn">☰</button>
                <h3 style="font-size:1.1rem; font-weight:600; display:none;" class="desktop-only-title">Overview</h3>
                <div class="user-profile">
                    <span style="font-size: 0.9rem; font-weight: 500; margin-right: 0.5rem;"><b><?php echo $website_name; ?></b> | <?php echo $_SESSION['username'] ?></span>
                    <div class="avatar"></div>
                </div>
            </header>