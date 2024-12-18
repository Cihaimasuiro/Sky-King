// Handle data table pagination
document.addEventListener('DOMContentLoaded', function() {
    const entriesSelect = document.querySelector('.entries-select');
    if (entriesSelect) {
        entriesSelect.addEventListener('change', function() {
            // Update number of visible rows
            // To be implemented with backend pagination
        });
    }

    // Handle search functionality
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.data-table tbody tr');

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Handle edit button clicks
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const id = row.dataset.id;
            // Implement edit functionality
            console.log('Edit clicked for ID:', id);
        });
    });

    // Handle delete button clicks
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const id = row.dataset.id;
            if (confirm('Are you sure you want to delete this item?')) {
                // Implement delete functionality
                console.log('Delete clicked for ID:', id);
            }
        });
    });
});
