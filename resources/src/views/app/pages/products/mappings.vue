<template>
  <div class="main-content">
    <breadcumb :page="$t('Mapping')" :folder="$t('Products')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="mappings"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :search-options="{
        enabled: true,
        placeholder: $t('Search_this_table'),
      }"
        :select-options="{
          enabled: true ,
          clearSelectionText: '',
        }"
        @on-selected-rows-change="selectionChanged"
        :pagination-options="{
        enabled: true,
        mode: 'records',
        nextLabel: 'next',
        prevLabel: 'prev',
      }"
        styleClass="table-hover tableOne vgt-table"
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger btn-sm" @click="delete_by_selected()">{{$t('Del')}}</button>
        </div>
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button @click="New_Mapping()" class="btn-rounded" variant="btn btn-primary btn-icon m-1">
            <i class="i-Add"></i>
            {{$t('Add')}}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a @click="Edit_Mapping(props.row)" title="Edit" v-b-tooltip.hover>
              <i class="i-Edit text-25 text-success"></i>
            </a>
            <a title="Delete" v-b-tooltip.hover @click="Delete_Mapping(props.row.id)">
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
          <span v-else-if="props.column.field == 'image'">
            <b-img
              thumbnail
              height="50"
              width="50"
              fluid
              :src="'/images/brands/' + props.row.image"
              alt="image"
            ></b-img>
          </span>
        </template>
      </vue-good-table>
    </b-card>

    <validation-observer ref="Create_mapping">
      <b-modal hide-footer size="md" id="New_mapping" :title="editmode?$t('Edit'):$t('Add')">
        <b-form @submit.prevent="Submit_Mapping" enctype="multipart/form-data">
          <b-row>
             <!-- Product -->
             <b-col md="12">
              <b-form-group label="Product">
                <v-select
                  v-model="mapping.product_id"
                  :reduce="label => label.value"
                  placeholder="Choose Product"
                  :options="products.map(product => ({label: product.name, value: product.id}))"
                />
              </b-form-group>
            </b-col>

            <!-- Warehouse -->
            <b-col md="12">
              <b-form-group label="Warehouse">
                <v-select
                  v-model="mapping.warehouse_id"
                  @input="SelectWarehouse(mapping.warehouse_id)"
                  :reduce="label => label.value"
                  placeholder="Choose Warehouse"
                  :options="warehouses.map(warehouse => ({label: warehouse.name, value: warehouse.id}))"
                />
              </b-form-group>
            </b-col>

            <!-- Warehouse Location -->
            <b-col md="12">
              <b-form-group label="Warehouse Location">
                <v-select
                  v-model="mapping.warehouse_location_id"
                  :reduce="label => label.value"
                  placeholder="Choose Warehouse Location"
                  :options="filtered_warehouse_location.map(warehouse_location => ({label: warehouse_location.name, value: warehouse_location.id}))"
                />
              </b-form-group>
            </b-col>

            <b-col md="12" class="mt-3">
              <b-button variant="primary" type="submit"  :disabled="SubmitProcessing">{{$t('submit')}}</b-button>
                <div v-once class="typo__p" v-if="SubmitProcessing">
                  <div class="spinner sm spinner-primary mt-3"></div>
                </div>
            </b-col>

          </b-row>
        </b-form>
      </b-modal>
    </validation-observer>
  </div>
</template>

<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Brand"
  },
  data() {
    return {
      isLoading: true,
      SubmitProcessing:false,
      serverParams: {
        columnFilters: {},
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      selectedIds: [],
      totalRows: "",
      search: "",
      data: new FormData(),
      editmode: false,
      mappings: [],
      limit: "10",
      mapping: {
        id: "",
        product_id: "",
        warehouse_id: "",
        warehouse_location_id: ""
      },
      products: [],
      warehouses: [],
      warehouse_locations: [],
      filtered_warehouse_location: []
    };
  },
  computed: {
    columns() {
      return [
        {
          label: 'Product Code',
          field: "product.code",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: 'Product Name',
          field: "product.name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: 'Warehouse',
          field: "warehouse.name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: 'Warehouse Location',
          field: "warehouse_location.name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Action"),
          field: "actions",
          html: true,
          tdClass: "text-right",
          thClass: "text-right",
          sortable: false
        }
      ];
    }
  },

  methods: {
    //---- update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Mappings(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Mappings(1);
      }
    },

    //---- Event on Sort Change
    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.Get_Mappings(this.serverParams.page);
    },

    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },

    //---- Event on Search

    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Mappings(this.serverParams.page);
    },

    //---- Validation State Form

    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------------- Submit Validation Create & Edit Brand
    Submit_Mapping() {
      this.$refs.Create_mapping.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (!this.editmode) {
            this.Create_Mapping();
          } else {
            this.Update_Mapping();
          }
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

    //------------------------------ Modal (create Brand) -------------------------------\\
    New_Mapping() {
      this.reset_Form();
      this.editmode = false;
      this.$bvModal.show("New_mapping");
    },

    //------------------------------ Modal (Update Brand) -------------------------------\\
    Edit_Mapping(mapping) {
      this.Get_Mappings(this.serverParams.page);
      this.reset_Form();
      this.mapping = mapping;
      if(mapping.warehouse_id){
        this.LoadSelectWarehouseLocation(mapping.warehouse_id);
      }
      this.editmode = true;
      this.$bvModal.show("New_mapping");
    },

    //---------------------------------------- Get All brands-----------------\\
    Get_Mappings(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "mappings?page=" +
            page +
            "&SortField=" +
            this.serverParams.sort.field +
            "&SortType=" +
            this.serverParams.sort.type +
            "&search=" +
            this.search +
            "&limit=" +
            this.limit
        )
        .then(response => {
          this.mappings = response.data.mappings;
          this.totalRows = response.data.totalRows;

          this.products = response.data.products;
          this.warehouses = response.data.warehouses;
          this.warehouse_locations = response.data.warehouse_locations;

          // Complete the animation of theprogress bar.
          NProgress.done();
          this.isLoading = false;
        })
        .catch(response => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },

    //---------------------------------------- Create new brand-----------------\\
    Create_Mapping() {
      var self = this;
      self.SubmitProcessing = true;
      self.data.append("product_id", self.mapping.product_id);
      self.data.append("warehouse_id", self.mapping.warehouse_id);
      self.data.append("warehouse_location_id", self.mapping.warehouse_location_id);
      axios
        .post("mappings", self.data)
        .then(response => {
          if(response.data.exist == true) {
              NProgress.done();
              self.SubmitProcessing = false;

              this.makeToast(
              "danger",
              this.$t("Data has already exist"),
              this.$t("Failed")
              );

          } else if(response.data.exist == false) {
            this.SubmitProcessing = false;
            Fire.$emit("Event_Mapping");

            this.makeToast(
              "success",
              "Create Mapping",
              this.$t("Success")
            );
          }
        })
        .catch(error => {
           self.SubmitProcessing = false;
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //---------------------------------------- Update Brand-----------------\\
    Update_Mapping() {
      var self = this;
      self.SubmitProcessing = true;
      self.data.append("product_id", self.mapping.product_id);
      self.data.append("warehouse_id", self.mapping.warehouse_id);
      self.data.append("warehouse_location_id", self.mapping.warehouse_location_id);
      self.data.append("_method", "put");

      axios
        .post("mappings/" + self.mapping.id, self.data)
        .then(response => {
          if(response.data.exist == true) {
            NProgress.done();
              self.SubmitProcessing = false;

              this.makeToast(
              "danger",
              this.$t("Data has already exist"),
              this.$t("Failed")
              );
          } else if (response.data.exist == false) {
            self.SubmitProcessing = false;
            Fire.$emit("Event_Mapping");

            this.makeToast(
            "success",
            "Update Mapping",
            this.$t("Success")
            );
          }

        })
        .catch(error => {
           self.SubmitProcessing = false;
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //---------------------------------------- Reset Form -----------------\\
    reset_Form() {
      this.mapping = {
        id: "",
        product_id: "",
        warehouse_id: "",
        warehouse_location_id: ""
      };
      this.data = new FormData();
    },

    //---------------------------------------- Delete Brand -----------------\\
    Delete_Mapping(id) {
      this.$swal({
        title: this.$t("Delete.Title"),
        text: this.$t("Delete.Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete.cancelButtonText"),
        confirmButtonText: this.$t("Delete.confirmButtonText")
      }).then(result => {
        if (result.value) {
          axios
            .delete("mappings/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                "Delete Mapping",
                "success"
              );

              Fire.$emit("Delete_Mapping");
            })
            .catch(() => {
              this.$swal(
                this.$t("Delete.Failed"),
                this.$t("Delete.Therewassomethingwronge"),
                "warning"
              );
            });
        }
      });
    },

    //---- Delete brands by selection

    delete_by_selected() {
      this.$swal({
        title: this.$t("Delete.Title"),
        text: this.$t("Delete.Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete.cancelButtonText"),
        confirmButtonText: this.$t("Delete.confirmButtonText")
      }).then(result => {
        if (result.value) {
          // Start the progress bar.
          NProgress.start();
          NProgress.set(0.1);
          axios
            .post("mappings/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                "Delete Mapping",
                "success"
              );

              Fire.$emit("Delete_Mapping");
            })
            .catch(() => {
              // Complete the animation of theprogress bar.
              setTimeout(() => NProgress.done(), 500);
              this.$swal(
                this.$t("Delete.Failed"),
                this.$t("Delete.Therewassomethingwronge"),
                "warning"
              );
            });
        }
      });
    },

    SelectWarehouse(id){
        if(id){
            this.mapping.warehouse_location_id = '';
            const temp_warehouse_location = this.warehouse_locations.filter( warehouse_location => warehouse_location.warehouse_id === id);
            console.log(temp_warehouse_location);
            if(temp_warehouse_location.length){
                return this.filtered_warehouse_location = temp_warehouse_location;
            }
        }
        return this.filtered_warehouse_location = [];
    },

    LoadSelectWarehouseLocation(id){
        if(id){
            const temp_warehouse_location = this.warehouse_locations.filter( warehouse_location => warehouse_location.warehouse_id === id);
            console.log(temp_warehouse_location);
            if(temp_warehouse_location.length){
                return this.filtered_warehouse_location = temp_warehouse_location;
            }
        }
        return this.filtered_warehouse_location = [];
    }
  }, //end Methods
  created: function() {
    this.Get_Mappings(1);

    Fire.$on("Event_Mapping", () => {
      setTimeout(() => {
        this.Get_Mappings(this.serverParams.page);
        this.$bvModal.hide("New_mapping");
      }, 500);
    });

    Fire.$on("Delete_Mapping", () => {
      setTimeout(() => {
        this.Get_Mappings(this.serverParams.page);
      }, 500);
    });
  }
};
</script>
