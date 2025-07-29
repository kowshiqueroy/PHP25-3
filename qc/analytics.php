<?php include_once 'header.php'; ?>



            <div class="cards-container">
                <div class="card">
                    <h3><?php echo $lang[$language]['total_revenue']; ?></h3>
                    <div class="value">$45,231.89</div>
                    <div class="details">+15% from last month</div>
                </div>
                <div class="card">
                    <h3><?php echo $lang[$language]['subscriptions']; ?></h3>
                    <div class="value">1,234</div>
                    <div class="details">+20% from last month</div>
                </div>
                <div class="card">
                    <h3><?php echo $lang[$language]['sales']; ?></h3>
                    <div class="value">897</div>
                    <div class="details">+10% from last month</div>
                </div>
                <div class="card">
                    <h3><?php echo $lang[$language]['active_now']; ?></h3>
                    <div class="value">256</div>
                    <div class="details">Currently online</div>
                </div>
            </div>



         

            <div class="table-container">
                <div class="table-header">
                    <h2><?php echo $lang[$language]['recent_orders']; ?></h2>
                    <div class="table-actions">
                        <div class="dropdown">
                            <button class="btn"><?php echo $lang[$language]['export_options']; ?></button>
                            <div class="dropdown-content">
                                <a href="#"><?php echo $lang[$language]['export_csv']; ?></a>
                                <a href="#"><?php echo $lang[$language]['export_pdf']; ?></a>
                            </div>
                        </div>
                        <button class="btn" id="filter-btn"><?php echo $lang[$language]['filter']; ?></button>
                       
                    </div>
                </div>
              

                <div class="filter-modal-overlay" id="filter-modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2><?php echo $lang[$language]['filter']; ?> Options</h2>
                            <button class="menu-toggle" id="close-filter-modal-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <form>
                            <div class="form-group">
                                <label for="filter-status"><?php echo $lang[$language]['status']; ?>:</label>
                                <select id="filter-status" class="modern-select">
                                    <option value="">All</option>
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="filter-amount-min"><?php echo $lang[$language]['amount']; ?> (Min):</label>
                                <input type="number" id="filter-amount-min" class="modern-input" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label for="filter-amount-max"><?php echo $lang[$language]['amount']; ?> (Max):</label>
                                <input type="number" id="filter-amount-max" class="modern-input" placeholder="9999.99">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn-cancel" id="cancel-filter"><?php echo $lang[$language]['cancel']; ?></button>
                                <button type="submit" class="btn-submit"><?php echo $lang[$language]['filter']; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="responsive-table-container">
                    <table class="data-table" id="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $lang[$language]['invoice']; ?></th>
                                <th><?php echo $lang[$language]['customer_name']; ?></th>
                                <th><?php echo $lang[$language]['status']; ?></th>
                                <th><?php echo $lang[$language]['amount']; ?></th>
                                <th><?php echo $lang[$language]['date']; ?></th>
                                <th><?php echo $lang[$language]['payment_method']; ?></th>
                                <th><?php echo $lang[$language]['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="collapsible-row">
                                <td>INV-00128</td>
                                <td>John Doe</td>
                                <td>Paid</td>
                                <td>$100.00</td>
                                <td>2022-01-01</td>
                                <td>Bank Transfer</td>
                                <td>
                                    <button class="btn-edit" data-invoice="INV-00128">✏️</button>
                                    <button class="btn-delete" data-invoice="INV-00128">❌</button>
                                </td>
                            </tr>
                            <tr class="collapsible-row">
                                <td>INV-00129</td>
                                <td>Jane Smith</td>
                                <td>Pending</td>
                                <td>$200.00</td>
                                <td>2022-01-02</td>
                                <td>PayPal</td>
                                <td>
                                    <button class="btn-edit" data-invoice="INV-00129">Edit</button>
                                    <button class="btn-delete" data-invoice="INV-00129">Delete</button>
                                </td>
                            </tr>
                            <tr class="collapsible-row">
                                <td>INV-00130</td>
                                <td>Mike Johnson</td>
                                <td>Refunded</td>
                                <td>$300.00</td>
                                <td>2022-01-03</td>
                                <td>Credit Card</td>
                                <td>
                                    <button class="btn-edit" data-invoice="INV-00130">Edit</button>
                                    <button class="btn-delete" data-invoice="INV-00130">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>


       
                </div>




            </div>

 


<?php include_once 'footer.php'; ?>

      