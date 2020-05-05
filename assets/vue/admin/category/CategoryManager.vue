<template>
    <section class="hatter">
        <button type="button" @click="message" >Click</button>
        <edit-form
                :category="categoryInForm"
                :parentCategories="categories"
                :errors="formErrors"
                :resetForm="resetForm"
                v-on:submit="saveForm"
                v-on:click="cancelForm"
        >
        </edit-form>
        <template>
            <category-list
                    :isDataLoading="dataIsLoading"
                    :categories1="categories"
                    v-on:edit="onEditClicked"
                    v-on:removeEntry="onDeleteClicked"
            >
            </category-list>
        </template>
    </section>
</template>

<script>
    import CategoryList from './CategoryList'
    import EditForm from './EditForm'

    const initialData = () => {
        return {
            dataIsLoading: true,
            categoryInForm: {
//                id: null,
                name: '',
                description: '',
                slug: '',
                parentCategory: '',
                enabled: false,
                image: null,
                imageUrl: '',
            },
            categories: [],
            formErrors: [],
            resetForm: false,  // If true will hide form errors and uploaded images preview
        }
    };

    export default {
        components: {
            CategoryList,
            EditForm,
//            Modal,
        },
        data: initialData,
//        watch:
        methods: {
            message () {
                this.$bvToast.toast('Kategória sikeresen törölve!', {
                    title: 'SIKERES MŰVELET',
                    autoHideDelay: 500000,
                    appendToast: true,
                    variant: "success",
                    toaster: "b-toaster-bottom-left",
                });
            },
            saveForm (categoryData) {
                const index = this.categories.findIndex((c) => c.id === categoryData.id);
                    // update category if it exists or create it if it doesn't
                    if (index !== -1) {
                        this.$http.put('/hu/admin/api/product/categories/'+ categoryData.id, categoryData)
                            .then(
                                response => {
//                                    console.log(response.body.categories[0]);
                                    this.categories.splice(index, 1, response.body.categories[0]);
                                    this.showToast('Adatok sikeresen elmentve!', 'success');
                                    this.resetCategoryInForm();
                                },
                                response => {
                                    if (response.status === 422) {
                                        for (let item of Object.values(response.body.errors)) {
                                            this.formErrors.push(item.message);
                                            this.showToast(item.message, 'danger');
                                        }
                                    }
                                    if (!this.formErrors.length) {
                                        this.resetCategoryInForm()
                                    }
                                })
                    } else {
                        // add it to the category list
                        this.$http.post('/hu/admin/api/product/categories/', categoryData)
                            .then(
                                response => {
                                    this.categories.push(response.body.categories[0]);
                                    this.showToast('Kategória sikeresen létrehozva!', 'success');
                                    this.resetCategoryInForm();
                                },
                                response => {
                                    if (response.status === 422) {
                                        for (let item of Object.values(response.body.errors)) {
                                            this.formErrors.push(item.value);
                                            this.showToast(item.message, 'danger');
                                        }
                                    }
                                    if (!this.formErrors.length) {
                                        this.resetCategoryInForm()
                                    }
                                })
                    }
            },
            resetCategoryInForm () {
                this.categoryInForm = initialData().categoryInForm;
                this.formErrors = initialData().formErrors;
//                this.resetForm = true;  // azert vettem ki, mert ugyis a kategoria valtozast figyeli
            },
            onEditClicked (categoryData) {
                // since objects are passed by reference we need to clone the category
                // either by using Object.assign({}, category) or by using object
                // spread like we do here.
                this.categoryInForm = {...categoryData};
                this.resetForm = initialData().resetForm;
            },
            cancelForm () {
                this.categoryInForm = initialData().categoryInForm;
                this.resetForm = true
            },
            onDeleteClicked (catId) {
                this.$bvModal.msgBoxConfirm('A Törlés gomb megnyomásával véglegesen törölni fogod a tartalmat.', {
                    title: 'Biztosan törölni akarod?',
                    okVariant: 'danger',
                    okTitle: 'Törlés',
                    cancelTitle: 'Mégse',
                    headerClass: 'h3',
                    footerClass: 'p-2',
                    hideHeaderClose: false,
                    centered: false,
                }).then(value => {
                    if (value === true) {
                        this.deleteEntry(catId);
                    } else {
                        this.$bvModal.hide('msgBoxConfirm');
                    }
                }).catch(err => {});
            },
            deleteEntry (catId) {
                this.$http.delete(`/hu/admin/api/product/categories/${catId}`)
                    .then(
                        response => {
                            const index = this.categories.findIndex((c) => c.id === catId);
                            this.categories.splice(index, 1);

                            if (catId === this.categoryInForm.id) {
                                this.resetCategoryInForm()
                            }
//                            this.categories.push(response.body.categories[0]);
                            this.showToast('Kategória sikeresen törölve!', 'success');
                        },
                        response => {
                            if (response.status === 422) {
                                for (let item of Object.values(response.body.errors)) {
                                    this.formErrors.push(item.value);
                                    this.showToast(item.message, 'danger');
                                }
                            }
                        })
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
                    toaster: "b-toaster-bottom-left",
                });
            },
        },
        created () {
            this.$http.get('/hu/admin/api/product/categories/')
                .then(response => {
                    this.categories = response.data.categories;
                    this.dataIsLoading = false;
                }, response => {
                    if (response.status === 422) {
                        for (let item of Object.values(response.body.errors)) {
                            this.showToast(item.message, 'danger');
                        }
                    }
                });
        },
    }
</script>


<style scoped>
</style>

