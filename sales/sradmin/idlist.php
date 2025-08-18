<?php
require_once '../conn.php';
require_once 'header.php';
?>
<div class="card p-1 text-center">Print By ID

<br><br><br>

 <form action="idprint.php" method="GET">
            <input style="width: 100%;" type="text" name="idall" placeholder="1,2,3,4,5" pattern="\d+(,\d+)*" required>
            <br><input type="submit" value="Print">
        </form>
</div>




<?php
require_once 'footer.php';
?>