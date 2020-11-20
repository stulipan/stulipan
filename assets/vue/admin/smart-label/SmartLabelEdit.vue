<template>
    <div>
        <div v-if="isDataLoading" class="d-flex justify-content-center w-100 h-100" style="z-index: 15000; opacity: 1;">
            <div class="d-flex justify-content-center h-100 mt-5 align-items-center">
                <div>
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div class="d-flex justify-content-center mt-2">Matrica adatok betöltése...</div>
                </div>

            </div>
        </div>
        <edit-form v-else
                   :productId="productId"
                   :product="productInForm"
                   :categories="categories"
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
    import EditForm from './EditForm'

    const initialData = () => {
        return {
            isDataLoading: true,
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
            statuses: [],
            badges: [],
            formErrors: [],
            resetForm: false,  // If true will hide form errors and uploaded images preview
        }
    };

    export default {
        components: {
            Multiselect,
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
        },
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
                    this.$http.put('/hu/admin/api/products/'+ productData.id, productData)
                        .then(
                            response => {
                                this.showToast('A módosítások sikeresen elmentve.', 'success');
                                this.productInForm = response.data.products[0];  /// ITT CELSZERU BETOLTENI A VISSZAJOVO objektumot
                                this.formIsPosting = false;
                            },
                            response => {
                                this.formIsPosting = false;
                                if (response.status === 422) {
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
                    this.$http.post('/hu/admin/api/products/', productData)
                        .then(
                            response => {
                                this.formIsPosting = false;
//                                this.products.push(response.body);
                            },
                            response => {
                                this.formIsPosting = false;
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
                var p1 = this.$http.get(`/hu/admin/api/products/${this.productId}`)
                    // .then(response => {
                    //     this.productInForm = response.data.products[0];
                    //     this.productInForm.images.sort(function(a, b){return a.ordering - b.ordering});
                    //     this.productKindId = this.productInForm.kind.id;
                    // }, response => {
                    //     if (response.status === 422) {
                    //         for (let item of Object.values(response.body.errors)) {
                    //             //                            this.dataLoadingErrors.push(item.message);
                    //             this.showToast(item.message, 'danger');
                    //         }
                    //     }
                    // })
                ;
                var p3 = this.$http.get('/hu/admin/api/product/statuses')
//                     .then(response => {
//                         this.statuses = response.data.statuses;
//                     }, response => {
//                         if (response.status === 422) {
//                             for (let item of Object.values(response.body.errors)) {
// //                            this.dataLoadingErrors.push(item.message);
//                                 this.showToast(item.message, 'danger');
//                             }
//                         }
//                     })
                ;
                var p4 = this.$http.get('/hu/admin/api/product/categories')
//                     .then(response => {
//                         this.categories = response.data.categories;
//                         // this.isDataLoading = false;
//                     }, response => {
//                         if (response.status === 422) {
//                             for (let item of Object.values(response.body.errors)) {
// //                            this.dataLoadingErrors.push(item.message);
//                                 this.showToast(item.message, 'danger');
//                             }
//                         }
//                     })
                ;
                var p5 = this.$http.get('/hu/admin/api/product/badges')
//                     .then(response => {
//                         this.badges = response.data.badges;
//                         // this.isDataLoading = false;
//                     }, response => {
//                         if (response.status === 422) {
//                             for (let item of Object.values(response.body.errors)) {
// //                            this.dataLoadingErrors.push(item.message);
//                                 this.showToast(item.message, 'danger');
//                             }
//                         }
//                     }).finally(() => {
//                     this.isDataLoading = false;
//                 })
                ;
                Promise.all([p1,p3,p4,p5]).then(([r1,r3,r4,r5]) => {
                    this.productInForm = r1.data.products[0];
                    this.productInForm.images.sort(function(a, b){return a.ordering - b.ordering});
                    this.productKindId = this.productInForm.kind.id;

                    this.statuses = r3.data.statuses;
                    this.categories = r4.data.categories;
                    this.badges = r5.data.badges;
                }, ([r1,r3,r4,r5]) => {
                    if (r1.status === 422) {
                        for (let item of Object.values(r1.body.errors)) {
                            this.showToast(item.message, 'danger');
                        }
                    }
                    if (r3.status === 422) {
                        for (let item of Object.values(r3.body.errors)) {
                            this.showToast(item.message, 'danger');
                        }
                    }
                    if (r4.status === 422) {
                        for (let item of Object.values(r4.body.errors)) {
                            this.showToast(item.message, 'danger');
                        }
                    }
                    if (r5.status === 422) {
                        for (let item of Object.values(r5.body.errors)) {
                            this.showToast(item.message, 'danger');
                        }
                    }
                }).finally(() => {
                    this.isDataLoading = false;
                })
                ;

            }

        },
        mounted () {
        },
    }
</script>

<style scoped>
</style>

