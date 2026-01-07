import './bootstrap';

import Alpine from 'alpinejs';
import api, { orderApi, inventoryApi } from './api';
import { showSuccess, showError, showInfo, handleApiError } from './notifications';

window.Alpine = Alpine;
window.api = api;
window.orderApi = orderApi;
window.inventoryApi = inventoryApi;
window.showSuccess = showSuccess;
window.showError = showError;
window.showInfo = showInfo;
window.handleApiError = handleApiError;

Alpine.start();
