<template>
  <div class="main-content">
    <breadcumb :page="$t('AddProduct')" :folder="$t('Products')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <validation-observer ref="Create_Product" v-if="!isLoading">
      <b-form @submit.prevent="Submit_Product" enctype="multipart/form-data">
        <b-row>
          <b-col md="8" class="mb-2">
            <b-card>
              <b-row>
                <!-- Name -->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="Name"
                    :rules="{required:true , min:3 , max:55}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Name_product') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Name-feedback"
                        label="Name"
                        :placeholder="$t('Enter_Name_Product')"
                        v-model="product.name"
                      ></b-form-input>
                      <b-form-invalid-feedback id="Name-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Category -->
                <b-col md="6" class="mb-2">
                  <validation-provider name="category" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('Categorie') + ' ' + '*'">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Category')"
                        v-model="product.category_id"
                        :options="categories.map(categories => ({label: categories.name, value: categories.id}))"
                      />
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Brand  -->
                <b-col md="6" class="mb-2">
                  <b-form-group :label="$t('Brand')">
                    <v-select
                      :placeholder="$t('Choose_Brand')"
                      :reduce="label => label.value"
                      v-model="product.brand_id"
                      :options="brands.map(brands => ({label: brands.name, value: brands.id}))"
                    />
                  </b-form-group>
                </b-col>

                <!-- Barcode Symbology  -->
                <b-col md="6" class="mb-2">
                  <validation-provider name="Barcode Symbology" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('BarcodeSymbology') + ' ' + '*'">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="product.Type_barcode"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Symbology')"
                        :options="
                            [
                              {label: 'Code 128', value: 'CODE128'},
                              {label: 'Code 39', value: 'CODE39'},
                              {label: 'EAN8', value: 'EAN8'},
                              {label: 'EAN13', value: 'EAN13'},
                              {label: 'UPC', value: 'UPC'},
                            ]"
                      ></v-select>
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Product Cost -->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="Product Cost"
                    :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('ProductCost') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="ProductCost-feedback"
                        label="Cost"
                        :placeholder="$t('Enter_Product_Cost')"
                        v-model="product.cost"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="ProductCost-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Product Price -->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="Product Price"
                    :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('ProductPrice') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="ProductPrice-feedback"
                        label="Price"
                        :placeholder="$t('Enter_Product_Price')"
                        v-model="product.price"
                      ></b-form-input>

                      <b-form-invalid-feedback
                        id="ProductPrice-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Unit Product -->
                <b-col md="6" class="mb-2">
                  <validation-provider name="Unit Product" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('UnitProduct') + ' ' + '*'">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="product.unit_id"
                        class="required"
                        required
                        @input="Selected_Unit"
                        :placeholder="$t('Choose_Unit_Product')"
                        :reduce="label => label.value"
                        :options="units.map(units => ({label: units.name, value: units.id}))"
                      />
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Unit Sale -->
                <b-col md="6" class="mb-2">
                  <validation-provider name="Unit Sale" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('UnitSale') + ' ' + '*'">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="product.unit_sale_id"
                        :placeholder="$t('Choose_Unit_Sale')"
                        :reduce="label => label.value"
                        :options="units_sub.map(units_sub => ({label: units_sub.name, value: units_sub.id}))"
                      />
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Unit Purchase -->
                <b-col md="6" class="mb-2">
                  <validation-provider name="Unit Purchase" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('UnitPurchase') + ' ' + '*'">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="product.unit_purchase_id"
                        :placeholder="$t('Choose_Unit_Purchase')"
                        :reduce="label => label.value"
                        :options="units_sub.map(units_sub => ({label: units_sub.name, value: units_sub.id}))"
                      />
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Stock Alert -->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="Stock Alert"
                    :rules="{ regex: /^\d*\.?\d*$/}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('StockAlert')">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="StockAlert-feedback"
                        label="Stock alert"
                        :placeholder="$t('Enter_Stock_alert')"
                        v-model="product.stock_alert"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="StockAlert-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Order Tax -->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="Order Tax"
                    :rules="{regex: /^\d*\.?\d*$/}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('OrderTax')">
                      <div class="input-group">
                        <input
                          :state="getValidationState(validationContext)"
                          aria-describedby="OrderTax-feedback"
                          v-model.number="product.TaxNet"
                          type="text"
                          class="form-control"
                        >
                        <div class="input-group-append">
                          <span class="input-group-text">%</span>
                        </div>
                      </div>
                      <b-form-invalid-feedback
                        id="OrderTax-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Tax Method -->
                <b-col lg="6" md="6" sm="12" class="mb-2">
                  <validation-provider name="Tax Method" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('TaxMethod') + ' ' + '*'">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="product.tax_method"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Method')"
                        :options="
                           [
                            {label: 'Exclusive', value: '1'},
                            {label: 'Inclusive', value: '2'}
                           ]"
                      ></v-select>
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Description -->
                <b-col md="12" class="mb-2">
                    <validation-provider
                        name="Description"
                        :rules="{ required: true, min: 3, max: 2000 }"
                        v-slot="validationContext"
                    >
                        <b-form-group :label="$t('Description') + ' ' + '*'">
                        <b-textarea
                            rows="4"
                            :state="getValidationState(validationContext)"
                            aria-describedby="Description-feedback"
                            :placeholder="$t('Enter the Description')"
                            v-model="product.note"
                        ></b-textarea>
                        <b-form-invalid-feedback id="Description-feedback">
                            {{ validationContext.errors[0] }}
                        </b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                </b-col>

                 <!-- Multiple Variants -->
                  <b-col md="12 mb-2">
                    <ValidationProvider rules vid="product" v-slot="x">
                      <div class="form-check">
                        <label class="checkbox checkbox-outline-primary">
                          <input type="checkbox" v-model="product.is_variant">
                          <!-- <h5>{{$t('ProductHasMultiVariants')}}</h5> -->
                          <h5>This Product has Multi Variants</h5>
                          <span class="checkmark"></span>
                        </label>
                      </div>
                    </ValidationProvider>
                  </b-col>
                  <b-col md="12 mb-5" v-show="product.is_variant">
                    <vue-tags-input
                      placeholder="+ add"
                      v-model="tag"
                      :tags="variants"
                      class="tag-custom text-15"
                      @adding-duplicate="showNotifDuplicate()"
                      @tags-changed="newTags => variants = newTags"
                    />
                  </b-col>

                  <!-- Product_Has_Imei_Serial_number -->
                  <b-col md="12 mb-2">
                    <ValidationProvider rules vid="product" v-slot="x">
                      <div class="form-check">
                        <label class="checkbox checkbox-outline-primary">
                          <input type="checkbox" v-model="product.is_imei">
                          <!-- <h5>{{$t('Product_Has_Imei_Serial_number')}}</h5> -->
                          <h5>This Product has Imei/Serial Number</h5>
                          <span class="checkmark"></span>
                        </label>
                      </div>
                    </ValidationProvider>
                  </b-col>

                   <!-- Product_Has_Expiry -->
                   <b-col md="12 mb-2">
                    <ValidationProvider rules vid="product" v-slot="x">
                      <div class="form-check">
                        <label class="checkbox checkbox-outline-primary">
                          <input type="checkbox" v-model="product.is_expire">
                          <h5>This Product has Expiry</h5>
                          <span class="checkmark"></span>
                        </label>
                      </div>
                    </ValidationProvider>
                  </b-col>

                  <!-- Product_Has_Qoutation -->
                  <b-col md="12 mb-2">
                    <ValidationProvider rules vid="product" v-slot="x">
                      <div class="form-check">
                        <label class="checkbox checkbox-outline-primary">
                          <input type="checkbox" v-model="product.is_quotation">
                          <h5>This Product is for Quotation</h5>
                          <span class="checkmark"></span>
                        </label>
                      </div>
                    </ValidationProvider>
                  </b-col>

                   <!-- Product_Has_Warranty -->
                   <b-col md="12 mb-2">
                    <ValidationProvider rules vid="product" v-slot="x">
                      <div class="form-check">
                        <label class="checkbox checkbox-outline-primary">
                          <input type="checkbox" v-model="product.is_warranty">
                          <h5>This Product has Warranty</h5>
                          <span class="checkmark"></span>
                        </label>
                      </div>
                    </ValidationProvider>
                  </b-col>

              </b-row>
            </b-card>
          </b-col>


          <b-col md="4">
            <!-- upload-multiple-image -->
            <b-card>
              <div class="card-header">
                <h5>{{$t('MultipleImage')}}</h5>
              </div>
              <div class="card-body">
                <b-row class="form-group">
                  <b-col md="12 mb-5">
                    <div
                      id="my-strictly-unique-vue-upload-multiple-image"
                      class="d-flex justify-content-center"
                    >
                      <vue-upload-multiple-image
                      @upload-success="uploadImageSuccess"
                      @before-remove="beforeRemove"
                      dragText="Drag & Drop Multiple images For product"
                      dropText="Drag & Drop image"
                      browseText="(or) Select"
                      accept=image/gif,image/jpeg,image/png,image/bmp,image/jpg
                      primaryText='success'
                      markIsPrimaryText='success'
                      popupText='have been successfully uploaded'
                      :data-images="images"
                      idUpload="myIdUpload"
                      :showEdit="false"
                      />
                    </div>
                  </b-col>

                </b-row>
              </div>
            </b-card>
          </b-col>
          <b-col md="12" class="mt-3">
             <b-button variant="primary" type="submit" :disabled="SubmitProcessing">{{$t('submit')}}</b-button>
              <div v-once class="typo__p" v-if="SubmitProcessing">
                <div class="spinner sm spinner-primary mt-3"></div>
              </div>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>
  </div>
</template>


<script>
import VueUploadMultipleImage from "vue-upload-multiple-image";
import VueTagsInput from "@johmun/vue-tags-input";
import NProgress from "nprogress";
import { exportDefaultSpecifier } from "babel-types";

export default {
  metaInfo: {
    title: "Create Product"
  },
  data() {
    return {
      tag: "",
      len: 8,
      images: [],
      imageArray: [],
      change: false,
      isLoading: true,
      SubmitProcessing:false,
      data: new FormData(),
      categories: [],
      units: [],
      units_sub: [],
      brands: [],
      roles: {},
      variants: [],
      product: {
        name: "",
        code: "",
        Type_barcode: "",
        cost: "",
        price: "",
        brand_id: "",
        category_id: "",
        TaxNet: "0",
        tax_method: "1",
        unit_id: "",
        unit_sale_id: "",
        unit_purchase_id: "",
        stock_alert: "0",
        image: "",
        note: "",
        is_variant: false,
        is_imei: false,
        is_expire: false,
        is_quotation: false,
        is_warranty: false
      },
      code_exist: ""
    };
  },

  components: {
    VueUploadMultipleImage,
    VueTagsInput
  },

  methods: {
    //------------- Submit Validation Create Product
    Submit_Product() {
      this.$refs.Create_Product.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else if (this.product.cost >= this.product.price) {
          this.makeToast(
            "danger",
            this.$t("Selling price must be greater than its cost price"),
            this.$t("Failed")
          );
        } else {
          this.Create_Product();
        }
      });
    },



    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //------ Validation State
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------Show Notification If Variant is Duplicate
    showNotifDuplicate() {
      this.makeToast(
        "warning",
        this.$t("VariantDuplicate"),
        this.$t("Warning")
      );
    },

    //------ Event upload Image Success
    uploadImageSuccess(formData, index, fileList, imageArray) {
      this.images = fileList;
      console.log(this.images);
    },

    //------ Event before Remove Image
    beforeRemove(index, done, fileList) {
      var remove = confirm("remove image");
      if (remove == true) {
        this.images = fileList;
        done();
      } else {
      }
    },

    //-------------- Product Get Elements
    GetElements() {
      axios
        .get("Products/create")
        .then(response => {
          this.categories = response.data.categories;
          this.brands = response.data.brands;
          this.units = response.data.units;
          console.log(this.units);
          this.isLoading = false;
        })
        .catch(response => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //---------------------- Get Sub Units with Unit id ------------------------------\\
    Get_Units_SubBase(value) {
      axios
        .get("Get_Units_SubBase?id=" + value)
        .then(({ data }) => (this.units_sub = data));
    },

    //---------------------- Event Select Unit Product ------------------------------\\
    Selected_Unit(value) {
      this.units_sub = [];
      this.product.unit_sale_id = "";
      this.product.unit_purchase_id = "";
      this.Get_Units_SubBase(value);
    },

    //------------------------------ Create new Product ------------------------------\\
    Create_Product() {
    //   Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      var self = this;
      self.SubmitProcessing = true;

      if (self.product.is_variant && self.variants.length <= 0) {
        self.product.is_variant = false;
      }
      // append objet product
      Object.entries(self.product).forEach(([key, value]) => {
        self.data.append(key, value);
      });

      // append array variants
      if (self.variants.length) {
        for (var i = 0; i < self.variants.length; i++) {
          self.data.append("variants[" + i + "]", self.variants[i].text);
        }
      }
      //append array images
      if (self.images.length > 0) {
        for (var k = 0; k < self.images.length; k++) {
          Object.entries(self.images[k]).forEach(([key, value]) => {
            self.data.append("images[" + k + "][" + key + "]", value);
          });
        }
      }

      // Send Data with axios
      axios
        .post("Products", self.data)
        .then(response => {

          if(response.data.exist == true) {
                NProgress.done();
                self.SubmitProcessing = false;

                this.makeToast(
                "danger",
                this.$t("Data has already exist"),
                this.$t("Failed")
              );

          } else if(response.data.exist == false)  {
                NProgress.done();
                self.SubmitProcessing = false;

                this.$router.push({ name: "index_products" });
                this.makeToast(
                "success",
                this.$t("Successfully_Created"),
                this.$t("Success")
              );
          }
          // Complete the animation of theprogress bar.

        })
        .catch(error => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          if (error.errors.code.length > 0) {
            self.code_exist = error.errors.code[0];
          }
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          self.SubmitProcessing = false;
        });
    }
  }, //end Methods

  //-----------------------------Created function-------------------

  created: function() {
    this.GetElements();
  }
};
</script>
