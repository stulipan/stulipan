/**
 * Object.assign() is JavaScript's equivalent of array_merge() when dealing with objects: it takes any options
 * we might pass in and merges them into this object. So, credentials will always be in the final options.
 */
function fetchJson(url, options) {
    return fetch(url, Object.assign({
        credentials: 'same-origin',
    }, options))
        .then(response => {
            return response.json();
        });
}

/**
 * Returns a promise where the data is the rep log collection
 *
 * @return {Promise<Response>}
 */
export function getProducts() {
    return fetch('/admin/api/products', {
        credentials: 'same-origin'
    })
        .then(response => {
            return response.json().then((data) => data.items);
        });
}

export function deleteProduct(id) {
    return fetchJson(`/admin/api/deleteProduct/${id}`, {
        method: 'DELETE'
    });
}

export function createProduct(product) {
    return fetchJson('/admin/api/products', {
        method: 'POST',
        body: JSON.stringify(product),
        headers: {
            'Content-Type': 'application/json'
        }
    });
}