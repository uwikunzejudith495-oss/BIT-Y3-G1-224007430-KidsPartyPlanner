document.addEventListener('DOMContentLoaded', function () {

    // MOBILE NAVIGATION MENU
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function () {
            mainNav.classList.toggle('show');
        });
    }

    // FORM VALIDATION
    const forms = document.querySelectorAll('form');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const password = form.querySelector('input[name="password"]');

            if (password && password.value.length < 8) {
                event.preventDefault();
                alert('Password must contain at least 8 characters.');
                password.focus();
                return;
            }

            const email = form.querySelector('input[type="email"]');

            if (email && !email.checkValidity()) {
                event.preventDefault();
                alert('Please enter a valid email address.');
                email.focus();
            }
        });
    });

    // CONFIRM DELETE ACTIONS
    const deleteButtons = document.querySelectorAll('.danger');

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            const form = button.closest('form');

            if (form && !button.hasAttribute('onclick')) {
                const confirmed = confirm('Are you sure you want to delete this item?');

                if (!confirmed) {
                    event.preventDefault();
                }
            }
        });
    });

    // VENDOR LIVE SEARCH
    const searchInput = document.getElementById('vendorSearch');
    const vendorList = document.getElementById('vendorList');

    if (searchInput && vendorList) {
        searchInput.addEventListener('input', async function () {
            const query = searchInput.value.trim();

            try {
                const response = await fetch(
                    'api/vendors.php?q=' + encodeURIComponent(query)
                );

                if (!response.ok) {
                    throw new Error('Could not load vendors.');
                }

                const vendors = await response.json();
                vendorList.innerHTML = '';

                if (vendors.length === 0) {
                    vendorList.textContent = 'No vendors found.';
                    return;
                }

                vendors.forEach(function (vendor) {
                    const item = document.createElement('div');
                    item.className = 'vendor-result';

                    const name = document.createElement('strong');
                    name.textContent = vendor.name;

                    const service = document.createElement('p');
                    service.textContent = 'Service: ' + vendor.service_type;

                    const price = document.createElement('p');
                    price.textContent = 'Price: ' + vendor.price;

                    item.appendChild(name);
                    item.appendChild(service);
                    item.appendChild(price);
                    vendorList.appendChild(item);
                });

            } catch (error) {
                vendorList.textContent = 'Unable to load vendors. Please try again.';
            }
        });
    }

});