document.addEventListener('DOMContentLoaded', function() {
    // Search form validation
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const from = document.getElementById('from').value;
            const to = document.getElementById('to').value;
            const date = document.getElementById('date').value;

            if (!from || !to || !date) {
                e.preventDefault();
                alert('Please fill in all search fields');
            }
        });
    }

    // Booking form validation
    const bookingForm = document.querySelector('.booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            const passengers = document.querySelectorAll('.passenger-details');
            let valid = true;

            passengers.forEach(passenger => {
                const inputs = passenger.querySelectorAll('input[required]');
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        valid = false;
                    }
                });
            });

            if (!valid) {
                e.preventDefault();
                alert('Please fill in all passenger details');
            }
        });
    }

    // Payment method selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    if (paymentMethods.length > 0) {
        paymentMethods.forEach(method => {
            method.addEventListener('click', function() {
                paymentMethods.forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('payment_method').value = this.dataset.method;
            });
        });
    }

    // Date picker minimum date
    const dateInputs = document.querySelectorAll('input[type="date"]');
    if (dateInputs.length > 0) {
        const today = new Date().toISOString().split('T')[0];
        dateInputs.forEach(input => {
            input.min = today;
        });
    }

    // Initialize date picker
    flatpickr("#date", {
        minDate: "today",
        dateFormat: "Y-m-d"
    });

    // Validate form before submission
    document.querySelector('form').addEventListener('submit', function(e) {
        const from = document.getElementById('from').value;
        const to = document.getElementById('to').value;
        const date = document.getElementById('date').value;
        const passengers = document.getElementById('passengers').value;
        
        if (from === to) {
            e.preventDefault();
            alert('Kota asal dan tujuan tidak boleh sama');
            return false;
        }
        
        if (parseInt(passengers) < 1 || parseInt(passengers) > 10) {
            e.preventDefault();
            alert('Jumlah penumpang harus antara 1-10 orang');
            return false;
        }
        
        return true;
    });
});
