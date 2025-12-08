<?php
include 'header.php';
?>

<div class="container">

   

  

  <style>
   

    .targets-and-achievements {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      gap: 30px;
      flex-wrap: wrap;
      margin: 0 auto;
      max-width: 900px;
    }

    .targets-and-achievements > div {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 20px 25px;
      flex: 1;
      min-width: 280px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .targets-and-achievements > div:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }

    .targets-and-achievements > div > p {
      font-weight: 600;
      font-size: 1.2rem;
      color: #444;
      margin-bottom: 15px;
      border-bottom: 2px solid #eee;
      padding-bottom: 8px;
    }

    .targets-and-achievements > div > ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .targets-and-achievements > div > ul > li {
      margin-bottom: 12px;
      color: #555;
      font-size: 0.95rem;
      line-height: 1.5;
      position: relative;
      padding-left: 20px;
    }

    .targets-and-achievements > div > ul > li::before {
      content: "✔";
      position: absolute;
      left: 0;
      color: #4CAF50;
      font-size: 0.9rem;
    }
  </style>

   <div class="text-center" style="text-align: center; margin: 30px 0;">
        <h2 style="font-weight: 300; font-size: 2rem;">🎯 Targets & Achievements</h2>
       
    </div>

  <!-- <h2 class="section-title"></h2> -->
  <div class="targets-and-achievements">
    <div>
      <p>Demo Target List</p>
      <ul>
        <li>Increase sales by 15% in the next quarter</li>
        <li>Reduce costs by 10% in the next quarter</li>
        <li>Increase customer satisfaction by 20% in the next quarter</li>
         <li>Reduce costs by 10% in the next quarter</li>
        <li>Increase customer satisfaction by 20% in the next quarter</li>
      </ul>
      <div style="margin-top:20px; text-align:center;">
        <button class="btn btn-primary" onclick="">See All</button>
      </div>
    </div>
    <div>
      <p>Demo Achievement List</p>
      <ul>
        <li>Increase sales by 10% in the next quarter</li>
        <li>Reduce costs by 5% in the next quarter</li>
        <li>Increase customer satisfaction by 15% in the next quarter</li>
         <li>Reduce costs by 10% in the next quarter</li>
        <li>Increase customer satisfaction by 20% in the next quarter</li>
      </ul>
      <div style="margin-top:20px; text-align:center;">
        <button class="btn btn-primary" onclick="">See All</button>
      </div>
    </div>
  </div>

   <div class="text-center" style="text-align: center; margin: 30px 0;">
        <h2 style="font-weight: 300; font-size: 2rem;"> 🎁 Gifts & Promotions</h2>
       
    </div>

  <!-- <h2 class="section-title"></h2> -->
  <div class="targets-and-achievements">
    <div>
      <p>Demo Gift List</p>
      <ul>
        <li>Increase sales by 15% in the next quarter</li>
        <li>Reduce costs by 10% in the next quarter</li>
        <li>Increase customer satisfaction by 20% in the next quarter</li>
         <li>Reduce costs by 10% in the next quarter</li>
        <li>Increase customer satisfaction by 20% in the next quarter</li>
      </ul>
      <div style="margin-top:20px; text-align:center;">
        <button class="btn btn-primary" onclick="">See All</button>
      </div>
    </div>
    <div>
      <p>Demo Promotion List</p>
      <ul>
        <li>Increase sales by 10% in the next quarter</li>
        <li>Reduce costs by 5% in the next quarter</li>
        <li>Increase customer satisfaction by 15% in the next quarter</li>
         <li>Reduce costs by 10% in the next quarter</li>
        <li>Increase customer satisfaction by 20% in the next quarter</li>
      </ul>
      <div style="margin-top:20px; text-align:center;">
        <button class="btn btn-primary" onclick="">See All</button>
      </div>
    </div>
  </div>



    <div class="box" style="display:flex; justify-content:center; align-items:center; margin:30px auto; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-radius: 12px; padding: 20px; background-color: #fff;">
      <p id="latitude"><span id="latitude-val"></span></p>
      <p id="longitude"><span id="longitude-val"></span></p>
      <p id="address" style="font-weight:bold; color:#333;">
        <span id="address-val"></span>
      </p>
    </div>
  </div>

  <script>
    function getLocation() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async function (position) {
          let latitude = position.coords.latitude;
          let longitude = position.coords.longitude;

          document.getElementById("latitude-val").textContent = "Latitude: " + latitude.toFixed(6);
          document.getElementById("longitude-val").textContent = "Longitude: " + longitude.toFixed(6);

          try {
            // Call BigDataCloud Reverse Geocoding API
            const response = await fetch(
              `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latitude}&longitude=${longitude}&localityLanguage=en`
            );
            const data = await response.json();

            // Build a clean address string
            const parts = [
              data.locality || data.city || "",
              data.principalSubdivision || "",
              data.countryName || ""
            ].filter(Boolean);

            document.getElementById("address-val").textContent =
              "Address: " + (parts.length ? parts.join(", ") : "Unknown location");
          } catch (err) {
            document.getElementById("address-val").textContent = "Error fetching address.";
          }
        }, function () {
          alert("Please allow LOCATION permission.");
        });
      } else {
        document.getElementById("address-val").textContent = "Geolocation not supported.";
      }
    }

    getLocation();
  </script>


</div>

<?php
include 'footer.php';
?>