<template>
    <div>
        <content-loader v-if="dataIsLoading"
                        :speed="2"
                        :animate="true"
                        :height="30"
        ></content-loader>
        <edit-form v-else
                   :productId="productId"
                   :product="productInForm"
                   :categories="categories"
                   :kinds="kinds"
                   :attributes="attributes"
                   :statuses="statuses"
                   :badges="badges"
                   :formIsPosting="formIsPosting"
                   :errors="formErrors"
                   :resetForm="resetForm"
                   v-on:submit="saveForm"
                   v-on:click="cancelForm"
        >
        </edit-form>
    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect'
    import { ContentLoader } from 'vue-content-loader'
    import EditForm from './EditForm'

    const initialData = () => {
        return {
            dataIsLoading: true,
            formIsPosting: false,
            productInForm: {
                name: '',
                description: '',
                kind: {},
                price: {
                    value: null,
                },
                images: [],
                image: null, //nem lehet hasznalatban
                imageUrl: '',  //nem lehet hasznalatban
            },
            productKindId: '',
            categories: [],
            kinds: [],
            attributes: [],
            statuses: [],
            badges: [],
            formErrors: [],
            resetForm: false,  // If true will hide form errors and uploaded images preview
        }
    }

    export default {
        components: {
            Multiselect,
            ContentLoader,
            EditForm,
        },
        props: [
            'productId',
        ],
        data: initialData,
        watch: {
            productInForm: {
                handler () {
                    this.initialProductInForm = true;
                }
            },
            'productInForm.kind': { handler() {
                this.$http.get(`/admin/api/product/kinds/${this.productInForm.kind.id}/attributes/`)
                    .then(response => {
                        this.attributes = response.data.attributes;
                    }, response => {
                        return [];
                    });
                }
            },
        },
//        computed: {
//            attributes: function() {
//                this.$http.get(`/admin/api/product/kinds/${this.productInForm.kind.id}/attributes/`)
//                    .then(response => {
//                        this.attributes = response.data.attributes;
//                    }, response => {
//                    });
////                return this.attributes;
//            }
//        },
        methods: {
            cityWithZip(cityObj) {
                if (!cityObj.city) {
                    return 'Település vagy Irányítószám'
                } else return `[${cityObj.city.zip}] ${cityObj.city.city}`
//                return '[' + zip + ']  ' + city  // ugyanaz mint: return `[${zip}] ${city}`
            },
            
            saveForm (productData) {
                if (this.productId > 0) {
                    this.formIsPosting = true;
                    this.$http.put('/admin/api/products/'+ productData.id, productData)
                        .then(
                            response => {
                                this.showToast('A módosítások sikeresen elmentve.', 'success');
                                this.formIsPosting = false;
//                                this.products.splice(index, 1, response.data.products[0]);   /// ITT MAJD LEHET HOGY CELSZERU BETOLTENI A VISSZAJOVO objektumot
                            },
                            response => {
                                if (response.status === 422) {
                                    this.formIsPosting = false;
                                    this.showToast('Upsz... valami hiba történt. Ellenőrizd az adatokat.', 'danger');
                                    for (let value of Object.values(response.body.errors)) {
                                        this.formErrors.push(value);
                                    }
                                }
                                if (!this.formErrors.length) {
                                    this.resetErrosInForm()
                                }
                            })
                } else {
                    // add it to the product list
                    this.formIsPosting = true;
                    this.$http.post('/admin/api/products/', productData)
                        .then(
                            response => {
                                this.formIsPosting = true;
//                                this.products.push(response.body);
                            },
                            response => {
                                if (response.status === 422) {
                                    for (let value of Object.values(response.body.errors)) {
                                        this.formErrors.push(value);
                                    }
                                }
                                if (!this.formErrors.length) {
                                    this.resetErrosInForm()
                                }
                            })
                }
            },
            cancelForm () {
//                this.productInForm = initialData().productInForm
                this.resetForm = true
            },
            resetErrosInForm () {
                this.formErrors = initialData().formErrors;
            },
            showToast (message, type) {
                let title = '';
                switch (type) {
                    case 'success':
                        title = 'SIKERES MŰVELET';
                        break;
                    case 'danger':
                        title = 'HIBA!';
                        break;
                    case 'warning':
                        title = 'FIGYELEM!';
                        break;
                    default:
                        title = 'FIGYELEM!';
                        break;
                }
                this.$bvToast.toast(message, {
                    title: title,
                    autoHideDelay: 5000,
                    appendToast: true,
                    variant: type,
                    toaster: "b-toaster-top-right",
                });
            },
        },
        created () {
            if (this.productId > 0 ) {
                this.$http.get(`/admin/api/products/${this.productId}`)
                    .then(response => {
                        this.productInForm = response.data.products[0];
                        this.productInForm.images.sort(function(a, b){return a.ordering - b.ordering});
                        this.productKindId = this.productInForm.kind.id;
                    }, response => {
                        if (response.status === 422) {
                            for (let item of Object.values(response.body.errors)) {
                                //                            this.dataLoadingErrors.push(item.message);
                                this.showToast(item.message, 'danger');
                            }
                        }
                    });
            }
            this.$http.get('/admin/api/product/kinds')
                .then(response => {
                    this.kinds = response.data.kinds;
                }, response => {
                    if (response.status === 422) {
                        for (let item of Object.values(response.body.errors)) {
//                            this.dataLoadingErrors.push(item.message);
                            this.showToast(item.message, 'danger');
                        }
                    }
                });
            this.$http.get('/admin/api/product/statuses')
                .then(response => {
                    this.statuses = response.data.statuses;
                }, response => {
                    if (response.status === 422) {
                        for (let item of Object.values(response.body.errors)) {
//                            this.dataLoadingErrors.push(item.message);
                            this.showToast(item.message, 'danger');
                        }
                    }
                });
            this.$http.get('/admin/api/product/categories')
                .then(response => {
                    this.categories = response.data.categories;
                    this.dataIsLoading = false;
                }, response => {
                    if (response.status === 422) {
                        for (let item of Object.values(response.body.errors)) {
//                            this.dataLoadingErrors.push(item.message);
                            this.showToast(item.message, 'danger');
                        }
                    }
                });
            this.$http.get('/admin/api/product/badges')
                .then(response => {
                    this.badges = response.data.badges;
                    this.dataIsLoading = false;
                }, response => {
                    if (response.status === 422) {
                        for (let item of Object.values(response.body.errors)) {
//                            this.dataLoadingErrors.push(item.message);
                            this.showToast(item.message, 'danger');
                        }
                    }
                });
            
        },
        mounted () {
        
        },
    }
</script>

<!--<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>-->
<style scoped>
</style>

