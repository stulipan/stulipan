<template>
    <div class="">
        <div v-if="items === undefined || items.length == 0" class="cart-item text-center">
            <div>
                <h6 class="text-uppercase">A kosarad jelenleg üres</h6>
                <p class="mb-0">...de nem sokáig! Válogass a több mint 100 gyönyörű <a href="/">csokor és virágdoboz</a> közül.</p>
            </div>
            <span class="py-5"></span>
        </div>
        <template v-else v-for="item in items">
            <div class="cart-item py-3">
                <div class="row align-items-center text-center">
                    <div class="col-9 d-flex align-items-center">
                            <a href="/product/id" class="">
                                <img :src="item.product.images[0].imageUrl" :alt="item.product.name" class="cart-item-img">
                            </a>
                            <div class="cart-title text-left">
                                <a class="text-uppercaseX text-dark" href="/product/id"><strong>{{ item.product.name }}</strong></a>
                                <p class="text-muted text-smX mb-0">
                                    Ár: {{ item.price }} Ft <br>
                                    Összesen: {{ item.price * item.quantity }} Ft <br>
                                    <!--<template v-if="item.errors">-->
                                        <!--<span class="text-danger">{{ item.errors[0] }}</span>-->
                                    <!--</template>-->
                                </p>
                                <!--<br>-->
                            </div>
                    </div>
            
                    <div class="col-2 px-0">
                        <div class="d-flex">
                            <div class="quantity">
                                <div class="float-md-left">
                                    <select v-model="item.quantity"
                                            @change.prevent="updateQuantity(item)"
                                            id="itemQuantity" class="form-control form-control-sm">
                                            <option v-for="quantity in quantities" :value="quantity">
                                                {{quantity}}
                                            </option>
                                    </select>
                                    <span class="text-muted text-sm">db</span>
                                </div>
                            </div>
                        </div>
                    </div>
            
                    <div class="col-1 pl-0">
                        <delete-button @onRemove="removeFromCart(item)"
                        ></delete-button>
                        <!--<a @click.prevent="removeFromCart(item)" href="" class="cart-remove text&#45;&#45;toolLink">-->
                            <!--<i v-if="isItemDeleting" class="fas fa-lg fa-spinner fa-spin"></i>-->
                            <!--<i v-else class="far fa-trash-alt"></i>-->
                        <!--</a>-->
                    </div>
        
                </div>
            </div>
        </template>
        
    </div>
</template>

<script>
    import DeleteButton from './../_components/delete-button/DeleteButton.vue'
    
    const initialData = () => {
        return {
            quantities: [1,2,3,4,5,6,7,8,9,10],
            isItemDeleting: false,
        }
    }

    export default {
        props: [
            'items',
        ],
        components: {
            DeleteButton,
        },
        data: initialData,
        methods: {
            updateQuantity (item) {
                this.$emit('updateQuantity', item);
            },
            removeFromCart(item) {
                this.isItemDeleting = true;
                this.$emit('removeFromCart', item);
            }
        },
        created () {
        },
    }
</script>

<style scoped>
</style>

