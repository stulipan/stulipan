// EGYELORE NINCS HASZNALVA SEHOL

import axios from 'axios';

const baseDomain = 'http://stulipan.dfr';
const baseUrl = `${baseDomain}/hu/admin/api`;
    
export default axios.create({
    baseUrl
    // in case you need a token:
    // header: {"Authorization": "Bearer yourToken"}
});