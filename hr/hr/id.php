<?php
include 'head1.php';

$name = $id = $post = $dept = $phone = $email = $issuedate = $bg = $photo = "";

if (isset($_REQUEST['pid'])) {
    $pid = $_REQUEST['pid'];
    $sql = "SELECT * FROM person WHERE pid='$pid'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $name = $row["name"];
        $id = $row["pid"];
        $post = $row["post"];
        $dept = $row["dept"];
        $phone = $row["phone"];
        $email = $row["email"];
        $issuedate = $row["issuedate"];
        $bg = $row["bloodgroup"];
        $photo = $row["photo"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ID Card Print</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      font-family: sans-serif;
    }
    .buttons {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin: 20px 0;
    }
    .card-container {
      display: flex;
      justify-content: center;
      gap: 1cm;
      padding: 2cm 0;
    }
    .card {
      width: .1cm;
      height: 8.4cm;
      border: 1px solid #000;
      border-radius: 8px;
      padding: 5px;
      background: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      font-size: 10pt;
    }
    .card img {
      max-width: 100%;
      height: auto;
    }
    .card h2, .card p {
      margin: 4px 0;
      text-align: center;
      font-size: 10pt;
    }
    @media print {
      .buttons {
        display: none;
      }
      @page {
        size: A4;
        margin: 0;
      }
      body {
        -webkit-print-color-adjust: exact;
      }
    }
  </style>
</head>
<body>

<div class="buttons">
  <button onclick="window.print()">Download</button>
  <button onclick="history.back()">Return</button>
</div>

<div class="card-container">
  <!-- Front Side -->
  <div class="card">
    <img src="../assets/img/logo.png" alt="Logo" style="height: 40px; margin: 4px 0;">
    <img src="../assets/person/<?php echo $photo; ?>" alt="Photo" style="height: 90px; border: 1px solid black;">
    <h2><?php echo $name; ?></h2>
    <p><b><?php echo $post; ?></b><br>(<?php echo $dept; ?>)</p>
    <p><?php echo $email; ?></p>
    <p><?php echo $phone; ?></p>
    <p style="margin-top: auto; font-size: 9pt;">Ovijat Food & Beverage Industries Ltd.</p>
  </div>

  <!-- Back Side -->
  <div class="card">
    <img src="../assets/img/logo.png" alt="Logo" style="height: 25px; margin-top: 5px;">
    <img src="../assets/img/qr.png" alt="QR Code" style="height: 80px; border: 1px solid black;">
    <p style="font-size: 8pt;">This ID card is digitally generated.<br>Visit: <b>www.ovijatfood.com/id</b></p>
    <p><b><?php echo $name; ?></b><br>ID: <?php echo $id; ?></p>
    <p>Blood: <?php echo $bg; ?></p>
    <p>Issued: <?php echo $issuedate; ?></p>
    <p style="margin-top: auto; font-size: 8pt;">If found, contact:<br>Ovijat Food & Beverage Industries Ltd.<br>Motijheel, Dhaka — Factory: Nilphamari</p>
  </div>
</div>

</body>
</html>