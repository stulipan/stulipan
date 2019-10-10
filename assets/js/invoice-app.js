import React from 'react';
import ReactDom from 'react-dom';

import MyReactApp from './MyReactApp/InvoiceCompanyApp';

ReactDom.render(
    <div>
        <InvoiceCompanyApp />
    </div>,
    document.getElementById('RA--invoice')
);

console.log('Oh hallo React peeps! 🏋️');
console.log(<MyReactApp />);