import React from 'react';
import PropTypes from 'prop-types';

export default function ProductList(props) {
    const {
        highlightedRowId,
        onRowClick,
        products,
        onDeleteProduct,
        isProductListLoaded,
    } = props;

    const handleDeleteAction = function (e, productId) {
        e.preventDefault();
        onDeleteProduct(productId);
    };

    if (!isProductListLoaded) {
        return (
            <tbody>
            <tr>
                <td colSpan="8"><span className="text-danger">Loading product list...</span></td>
            </tr>
            </tbody>
        );
    }
    return (
        <tbody>
            {products.map((product) => (
                    <tr
                        key={product.id}
                        className={highlightedRowId === product.id ? 'text-uppercase' : ''}
                        onClick={() => onRowClick(product.id) }
                    >
                        <td className="align-middle">
                            <a href={product.image} target="_blank">
                                <img className="img-thumbnail rounded-circleX" src={product.image}
                                     width="50px;"/>
                            </a>
                        </td>
                        <td className="align-middle text-left"><a href={'/admin/termek/edit/' + product.id} className="">
                            {product.productName}</a>
                        </td>
                        <td className="align-middle text-right text-nowrap">{product.price}</td>
                        <td className="align-middle text-right">
                            <span className="badge badge-primary">
                                <span className="JS--result">{product.stock}</span>
                                <a href="" className="JS--editButton text-white ml-1"
                                   data-url="/admin/product/editStock/1"><i className="fas fa-pen"></i></a>
                            </span>
                            <span className="d-none"></span>
                        </td>
                        <td className="align-middle">{product.category}</td>
                        <td className="align-middle">{product.sku}</td>
                        <td className="align-middle">
                            <span
                                className="badge text-mutedX font-weight-normalX badge-success">{product.status}</span>
                        </td>
                        <td className="align-middle">
                            <a href={'/admin/termek/edit/' + product.id} className="btn btn-sm btn-primary mr-2">
                                <i className="far fa-edit mr-1"></i> Módosít</a>
                            <a href={'/termek/' + product.id} className="btn-smX" target="_blank">
                                <i className="fas fa-external-link-alt mr-1"></i></a>

                            <a href="#"
                               onClick={(e) => handleDeleteAction(e, product.id)}
                            >
                                <span className="fa fa-trash"></span>
                            </a>
                        </td>
                    </tr>
            ))}
        </tbody>
    );
}

ProductList.propTypes = {
    highlightedRowId: PropTypes.any,
    onRowClick: PropTypes.func.isRequired,
    products: PropTypes.array.isRequired,
    onDeleteProduct: PropTypes.func.isRequired,
    isProductListLoaded: PropTypes.bool.isRequired,
};