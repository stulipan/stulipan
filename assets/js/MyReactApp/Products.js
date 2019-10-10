import React from 'react';
import PropTypes from 'prop-types';
import ProductList from './ProductList';
import ProductCreator from './ProductCreator';
// import ProductCreator from './ProductCreatorControlledComponent';

export default function Products(props) {
    const {
        withHeart,
        numberOfHearts,
        onUpdateHearts,
        highlightedRowId,
        onRowClick,
        products,
        onAddProduct,
        onDeleteProduct,
        isProductListLoaded,
    } = props;

    let heart = '';
    if (withHeart) {
        heart = <span>{'❤️'.repeat(numberOfHearts)}</span>;
    }


    return (
        <div className="table-responsive mb-3 px-1">
            {/*<h2>Lift Stuff! {heart}</h2>*/}
            {/*<input type="range" value={numberOfHearts} className="form-control mb-3 shadow-none border-0"*/}
                   {/*onChange={(e) => {*/}
                       {/*onUpdateHearts(+e.target.value);*/}
                       {/*// The '+' sign converts string to number*/}
                       {/*// When you read a value from a field, it is, of course, always a string!*/}
                   {/*}}*/}
            {/*/>*/}

            <table className="table table-sm table-centered table-striped table-bordered table-hover mb-0">
                <thead className="thead-light">
                <tr>
                    <th scope="col">Kép</th>
                    <th scope="col">Terméknév</th>
                    <th scope="col">Ár</th>
                    <th scope="col">Mennyiség</th>
                    <th scope="col">Kategória</th>
                    <th scope="col">SKU</th>
                    <th scope="col">Állapot</th>
                    <th scope="col"><i className="fas fa-ellipsis-h"></i></th>
                </tr>
                </thead>
                <ProductList
                    highlightedRowId={highlightedRowId}
                    onRowClick={onRowClick}
                    products={products}
                    onDeleteProduct={onDeleteProduct}
                    isProductListLoaded={isProductListLoaded}
                />
            </table>
            <div className="rowX">
                <div className="col-md-6 mt-3">
                    <ProductCreator
                        onNewProduct={onAddProduct}
                    />
                </div>
            </div>
        </div>
    );
}

Products.propTypes = {
    withHeart: PropTypes.bool,
    highlightedRowId: PropTypes.any,
    onRowClick: PropTypes.func.isRequired,
    onAddProduct: PropTypes.func.isRequired,
    products: PropTypes.array.isRequired,
    numberOfHearts: PropTypes.number.isRequired,
    onUpdateHearts: PropTypes.func.isRequired,
    onDeleteProduct: PropTypes.func.isRequired,
    isProductListLoaded: PropTypes.bool.isRequired,
}