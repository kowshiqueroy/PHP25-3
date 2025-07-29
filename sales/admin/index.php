<?php
require_once '../conn.php';
require_once 'header.php';
?>



<style>


.browser {
    width: 100%;
 
    border: 2px solid #ccc;
    border-radius: 10px;
    overflow: hidden;
    background: white;
    margin: 20px auto;
}

.browser-header {
    background: #ddd;
    padding: 10px;
    display: flex;
    align-items: center;
}

.buttons span {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 5px;
}

.red { background: red; }
.yellow { background: yellow; }
.green { background: green; }

.url-bar {
    flex-grow: 1;
    text-align: center;
    font-weight: bold;
}

.browser-content {
    padding: 2px;
   
}

.img-container img {
    display: block;
    margin: auto;
    max-width: 100px;
}

footer {
    text-align: center;
    padding: 10px;
    background: #ddd;
}
</style>
<div class="browser">
    <div class="browser-header">
        <div class="buttons">
            <span class="red"></span>
            <span class="yellow"></span>
            <span class="green"></span>
        </div>
       
               
           
        <div class="url-bar">  <?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></div>
    </div>

    <div class="browser-content">
        
            <div class="img-container">
                <?php
                    $sql = "SELECT companyname, tagline, logo FROM companyinfo WHERE id = 1";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        echo '<img src="' . $row['logo'] . '" alt="Company Logo">';
                    }
                ?>
            </div>
            
      
        
        <main>
            <style>
                .notice-container {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    padding: 10px;
                }
                .notice {
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
                    border-radius: 8px;
                    width: 100%;
                    background-color: #f5f5f5;
                    margin-bottom: 15px;
                    padding: 15px;
                }
                .notice p:first-child {
                    font-size: 14px;
                   
                    display: flex;
                    align-items: center;
                }
                .notice p:first-child i {
                    margin-right: 10px;
                }
                .notice p:last-child {
                   
                    margin-top: 10px;
                }
            </style>
            <div style="width: 100%;">
                <h2 style="text-align: center; color: #337ab7; margin-bottom: 20px;">Notice Board</h2>
                <section class="notice-container">
                    <?php
                        $sql = "SELECT * FROM notice ORDER BY id DESC LIMIT 10";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $r = rand(0, 255);
                                $g = rand(0, 255);
                                $b = rand(0, 255);
                                $contrast = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) > 127.5 ? 'black' : 'white';
                                echo '<div class="notice" style="background-color: rgb(' . $r . ', ' . $g . ', ' . $b . ')">';
                                echo '<p style="color: ' . $contrast . '; display: flex; justify-content: space-between;"><span><i class="fa fa-id-badge"></i> ID: ' . $row['id'] . '</span><span><i class="fa fa-user"></i> ' . $row['username'] . ' </span><span><i class="fa fa-calendar"></i> ' . $row['datetime'] . '</span></p>';
                               
                                echo '<div style="margin: 1px; padding: 10px; background-color: white;"><pre>' . $row['content'] . '</pre></div>';
                                echo '</div>';
                            }
                        }
                    ?>
                </section>
            </div>
            
            
            
          
        </main>

        <footer>
            <p>&copy; 2025 kowshiqueroy - All rights reserved.</p>
        </footer>
    </div>
</div>



<?php
require_once 'footer.php';
?>
