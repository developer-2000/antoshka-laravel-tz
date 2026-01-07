export function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    const textColor = 'text-white';
    
    notification.className = `fixed top-4 right-4 ${bgColor} ${textColor} px-6 py-4 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-0`;
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

export function showError(message) {
    showNotification(message, 'error');
}

export function showSuccess(message) {
    showNotification(message, 'success');
}

export function showInfo(message) {
    showNotification(message, 'info');
}

export function handleApiError(error) {
    if (error.response) {
        if (error.response.status === 422) {
            const errors = error.response.data.errors || {};
            let message = error.response.data.message || 'Ошибка валидации';
            
            if (Object.keys(errors).length > 0) {
                const firstError = Object.values(errors)[0];
                message = Array.isArray(firstError) ? firstError[0] : firstError;
            }
            
            showError(message);
            return errors;
        } else if (error.response.status === 404) {
            showError('Ресурс не найден');
        } else if (error.response.status === 500) {
            showError('Внутренняя ошибка сервера');
        } else {
            showError(error.response.data.message || 'Произошла ошибка');
        }
    } else if (error.request) {
        showError('Ошибка сети. Проверьте подключение к интернету.');
    } else {
        showError('Произошла непредвиденная ошибка');
    }
    
    return null;
}

