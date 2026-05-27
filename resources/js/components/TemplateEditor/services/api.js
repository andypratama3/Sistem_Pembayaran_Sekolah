/**
 * ApiService — Thin fetch wrapper for TemplateEditor.
 *
 * - Always sends X-CSRF-TOKEN.
 * - Strips `_method` from JSON bodies (Laravel does NOT honor method spoofing
 *   for application/json — caller must use the real HTTP method).
 * - Supports AbortSignal so callers can cancel in-flight requests on unmount.
 * - Handles AbortController cleanup, race conditions, and memory leaks.
 */

const ApiService = {
    csrfToken: null,
    activeRequests: new Map(),

    setCsrfToken(token) {
        if (typeof token !== 'string') {
            console.warn('ApiService.setCsrfToken: token must be a string');
            return;
        }
        this.csrfToken = token;
    },

    /** Resolve CSRF token from instance or <meta>. Validates token format. */
    _resolveCsrf() {
        const token = this.csrfToken
            || document.querySelector('meta[name="csrf-token"]')?.content
            || '';
        
        if (!token) {
            console.warn('ApiService: No CSRF token found. Requests may fail.');
        }
        
        if (typeof token !== 'string') {
            console.error('ApiService: CSRF token is not a string');
            return '';
        }
        
        return token;
    },

    /** Validate URL to prevent SSRF attacks. */
    _validateUrl(url) {
        if (!url || typeof url !== 'string') {
            throw new Error('Invalid URL: must be a non-empty string');
        }
        
        try {
            const parsed = new URL(url, window.location.origin);
            const protocol = parsed.protocol;
            
            if (!['http:', 'https:'].includes(protocol)) {
                throw new Error(`Invalid protocol: ${protocol}`);
            }
            
            return parsed.toString();
        } catch (e) {
            throw new Error(`URL validation failed: ${e.message}`);
        }
    },

    /** Track active requests to prevent memory leaks and race conditions. */
    _trackRequest(key, controller) {
        if (this.activeRequests.has(key)) {
            const existing = this.activeRequests.get(key);
            existing.abort();
        }
        this.activeRequests.set(key, controller);
    },

    /** Clean up tracked request. */
    _untrackRequest(key) {
        this.activeRequests.delete(key);
    },

    async request(url, options = {}) {
        const validatedUrl = this._validateUrl(url);
        const { signal, headers: extraHeaders, ...rest } = options;
        
        let controller = null;
        let requestKey = null;
        let responseCleanup = null;

        try {
            // Create AbortController if signal not provided
            if (!signal) {
                controller = new AbortController();
            }
            
            const finalSignal = signal || controller.signal;
            
            // Track request to prevent race conditions
            requestKey = `${rest.method || 'GET'}:${validatedUrl}`;
            if (controller) {
                this._trackRequest(requestKey, controller);
            }

            const csrfToken = this._resolveCsrf();
            if (!csrfToken) {
                throw new Error('CSRF token is missing or invalid');
            }

            const response = await fetch(validatedUrl, {
                ...rest,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(extraHeaders || {}),
                },
                signal: finalSignal,
                credentials: 'same-origin',
            });

            // Store response for cleanup
            responseCleanup = response;

            if (!response.ok) {
                let error = {};
                try {
                    error = await response.json();
                } catch (parseError) {
                    // Response body is not JSON, continue with empty error object
                }
                
                const message = error?.message || `Request failed with status ${response.status}`;
                const err = new Error(message);
                err.status = response.status;
                err.payload = error;
                throw err;
            }

            // Parse JSON when present, otherwise return null.
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                try {
                    return await response.json();
                } catch (parseError) {
                    throw new Error(`Failed to parse JSON response: ${parseError.message}`);
                }
            }
            return null;
        } catch (error) {
            // Handle AbortError separately
            if (error.name === 'AbortError') {
                throw error;
            }
            
            // Ensure error has proper structure
            if (!(error instanceof Error)) {
                throw new Error(String(error));
            }
            
            throw error;
        } finally {
            // Cleanup: untrack request and abort controller if created
            if (requestKey) {
                this._untrackRequest(requestKey);
            }
            
            // Note: Do NOT abort controller here if signal was provided by caller
            // Caller is responsible for their signal lifecycle
            
            // Cleanup response body if not consumed
            if (responseCleanup && !responseCleanup.bodyUsed) {
                try {
                    responseCleanup.body?.cancel();
                } catch (e) {
                    // Ignore cleanup errors
                }
            }
        }
    },

    async saveTemplate(url, data, { signal } = {}) {
        if (!url || typeof url !== 'string') {
            throw new Error('saveTemplate: url must be a non-empty string');
        }
        
        if (data === null || data === undefined) {
            throw new Error('saveTemplate: data is required');
        }
        
        const { _method, ...payload } = data;
        
        if (Object.keys(payload).length === 0) {
            throw new Error('saveTemplate: payload is empty after removing _method');
        }
        
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(payload),
            signal,
        });
    },

    async autosave(url, data, { signal } = {}) {
        if (!url || typeof url !== 'string') {
            throw new Error('autosave: url must be a non-empty string');
        }
        
        if (data === null || data === undefined) {
            throw new Error('autosave: data is required');
        }
        
        const { _method, ...payload } = data;
        
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify({ ...payload, is_autosave: true }),
            signal,
        });
    },

    async preview(url, data, { signal } = {}) {
        if (!url || typeof url !== 'string') {
            throw new Error('preview: url must be a non-empty string');
        }
        
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data),
            signal,
        });
    },

    async exportPdf(url, data, { signal } = {}) {
        if (!url || typeof url !== 'string') {
            throw new Error('exportPdf: url must be a non-empty string');
        }
        
        if (!data || typeof data !== 'object') {
            throw new Error('exportPdf: data must be a non-null object');
        }
        
        // Create a copy to avoid mutating input
        const dataCopy = { ...data };
        
        // Sanitize filename
        let filename = dataCopy.filename || 'template.pdf';
        if (!/^[a-zA-Z0-9_\-\.]+$/.test(filename) || !filename.endsWith('.pdf')) {
            filename = 'template.pdf';
        }
        dataCopy.filename = filename;

        let controller = null;
        let requestKey = null;
        let responseCleanup = null;

        try {
            const validatedUrl = this._validateUrl(url);
            
            // Create AbortController if signal not provided
            if (!signal) {
                controller = new AbortController();
            }
            
            const finalSignal = signal || controller.signal;
            
            // Track request
            requestKey = `POST:${validatedUrl}`;
            if (controller) {
                this._trackRequest(requestKey, controller);
            }

            const csrfToken = this._resolveCsrf();
            if (!csrfToken) {
                throw new Error('CSRF token is missing or invalid');
            }

            const response = await fetch(validatedUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/pdf',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(dataCopy),
                signal: finalSignal,
                credentials: 'same-origin',
            });

            responseCleanup = response;

            if (!response.ok) {
                let error = {};
                try {
                    error = await response.json();
                } catch (parseError) {
                    // Response body is not JSON
                }
                
                const message = error?.message || `PDF export failed with status ${response.status}`;
                const err = new Error(message);
                err.status = response.status;
                err.payload = error;
                throw err;
            }

            // Validate content-type
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/pdf')) {
                throw new Error(`Invalid content-type for PDF: ${contentType}`);
            }

            return await response.blob();
        } catch (error) {
            // Handle AbortError separately
            if (error.name === 'AbortError') {
                throw error;
            }
            
            // Ensure error has proper structure
            if (!(error instanceof Error)) {
                throw new Error(String(error));
            }
            
            throw error;
        } finally {
            // Cleanup
            if (requestKey) {
                this._untrackRequest(requestKey);
            }
            
            if (responseCleanup && !responseCleanup.bodyUsed) {
                try {
                    responseCleanup.body?.cancel();
                } catch (e) {
                    // Ignore cleanup errors
                }
            }
        }
    },

    /** Abort all active requests. Call on component unmount. */
    abortAll() {
        for (const [key, controller] of this.activeRequests.entries()) {
            try {
                controller.abort();
            } catch (e) {
                console.error(`Failed to abort request ${key}:`, e);
            }
        }
        this.activeRequests.clear();
    },
};

export default ApiService;
