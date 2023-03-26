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
        <edit-form v-else
                   :productId="productId"
                   :product="productInForm"
                   :categories="categories"
                   :statuses="statuses"
                   :badges="badges"
                   :salesChannels="salesChannels"
                   :product-kinds="productKinds"
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
    import Notify from "../../../js/alerts/notify";
    import Multiselect from 'vue-multiselect'
    import EditForm from './EditForm'

    const initialData = () => {
        return {
            isDataLoading: true,
            formIsPosting: false,
            productInForm: {
                name: '',
                description: '',
                price: {
                    numericValue: null,
                },
                status: {},
                options: [],
                // kind: {
                //
                // }
                images: [],
                image: null, //nem lehet hasznalatban
                imageUrl: '',  //nem lehet hasznalatban
            },
            productKindId: '',
            // categories: [],
            // statuses: [],
            // badges: [],
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
            'product',
            'statuses',
            'categories',
            'badges',
            'salesChannels',
            'productKinds',
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
                this.resetErrosInForm();
                if (this.productId > 0) {
                    this.formIsPosting = true;
                    this.$http.put('/hu/admin/api/products/'+ productData.id, productData)
                        .then(
                            response => {
                                Notify.success('A módosítások sikeresen elmentve.', 'success');
                                this.productInForm = response.data.products[0];  /// ITT CELSZERU BETOLTENI A VISSZAJOVO objektumot
                                this.orderImages(this.productInForm.images);
                                this.formIsPosting = false;
                            },
                            response => {
                                this.formIsPosting = false;
                                if (response.status === 422) {
                                    Notify.error('Upsz... valami hiba történt. Ellenőrizd az adatokat.', 'danger');
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
                                Notify.success('A módosítások sikeresen elmentve.', 'success');
//                                this.products.push(response.body);
                            
                            },
                            response => {
                                this.formIsPosting = false;
                                if (response.status === 422) {
                                    Notify.error('Upsz... valami hiba történt. Ellenőrizd az adatokat.', 'danger');
                                    for (let value of Object.values(response.body.errors)) {
                                        // this.formErrors.push(value);
                                        
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
                this.resetForm = true
            },
            resetErrosInForm () {
                this.formErrors = initialData().formErrors;
            },
            orderImages(images) {
                images.sort(function(a, b) {
                    return a.ordering - b.ordering;
                });
            },
        },
        created () {
            this.productInForm = this.product;

            if (this.productInForm.id !== null) {
                this.orderImages(this.productInForm.images);
              // this.productInForm.images.sort(function (a, b) {
              //   return a.ordering - b.ordering
              // });
              this.productKindId = this.productInForm.kind.id;
            }

            this.isDataLoading = false;

            // var p3 = this.$http.get('/hu/admin/api/product/statuses');
            // var p4 = this.$http.get('/hu/admin/api/product/categories');
            // var p5 = this.$http.get('/hu/admin/api/product/badges');
            //
            // Promise.all([p3, p4, p5]).then(([r3, r4, r5]) => {
            //   this.statuses = r3.data.statuses;
            //   this.categories = r4.data.categories;
            //   this.badges = r5.data.badges;
            // }, ([r3, r4, r5]) => {
            //   if (r3.status === 422) {
            //     for (let item of Object.values(r3.body.errors)) {
            //       Notify.error(item.message, 'danger');
            //     }
            //   }
            //   if (r4.status === 422) {
            //     for (let item of Object.values(r4.body.errors)) {
            //       Notify.error(item.message, 'danger');
            //     }
            //   }
            //   if (r5.status === 422) {
            //     for (let item of Object.values(r5.body.errors)) {
            //       Notify.error(item.message, 'danger');
            //     }
            //   }
            // }).finally(() => {
            //   this.isDataLoading = false;
            // })
            // ;


            // if (this.productId > 0 ) {
            //   var p1 = this.$http.get(`/hu/admin/api/products/${this.productId}`);
            // }
            // var p3 = this.$http.get('/hu/admin/api/product/statuses');
            // var p4 = this.$http.get('/hu/admin/api/product/categories');
            // var p5 = this.$http.get('/hu/admin/api/product/badges');
            // if (this.productId > 0 ) {
            //   Promise.all([p1, p3, p4, p5]).then(([r1, r3, r4, r5]) => {
            //     this.productInForm = r1.data.products[0];
            //     this.productInForm.images.sort(function (a, b) {
            //       return a.ordering - b.ordering
            //     });
            //     this.productKindId = this.productInForm.kind.id;
            //
            //     this.statuses = r3.data.statuses;
            //     this.categories = r4.data.categories;
            //     this.badges = r5.data.badges;
            //   }, ([r1, r3, r4, r5]) => {
            //     if (r1.status === 422) {
            //       for (let item of Object.values(r1.body.errors)) {
            //         Notify.error(item.message, 'danger');
            //       }
            //     }
            //     if (r3.status === 422) {
            //       for (let item of Object.values(r3.body.errors)) {
            //         Notify.error(item.message, 'danger');
            //       }
            //     }
            //     if (r4.status === 422) {
            //       for (let item of Object.values(r4.body.errors)) {
            //         Notify.error(item.message, 'danger');
            //       }
            //     }
            //     if (r5.status === 422) {
            //       for (let item of Object.values(r5.body.errors)) {
            //         Notify.error(item.message, 'danger');
            //       }
            //     }
            //   }).finally(() => {
            //     this.isDataLoading = false;
            //   })
            //   ;
            // }
            // else {
            //       Promise.all([p3, p4, p5]).then(([r3, r4, r5]) => {
            //         this.statuses = r3.data.statuses;
            //         this.categories = r4.data.categories;
            //         this.badges = r5.data.badges;
            //       }, ([r3, r4, r5]) => {
            //         if (r3.status === 422) {
            //           for (let item of Object.values(r3.body.errors)) {
            //             Notify.error(item.message, 'danger');
            //           }
            //         }
            //         if (r4.status === 422) {
            //           for (let item of Object.values(r4.body.errors)) {
            //             Notify.error(item.message, 'danger');
            //           }
            //         }
            //         if (r5.status === 422) {
            //           for (let item of Object.values(r5.body.errors)) {
            //             Notify.error(item.message, 'danger');
            //           }
            //         }
            //       }).finally(() => {
            //         this.isDataLoading = false;
            //       })
            //       ;
            //     }

        },
        mounted () {
        },
    }
</script>

<style scoped>
</style>

