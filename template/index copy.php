<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Modern Admin Panel</title>

  <!-- Bootstrap 5 + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <style>
    body {
      font-family: "Open Sans", sans-serif;
      background-color: #f8f9fa;
    }
    .sidebar {
      min-height: 100vh;
      background-color: #343a40;
    }
    .sidebar .nav-link {
      color: #fff;
    }
    .sidebar .nav-link:hover {
      background-color: #495057;
    }
    .add-btn {
      position: fixed;
      bottom: 30px;
      right: 30px;
      z-index: 1050;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand fw-bold text-uppercase" href="#">Admin Panel</a>
    <button class="btn btn-outline-light d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
      <i class="bi bi-list"></i>
    </button>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar for Desktop -->
      <nav class="col-md-2 d-none d-md-block sidebar p-3">
        <h5 class="text-white mb-4">Menu</h5>
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-box-seam me-2"></i> Products</a></li>
          <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-people me-2"></i> Customers</a></li>
          <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-bar-chart me-2"></i> Reports</a></li>
         
        </ul>
      </nav>

        <!-- Mobile Sidebar Offcanvas -->
  <div class="offcanvas offcanvas-start text-bg-dark" id="mobileSidebar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">Menu</h5>
      <button class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link text-white" href="#"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="#"><i class="bi bi-box-seam me-2"></i> Products</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="#"><i class="bi bi-people me-2"></i> Customers</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="#"><i class="bi bi-bar-chart me-2"></i> Reports</a></li>
      </ul>
    </div>
  </div>

      <!-- Main Content -->
      <main class="col-md-10 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="fw-bold">Dashboard</h2>
          <button class="btn btn-primary d-none d-md-inline" data-bs-toggle="modal" data-bs-target="#addNewModal">
            <i class="bi bi-plus-lg"></i> Add New
          </button>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
          <div class="dropdown">
            <button class="btn btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-box-arrow-down"></i> Export
            </button>
            <ul class="dropdown-menu">
              <li><button class="dropdown-item" onclick="exportTable('pdf')">PDF</button></li>
              <li><button class="dropdown-item" onclick="exportTable('excel')">Excel</button></li>
              <li><button class="dropdown-item" onclick="exportTable('csv')">CSV</button></li>
            </ul>
          </div>
          <select class="form-select w-auto" onchange="filterTable(this.value)">
            <option value="all">All</option>
            <option value="Product">Product</option>
            <option value="Service">Service</option>
            <option value="Report">Report</option>
          </select>
          <button class="btn btn-outline-primary" onclick="toggleSearchForm()">
            <i class="bi bi-search"></i> Search
          </button>
        
        </div>

        <!-- Search Form -->
        <div id="searchForm" class="card mb-4 shadow-sm d-none">
          <div class="card-body">
            <form onsubmit="event.preventDefault(); applySearch();">
              <div class="row g-3">
                <div class="col-md-4">
                  <input type="text" class="form-control" id="searchName" placeholder="Search by name" />
                </div>
                <div class="col-md-4">
                  <select class="form-select" id="searchCategory">
                    <option value="">Any category</option>
                    <option>Product</option>
                    <option>Service</option>
                    <option>Report</option>
                  </select>
                </div>
                <div class="col-md-4 d-grid">
                  <button type="submit" class="btn btn-success">Apply</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Table -->
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-3">Recent Items</h5>
            <div class="table-responsive">
              <table class="table table-hover table-borderless align-middle" id="itemTable">
                <thead class="table-dark">
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Category</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>1</td>
                    <td><strong>Item A</strong></td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td>Product</td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary">Edit</button>
                      <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                  </tr>
                  <tr>
                    <td>2</td>
                    <td><strong>Item B</strong></td>
                    <td><span class="badge bg-secondary">Pending</span></td>
                    <td>Service</td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary">Edit</button>
                      <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Floating Add New Button -->
  <button class="btn btn-primary rounded-circle add-btn shadow" data-bs-toggle="modal" data-bs-target="#addNewModal">
    <i class="bi bi-plus-lg fs-4"></i>
  </button>

  <!-- Add New Modal -->
  <div class="modal fade" id="addNewModal" tabindex="-1" aria-labelledby="addNewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="addNewModalLabel">Add New Record</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" class="form-control form-control-lg" placeholder="Enter item name" />
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control form-control-lg" rows="3" placeholder="Brief description"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Category</label>
              <select class="form-select form-select-lg">
                <option selected disabled>Choose category</option>
                <option>Product</option>
                <option>Service</option>
                <option>Report</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>



    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script>
      // Custom JavaScript can go here
        function exportTable() {
            alert("Export functionality is not implemented yet.");
        }
        function filterTable(category) {
            const rows = document.querySelectorAll('#itemTable tbody tr');
            rows.forEach(row => {
                const rowCategory = row.cells[3].textContent;
                if (category === 'all' || rowCategory === category) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        function toggleSearchForm() {
            const searchForm = document.getElementById('searchForm');
            searchForm.classList.toggle('d-none');
        }
        function applySearch() {
            const searchName = document.getElementById('searchName').value;
            const searchCategory = document.getElementById('searchCategory').value;
            // Perform search logic here
        }
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        });
        
    </script>
</body>
</html>