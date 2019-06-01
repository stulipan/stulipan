<template>
    <div>
        <content-loader v-if="isDataLoading"
                        :speed="2"
                        :animate="true"
                        :height="30"
        ></content-loader>
        <!--<select v-if="!isDataLoading"-->
                <!--v-model="cityInForm.id"-->
                <!--id="whereTo" name="whereTo"-->
                <!--class="form-control"-->
                <!--required="required">-->
            <!--<option value="" default> - Település: Budakalász, vagy Irányítószám: 2011 - </option>-->
            <!--<template v-for="item in cities" track-by="id">-->
                <!--<option :value="item.id">{{item.city}} - {{item.zip}}</option>-->
            <!--</template>-->
        <!--</select>-->
        
        <!--<multiselect v-else-if="dataLoadingError"-->
                <!--v-model="cityInForm"-->
                <!--:options="cities"-->
                <!--:custom-label="cityWithZip"-->
                <!--placeholder="dataLoadingError"-->
                <!--track-by="id"-->
                <!--:showLabels="false"-->
                <!--:loading="isDataLoading"-->
                <!--class="mb-2"-->
        <!--&gt;-->
            <!--<span slot="noResult">Upsz! Nincs ilyen település vagy elírtad...</span>-->
            <!--<span slot="noOptions">Upsz! {{ dataLoadingError }}...</span>-->
        <!--</multiselect>-->
        <multiselect v-else
                     v-model="cityInForm"
                     :options="cities"
                     :custom-label="cityWithZip"
                     placeholder=""
                     track-by="id"
                     :showLabels="false"
                     :loading="isDataLoading"
                     class="mb-2"
        >
            <span slot="noResult">Upsz! Nincs ilyen település vagy elírtad...</span>
            <span slot="noOptions">Upsz! {{ dataLoadingErrors[0] }}...</span>
        </multiselect>
        <span v-if="cityInForm.city" class="text-sm">Szállítási díj: <em>{{ cityInForm.price.value }} Ft</em></span>
    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect'
    import { ContentLoader } from 'vue-content-loader'

    const initialData = () => {
        return {
            isDataLoading: true,
            dataLoadingErrors: [],
            cityInForm: {},
            cities: [],
            formErrors: [],
            resetForm: false,  // If true will hide form errors and uploaded images preview
        }
    }

    export default {
        components: {
            Multiselect,
            ContentLoader,
        },
        data: initialData,
        methods: {
            cityWithZip(cityObj) {
                if (!cityObj.city) {
                    return 'Település vagy Irányítószám'
                } else return `[${cityObj.city.zip}] ${cityObj.city.city}`
//                return '[' + zip + ']  ' + city  // ugyanaz mint: return `[${zip}] ${city}`
            },
            
            
            saveForm (categoryData) {
                const index = this.categories.findIndex((c) => c.id === categoryData.id);
                    // update category if it exists or create it if it doesn't
                    if (index !== -1) {
                        this.$http.put('/admin/api/categories/'+ categoryData.id, categoryData)
                            .then(
                                response => {
                                    this.categories.splice(index, 1, response.body);
                                    this.resetCategoryInForm();
                                },
                                response => {
                                    if (response.status === 422) {
                                        for (let value of Object.values(response.body.errors)) {
                                            this.formErrors.push(value);
                                        }
                                    }
                                    if (!this.formErrors.length) {
                                        this.resetCategoryInForm()
                                    }
                                })
                    } else {
                        // add it to the category list
                        this.$http.post('/admin/api/categories', categoryData)
                            .then(
                                response => {
                                    this.categories.push(response.body);
                                    this.resetCategoryInForm();
                                },
                                response => {
                                    if (response.status === 422) {
                                        for (let value of Object.values(response.body.errors)) {
                                            this.formErrors.push(value);
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
        },
        created () {
            this.$http.get('/admin/api/geoplace/prices/')
                .then(response => {
                    this.cities = response.data.cities;
                    this.isDataLoading = false;
                }, response => {
                    if (response.status === 422) {
                        for (let item of Object.values(response.body.errors)) {
                            this.dataLoadingErrors.push(item.message);
                        }
                    }
                    this.isDataLoading = false;
                });
        },
    }
</script>

<!--<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>-->
<style scoped>
</style>

