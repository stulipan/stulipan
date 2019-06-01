import React from 'react';
import ReactDom from 'react-dom';

import MyReactApp from './MyReactApp/MyReactApp';

const showHeart = true;

ReactDom.render(
    <div>
        <MyReactApp withHeart={showHeart} />
    </div>,
    document.getElementById('RA--valami')
);

console.log('Oh hallo React peeps! 🏋️');
console.log(<MyReactApp />);