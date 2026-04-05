// public/js/admin/layanan-edit.js

document.addEventListener('DOMContentLoaded', function () {

    // ════════════════════════════════════════
    // AUTO-HIDE SUCCESS MESSAGE
    // ════════════════════════════════════════
    const successMsg = document.getElementById('successMsg');
    if (successMsg) {
        setTimeout(() => {
            successMsg.style.transition = 'opacity 0.5s ease-out';
            successMsg.style.opacity    = '0';
            setTimeout(() => successMsg.remove(), 500);
        }, 5000);
    }

    // ════════════════════════════════════════
    // CHECKBOX VISUAL FEEDBACK
    // ════════════════════════════════════════
    document.querySelectorAll('input[type="checkbox"][name="proses[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const label = this.closest('label');
            if (this.checked) {
                label.classList.add('bg-yellow-50', 'border-yellow-400', 'text-yellow-700');
                label.classList.remove('bg-white', 'border-gray-200');
            } else {
                label.classList.remove('bg-yellow-50', 'border-yellow-400', 'text-yellow-700');
                label.classList.add('bg-white', 'border-gray-200');
            }
        });
    });

});