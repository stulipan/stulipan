// EGYELORE NINCS HASZNALVA SEHOL

import Repository from './Repository';

const resource = '/upload/productImage/';

export default ({
    uploadImage(payload) {
        return Repository.post(`${resource}`, payload);
    }
});