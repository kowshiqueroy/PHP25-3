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

        // --- SEARCHABLE SELECT LOGIC ---
const comboInput = document.getElementById('comboInput');
const comboOptions = document.getElementById('comboOptions');
const optionItems = document.querySelectorAll('.option-item');
const hiddenValue = document.getElementById('selectedValue');

// 1. Show options when input is focused
comboInput.addEventListener('focus', () => {
    comboOptions.classList.add('show');
});

// 2. Filter options as user types
comboInput.addEventListener('input', (e) => {
    const filterText = e.target.value.toLowerCase();
    
    optionItems.forEach(item => {
        const text = item.innerText.toLowerCase();
        if (text.includes(filterText)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
    
    comboOptions.classList.add('show'); // Ensure it stays open while typing
});

// 3. Handle Click on an Option
optionItems.forEach(item => {
    item.addEventListener('click', () => {
        // Set visible text
        comboInput.value = item.innerText;
        // Set hidden ID value (for backend)
        hiddenValue.value = item.getAttribute('data-value');
        // Hide dropdown
        comboOptions.classList.remove('show');
    });
});

// 4. Close dropdown if clicking outside
document.addEventListener('click', (e) => {
    // If click is NOT inside the wrapper, close the dropdown
    if (!e.target.closest('.custom-select-wrapper')) {
        comboOptions.classList.remove('show');
    }
});
    </script>
</body>
</html>