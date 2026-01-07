import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

api.interceptors.request.use(
    (config) => {
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            config.headers['X-CSRF-TOKEN'] = token.content;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

api.interceptors.response.use(
    (response) => {
        return response;
    },
    async (error) => {
        const originalRequest = error.config;

        if (error.response) {
            if (error.response.status === 422) {
                return Promise.reject(error);
            }

            if (error.response.status === 500) {
                if (!originalRequest._retry) {
                    originalRequest._retry = true;
                    return api(originalRequest);
                }
            }
        }

        if (error.code === 'ECONNABORTED' || error.message === 'Network Error') {
            if (!originalRequest._retry) {
                originalRequest._retry = true;
                return new Promise((resolve) => {
                    setTimeout(() => {
                        resolve(api(originalRequest));
                    }, 1000);
                });
            }
        }

        return Promise.reject(error);
    }
);

export default api;

export const orderApi = {
    create: (data) => api.post('/order', data),
    get: (id) => api.get(`/orders/${id}`),
};

export const inventoryApi = {
    getMovements: (sku) => api.get(`/inventory/${sku}/movements`),
};

