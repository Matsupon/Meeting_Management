/**
 * API helpers for Schedule Management
 */
export const API_BASE = '/api/events';

export function getFetchHeaders(method = 'GET') {
    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    if (method !== 'GET') {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN='))?.split('=')[1];
        if (token) {
            headers['X-CSRF-TOKEN'] = decodeURIComponent(token);
        }
    }
    return headers;
}
