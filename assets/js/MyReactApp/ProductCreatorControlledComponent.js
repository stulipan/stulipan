import React, { Component } from 'react';
import PropTypes from 'prop-types';

export default class ProductCreator extends Component {
    constructor(props) {
        super(props);
        this.quantityInput = React.createRef();
        this.itemSelect = React.createRef();
        this.itemOptions = [
            { id: 'cat', text: 'Cat' },
            { id: 'fat_cat', text: 'Big Fat Cat' },
            { id: 'laptop', text: 'My Laptop' },
            { id: 'coffee_cup', text: 'Coffee Cup' },
        ];
        this.state = {
            selectedItemId: '',
            quantityValue: 0,
            quantityInputError: '',
        };

        this.handleFormSubmit = this.handleFormSubmit.bind(this);
        this.handleUpdateSelectedItem = this.handleUpdateSelectedItem.bind(this);
        this.handleUpdateQuantity = this.handleUpdateQuantity.bind(this);
    }

    handleFormSubmit(event) {
        event.preventDefault();

        const { onNewProduct } = this.props;
        const { selectedItemId, quantityValue } = this.state;
        const itemLabel = this.itemOptions.find((option) => {
            return option.id === this.state.selectedItemId
        }).text;
        console.log('I love when a good form submits!');

        if (quantityValue <= 0) {
            this.setState({
                quantityInputError: 'Please enter a value greater than 0',
            });
            return;
        }

        onNewProduct(
            itemLabel,
            quantityValue
        );

        // After the form is submitted: clears the form + resets the error messages
        this.setState({
            selectedItemId: '',
            quantityValue: 0,
            quantityInputError: '',
        });
    }

    handleUpdateSelectedItem(e) {
        this.setState({
            selectedItemId: e.target.value
        });
    }
    handleUpdateQuantity(e) {
        this.setState({
            quantityValue: e.target.value
        });
    }

    render() {
        const { selectedItemId, quantityValue, quantityInputError } = this.state;
        return (
            //<div>I'm going to be a form!</div>

            <form onSubmit={this.handleFormSubmit}>
                <div className="form-group">
                    <label className="sr-only control-label required"
                           htmlFor="rep_log_item">
                        What did you lift?
                    </label>
                    <select id="rep_log_item"
                            value={selectedItemId}
                            onChange={this.handleUpdateSelectedItem}
                            required="required"
                            className="form-control">
                        <option value="">What did you
                            lift?
                        </option>
                        {this.itemOptions.map(option => {
                            return <option value={option.id} key={option.id}>{option.text}</option>
                        })}
                    </select>
                </div>
                {' '}
                <div className={`form-group ${quantityInputError ? 'has-error' : ''}`}>
                    <label className="sr-only control-label required"
                           htmlFor="rep_log_reps">
                        How many times?
                    </label>
                    <input type="number" id="rep_log_reps"
                           value={quantityValue}
                           onChange={this.handleUpdateQuantity}
                           required="required"
                           placeholder="How many times?"
                           className={`form-control ${quantityInputError ? 'is-invalid' : ''}`}
                    />
                    {quantityInputError && <span className="help-block text-danger">{quantityInputError}</span>}
                </div>
                {' '}
                <button type="submit" className="btn btn-primary">I Lifted
                    it!
                </button>
            </form>

        );
    }
}

ProductCreator.propTypes = {
    onNewProduct: PropTypes.func.isRequired,
};