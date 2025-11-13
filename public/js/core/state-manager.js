/**
 * UniPrint State Manager
 * Centralized state management for the frontend application
 */

class StateManager {
    constructor() {
        this.state = {
            // User state
            user: {
                data: null,
                isAuthenticated: false,
                role: null,
                permissions: []
            },
            
            // UI state
            ui: {
                loading: false,
                sidebarOpen: true,
                theme: 'light',
                notifications: []
            },
            
            // Chat state
            chat: {
                conversations: [],
                activeConversation: null,
                onlineUsers: [],
                unreadCount: 0,
                connectionState: 'disconnected',
                typingUsers: []
            },
            
            // Orders state
            orders: {
                list: [],
                filters: {
                    status: 'all',
                    dateRange: null,
                    search: ''
                },
                pagination: {
                    page: 1,
                    perPage: 10,
                    total: 0
                },
                loading: false
            },
            
            // Products state
            products: {
                list: [],
                categories: [],
                filters: {
                    category: 'all',
                    priceRange: null,
                    search: ''
                },
                loading: false
            },
            
            // Saved services state
            savedServices: {
                items: [],
                count: 0,
                loading: false
            }
        };
        
        this.subscribers = new Map();
        this.middleware = [];
        this.history = [];
        this.maxHistorySize = 50;
        
        this.init();
    }
    
    init() {
        // Load initial state from localStorage
        this.loadFromStorage();
        
        // Set up auto-save
        this.setupAutoSave();
        
        // Initialize user data if available
        if (window.Laravel && window.Laravel.user) {
            this.setState('user', {
                data: window.Laravel.user,
                isAuthenticated: true,
                role: window.Laravel.user.role_type,
                permissions: window.Laravel.user.permissions || []
            });
        }
    }
    
    /**
     * Subscribe to state changes
     */
    subscribe(path, callback) {
        if (!this.subscribers.has(path)) {
            this.subscribers.set(path, []);
        }
        
        const callbacks = this.subscribers.get(path);
        callbacks.push(callback);
        
        // Return unsubscribe function
        return () => {
            const index = callbacks.indexOf(callback);
            if (index > -1) {
                callbacks.splice(index, 1);
            }
        };
    }
    
    /**
     * Get state value by path
     */
    getState(path = null) {
        if (!path) return this.state;
        
        return this.getNestedValue(this.state, path);
    }
    
    /**
     * Set state value by path
     */
    setState(path, value, options = {}) {
        const { silent = false, merge = false } = options;
        
        // Save to history
        this.saveToHistory();
        
        // Apply middleware
        const processedValue = this.applyMiddleware(path, value);
        
        // Update state
        if (merge && typeof value === 'object' && value !== null) {
            const currentValue = this.getState(path) || {};
            this.setNestedValue(this.state, path, { ...currentValue, ...processedValue });
        } else {
            this.setNestedValue(this.state, path, processedValue);
        }
        
        // Notify subscribers
        if (!silent) {
            this.notifySubscribers(path, processedValue);
        }
        
        // Auto-save to localStorage
        this.saveToStorage();
    }
    
    /**
     * Update state with partial data
     */
    updateState(path, updates) {
        this.setState(path, updates, { merge: true });
    }
    
    /**
     * Reset state to initial values
     */
    resetState(path = null) {
        if (path) {
            this.setState(path, this.getInitialState(path));
        } else {
            this.state = this.getInitialState();
            this.notifyAllSubscribers();
        }
    }
    
    /**
     * Add middleware for state changes
     */
    addMiddleware(middleware) {
        this.middleware.push(middleware);
    }
    
    /**
     * Apply middleware to state changes
     */
    applyMiddleware(path, value) {
        return this.middleware.reduce((processedValue, middleware) => {
            return middleware(path, processedValue, this.state);
        }, value);
    }
    
    /**
     * Notify subscribers of state changes
     */
    notifySubscribers(path, value) {
        // Notify exact path subscribers
        const callbacks = this.subscribers.get(path) || [];
        callbacks.forEach(callback => {
            try {
                callback(value, path);
            } catch (error) {
                console.error('Error in state subscriber:', error);
            }
        });
        
        // Notify parent path subscribers
        const pathParts = path.split('.');
        for (let i = pathParts.length - 1; i > 0; i--) {
            const parentPath = pathParts.slice(0, i).join('.');
            const parentCallbacks = this.subscribers.get(parentPath) || [];
            const parentValue = this.getState(parentPath);
            
            parentCallbacks.forEach(callback => {
                try {
                    callback(parentValue, parentPath);
                } catch (error) {
                    console.error('Error in parent state subscriber:', error);
                }
            });
        }
    }
    
    /**
     * Notify all subscribers
     */
    notifyAllSubscribers() {
        this.subscribers.forEach((callbacks, path) => {
            const value = this.getState(path);
            callbacks.forEach(callback => {
                try {
                    callback(value, path);
                } catch (error) {
                    console.error('Error in state subscriber:', error);
                }
            });
        });
    }
    
    /**
     * Get nested value from object
     */
    getNestedValue(obj, path) {
        return path.split('.').reduce((current, key) => {
            return current && current[key] !== undefined ? current[key] : undefined;
        }, obj);
    }
    
    /**
     * Set nested value in object
     */
    setNestedValue(obj, path, value) {
        const keys = path.split('.');
        const lastKey = keys.pop();
        const target = keys.reduce((current, key) => {
            if (!current[key] || typeof current[key] !== 'object') {
                current[key] = {};
            }
            return current[key];
        }, obj);
        
        target[lastKey] = value;
    }
    
    /**
     * Save state to localStorage
     */
    saveToStorage() {
        try {
            const persistentState = {
                user: this.state.user,
                ui: {
                    theme: this.state.ui.theme,
                    sidebarOpen: this.state.ui.sidebarOpen
                },
                savedServices: this.state.savedServices
            };
            
            localStorage.setItem('uniprint_state', JSON.stringify(persistentState));
        } catch (error) {
            console.warn('Failed to save state to localStorage:', error);
        }
    }
    
    /**
     * Load state from localStorage
     */
    loadFromStorage() {
        try {
            const saved = localStorage.getItem('uniprint_state');
            if (saved) {
                const parsedState = JSON.parse(saved);
                
                // Merge with current state
                Object.keys(parsedState).forEach(key => {
                    if (this.state[key]) {
                        this.state[key] = { ...this.state[key], ...parsedState[key] };
                    }
                });
            }
        } catch (error) {
            console.warn('Failed to load state from localStorage:', error);
        }
    }
    
    /**
     * Set up auto-save interval
     */
    setupAutoSave() {
        // Save state every 30 seconds
        setInterval(() => {
            this.saveToStorage();
        }, 30000);
        
        // Save on page unload
        window.addEventListener('beforeunload', () => {
            this.saveToStorage();
        });
    }
    
    /**
     * Save current state to history
     */
    saveToHistory() {
        this.history.push(JSON.parse(JSON.stringify(this.state)));
        
        // Limit history size
        if (this.history.length > this.maxHistorySize) {
            this.history.shift();
        }
    }
    
    /**
     * Undo last state change
     */
    undo() {
        if (this.history.length > 0) {
            this.state = this.history.pop();
            this.notifyAllSubscribers();
            this.saveToStorage();
        }
    }
    
    /**
     * Get initial state
     */
    getInitialState(path = null) {
        const initialState = {
            user: {
                data: null,
                isAuthenticated: false,
                role: null,
                permissions: []
            },
            ui: {
                loading: false,
                sidebarOpen: true,
                theme: 'light',
                notifications: []
            },
            chat: {
                conversations: [],
                activeConversation: null,
                onlineUsers: [],
                unreadCount: 0,
                connectionState: 'disconnected',
                typingUsers: []
            },
            orders: {
                list: [],
                filters: {
                    status: 'all',
                    dateRange: null,
                    search: ''
                },
                pagination: {
                    page: 1,
                    perPage: 10,
                    total: 0
                },
                loading: false
            },
            products: {
                list: [],
                categories: [],
                filters: {
                    category: 'all',
                    priceRange: null,
                    search: ''
                },
                loading: false
            },
            savedServices: {
                items: [],
                count: 0,
                loading: false
            }
        };
        
        return path ? this.getNestedValue(initialState, path) : initialState;
    }
    
    /**
     * Debug helper
     */
    debug() {
        console.group('State Manager Debug');
        console.log('Current State:', this.state);
        console.log('Subscribers:', this.subscribers);
        console.log('History Length:', this.history.length);
        console.groupEnd();
    }
}

// Create global instance
window.stateManager = new StateManager();

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StateManager;
}
