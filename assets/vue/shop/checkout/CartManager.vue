<template>
    <div>
        <div v-if="isDataLoading" class="d-flex justify-content-center w-100 h-100" style="z-index: 15000; opacity: 1;">
            <div class="d-flex justify-content-center h-100 mt-5 align-items-center">
                <div>
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div class="d-flex justify-content-center mt-2">Tartalom betöltése...</div>
                </div>
            </div>
        </div>
        <div v-else name="left_column" class="pt-3">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="cart bg-white">
                            <div class="cart-wrapper" ref="cartRef">
                                    <div class="checkout-block-header px-2 px-md-3">
                                        <h4>Kosár</h4>
                                    </div>
                                    <div v-if="emptyCartMessage"
                                         class="alert alert-danger mt-4 px-3 px-md-4 rounded-0" role="alert">
                                        <i class="fas fa-exclamation-circle mr-1"></i> {{ emptyCartMessage }}
                                    </div>
                                
                                    <div class="cart-body px-2 px-md-4">
                                            <items :items="order.items"
                                                   @removeFromCart="removeFromCart"
                                                   @updateQuantity="updateQuantity"
                                            ></items>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                <gifts :gifts="giftProducts"
                       @selectGift="addGiftToCart"
                ></gifts>
                <message-form :data="messageAndCustomer"
                              @submit="saveForm"
                ></message-form>
                
        </div>
        
        
        
    </div>
</template>

<script>
    import { required, minLength, alpha, email } from 'vuelidate/lib/validators'
    import Items from './Items.vue'
    import Gifts from './Gifts.vue'
    import MessageForm from './MessageForm.vue'

    const initialData = () => {
        return {
            isDataLoading: true,
            order: {
                items: [],
                message: '',
                messageAuthor: '',
                customer: {},
                recipient: {},
                sender: {},
                shipping: {},
                payment: {},
                billingName: '',
                billingPhone: '',
                billingCompany: '',
                billingVatNumber: 0,
                billingAddress: {},
                deliveryDate: '',
                deliveryInterval: '',
                shippingName: '',
                shippingPhone: '',
                shippingAddress: {},
            },
            giftProducts: [],
            messageAndCustomer: {
                card: {
                    message: '',
                    author: '',
                },
                customer: {
                    email: '',
                    firstname: '',
                    lastname: '',
                    phone: '',
                }
            },
            errors: [],
        }
    };

    export default {
        components: {
            Items,
            Gifts,
            MessageForm,
            Circle2
        },
        props: [
            'orderId',
            'giftCategoryId',
        ],
        data: function () {
            return {
                isDataLoading: true,
                order: initialData().order,
                giftProducts: initialData().giftProducts,
                messageAndCustomer: initialData().messageAndCustomer,
                errors: initialData().errors,
                emptyCartMessage: '',
            }
        },
        computed: {
            itemsInCart () {
                return this.order.items.length;
            },
            currentOrderId () {
                return this.order.id;
            },
        },
        validations: {
            messageAndCustomer: {
                customer: {
                    email: {
                        required,
                        email,
                    },
                    firstname: {
                        required,
                        alpha,
                        minLength: minLength(2),
                    },
                    lastname: {
                        required,
                        alpha,
                        minLength: minLength(2),
                    },
                    phone: {
                        required,
                        alpha,
                        minLength: minLength(2),
                    },
                }
            },
        },
        watch: {
            messageAndCustomer: {
                handler () {
                    this.order.message = this.messageAndCustomer.card.message;
                    this.order.messageAuthor = this.messageAndCustomer.card.author;
                    this.order.email = this.messageAndCustomer.customer.email;
                    this.order.billingName = this.messageAndCustomer.customer.lastname + ' ' + this.messageAndCustomer.customer.firstname;
                    this.order.billingPhone = this.messageAndCustomer.customer.phone;
                },
                deep: true,
            },
            itemsInCart: {
                handler () {
                    if (this.itemsInCart === 0) {
                        this.showEmptyCartError();
                    } else {
                        this.hideEmptyCartError();
                    }
                },
            }
        },
        methods: {
            addGiftToCart (product) {
                if (this.currentOrderId) {
                    this.$http.put(`/admin/api/orders/${this.currentOrderId}/items/`+ product.id, product)
                        .then(response => {
                            this.order.items = response.data.items;
                            this.scrollTo(this.$refs.cartRef);
                        }, response => {
                            if (response.status === 422) {
                                for (let value of Object.values(response.body.errors)) {
                                    console.log(value);
                                }
                            }
                        })
                } else {
                    // cloning this.order without reference to it
                    let newOrder = JSON.parse(JSON.stringify(this.order));
                    let newItem = { product: product };
                    newOrder.items.push(newItem);
                    this.$http.post(`/admin/api/orders/`, newOrder)
                        .then(response => {
                            this.order = response.data.orders[0];
                            this.scrollTo(this.$refs.cartRef);
                        }, response => {
                            if (response.status === 422) {
                                for (let value of Object.values(response.body.errors)) {
                                    console.log(value);
                                }
                            }
                        })
                }
                
            },
            updateQuantity (item) {
                const index = this.order.items.findIndex((oi) => oi.id === item.id);
                if (index !== -1) {
                    this.$http.put(`/admin/api/items/`+ item.id, item)
                        .then(response => {
                            this.order.items.splice(index, 1, response.body.items[0]);
                        }, response => {
                            if (response.status === 422) {
                                for (let value of Object.values(response.body.errors)) {
                                    console.log(value);
                                }
                            }
                        });
                }
                
            },
            removeFromCart (item) {
                this.$http.delete(`/admin/api/orders/${this.currentOrderId}/items/${item.id}`)
                    .then(response => {
                        this.order.items = response.data.items;
                    }, response => {
                        if (response.status === 422) {
                            for (let value of Object.values(response.body.errors)) {
                                console.log(value);
                            }
                        }
                    });
            },
            saveForm (mac) {
                if (this.itemsInCart === 0) {
                    this.showEmptyCartError();
                } else {
//                    this.$v.$touch();
                    this.order.message = this.messageAndCustomer.card.message;
                    this.order.messageAuthor = this.messageAndCustomer.card.author;
                    this.order.email = this.messageAndCustomer.customer.email;
                    this.order.billingName = this.messageAndCustomer.customer.lastname + ' ' + this.messageAndCustomer.customer.firstname;
                    this.order.billingPhone = this.messageAndCustomer.customer.phone;
                    this.$http.put(`/admin/api/orders/${this.currentOrderId}`, this.order)
                        .then(response => {
                            this.order = response.data.orders[0];
                        }, response => {
                            if (response.status === 422) {
                                for (let value of Object.values(response.body.errors)) {
                                    console.log(value);
                                }
                            }
                        })
                }
            },
            showEmptyCartError () {
                this.emptyCartMessage = 'Nincs termék a kosárba!';
                this.scrollTo(this.$refs.cartRef);
            },
            hideEmptyCartError () {
                this.emptyCartMessage = '';
            },
            scrollTo (ref) {
                let el = ref;
                el.scrollIntoView({ block: 'start',  behavior: 'smooth' });
            },
        },
        created () {
            this.order.id = this.orderId;
            if (this.currentOrderId) {
                this.$http.get(`/admin/api/orders/${this.currentOrderId}/items/`)
                    .then(response => {
                        this.order.items = response.data.items;
                        this.isDataLoading = false;
                    }, response => {
                        if (response.status === 422) {
                            for (let item of Object.values(response.body.errors)) {
                                this.dataLoadingErrors.push(item.message);
                            }
                        }
                        this.isDataLoading = false;
                    });
            }
            this.$http.get(`/admin/api/product/categories/${this.giftCategoryId}/products/`)
                .then(response => {
                    this.giftProducts = response.data.products;
                    this.isDataLoading = false;
                }, response => {
                    if (response.status === 422) {
                        for (let item of Object.values(response.body.errors)) {
                            this.dataLoadingErrors.push(item.message);
                        }
                    }
                    this.isDataLoading = false;
                });
//            this.order.message = this.messageAndCustomer.card.message;
//            this.order.messageAuthor = this.messageAndCustomer.card.author;
//            this.order.email = this.messageAndCustomer.customer.email;
//            this.order.phone = this.messageAndCustomer.customer.phone;
//            this.order.shippingName = this.messageAndCustomer.customer.lastname + this.messageAndCustomer.customer.firstname;
            
        },
    }
</script>

<style scoped>
</style>

