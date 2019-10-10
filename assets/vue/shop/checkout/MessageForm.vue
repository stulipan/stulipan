<template>
    <form name="message_and_customer_form" method="post" action="/cart/setMessageAndCustomer" class="mt-3">
        <div name="write_message" class="mb-20pxX">
            <div class="row">
                <div class="col-lg-12">
                    <div class="cart bg-white shadow-none">
                        <div class="cart-wrapper">
                            <div class="checkout-block-header px-2 px-md-3">
                                <h4>Üzenet a címzettnek</h4>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mt-2">
                                    <div class="block-body px-2 px-md-4">
                                        <div class="row">
                                            <div class="col-md-8 col-lg-7 order-2 order-md-1">
                                                <div class="">
                                                    <div class="row">
                                                        <div class="form-group col-md-12" ref="cardMessageRef">
                                                            <textarea v-model="data.card.message"
                                                                      :class="{ 'is-invalid': error.message.message }"
                                                                      id="cardMessage" rows="5" placeholder="Ide írd az üzenetet..." class="form-control"></textarea>
                                                            <span v-if="error.message.message" class="invalid-feedback d-block"><span class="d-block">
                                                                <span class="form-error-message">{{ error.message.message }}</span>
                                                            </span></span>
                                                        </div>
                                                        <div class="form-group col-md-8" ref="cardAuthorRef">
                                                            <input v-model="data.card.author"
                                                                   :class="{ 'is-invalid': error.author.message }"
                                                                   type="text" id="cardAuthor" placeholder="Kinek a részéről (neved)" class="form-control">
                                                            <span v-if="error.author.message" class="invalid-feedback d-block"><span class="d-block">
                                                                <span class="form-error-message">{{ error.author.message }}</span>
                                                            </span></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-sm">
                                                    <span class="font-weight-bold text-muted">Nem találod a szavakat?</span>
                                                    <a href="#cardMessageDropdown" data-toggle="collapse" aria-expanded="false" aria-controls="cardMessageDropdown" class="dropdown-toggle JS--dropdown-toggle text-underline">Íme néhány üzenetötlet, amit kedvedre használhatsz.</a>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-lg-5 order-1 order-md-2">
                                                <div class="card h-100X ml-lg-2 ml-0 mb-2 mb-lg-0">
                                                    <div class="minicard--body bg-light d-flex flex-column h-100">
                                                        <div class="text-sm text-mutedX">
                                                            <i class="fas fa-info-circle mr-1"></i> A virág mellé INGYEN igényelhetsz egy üdvözlőkártyát a saját üzeneteddel.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 order-3">
                                                <div id="cardMessageDropdown" class="collapse">
                                                    <div class="cardX h-100X mt-2">
                                                        <ul role="tablist" class="nav nav-pills cardMessage--nav">
                                                            <li class="nav-item">
                                                                <a data-toggle="pill" href="#Szerelem" role="tab" aria-selected="true" class="nav-link detail-nav-link active">Szerelem</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a data-toggle="pill" href="#Születésnap" role="tab" aria-selected="true" class="nav-link detail-nav-link ">Születésnap</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a data-toggle="pill" href="#Évforduló" role="tab" aria-selected="true" class="nav-link detail-nav-link ">Évforduló</a>
                                                            </li>
                                                        </ul>
                                                        <div class="tab-content py-2 mt-1">
                                                            <div id="Szerelem" role="tabpanel" class="cart--scroll card cardMessage--list-group list-group-striped tab-pane active">
                                                                <ul class="list-group customer-nav">
                                                                    <li class="list-group-item">Kimondhatatlanul szeretlek!</li>
                                                                    <li class="list-group-item">Imádlak!</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div name="sender_basic_details" class="mb-20px">
            <div class="row">
                <div class="col-lg-12">
                    <div class="cart bg-white shadow-none">
                        <div class="cart-wrapper">
                            <div class="checkout-block-header px-2 px-md-3">
                                <h4>Add meg adataidat</h4>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 mt-2">
                                    <div class="block-body px-2 px-md-4">
                                        <div class="row">
                                            <div class="col-md-8 col-lg-7 order-2 order-md-1">
                                                <div class="">
                                                    <div class="form-row">
                                                        <div class="form-group col-sm-6">
                                                            <label for="customerEmail" class="form-label">Email címed</label>
                                                            <input v-model="$v.data.customer.email.$model"
                                                                   type="text" id="customerEmail" placeholder="" autocomplete="email" class="form-control">
                                                            <span v-if="!$v.data.customer.email.required" class="invalid-feedback d-block"><span class="d-block">
                                                                <span class="form-error-message">Email is required</span>
                                                            </span></span>
                                                            <span v-if="!$v.data.customer.email.email" class="invalid-feedback d-block"><span class="d-block">
                                                                <span class="form-error-message">This isn't a valid email.</span>
                                                            </span></span>
                                                        </div>
                                                        
                                                        <div class="form-group col-sm-6">
                                                            <label for="customerPhone" class="form-label">Telefonszámod</label>
                                                            <input v-model="data.customer.phone" type="tel" id="customerPhone" class="form-control">
                                                        </div>
                                                        
                                                        
                                                        <div class="form-group col-sm-6">
                                                            <label for="customerLastname" class="form-label">Vezetéknév</label>
                                                            <input v-model="data.customer.lastname" type="text" id="customerLastname" placeholder="" autocomplete="lastname" class="form-control">
                                                        </div>
                                                        
                                                        
                                                        <div class="form-group col-sm-6"><label for="customerFirstname" class="form-label">Keresztnév</label>
                                                            <input v-model="data.customer.firstname" type="text" id="customerFirstname" placeholder="" autocomplete="firstname" class="form-control">
                                                        </div>
                                                        
                                                        
                                                        <div class="form-group col-lg-12">
                                                            <div class="custom-control custom-checkbox mb-2 text-sm text-muted">
                                                                <input id="customerOptin" type="checkbox" class="custom-control-input">
                                                                <label for="customerOptin" class="custom-control-label align-middle">Pipáld be és értesíteni fogunk akcióinkról. Kizárólag vásárlóink részére!</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-lg-5 order-1 order-md-2">
                                                <div class="card ml-lg-2 ml-0 mt-md-4 mb-2 mb-lg-0">
                                                    <div class="minicard--body bg-light d-flex flex-column h-100">
                                                        <span class="text-sm text-mutedX">
                                                            <i class="fas fa-info-circle mr-1"></i>
                                                            Ezen az email címen és telefonszámon fogunk értesíteni a rendelés állapotáról, illetve a sikeres kézbesítésről.
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="display: none;">
            <input type="hidden" id="customerToket" value="FwF7c1_8o3zs6_6PLvS75NNszxHu1wJ-kLjzKGhIEow" />
        </div>
    
        <div name="goto_checkout">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-betweenX justify-content-center flex-column flex-md-row mb-4 px-4 px-sm-0">
                        <a href="#" @click.prevent="onSubmit" class="btn btn-lg btn-success order-1 order-lg-2 px-lg-6 py-4">Tovább a címzett megadásához <i class="fa fa-chevron-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-12 py-4"> </div>
            </div>
        </div>
    </form>
    
</template>

<script>
    import { required, minLength, alpha, email } from 'vuelidate/lib/validators'
    
    const initialData = () => {
        return {
            error: {
                message: { message: '' },
                author: { message: '' },
            }
        }
    };
    
    export default {
        components: {
        },
        props: [
            'data',
        ],
        data: function () {
            return {
                error: initialData().error,
            }
        },
        validations: {
            data: {
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
        computed: {
            card () {
                return this.data.card;
            },
        },
        watch: {
            card: {
                handler () {
                    if (this.card.message) {
                        this.error.message = initialData().error.message;
                    }
                    if (this.card.author) {
                        this.error.author = initialData().error.author;
                    }
                    
                },
                deep: true,
            }
        },
        methods: {
            onSubmit () {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    console.log('ERROR');
                } else {
                    console.log('SUCCESS');
                }
                
                if (this.isValidCard(this.data.card)) {
                    this.$emit('submit', this.data);    
                }
            },
            isValidCard (card) {
                if ( (!card.message && !card.author) || (card.message && card.author) ) {
                    return true;
                } else {
                    if (!card.message) {
                        this.error.message.message = 'Az üzenet lemaradt.';
                        this.scrollTo(this.$refs.cardMessageRef);
                    }
                    if (!card.author) {
                        this.error.author.message = 'Nem írtad alá az üzenetet.';
                        this.scrollTo(this.$refs.cardAuthorRef);
                    }
                    return false;
                }
            },
            scrollTo (ref) {
                let el = ref;
                el.scrollIntoView({ block: 'start',  behavior: 'smooth' });
            },
        },
        mounted() {
        }
    }
</script>

<style scoped>
</style>