<?php include 'head.php'; 





?>
      
      
     


      


       


       


        <!-- Property List Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="row g-0 gx-5 align-items-end">
                    <div class="col-lg-6">
                        <div class="text-start mx-auto mb-5 wow slideInLeft" data-wow-delay="0.1s">
                            <h1 class="mb-3"><?php echo isset($_GET['category']) ? $_GET['category'] . ' Products' : 'All Products' ?></h1>
                            <!-- <p>Eirmod sed ipsum dolor sit rebum labore magna erat. Tempor ut dolore lorem kasd vero ipsum sit eirmod sit diam justo sed rebum.</p> -->
                        </div>
                    </div>
                    
                </div>

                <div class="tab-content">
                    <div id="tab-1" class="tab-pane fade show p-0 active">
                        <?php
                        if (isset($_GET['category'])) {
                            $category = $_GET['category'];
                            $sql = "SELECT * FROM products WHERE category = '$category'  ORDER BY id DESC LIMIT 6";
                        } else {
                            $sql = "SELECT * FROM products ORDER BY id DESC";
                        }
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0): 
                            echo '<div class="row g-4">';
                            while ($row = $result->fetch_assoc()): ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="property-item rounded overflow-hidden">
                                        <div class="position-relative">
                                            <img class="img-fluid" src="<?= $row['image'] ?>" alt="">
                                            <div class="bg-secondary rounded text-white position-absolute start-0 top-0 mt-3 px-3 py-2">
                                                <h6 class="mb-0"><?= $row['category'] ?></h6>
                                            </div>
                                        </div>
                                        <div class="p-4 pb-0">
                                            <h5 class="text-primary mb-3"><?= $row['name'] ?></h5>
                                            <?php if ($row['price'] > $row['saleprice']): ?>
                                            <p class="mb-1"><strike><?= $row['price'] ?> USD</strike>  <strong><?= $row['saleprice'] ?> USD</strong></p>
                                            <?php else: ?>
                                            <p class="mb-1"><strong><?= $row['price'] ?> USD</strong></p>
                                            <?php endif; ?>

                                            <p class="mb-0"><i class="fa fa-map-marker-alt text-primary me-2"></i><strong><?= $row['address'] ?></strong></p>
                                            <div class="d-flex border-top">
                                                 <small class="text-primary ms-auto"><?= $row['pack'] ?> </small>
                                                <small class="text-primary ms-auto"><?= $row['type'] ?> </small>
                                                <small class="text-muted ms-auto">
                                                    <?php for ($i=1; $i <= 5; $i++): ?>
                                                        <i class="fa fa-star<?= $i <= $row['star'] ? '' : '-o' ?>" style="color: <?= $i <= $row['star'] ? '#ffc107' : '#ccc' ?>"></i>
                                                    <?php endfor; ?>
                                                </small>
                                            </div>

                                            <?php if ($row['status'] == 0): ?>
                                            <div class="text-center w-100 mt-3">
                                                <button class="btn btn-primary w-100" disabled>Coming Soon</button>
                                            </div>
                                            <?php else: ?>
                                            <div class="text-center w-100 mt-3">
                                                <a href="cart.php?action=add&id=<?= $row['id'] ?>" class="btn btn-primary w-100">Add to Cart</a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile;
                            echo '</div>';
                        endif; ?>
                    </div>
                    
                    
                </div>
                






            </div>
        </div>
        <!-- Property List End -->


       


        


       
        

 <?php include 'foot.php'; ?>