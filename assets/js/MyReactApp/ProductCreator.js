import React, { Component } from 'react';
import PropTypes from 'prop-types';
import {Growl} from 'primereact/growl';
// import {Button} from 'primereact/button';
import classNames from 'classnames';
import { withStyles } from '@material-ui/core/styles';
import Button from '@material-ui/core/Button';
import DeleteIcon from '@material-ui/icons/Delete';
import Icon from '@material-ui/core/Icon';

const styles = theme => ({
    button: {
        margin: theme.spacing.unit,
    },
    leftIcon: {
        marginRight: theme.spacing.unit,
    },
    rightIcon: {
        marginLeft: theme.spacing.unit,
    },
    iconSmall: {
        fontSize: 18,
    },
});

class ProductCreator extends Component {
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
            quantityInputError: '',
        };

        this.showSuccess = this.showSuccess.bind(this);
        this.showInfo = this.showInfo.bind(this);
        this.showWarn = this.showWarn.bind(this);
        this.showError = this.showError.bind(this);
        this.showMultiple = this.showMultiple.bind(this);
        this.showSticky = this.showSticky.bind(this);
        this.showCustom = this.showCustom.bind(this);
        this.clear = this.clear.bind(this);


        this.handleFormSubmit = this.handleFormSubmit.bind(this);
    }

    showSuccess() {
        this.growl.show({severity: 'success', summary: 'Success Message', detail: 'Order submitted'});
    }

    showInfo() {
        this.growl.show({severity: 'info', summary: 'Info Message', detail: 'PrimeReact rocks'});
    }

    showWarn() {
        this.growl.show({severity: 'warn', summary: 'Warn Message', detail: 'There are unsaved changes'});
    }

    showError() {
        this.growl.show({severity: 'error', summary: 'Error Message', detail: 'Validation failed'});
    }

    showSticky() {
        this.growl.show({severity: 'warn', summary: 'Sticky Message', detail: 'You need to close Me', sticky: true});
    }

    showCustom() {
        const summary = <span><i className="pi pi-check" /> <strong>PrimeReact</strong></span>;
        const detail = <img alt="PrimeReact" src="showcase/resources/images/primereact-logo.png" width="250px" />

        this.growl.show({severity: 'info', summary: summary, detail: detail, sticky: true });
    }

    showMultiple() {
        this.growl.show([
            {severity:'info', summary:'Message 1', detail:'PrimeReact rocks'},
            {severity:'info', summary:'Message 2', detail:'PrimeReact rocks'},
            {severity:'info', summary:'Message 3', detail:'PrimeFaces rocks'}
        ]);
    }

    clear() {
        this.growl.clear();
    }

    handleFormSubmit(event) {
        event.preventDefault();

        const { onNewProduct } = this.props;
        const quantityInput = this.quantityInput.current;
        const itemSelect = this.itemSelect.current;

        console.log('I love when a good form submits!');
        // console.log(event.target.elements.namedItem('reps').value);

        console.log(quantityInput.value);
        console.log(itemSelect.options[itemSelect.selectedIndex].value);

        if (quantityInput.value <= 0) {
            this.setState({
                quantityInputError: 'Please enter a value greater than 0',
            });
            // don't submit, or clear the form
            return;
        }

        onNewProduct(
            itemSelect.options[itemSelect.selectedIndex].text,
            quantityInput.value
        );

        // After the form is submitted:
        // clears the form
        quantityInput.value = '';
        itemSelect.selectedIndex = 0;
        // resets the error messages
        this.setState({
            quantityInputError: ''
        });
    }

    render() {
        const { quantityInputError } = this.state;
        const { classes } = this.props;

        return (
            //<div>I'm going to be a form!</div>

            <form onSubmit={this.handleFormSubmit}>
                <div className="form-group">

                    <div className="content-section implementation p-fluid">
                        <Growl ref={(el) => this.growl = el} />

                        <h3>Severities</h3>
                        <div className="row">
                            <div className="col-12 md-3">
                                <Button variant="contained" color="secondary"
                                        className={classes.button}
                                        onClick={this.showSticky}
                                >
                                    Hello World
                                    <DeleteIcon className={classes.rightIcon, classes.iconSmall} />
                                </Button>
                            </div>

                            {/*<div className="col-12 md-3">*/}
                                {/*<button type="button" onClick={this.showSticky} value="Success" className="btn btn-success btn">Success</button>*/}
                            {/*</div>*/}
                            {/*<div className="col-12 md-3">*/}
                                {/*<Button onClick={this.showInfo} label="Info" className="btn btn-info" />*/}
                            {/*</div>*/}
                            {/*<div className="col-12 md-3">*/}
                                {/*<Button onClick={this.showWarn} label="Warn" className="btn btn-warning" />*/}
                            {/*</div>*/}
                            {/*<div className="col-12 md-3">*/}
                                {/*<Button onClick={this.showError} label="Error" className="btn btn-danger" />*/}
                            {/*</div>*/}
                        </div>
                    </div>



                    <label className="sr-only control-label required"
                           htmlFor="rep_log_item">
                        What did you lift?
                    </label>
                    <select id="rep_log_item"
                            // name="item"
                            ref={this.itemSelect}
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
                           // name="reps"
                           ref={this.quantityInput}
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

export default withStyles(styles)(ProductCreator);