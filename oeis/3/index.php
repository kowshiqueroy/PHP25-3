<?php
include 'header.php';
?>

<div class="container">

    <div class="text-center" style="text-align: center; margin: 30px 0;">
        <h2 style="font-weight: 300; font-size: 2rem;">Welcome Back</h2>
        <p style="color: #666;">Manage your company with modern, vibrant interface.</p>
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