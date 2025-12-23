<?php include 'header.php'; ?>

<main>

    <section class="search-section no-print">
        <h2>Students</h2>
        <form method="GET" action="id.php">
            <div class="search-grid">
                <input type="text" name="search_id" placeholder="reg id,id,id or id-id" value="<?php if (isset($_GET['search_id'])) echo htmlspecialchars($_GET['search_id']); ?>" />
                <button type="submit">Search</button>
            </div>
        </form>
    </section>

    
    

</main>

<?php
require_once 'footer.php';
?>