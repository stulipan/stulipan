// EGYELORE NINCS HASZNALVA SEHOL

import Repository from './Repository';

const resource = '/images/product/';

export default ({
    uploadImage(payload) {
        return Repository.post(`${resource}`, payload);
    }
});