<div id="customAlert" class="custom-alert">
    <i class="fas fa-check-circle"></i>
    <span id="alertMessage"></span>
</div>

<style>
    :root {
        --success: #10b981;
        --success-light: #d1fae5;
        --danger: #ef4444;
        --danger-light: #fee2e2;
    }

    .custom-alert {
        position: fixed;
        top: 10px;
        right: -5px;
        background: #333;
        color: #fff;
        padding: 12px 18px;
        border-radius: 8px;
        font-size: 15px;
        font-family: "Poppins", sans-serif;
        display: flex;
        align-items: center;
        gap: 10px;
        opacity: 0;
        transform: translateX(120%);
        transition: all 0.4s ease;
        z-index: 9999;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
    }

    .custom-alert i {
        font-size: 18px;
    }

    .custom-alert.show {
        opacity: 1;
        transform: translateX(0);
    }

    .custom-alert.success {
        background: var(--success-light);
        color: var(--success);
    }

    .custom-alert.error {
        background: var(--danger-light);
        color: var(--danger);
    }
</style>

<script>
function showAlert(message, type = 'success') {
    const alertBox = document.getElementById('customAlert');
    const alertMessage = document.getElementById('alertMessage');
    const icon = alertBox.querySelector('i');
    
    alertBox.classList.remove('success', 'error');
    if (type === 'success') {
        alertBox.classList.add('success');
        icon.className = 'fas fa-check-circle';
    } else {
        alertBox.classList.add('error');
        icon.className = 'fas fa-times-circle';
    }

    alertMessage.textContent = message;

    alertBox.classList.add('show');

    setTimeout(() => {
        alertBox.classList.remove('show');
    }, 3000);
}

</script>
