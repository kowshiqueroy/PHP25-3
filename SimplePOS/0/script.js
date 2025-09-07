document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
  
    const mainContainer = document.getElementById('main-container');

    menuToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.classList.toggle('open');
        if (window.innerWidth <= 768) {
            mainContainer.classList.toggle('sidebar-open');
        }
    });

    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('open');
            if (window.innerWidth <= 768) {
                mainContainer.classList.remove('sidebar-open');
            }
        }
    });

  
    const msg = document.getElementById('msg');
    if (msg) {
        setTimeout(function () {
            msg.style.display = 'none';
        }, 5000);
    }
    // Filter Modal functionality
    const filterBtn = document.getElementById('filter-btn');
    const filterModal = document.getElementById('filter-modal');
    const closeFilterModalBtn = document.getElementById('close-filter-modal-btn');
    const cancelFilterBtn = document.getElementById('cancel-filter');

    if (filterBtn) {
        filterBtn.addEventListener('click', () => {
            filterModal.classList.add('open');
        });
    }

    if (closeFilterModalBtn) {
        closeFilterModalBtn.addEventListener('click', () => {
            filterModal.classList.remove('open');
        });
    }

    if (cancelFilterBtn) {
        cancelFilterBtn.addEventListener('click', () => {
            filterModal.classList.remove('open');
        });
    }

    if (filterModal) {
        filterModal.addEventListener('click', (e) => {
            if (e.target === filterModal) {
                filterModal.classList.remove('open');
            }
        });
    }

    // Modal functionality
    const addRecordBtn = document.getElementById('add-new-record-btn');
    const addRecordModal = document.getElementById('add-record-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const cancelAddRecordBtn = document.getElementById('cancel-add-record');
    if (addRecordBtn) {
        addRecordBtn.addEventListener('click', () => {
            addRecordModal.classList.add('open');
        });
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            addRecordModal.classList.remove('open');
        });
    }

    if (cancelAddRecordBtn) {
        cancelAddRecordBtn.addEventListener('click', () => {
            addRecordModal.classList.remove('open');
        });
    }

    if (addRecordModal) {
        addRecordModal.addEventListener('click', (e) => {
            if (e.target === addRecordModal) {
                addRecordModal.classList.remove('open');
            }
        });
    }

    // Add event listener for window resize to handle sidebar state
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('open');
            mainContainer.classList.remove('sidebar-open');
        }
    });
});

