<template>
    <div class="table-responsive mb-3">
        <template v-if="isDataLoading">
            <div class="d-flex">
                <span class="alert alert-secondary w-100 mb-2 py-3">
                    <i class="fas fa-lg fa-spinner fa-spin mr-2"></i>
                    Adatok betöltése folyamatban...
                </span>
            </div>
        </template>
        <template v-else>
            <table class="table table-striped table-borderedX table-borderless table-hover mb-0 JS--wrapper" id="product-listX" data-page-length="25">
                <thead class="thead-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col"><i class="fas fa-check-circle"></i></th>
                    <th scope="col">Név</th>
                    <th scope="col">Kép</th>
                    <th scope="col">Leírás</th>
                    <th scope="col">Slug</th>
                    <!--<th scope="col">Termékek</th>-->
                    <th scope="col"><i class="fas fa-ellipsis-h"></i></th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="categories1.length" v-for="category in categories1" track-by="id">
                    <td>
                        {{category.id}}
                    </td>
                    <td class="align-top text-left">
                        <i v-if="category.enabled" class="fas fa-check-circle text-success"></i>
                        <i v-else class="fas fa-times-circle text-danger"></i>
                    </td>
                    <td>
                        <a v-on:click.prevent="onEdit(category)" href="#" class="">
                            {{category.name}}
                        </a>
                        <br>
                        <span class="text-xs">
                        <span class="text-muted">Szülő:</span>
                        <em v-if="category.parentCategory">{{category.parentCategory.name}}</em>
                        <em v-else>-</em>
                    </span>
                    </td>
                    <td>
                        <a v-bind:href="category.imageUrl" target="_blank">
                            <img class="img-thumbnail" v-bind:src="category.imageUrl" style="max-height: 40px; width: 160px;" />
                        </a>
                    </td>
                    <td class="align-top text-sm w-33">
                        {{category.description}}
                    </td>
                    <td>{{category.slug}}</td>
                    <td>
                        <!--<a v-bind:href="category.editLink" class="btnX btn-smX btn-primaryX mr-2" title="Módosít">-->
                        <a v-on:click.prevent="onEdit(category)" href="#" class="btnX btn-smX btn-primaryX mr-2" title="Módosít">
                            <i class="far fa-edit"></i></a>
                        <a v-bind:href="category.urlToView" class="btn-smX mr-2" target="_blank" title="Megtekinti a weboldalon">
                            <i class="fas fa-external-link-alt"></i></a>
                        <a v-on:click.prevent="onRemove(category.id)" href="#" class="btn-smX" title="Töröl">
                            <i class="far fa-trash-alt"></i></a>
                    </td>
                </tr>
                <tr v-if="!categories1.length">
                    <td class="text-danger">
                        Upsz... nincs kategória!</td>
                </tr>
                </tbody>
            </table>
        </template>
    </div>
</template>

<script>
    import { ContentLoader } from 'vue-content-loader'
    
    export default {
        components: {
            ContentLoader,
        },
        props: [
            'categories1',
            'isDataLoading',
        ],
        methods: {
            onEdit (cat) {
                this.$emit('edit',cat);
            },
            onRemove (catId) {
                this.$emit('removeEntry', catId)
            },
        },
    }
</script>