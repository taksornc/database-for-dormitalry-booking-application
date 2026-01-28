document.addEventListener('DOMContentLoaded', function() {
    const paymentTypeSelect = document.getElementById('payment_type');
    const paymentMethodSelect = document.getElementById('payment_method');

    const paymentMethods = {
        'Room Booking': [
            'KBank Mobile Banking',
            'SCB Easy',
            'Bangkok Bank Mobile Banking',
            'Krunthai NEXT',
            'GSB Mobile Banking'
        ],
        'Credit Card': [
            'Visa',
            'MasterCard',
            'JCB'
        ],
        'QR Code': [
            'PromptPay QR',
            'Thai QR Payment',
            'Mobile Banking QR Scan'
        ]
    };

    function updatePaymentMethods() {
        const selectedType = paymentTypeSelect.value;
        const methods = paymentMethods[selectedType] || [];

        // Clear existing options
        paymentMethodSelect.innerHTML = '<option value="">-- เลือกวิธีการชำระเงิน --</option>';

        // Add new options
        methods.forEach(method => {
            const option = document.createElement('option');
            option.value = method;
            option.textContent = method;
            paymentMethodSelect.appendChild(option);
        });
    }

    paymentTypeSelect.addEventListener('change', updatePaymentMethods);
    
    // Initialize payment methods for initial payment type
    updatePaymentMethods();
});