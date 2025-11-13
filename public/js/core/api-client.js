/**
 * UniPrint API Client
 * Centralized HTTP client with error handling, caching, and state integration
 */

class ApiClient {
    constructor() {
        this.baseURL = window.location.origin;
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            this.defaultHeaders['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
        }
        
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
        this.requestQueue = new Map();
        this.retryAttempts = 3;
        this.retryDelay = 1000;
        
        this.setupInterceptors();
    }
    
    /**
     * Set up request/response interceptors
     */
    setupInterceptors() {
        // Request interceptor
        this.requestInterceptor = (config) => {
            // Add loading state
            if (window.stateManager) {
                window.stateManager.setState('ui.loading', true);
            }
            
            return config;
        };
        
        // Response interceptor
        this.responseInterceptor = (response) => {
            // Remove loading state
            if (window.stateManager) {
                window.stateManager.setState('ui.loading', false);
            }
            
            return response;
        };
        
        // Error interceptor
        this.errorInterceptor = (error) => {
            // Remove loading state
            if (window.stateManager) {
                window.stateManager.setState('ui.loading', false);
            }
            
            // Handle common errors
            this.handleError(error);
            
            return Promise.reject(error);
        };
    }
    
    /**
     * Make HTTP request
     */
    async request(config) {
        const {
            url,
            method = 'GET',
            data = null,
            headers = {},
            cache = false,
            retry = true,
            timeout = 10000
        } = config;
        
        // Apply request interceptor
        const processedConfig = this.requestInterceptor({
            ...config,
            headers: { ...this.defaultHeaders, ...headers }
        });
        
        const fullUrl = url.startsWith('http') ? url : `${this.baseURL}${url}`;
        const cacheKey = `${method}:${fullUrl}:${JSON.stringify(data)}`;
        
        // Check cache for GET requests
        if (method === 'GET' && cache && this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < this.cacheTimeout) {
                return Promise.resolve(cached.data);
            }
        }
        
        // Check if request is already in progress
        if (this.requestQueue.has(cacheKey)) {
            return this.requestQueue.get(cacheKey);
        }
        
        // Create request promise
        const requestPromise = this.executeRequest(processedConfig, fullUrl, timeout);
        
        // Add to queue
        this.requestQueue.set(cacheKey, requestPromise);
        
        try {
            const response = await requestPromise;
            
            // Cache GET responses
            if (method === 'GET' && cache) {
                this.cache.set(cacheKey, {
                    data: response,
                    timestamp: Date.now()
                });
            }
            
            // Apply response interceptor
            return this.responseInterceptor(response);
            
        } catch (error) {
            // Retry logic
            if (retry && error.status >= 500 && error.retryCount < this.retryAttempts) {
                await this.delay(this.retryDelay * Math.pow(2, error.retryCount));
                error.retryCount = (error.retryCount || 0) + 1;
                return this.request({ ...config, retry: true });
            }
            
            throw this.errorInterceptor(error);
            
        } finally {
            // Remove from queue
            this.requestQueue.delete(cacheKey);
        }
    }
    
    /**
     * Execute the actual HTTP request
     */
    async executeRequest(config, url, timeout) {
        const { method, data, headers } = config;
        
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeout);
        
        try {
            const fetchConfig = {
                method,
                headers,
                signal: controller.signal
            };
            
            if (data) {
                if (data instanceof FormData) {
                    // Remove content-type for FormData
                    delete fetchConfig.headers['Content-Type'];
                    fetchConfig.body = data;
                } else {
                    fetchConfig.body = JSON.stringify(data);
                }
            }
            
            const response = await fetch(url, fetchConfig);
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw await this.createError(response);
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            }
            
            return await response.text();
            
        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                throw this.createError(null, 'Request timeout', 408);
            }
            
            throw error;
        }
    }
    
    /**
     * Create standardized error object
     */
    async createError(response, message = null, status = null) {
        const error = new Error(message || 'Request failed');
        error.status = status || (response ? response.status : 0);
        error.statusText = response ? response.statusText : '';
        
        if (response) {
            try {
                const errorData = await response.json();
                error.data = errorData;
                error.message = errorData.message || error.message;
            } catch {
                error.data = await response.text();
            }
        }
        
        return error;
    }
    
    /**
     * Handle common errors
     */
    handleError(error) {
        const { status } = error;
        
        switch (status) {
            case 401:
                // Unauthorized - redirect to login
                if (window.stateManager) {
                    window.stateManager.setState('user.isAuthenticated', false);
                }
                this.showNotification('Session expired. Please log in again.', 'error');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
                break;
                
            case 403:
                // Forbidden
                this.showNotification('You do not have permission to perform this action.', 'error');
                break;
                
            case 404:
                // Not found
                this.showNotification('The requested resource was not found.', 'error');
                break;
                
            case 422:
                // Validation error
                if (error.data && error.data.errors) {
                    Object.values(error.data.errors).flat().forEach(message => {
                        this.showNotification(message, 'error');
                    });
                } else {
                    this.showNotification('Validation failed. Please check your input.', 'error');
                }
                break;
                
            case 429:
                // Rate limited
                this.showNotification('Too many requests. Please try again later.', 'warning');
                break;
                
            case 500:
                // Server error
                this.showNotification('Server error. Please try again later.', 'error');
                break;
                
            default:
                if (status >= 400) {
                    this.showNotification(error.message || 'An error occurred.', 'error');
                }
        }
    }
    
    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        if (window.stateManager) {
            const notification = {
                id: Date.now(),
                message,
                type,
                timestamp: new Date()
            };
            
            const notifications = window.stateManager.getState('ui.notifications') || [];
            window.stateManager.setState('ui.notifications', [...notifications, notification]);
        } else {
            // Fallback to alert
            alert(message);
        }
    }
    
    /**
     * Delay helper for retries
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    /**
     * Clear cache
     */
    clearCache(pattern = null) {
        if (pattern) {
            for (const key of this.cache.keys()) {
                if (key.includes(pattern)) {
                    this.cache.delete(key);
                }
            }
        } else {
            this.cache.clear();
        }
    }
    
    /**
     * Convenience methods
     */
    get(url, config = {}) {
        return this.request({ ...config, url, method: 'GET' });
    }
    
    post(url, data, config = {}) {
        return this.request({ ...config, url, method: 'POST', data });
    }
    
    put(url, data, config = {}) {
        return this.request({ ...config, url, method: 'PUT', data });
    }
    
    patch(url, data, config = {}) {
        return this.request({ ...config, url, method: 'PATCH', data });
    }
    
    delete(url, config = {}) {
        return this.request({ ...config, url, method: 'DELETE' });
    }
    
    /**
     * File upload helper
     */
    upload(url, file, data = {}, onProgress = null) {
        const formData = new FormData();
        formData.append('file', file);
        
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });
        
        return this.request({
            url,
            method: 'POST',
            data: formData,
            onProgress
        });
    }
}

// Create global instance
window.apiClient = new ApiClient();

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ApiClient;
}
