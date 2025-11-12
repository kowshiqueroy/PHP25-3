<?php include 'header.php'; ?>

<main>
    <section class="search-section no-print">
      <h2>Search Panel</h2>
      <p>Demo search inputs below</p>
      <form action="" method="post">
      <div class="search-grid">
        <input type="text" placeholder="Search 1" />
        <input type="text" placeholder="Search 2" />
        <input type="text" placeholder="Search 3" />
        <input type="text" placeholder="Search 4" />
        <input type="text" placeholder="Search 5" />
        <select>
          <option>Choose</option>
          <option>Option 1</option>
        </select>
      </div>
      <button type="submit">Search</button>
      </form>
    </section>

    <section class="form-section no-print">
      <form action="" method="post">
        <div class="form-group group-1">
          <input type="text" placeholder="Full Name" />
        </div>
        <div class="form-group group-2">
          <input type="email" placeholder="Email" />
          <input type="number" placeholder="Phone" />
        </div>
        <div class="form-group group-3">
          <input type="number" placeholder="Age" />
          <select>
            <option>Gender</option>
            <option>Male</option>
            <option>Female</option>
          </select>
          <input type="text" placeholder="City" />
        </div>
        <button type="submit">Submit</button>
      </form>
    </section>
 
    <section class="table-section">
      <h2>Data Table</h2>
      <p>Demo details and actions</p>
      <div class="table-actions">
        <button onClick="window.print()">Print</button>
        <button>Export</button>
        <button>Copy</button>
      </div>
       <div class="table-scroll">

      <table>
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Status</th><th class="no-print">🗑️</th><th class="no-print">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr>

           <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr>

           <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr>

           <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr>
           <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr> <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr> <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr> <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr> <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr> <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr> <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr> <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr>
        </tbody>
      </table>
        </div>
    </section>
  </main>

  <?php
  require_once 'footer.php';
  ?>