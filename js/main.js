document.addEventListener('DOMContentLoaded', function() {
    // Initialize GSAP animations
    initializeAnimations();

    // Initialize sorting
    initializeTableSorting();

    // Modal handling
    const addProductBtn = document.getElementById('addProductBtn');
    const modal = document.getElementById('addProductModal');
    const closeModal = document.querySelector('.close-modal');
    const productForm = document.getElementById('productForm');
    const searchInput = document.getElementById('searchProducts');
    let isEditing = false;

    if (addProductBtn) {
        addProductBtn.addEventListener('click', () => {
            isEditing = false;
            productForm.reset();
            document.getElementById('product_id').value = '';
            modal.querySelector('h2').textContent = 'Add New Product';
            modal.querySelector('button[type="submit"]').textContent = 'Add Product';
            showModal();
        });
    }

    if (closeModal) {
        closeModal.addEventListener('click', hideModal);
    }

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            hideModal();
        }
    });

    // Edit product
    document.addEventListener('click', (e) => {
        if (e.target.closest('.edit-product')) {
            const button = e.target.closest('.edit-product');
            isEditing = true;
            
            // Fill form with product data
            document.getElementById('product_id').value = button.dataset.id;
            document.getElementById('product_name').value = button.dataset.name;
            document.getElementById('category').value = button.dataset.category;
            document.getElementById('price').value = button.dataset.price;
            document.getElementById('stock').value = button.dataset.stock;

            // Update modal title and button
            modal.querySelector('h2').textContent = 'Edit Product';
            modal.querySelector('button[type="submit"]').textContent = 'Save Changes';
            showModal();
        }
    });

    // Form submission
    if (productForm) {
        productForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(productForm);
            const url = isEditing ? 'includes/update_product.php' : 'includes/add_product.php';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    showNotification(isEditing ? 'Product updated successfully!' : 'Product added successfully!', 'success');
                    hideModal();
                    productForm.reset();
                    // Refresh notifications after successful operation
                    await loadNotifications();
                    // Show notifications dropdown
                    notificationsDropdown.classList.add('show');
                    window.location.reload();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred while saving the product', 'error');
            }
        });
    }

    // Search functionality with sorting preservation
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.products-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    gsap.to(row, {
                        opacity: 1,
                        height: 'auto',
                        duration: 0.3,
                        display: 'table-row'
                    });
                } else {
                    gsap.to(row, {
                        opacity: 0,
                        height: 0,
                        duration: 0.3,
                        display: 'none'
                    });
                }
            });
        }, 300));
    }

    // Delete product
    document.addEventListener('click', async (e) => {
        const deleteButton = e.target.closest('.delete-product');
        if (deleteButton) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this product?')) {
                const productId = deleteButton.dataset.id;
                const row = deleteButton.closest('tr');
                
                try {
                    const response = await fetch('includes/delete_product.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: productId })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        gsap.to(row, {
                            opacity: 0,
                            height: 0,
                            duration: 0.3,
                            onComplete: async () => {
                                row.remove();
                                showNotification('Product deleted successfully!', 'success');
                                // Refresh notifications after successful deletion
                                await loadNotifications();
                                // Show notifications dropdown
                                notificationsDropdown.classList.add('show');
                            }
                        });
                    } else {
                        showNotification('Error: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showNotification('An error occurred while deleting the product', 'error');
                }
            }
        }
    });

    // Notification handling
    const notificationIcon = document.querySelector('.notification-icon');
    const notificationBadge = document.querySelector('.notification-badge');
    const notificationsDropdown = document.querySelector('.notifications-dropdown');
    const notificationsList = document.querySelector('.notifications-list');
    const markAllReadBtn = document.querySelector('.mark-all-read');

    // Load notifications
    async function loadNotifications() {
        try {
            const response = await fetch('includes/notifications.php');
            const data = await response.json();
            
            if (data.success) {
                updateNotificationsUI(data.notifications);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    // Update notifications UI
    function updateNotificationsUI(notifications) {
        notificationsList.innerHTML = '';
        notificationBadge.textContent = notifications.length;

        if (notifications.length === 0) {
            notificationsList.innerHTML = '<div class="notification-item">No new notifications</div>';
            return;
        }

        notifications.forEach(notification => {
            const notificationItem = document.createElement('div');
            notificationItem.className = `notification-item ${notification.type}`;
            notificationItem.dataset.id = notification.id;
            
            const icon = document.createElement('div');
            icon.className = 'notification-icon-wrapper';
            
            const iconSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            iconSvg.setAttribute('width', '24');
            iconSvg.setAttribute('height', '24');
            iconSvg.setAttribute('viewBox', '0 0 24 24');
            iconSvg.setAttribute('fill', 'none');
            iconSvg.setAttribute('stroke', 'currentColor');
            
            if (notification.type === 'success') {
                iconSvg.innerHTML = '<path d="M20 6L9 17L4 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
            } else if (notification.type === 'error') {
                iconSvg.innerHTML = '<path d="M18 6L6 18M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
            } else {
                iconSvg.innerHTML = '<path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
            }
            
            icon.appendChild(iconSvg);
            
            const content = document.createElement('div');
            content.className = 'notification-content';
            
            const title = document.createElement('div');
            title.className = 'notification-title';
            title.textContent = notification.message;
            
            const time = document.createElement('div');
            time.className = 'notification-time';
            time.textContent = new Date(notification.created_at).toLocaleString();
            
            content.appendChild(title);
            content.appendChild(time);
            
            notificationItem.appendChild(icon);
            notificationItem.appendChild(content);
            notificationsList.appendChild(notificationItem);
        });
    }

    // Toggle notifications dropdown
    notificationIcon.addEventListener('click', () => {
        notificationsDropdown.classList.toggle('show');
        if (notificationsDropdown.classList.contains('show')) {
            loadNotifications();
        }
    });

    // Mark all notifications as read
    markAllReadBtn.addEventListener('click', async () => {
        const unreadNotifications = document.querySelectorAll('.notification-item:not(.read)');
        const ids = Array.from(unreadNotifications).map(item => item.dataset.id);
        
        if (ids.length > 0) {
            try {
                const response = await fetch('includes/notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ ids })
                });
                
                const data = await response.json();
                if (data.success) {
                    loadNotifications();
                }
            } catch (error) {
                console.error('Error marking notifications as read:', error);
            }
        }
    });

    // Close notifications dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!notificationIcon.contains(e.target) && !notificationsDropdown.contains(e.target)) {
            notificationsDropdown.classList.remove('show');
        }
    });

    // Load notifications periodically
    setInterval(loadNotifications, 30000); // Check every 30 seconds
    loadNotifications(); // Initial load
});

// Initialize GSAP animations
function initializeAnimations() {
    // Hide loading screen
    const loadingScreen = document.querySelector('.loading-overlay');
    gsap.to(loadingScreen, {
        opacity: 0,
        duration: 0.5,
        delay: 0.5,
        onComplete: () => {
            loadingScreen.style.display = 'none';
            animateContent();
        }
    });
}

// Animate main content
function animateContent() {
    // Fade in container
    gsap.to('.container', {
        opacity: 1,
        duration: 0.5
    });

    // Animate logo
    gsap.from('.logo svg', {
        rotate: 360,
        duration: 1,
        ease: 'power2.out'
    });

    // Animate stat cards
    gsap.from('.stat-card', {
        y: 30,
        opacity: 0,
        duration: 0.5,
        stagger: 0.1,
        ease: 'power2.out'
    });

    // Animate numbers
    const numberElements = document.querySelectorAll('.number');
    numberElements.forEach(el => {
        const value = parseInt(el.dataset.value);
        gsap.to(el, {
            innerHTML: value,
            duration: 1.5,
            snap: { innerHTML: 1 },
            ease: 'power1.out'
        });
    });

    // Animate table
    gsap.to('.products-table', {
        opacity: 1,
        duration: 0.5,
        delay: 0.3
    });

    gsap.from('.product-row', {
        opacity: 0,
        y: 20,
        duration: 0.5,
        stagger: 0.05,
        ease: 'power2.out',
        delay: 0.5
    });
}

// Modal animations
function showModal() {
    const modal = document.getElementById('addProductModal');
    const modalContent = modal.querySelector('.modal-content');
    
    modal.style.display = 'block';
    gsap.to(modal, {
        opacity: 1,
        duration: 0.3
    });
    
    gsap.from(modalContent, {
        y: -50,
        opacity: 0,
        duration: 0.3,
        ease: 'power2.out'
    });
}

function hideModal() {
    const modal = document.getElementById('addProductModal');
    const modalContent = modal.querySelector('.modal-content');
    
    gsap.to(modalContent, {
        y: -50,
        opacity: 0,
        duration: 0.3,
        ease: 'power2.in'
    });
    
    gsap.to(modal, {
        opacity: 0,
        duration: 0.3,
        onComplete: () => {
            modal.style.display = 'none';
            modalContent.style.transform = 'none';
            modalContent.style.opacity = '1';
        }
    });
}

// Show notification
function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    // Animate notification
    gsap.fromTo(notification,
        {
            x: 100,
            opacity: 0
        },
        {
            x: 0,
            opacity: 1,
            duration: 0.3,
            ease: 'power2.out',
            onComplete: () => {
                setTimeout(() => {
                    gsap.to(notification, {
                        x: 100,
                        opacity: 0,
                        duration: 0.3,
                        ease: 'power2.in',
                        onComplete: () => notification.remove()
                    });
                }, 3000);
            }
        }
    );
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Table sorting functionality
function initializeTableSorting() {
    const table = document.querySelector('.products-table table');
    const headers = table.querySelectorAll('th');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Add sortable class and click event to sortable columns
    headers.forEach((header, index) => {
        if (index !== 5) { // Skip the Actions column
            header.classList.add('sortable');
            header.addEventListener('click', () => {
                const isAscending = !header.classList.contains('sorted-asc');
                
                // Remove sorted classes from all headers
                headers.forEach(h => {
                    h.classList.remove('sorted-asc', 'sorted-desc');
                });

                // Add sorted class to clicked header
                header.classList.add(isAscending ? 'sorted-asc' : 'sorted-desc');

                // Sort the rows
                const sortedRows = sortRows(rows, index, isAscending);
                
                // Animate the rows
                animateSort(sortedRows);
            });
        }
    });
}

function sortRows(rows, columnIndex, isAscending) {
    return rows.sort((a, b) => {
        let aValue = a.cells[columnIndex].textContent.trim();
        let bValue = b.cells[columnIndex].textContent.trim();

        // Handle number sorting (price and stock)
        if (columnIndex === 2) { // Price column
            aValue = parseFloat(aValue.replace('$', '').replace(',', ''));
            bValue = parseFloat(bValue.replace('$', '').replace(',', ''));
        } else if (columnIndex === 3) { // Stock column
            aValue = parseInt(aValue);
            bValue = parseInt(bValue);
        }

        if (aValue < bValue) return isAscending ? -1 : 1;
        if (aValue > bValue) return isAscending ? 1 : -1;
        return 0;
    });
}

function animateSort(sortedRows) {
    const tbody = document.querySelector('.products-table tbody');
    const duration = 0.3;
    const stagger = 0.02;

    // First, animate all rows up
    gsap.to('.product-row', {
        y: -10,
        opacity: 0,
        duration: duration / 2,
        stagger: stagger,
        onComplete: () => {
            // Reorder the DOM elements
            sortedRows.forEach(row => tbody.appendChild(row));

            // Then animate them back down
            gsap.from('.product-row', {
                y: 10,
                opacity: 0,
                duration: duration / 2,
                stagger: stagger,
                clearProps: 'all'
            });
        }
    });
} 