<?php
require_once '../connection.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 0) {
    header("Location: ../");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ovijat</title>
  <style>
    :root {
      --primary: #2ecc71;
      --secondary: #27ae60;
      --accent: #a3e4d7;
      --text: #2c3e50;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: sans-serif;
      background: #fff;
      color: var(--text);
      overflow-x: hidden;
    }

    header, nav, .dropdown, table {
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.3);
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    header {
      position: fixed;
      top: 0;
      width: 100%;
      text-align: center;
      padding: 1rem;
      z-index: 1000;
    }

    header h1 {
      font-size: 1.5rem;
      color: var(--primary);
    }

    nav {
      position: fixed;
      bottom: 0;
      width: 100%;
      display: flex;
      justify-content: space-around;
      padding: 0.5rem 0;
      z-index: 1000;
    }

    nav button {
      background: none;
      border: none;
      font-size: 1rem;
      padding: 0.5rem;
      color: var(--text);
      touch-action: manipulation;
      position: relative;
    }

    .dropdown {
      position: absolute;
      display: none;
      flex-direction: column;
      background: rgba(255, 255, 255, 0.6);
      padding: 0.5rem;
      border-radius: 10px;
      z-index: 1001;
      min-width: 120px;
    }

    .dropdown button {
      background: none;
      border: none;
      padding: 0.5rem;
      text-align: left;
      color: var(--text);
    }

    main {
      margin-top: 4rem;
      margin-bottom: 4rem;
      padding: 1rem;
    }

    section {
      margin-bottom: 2rem;
    }

    .search-section {
      background: var(--accent);
      border-radius: 10px;
      padding: 1rem;
      text-align: center;
    }

    .search-section h2 {
      margin-bottom: 0.5rem;
    }

    .search-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(45%, 1fr));
      gap: 0.5rem;
      margin: 1rem 0;
    }

    .search-grid input,
    .search-grid select {
      padding: 0.5rem;
      border-radius: 8px;
      border: 1px solid var(--secondary);
    }

    .search-section button {
      padding: 0.5rem 1rem;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 8px;
    }

    .form-section form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .form-group {
      display: grid;
      gap: 1rem;
    }

    .group-1 {
      grid-template-columns: 1fr;
    }

    .group-2 {
      grid-template-columns: repeat(auto-fit, minmax(45%, 1fr));
    }

    .group-3 {
      grid-template-columns: repeat(auto-fit, minmax(30%, 1fr));
    }

    .form-group input,
    .form-group select {
      padding: 0.5rem;
      border-radius: 8px;
      border: 1px solid var(--secondary);
    }

    .form-section button {
      padding: 0.75rem;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 8px;
      width: 100%;
    }
.table-section {
  padding: 1rem;
}

.table-scroll {
  overflow-x: auto;

}

.table-scroll::-webkit-scrollbar {
  display: none; /* Chrome, Safari */
}

.table-scroll table {
  width: max-content;
  min-width: 100%;
}

    

    .table-section h2 {
      margin-bottom: 0.5rem;
    }

    .table-actions {
      margin-bottom: 0.5rem;
    }

    .table-actions button {
      margin-right: 0.5rem;
      padding: 0.3rem 0.6rem;
      background: var(--secondary);
      color: white;
      border: none;
      border-radius: 6px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(255, 255, 255, 0.6);
    }

    th, td {
      padding: 0.5rem;
      border: 1px solid var(--accent);
      text-align: left;
    }

    td.actions {
      white-space: nowrap;
    }

    td.actions button {
      margin-right: 0.3rem;
      background: none;
      border: none;
      font-size: 1rem;
      cursor: pointer;
    }

    @media print {
      nav, header, .dropdown, .table-actions, .table-actions button, .no-print {
        display: none;
      }
    }
  </style>
</head>
<body ontouchstart="">
  <header>
    <h1>Ovijat</h1>
  </header>

 
