const APP_BASE_URL = new URL('./', window.location.href);
const API_BASE_URL = new URL('api/', APP_BASE_URL);

export function appPath(path = '') {
    return new URL(path, APP_BASE_URL).pathname;
}

export function apiUrl(path) {
    return new URL(path, API_BASE_URL).href;
}

export function appHref(route = 'books') {
    return `${APP_BASE_URL.href}#/${route}`;
}

export async function apiRequest(path, { token, ...options } = {}) {
    const response = await fetch(apiUrl(path), {
        ...options,
        headers: {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...options.headers,
        },
    });

    const payload = response.status === 204 ? null : await response.json();

    if (!response.ok) {
        const validationMessage = payload?.errors
            ? Object.values(payload.errors).flat().join(' ')
            : null;
        throw new Error(validationMessage || payload?.message || 'Permintaan gagal. Coba lagi.');
    }

    return payload;
}
