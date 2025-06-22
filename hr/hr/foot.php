
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </main>
    <script>
        const menuIcon = document.querySelector('.menu-icon');
        const sidebar = document.querySelector('.sidebar');

        menuIcon.addEventListener('click', function() {
            if (sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                menuIcon.textContent = '☰';
            } else {
                sidebar.classList.add('open');
                menuIcon.textContent = '✖';
            }
        });

document.addEventListener('click', (event) => {
    const header = document.querySelector('header');
    const main = document.querySelector('main');

    if ((header.contains(event.target) || main.contains(event.target)) && !menuIcon.contains(event.target)) {
        sidebar.classList.remove('open');
        menuIcon.textContent = '☰';
    }
});
    </script>


</body>
</html>
