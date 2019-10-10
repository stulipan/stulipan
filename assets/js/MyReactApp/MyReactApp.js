import React, { Component } from 'react';
// import ProductList from './ProductList';
import Products from './Products';
import PropTypes from 'prop-types';
import uuid from 'uuid/v4';
import { getProducts, deleteProduct, createProduct } from '../api/product-api';
import 'primereact/resources/themes/nova-light/theme.css';
import 'primereact/resources/primereact.min.css';
import 'primeicons/primeicons.css';

export default class MyReactApp extends Component {
    constructor(props) {
        super(props);
        this.state = {
            highlightedRowId: null,
            products: [],
            // products: [
            //     {
            //         id: uuid(),
            //         image: '/uploads/images/termekek/vegyes-pink-extra-nagy-csokor-01.jpg',
            //         productName: 'Vegyes pink - Extra nagy',
            //         price: 11990,
            //         stock: 5,
            //         category: 'Csokrok',
            //         sku: 'DF1',
            //         state: 'Engedelyezett',
            //     },
            //     {
            //         id: uuid(),
            //         image: '/uploads/images/termekek/vegyes-pink-extra-nagy-csokor-01.jpg',
            //         productName: 'Vegyes pink - Extra nagy XXL',
            //         price: 14990,
            //         stock: 7,
            //         category: 'Csokrok',
            //         sku: 'DF2',
            //         state: 'Engedelyezett',
            //     },
            // ],
            numberOfHearts: 1,
            isProductListLoaded: false,
        };


        this.handleUpdateHearts = this.handleUpdateHearts.bind(this);
        this.handleRowClick = this.handleRowClick.bind(this);
        this.handleAddProduct = this.handleAddProduct.bind(this);
        this.handleDeleteProduct = this.handleDeleteProduct.bind(this);
    }

    componentDidMount() {
        getProducts()
            .then((data) => {
                this.setState({
                    products: data,
                    isProductListLoaded: true,
                })
            });
    }

    handleUpdateHearts(count) {
        this.setState({numberOfHearts: count})
    }

    handleRowClick(productId) {
        this.setState({highlightedRowId: productId});
    }

    handleAddProduct(itemLabel, reps) {
        const newProduct = {
            id: uuid(),
            image: '/uploads/images/termekek/vegyes-pink-extra-nagy-csokor-01.jpg',
            productName: itemLabel,
            price: reps,
            stock: 7,
            category: 'Csokrok',
            sku: 'DF2',
            status: 'Engedelyezett',
        };
        createProduct(newProduct)
            .then(data => {
                console.log(data);
            })
        ;
        this.setState(prevState => {
            const newProducts = [...prevState.products, newProduct];
            return {products: newProducts};
        })
    }

    handleDeleteProduct(id) {
        deleteProduct(id);
        // filter returns a new array
        this.setState((prevState) => {
            return {
                products: prevState.products.filter(product => product.id !== id),
            };
        });
    }

    render() {
        return (
            <Products
                {...this.props}
                {...this.state}
                onRowClick={this.handleRowClick}
                onAddProduct={this.handleAddProduct}
                onUpdateHearts={this.handleUpdateHearts}
                onDeleteProduct={this.handleDeleteProduct}
            />
        )
    }
}

MyReactApp.propTypes = {
    withHeart: PropTypes.bool,
}