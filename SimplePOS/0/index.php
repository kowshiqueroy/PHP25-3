





<?php include_once 'header.php'; ?>




<div class="cards-container">
                <div class="card" onClick="location.href='sales.php';" style="cursor: pointer;">
                    <h3><?php echo $lang[$language]['sales']; ?></h3>
                    <div class="value"><?php echo $lang[$language]['new']; ?> <?php echo $lang[$language]['sales']; ?></div>
                    <!-- <div class="details">+15% from last month</div> -->
                </div>
                <div class="card" onClick="location.href='products.php';" style="cursor: pointer;">
                    <h3><?php echo $lang[$language]['products']; ?></h3>
                    <div class="value"><?php echo $lang[$language]['new']; ?> <?php echo $lang[$language]['products']; ?></div>
                    <!-- <div class="details">+20% from last month</div> -->
                </div>
                <div class="card" onClick="location.href='reports.php';" style="cursor: pointer;">
                    <h3><?php echo $lang[$language]['reports']; ?></h3>
                    <div class="value"><?php echo $lang[$language]['view']; ?> <?php echo $lang[$language]['reports']; ?></div>
                    <!-- <div class="details">+10% from last month</div> -->
                </div>
              
            </div>



            <div style="display: flex; justify-content: center; position: absolute; bottom: 100px; left: 0; right: 0;">
                <div id="clock" style="font-size: 40px; font-weight: bold; border: 1px solid #ccc; padding: 10px; text-shadow: 2px 2px 4px #00000061;"></div>
            </div>
           
            

             <script>
                var clockElement = document.getElementById("clock");
                function updateClock() {
                    var now = new Date();
                    var time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                    clockElement.innerText = time.replace(/am|pm/gi, function(match) {
                        return match.toUpperCase();
                    });
                }
                updateClock();
                setInterval(updateClock, 1000);
            </script>


<div style="display: flex; justify-content: center; position: absolute; bottom: 50px; left: 0; right: 0;">
                <div id="date" style="font-size: 25px; font-weight: bold; border: 1px solid #ccc; padding: 10px; text-shadow: 2px 2px 4px #00000061;"></div>
            </div>
  <script>
    const dateElement = document.getElementById("date");

    function updateDate() {
        const now = new Date();

        const weekdayName = now.toLocaleString('en-US', { weekday: 'long' }); // e.g., Monday
        const dayOfMonth = now.getDate(); // e.g., 9
        const monthName = now.toLocaleString('en-US', { month: 'long' }); // e.g., September
        const monthNumber = now.getMonth() + 1; // JS months are 0-indexed
        const year = now.getFullYear(); // e.g., 2025

        const formattedDate = `
            ${(dayOfMonth < 10 ? '0' : '') + dayOfMonth} <span style="font-size: 0.5em; color: #888;">[${weekdayName}]</span>, 
            ${(monthNumber < 10 ? '0' : '') + monthNumber} <span style="font-size: 0.5em; color: #888;">[${monthName}]</span>, 
            ${year}
        `;
        dateElement.innerHTML = formattedDate;
    }

    updateDate();
    setInterval(updateDate, 1000);
</script>

<canvas id="clock-canvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<script>
    const canvas = document.getElementById("clock-canvas");
    const ctx = canvas.getContext("2d");

    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function drawClock() {
        const center_x = canvas.width / 2;
        const center_y = canvas.height / 2;
        const radius = 60;

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Clock face
        ctx.beginPath();
        ctx.arc(center_x, center_y, radius + 10, 0, 2 * Math.PI);
        ctx.fillStyle = "#fefefe";
        ctx.fill();
        ctx.strokeStyle = "#ccc";
        ctx.lineWidth = 4;
        ctx.stroke();

        // Time
        const now = new Date();
        const hour = now.getHours() % 12;
        const minute = now.getMinutes();
        const second = now.getSeconds();

        const hourAngle = ((hour + minute / 60) * 30) * Math.PI / 180;
        const minuteAngle = ((minute + second / 60) * 6) * Math.PI / 180;
        const secondAngle = (second * 6) * Math.PI / 180;

        // Draw hands
        drawHand(hourAngle, radius * 0.5, 6, "#333");
        drawHand(minuteAngle, radius * 0.75, 4, "#666");
        drawHand(secondAngle, radius * 0.9, 2, "#e63946");

        // Center dot
        ctx.beginPath();
        ctx.arc(center_x, center_y, 5, 0, 2 * Math.PI);
        ctx.fillStyle = "#333";
        ctx.fill();

        function drawHand(angle, length, width, color) {
            ctx.save();
            ctx.translate(center_x, center_y);
            ctx.rotate(angle);
            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.lineTo(0, -length);
            ctx.strokeStyle = color;
            ctx.lineWidth = width;
            ctx.lineCap = "round";
            ctx.stroke();
            ctx.restore();
        }
    }

    setInterval(drawClock, 1000);
</script>
        
            <p style="position: fixed; bottom: 5px; left: 50%; transform: translateX(-50%); text-align: center; font-size: 18px; color: #999;">Developed by <a href="mailto:KowshiqueRoy@gmail.com" style="color: #999;">KowshiqueRoy@gmail.com</a></p>
<?php include_once 'footer.php'; ?>

      