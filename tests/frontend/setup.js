/**
 * Frontend Testing Setup
 * Jest configuration and test utilities for UniPrint frontend
 */

// Mock DOM environment
import { JSDOM } from 'jsdom';

const dom = new JSDOM('<!DOCTYPE html><html><body></body></html>', {
    url: 'http://localhost',
    pretendToBeVisual: true,
    resources: 'usable'
});

global.window = dom.window;
global.document = dom.window.document;
global.navigator = dom.window.navigator;
global.HTMLElement = dom.window.HTMLElement;
global.Element = dom.window.Element;
global.Node = dom.window.Node;

// Mock fetch
global.fetch = jest.fn();

// Mock localStorage
const localStorageMock = {
    getItem: jest.fn(),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn(),
};
global.localStorage = localStorageMock;

// Mock console methods for cleaner test output
global.console = {
    ...console,
    log: jest.fn(),
    debug: jest.fn(),
    info: jest.fn(),
    warn: jest.fn(),
    error: jest.fn(),
};

// Mock Pusher
global.Pusher = jest.fn().mockImplementation(() => ({
    connection: {
        bind: jest.fn(),
        state: 'connected'
    },
    subscribe: jest.fn().mockReturnValue({
        bind: jest.fn(),
        unbind: jest.fn(),
        trigger: jest.fn()
    }),
    unsubscribe: jest.fn(),
    disconnect: jest.fn(),
    connect: jest.fn()
}));

// Mock jQuery if needed
global.$ = jest.fn((selector) => ({
    on: jest.fn(),
    off: jest.fn(),
    addClass: jest.fn(),
    removeClass: jest.fn(),
    html: jest.fn(),
    text: jest.fn(),
    val: jest.fn(),
    show: jest.fn(),
    hide: jest.fn(),
    fadeOut: jest.fn(),
    fadeIn: jest.fn(),
    append: jest.fn(),
    prepend: jest.fn(),
    remove: jest.fn(),
    find: jest.fn(),
    closest: jest.fn(),
    parent: jest.fn(),
    children: jest.fn()
}));

// Mock Laravel global
global.Laravel = {
    user: {
        id: 'test-user-id',
        name: 'Test User',
        role_type: 'customer'
    },
    pusher: {
        key: 'test-pusher-key',
        cluster: 'mt1'
    }
};

// Test utilities
export const TestUtils = {
    // Create a mock DOM element
    createElement(tag, attributes = {}, content = '') {
        const element = document.createElement(tag);
        Object.keys(attributes).forEach(key => {
            element.setAttribute(key, attributes[key]);
        });
        if (content) {
            element.innerHTML = content;
        }
        return element;
    },

    // Create a mock form
    createForm(fields = []) {
        const form = document.createElement('form');
        fields.forEach(field => {
            const input = document.createElement('input');
            input.name = field.name;
            input.type = field.type || 'text';
            input.value = field.value || '';
            if (field.required) input.required = true;
            form.appendChild(input);
        });
        return form;
    },

    // Simulate user input
    simulateInput(element, value) {
        element.value = value;
        const event = new dom.window.Event('input', { bubbles: true });
        element.dispatchEvent(event);
    },

    // Simulate form submission
    simulateSubmit(form) {
        const event = new dom.window.Event('submit', { bubbles: true });
        form.dispatchEvent(event);
    },

    // Simulate click
    simulateClick(element) {
        const event = new dom.window.Event('click', { bubbles: true });
        element.dispatchEvent(event);
    },

    // Wait for async operations
    async waitFor(condition, timeout = 1000) {
        const start = Date.now();
        while (Date.now() - start < timeout) {
            if (condition()) return;
            await new Promise(resolve => setTimeout(resolve, 10));
        }
        throw new Error('Timeout waiting for condition');
    },

    // Mock API response
    mockApiResponse(data, status = 200) {
        global.fetch.mockResolvedValueOnce({
            ok: status >= 200 && status < 300,
            status,
            json: async () => data,
            text: async () => JSON.stringify(data)
        });
    },

    // Mock API error
    mockApiError(message, status = 500) {
        global.fetch.mockRejectedValueOnce({
            status,
            message,
            data: { message }
        });
    },

    // Reset all mocks
    resetMocks() {
        jest.clearAllMocks();
        global.fetch.mockClear();
        Object.keys(localStorageMock).forEach(key => {
            localStorageMock[key].mockClear();
        });
    }
};

// Custom matchers
expect.extend({
    toHaveClass(received, className) {
        const pass = received.classList.contains(className);
        return {
            message: () => `expected element ${pass ? 'not ' : ''}to have class "${className}"`,
            pass
        };
    },

    toBeVisible(received) {
        const pass = received.style.display !== 'none' && 
                    received.style.visibility !== 'hidden' &&
                    !received.hidden;
        return {
            message: () => `expected element ${pass ? 'not ' : ''}to be visible`,
            pass
        };
    },

    toHaveValue(received, value) {
        const pass = received.value === value;
        return {
            message: () => `expected element to have value "${value}" but got "${received.value}"`,
            pass
        };
    }
});

// Setup and teardown
beforeEach(() => {
    // Reset DOM
    document.body.innerHTML = '';
    
    // Reset mocks
    TestUtils.resetMocks();
    
    // Reset global state
    if (global.stateManager) {
        global.stateManager.resetState();
    }
});

afterEach(() => {
    // Cleanup any timers
    jest.clearAllTimers();
});

export default TestUtils;
