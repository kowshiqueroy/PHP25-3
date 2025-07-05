<?php include 'head.php'; ?>
      
      
      <!-- Header Start -->
        <div class="container-fluid header bg-white p-0">
            <div class="row g-0 align-items-center flex-column-reverse flex-md-row">
                <div class="col-md-6 p-5 mt-lg-5">
                    <h1 class="display-5 animated fadeIn mb-4">Find <span class="text-primary">Healthy Products</span> for <span class="text-primary">Your Family</span></h1>
                    <!-- <p class="animated fadeIn mb-4 pb-2">Vero elitr justo clita lorem. Ipsum dolor at sed stet
                        sit diam no. Kasd rebum ipsum et diam justo clita et kasd rebum sea elitr.</p> -->
                    <a href="products.php" class="btn btn-primary py-3 px-5 me-3 animated fadeIn">View Products</a>
                </div>
                <div class="col-md-6 animated fadeIn">
                    <div class="owl-carousel header-carousel">
                       
                    
                        <?php
                        $sql = "SELECT * FROM banner ORDER BY id DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                        ?>
                        <div class="owl-carousel-item">
                            <img class="img-fluid" src="<?= $row['link'] ?>" alt="Banner">
                        </div>
                        <?php
                            }
                        }
                        ?>
                        
                    
                    
                        <!-- <div class="owl-carousel-item">
                            <img class="img-fluid" src="https://images.pexels.com/photos/461382/pexels-photo-461382.jpeg?auto=compress&w=800&q=80" alt="Snacks">
                        </div> -->
                        
                      
                       
                    </div>
                </div>
            </div>
        </div>
        <!-- Header End -->


      


        <!-- Category Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                    <h1 class="mb-3">Categories</h1>
                    <!-- <p>Eirmod sed ipsum dolor sit rebum labore magna erat. Tempor ut dolore lorem kasd vero ipsum sit eirmod sit. Ipsum diam justo sed rebum vero dolor duo.</p> -->
                </div>
                <div class="row g-4">
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
                        <a class="cat-item d-block bg-light text-center rounded p-3" href="">
                            <div class="rounded p-4">
                                <div class="icon mb-3">
                                    <img class="img-fluid" src="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f34e.png" alt="Fruits">
                                </div>
                                <h6>Fruits</h6>
                                <span>120 Products</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.3s">
                        <a class="cat-item d-block bg-light text-center rounded p-3" href="">
                            <div class="rounded p-4">
                                <div class="icon mb-3">
                                    <img class="img-fluid" src="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f966.png" alt="Vegetables">
                                </div>
                                <h6>Vegetables</h6>
                                <span>98 Products</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.5s">
                        <a class="cat-item d-block bg-light text-center rounded p-3" href="">
                            <div class="rounded p-4">
                                <div class="icon mb-3">
                                    <img class="img-fluid" src="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f95b.png" alt="Dairy">
                                </div>
                                <h6>Dairy</h6>
                                <span>65 Products</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.7s">
                        <a class="cat-item d-block bg-light text-center rounded p-3" href="">
                            <div class="rounded p-4">
                                <div class="icon mb-3">
                                    <img class="img-fluid" src="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f35e.png" alt="Bakery">
                                </div>
                                <h6>Bakery</h6>
                                <span>42 Products</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
                        <a class="cat-item d-block bg-light text-center rounded p-3" href="">
                            <div class="rounded p-4">
                                <div class="icon mb-3">
                                    <img class="img-fluid" src="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f357.png" alt="Meat & Poultry">
                                </div>
                                <h6>Meat & Poultry</h6>
                                <span>37 Products</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.3s">
                        <a class="cat-item d-block bg-light text-center rounded p-3" href="">
                            <div class="rounded p-4">
                                <div class="icon mb-3">
                                    <img class="img-fluid" src="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f990.png" alt="Seafood">
                                </div>
                                <h6>Seafood</h6>
                                <span>22 Products</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.5s">
                        <a class="cat-item d-block bg-light text-center rounded p-3" href="">
                            <div class="rounded p-4">
                                <div class="icon mb-3">
                                    <img class="img-fluid" src="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f964.png" alt="Beverages">
                                </div>
                                <h6>Beverages</h6>
                                <span>54 Products</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.7s">
                        <a class="cat-item d-block bg-light text-center rounded p-3" href="">
                            <div class="rounded p-4">
                                <div class="icon mb-3">
                                    <img class="img-fluid" src="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f36a.png" alt="Snacks">
                                </div>
                                <h6>Snacks</h6>
                                <span>76 Products</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Category End -->


       


        <!-- Property List Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="row g-0 gx-5 align-items-end">
                    <div class="col-lg-6">
                        <div class="text-start mx-auto mb-5 wow slideInLeft" data-wow-delay="0.1s">
                            <h1 class="mb-3">Products</h1>
                            <!-- <p>Eirmod sed ipsum dolor sit rebum labore magna erat. Tempor ut dolore lorem kasd vero ipsum sit eirmod sit diam justo sed rebum.</p> -->
                        </div>
                    </div>
                    <div class="col-lg-6 text-start text-lg-end wow slideInRight" data-wow-delay="0.1s">
                        <ul class="nav nav-pills d-inline-flex justify-content-end mb-5">
                            <li class="nav-item me-2">
                                <a class="btn btn-outline-primary active" data-bs-toggle="pill" href="#tab-1">Featured</a>
                            </li>
                            <li class="nav-item me-2">
                                <a class="btn btn-outline-primary" data-bs-toggle="pill" href="#tab-2">Top Rated</a>
                            </li>
                            <li class="nav-item me-0">
                                <a class="btn btn-outline-primary" data-bs-toggle="pill" href="#tab-3">Discounted</a>
                            </li>
                        </ul>
                    </div>
                </div>

                 <div class="tab-content">
                    <div id="tab-1" class="tab-pane fade show p-0 active">
                        <div class="row g-4">
                           
                             <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                                <div class="property-item rounded overflow-hidden">
                                    <div class="position-relative overflow-hidden">
                                        <a href="#"><img class="img-fluid" src="https://images.pexels.com/photos/461428/pexels-photo-461428.jpeg?auto=compress&w=800&q=80" alt="Rice Pack"></a>
                                        <div class="bg-primary rounded text-white position-absolute start-0 top-0 m-4 py-1 px-3">Featured</div>
                                        <div class="bg-white rounded-top text-primary position-absolute start-0 bottom-0 mx-4 pt-1 px-3">Grains</div>
                                    </div>
                                    <div class="p-4 pb-0">
                                        <h5 class="text-primary mb-3">$12.99</h5>
                                        <a class="d-block h5 mb-2" href="#">Premium Basmati Rice Pack</a>
                                        <p><i class="fa fa-map-marker-alt text-primary me-2"></i>Harvested in Punjab, India</p>
                                    </div>
                                    <div class="d-flex border-top">
                                        <small class="flex-fill text-center border-end py-2"><i class="fa fa-weight text-primary me-2"></i>5kg Pack</small>
                                        <small class="flex-fill text-center border-end py-2"><i class="fa fa-leaf text-primary me-2"></i>Non-GMO</small>
                                        <small class="flex-fill text-center py-2"><i class="fa fa-star text-primary me-2"></i>4.7/5</small>
                                    </div>

                                    <div class="d-flex border-top justify-content-center">
                                      <button class="w-100 btn btn-success">Coming Soon</button>
                                    </div>



                                </div>
                            </div>
                            <div class="col-12 text-center wow fadeInUp" data-wow-delay="0.1s">
                                <a class="btn btn-primary py-3 px-5" href="">Browse More</a>
                            </div>
                        </div>
                    </div>
                    <div id="tab-2" class="tab-pane  show p-0">
                        <div class="row g-4">
                             <div class="col-lg-4 col-md-6  " >
                                <div class="property-item rounded overflow-hidden">
                                    <div class="position-relative overflow-hidden">
                                        <a href="#"><img class="img-fluid" src="https://images.pexels.com/photos/461428/pexels-photo-461428.jpeg?auto=compress&w=800&q=80" alt="Rice Pack"></a>
                                        <div class="bg-primary rounded text-white position-absolute start-0 top-0 m-4 py-1 px-3">Top Rated</div>
                                        <div class="bg-white rounded-top text-primary position-absolute start-0 bottom-0 mx-4 pt-1 px-3">Grains</div>
                                    </div>
                                    <div class="p-4 pb-0">
                                        <h5 class="text-primary mb-3">$12.99</h5>
                                        <a class="d-block h5 mb-2" href="#">Premium Basmati Rice Pack</a>
                                        <p><i class="fa fa-map-marker-alt text-primary me-2"></i>Harvested in Punjab, India</p>
                                    </div>
                                    <div class="d-flex border-top">
                                        <small class="flex-fill text-center border-end py-2"><i class="fa fa-weight text-primary me-2"></i>5kg Pack</small>
                                        <small class="flex-fill text-center border-end py-2"><i class="fa fa-leaf text-primary me-2"></i>Non-GMO</small>
                                        <small class="flex-fill text-center py-2"><i class="fa fa-star text-primary me-2"></i>4.7/5</small>
                                    </div>

                                      <div class="d-flex border-top justify-content-center">
                                      <button class="w-100 btn btn-success">Coming Soon</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 text-center">
                                <a class="btn btn-primary py-3 px-5" href="">Browse More</a>
                            </div>
                        </div>
                    </div>
                    <div id="tab-3" class="tab-pane  show p-0">
                        <div class="row g-4">
                           
                           <div class="col-lg-4 col-md-6  " >
                                <div class="property-item rounded overflow-hidden">
                                    <div class="position-relative overflow-hidden">
                                        <a href="#"><img class="img-fluid" src="https://images.pexels.com/photos/461428/pexels-photo-461428.jpeg?auto=compress&w=800&q=80" alt="Rice Pack"></a>
                                        <div class="bg-primary rounded text-white position-absolute start-0 top-0 m-4 py-1 px-3">Offers</div>
                                        <div class="bg-white rounded-top text-primary position-absolute start-0 bottom-0 mx-4 pt-1 px-3">Grains</div>
                                        
                                     
                                    </div>
                                    <div class="p-4 pb-0">
                                        <h5 class="text-primary mb-3">$12.99 </h5>
                                        <a class="d-block h5 mb-2" href="#">Premium Basmati Rice Pack</a>
                                        <p><i class="fa fa-map-marker-alt text-primary me-2"></i>Harvested in Punjab, India</p>
                                    </div>
                                    <div class="d-flex border-top">
                                        <small class="flex-fill text-center border-end py-2"><i class="fa fa-weight text-primary me-2"></i>5kg Pack</small>
                                        <small class="flex-fill text-center border-end py-2"><i class="fa fa-leaf text-primary me-2"></i>Non-GMO</small>
                                        <small class="flex-fill text-center py-2"><i class="fa fa-star text-primary me-2"></i>4.7/5</small>
                                    </div>
                                      <div class="d-flex border-top justify-content-center">
                                      <button class="w-100 btn btn-success">Coming Soon</button>
                                    </div>





                                  
                                </div>
                            </div>
                            <div class="col-12 text-center">
                                <a class="btn btn-primary py-3 px-5" href="">Browse More</a>
                            </div>
                        </div>
                    </div>
                </div>
                






            </div>
        </div>
        <!-- Property List End -->


       


        


        <!-- Testimonial Start -->
        <div class="container-xxl py-5">
            <div class="container">
            <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <h1 class="mb-3">Our Clients Say!</h1>
            </div>
            <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.1s">
            <?php
            $sql = "SELECT name, job, star, comment FROM clients";
            $result = $conn->query($sql);
            if ($result->num_rows > 0): 
                while ($row = $result->fetch_assoc()): ?>
                    <div class="testimonial-item bg-light rounded p-3">
                        <div class="bg-white border rounded p-4">
                            <p><?= htmlspecialchars($row['comment']) ?></p>
                            <div class="d-flex align-items-center">
                                <div class="ps-3">
                                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($row['name']) ?></h6>
                                    <small><?= htmlspecialchars($row['job']) ?></small>
                                    <div class="mt-1">
                                        <?php for ($i = 0; $i < floor($row['star']); $i++): ?>
                                            <i class="fa fa-star text-warning"></i>
                                        <?php endfor; ?>
                                        <?php if ($row['star'] - floor($row['star']) > 0): ?>
                                            <i class="fa fa-star-half-alt text-warning"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; 
            endif; ?>
        
            
            </div>
            </div>
        </div>
        <!-- Testimonial End -->
        

 <?php include 'foot.php'; ?>