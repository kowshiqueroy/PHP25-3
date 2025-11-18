  </div>
    </div>

    <script>
        // Mobile Sidebar Toggle Logic
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.createElement('div');

        // Create an overlay for mobile when sidebar is open
        overlay.style.cssText = `
            position: fixed; inset: 0; background: rgba(0,0,0,0.5); 
            z-index: 45; opacity: 0; transition: opacity 0.3s; 
            pointer-events: none;
        `;
        document.body.appendChild(overlay);

        menuBtn.addEventListener('click', () => {
            const isActive = sidebar.style.transform === 'translateX(0%)';
            if (isActive) {
                sidebar.style.transform = 'translateX(-100%)';
                overlay.style.opacity = '0';
                overlay.style.pointerEvents = 'none';
            } else {
                sidebar.style.transform = 'translateX(0%)';
                overlay.style.opacity = '1';
                overlay.style.pointerEvents = 'auto';
            }
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', () => {
            sidebar.style.transform = 'translateX(-100%)';
            overlay.style.opacity = '0';
            overlay.style.pointerEvents = 'none';
        });
    </script>
</body>
</html>