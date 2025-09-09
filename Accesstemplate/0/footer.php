  </main>
    </div>

  

    <div class="modal-overlay" id="add-record-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $lang[$language]['add_new_record']; ?></h2>
                <button class="menu-toggle" id="close-modal-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form>
                <div class="form-group">
                    <label for="invoice-number"><?php echo $lang[$language]['invoice']; ?>:</label>
                    <input type="text" id="invoice-number" placeholder="e.g., INV-00128">
                </div>
                <div class="form-group">
                    <label for="customer-name"><?php echo $lang[$language]['customer_name']; ?>:</label>
                    <input type="text" id="customer-name" placeholder="e.g., John Doe">
                </div>
                <div class="form-group">
                    <label for="status"><?php echo $lang[$language]['status']; ?>:</label>
                    <select id="status">
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount"><?php echo $lang[$language]['amount']; ?>:</label>
                    <input type="text" id="amount" placeholder="e.g., $199.00">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancel-add-record"><?php echo $lang[$language]['cancel']; ?></button>
                    <button type="submit" class="btn-submit"><?php echo $lang[$language]['add_record']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>
</html>
