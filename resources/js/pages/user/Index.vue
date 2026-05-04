<template>

  <Head title="Users" />
  <Layout>
    <section>
      <!-- 👉 Widgets -->
      <div class="d-flex mb-6">
        <VRow>
          <template v-for="(data, id) in widgetData" :key="id">
            <VCol cols="12" md="3" sm="6">
              <VCard>
                <VCardText>
                  <div class="d-flex justify-space-between">
                    <div class="d-flex flex-column gap-y-1">
                      <div class="text-body-1 text-high-emphasis">
                        {{ data.title }}
                      </div>
                      <div class="d-flex gap-x-2 align-center">
                        <h4 class="text-h4">
                          {{ data.value }}
                        </h4>
                        <div class="text-base" :class="data.change > 0 ? 'text-success' : 'text-error'"
                          v-if="data.change">
                          ({{ prefixWithPlus(data.change) }}%)
                        </div>
                      </div>
                      <div class="text-sm">
                        {{ data.desc }}
                      </div>
                    </div>
                    <VAvatar :color="data.iconColor" variant="tonal" rounded size="42">
                      <VIcon :icon="data.icon" size="26" />
                    </VAvatar>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
          </template>
        </VRow>
      </div>
      <VCard class="mb-6">
        <VCardText class="d-flex flex-wrap gap-4">
          <div class="me-3 d-flex gap-3">
            <AppSelect :model-value="itemsPerPage" :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
              { value: -1, title: 'All' },
            ]" style="inline-size: 6.25rem;" @update:model-value="itemsPerPage = parseInt($event, 10)" />
          </div>
          <VSpacer />

          <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
            <!-- 👉 Search  -->
            <div style="inline-size: 15.625rem;">
              <AppTextField v-model="search" placeholder="Search User" />
            </div>
            <!-- 👉 Add user button -->
            <VBtn prepend-icon="tabler-plus" @click="isAddNewUserDrawerVisible = true">
              Add New User
            </VBtn>
          </div>
        </VCardText>
        <!-- SECTION datatable -->
        <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items-length="userData.total"
        :items-per-page-options="[
          { value: 5, title: '5' },
          { value: 10, title: '10' },
          { value: -1, title: '$vuetify.dataFooter.itemsPerPageAll' }
        ]"
        :headers="headers"
        :items="userData.data"
        item-value="id"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
          <!-- user -->
          <template #item.user="{ item }">
            <div class="d-flex align-center gap-x-4">
              <VAvatar size="34" :variant="!item.avatar ? 'tonal' : undefined"
                :color="!item.avatar ? resolveUserRoleVariant(item.role).color : undefined">
                <VImg v-if="item.avatar" :src="item.avatar" />
                <span v-else>{{ avatarText(item.fullName) }}</span>
              </VAvatar>
              <div class="d-flex flex-column">
                <h6 class="text-base">
                  <RouterLink :to="{ name: 'apps-user-view-id', params: { id: item.id } }"
                    class="font-weight-medium text-link">
                    {{ item.fullName }}
                  </RouterLink>
                </h6>
                <div class="text-sm">
                  {{ item.email }}
                </div>
              </div>
            </div>
          </template>

          <!-- Status -->
          <template #item.status="{ item }">
            <VChip :color="resolveUserStatusVariant(item.status)" size="small" label class="text-capitalize">
              {{ item.status }}
            </VChip>
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            
            <IconBtn @click="detailUser(item.id)">
              <VIcon icon="tabler-eye" />
            </IconBtn>

            <IconBtn @click="editlUser(item.id)">
              <VIcon icon="tabler-pencil" />
            </IconBtn>

            <IconBtn @click="deleteUser(item.id)">
              <VIcon icon="tabler-trash" />
            </IconBtn>

          </template>

          <!-- pagination -->
          <template #bottom>
            <TablePagination v-model:page="page" :items-per-page="itemsPerPage" :total-items="userData.total" />
          </template>
        </VDataTableServer>
        <!-- SECTION -->
      </VCard>
      <AddNewUserDrawer v-model:is-drawer-open="isAddNewUserDrawerVisible" @user-data="addNewUser"
        @userData="fetchData" :roles="roles" />
    </section>
  </Layout>
</template>
<script setup>
import { Head, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import Swal from "sweetalert2";
import { toast } from "vue3-toastify";
import Layout from "../../layouts/blank.vue";
import AddNewUserDrawer from '@/pages/user/AddNewUserDrawer.vue';

const users = ref([]);
const isAddNewUserDrawerVisible = ref(false);

const props = defineProps({
  userData: Object,
  filters: Object,
  roles: Object,
  qty_user: Number
})

const headers = [
  { title: "ID", key: "id"},
  { title: "Name", key: "name"},
  { title: "Email", key: "email"},
  { title: "Actions", key: "actions", sortable: false},
];

const search = ref(props.filters.search || "");
const page = ref(props.userData.current_page);
const itemsPerPage = ref(props.userData.per_page);

const fetchData = async () => {
 router.get(
    route("users.index"),
    {
      search: search.value,
      page: page.value,
      paginate: itemsPerPage.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

watch(search, debounce(fetchData, 300));
watch([page, itemsPerPage], fetchData);

const widgetData = ref([
  {
    title: 'Session',
    value: props.qty_user,
    desc: 'Total Users',
    icon: 'tabler-users',
    iconColor: 'primary',
  }
]);

const updateOptions = (options) => {
  page.value = options.page;
  itemsPerPage.value = options.itemsPerPage;
};

const deleteUser = async (id) => {
  const result = await Swal.fire({
    title: "Are you sure? <br> <i class='fa-solid fa-trash-can'></i>",
    text: "This action cannot be undone! The data will be permanently deleted!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#ea5455",
    cancelButtonColor: "#6CC9CF",
    confirmButtonText: "Yes, Proceed!",
    cancelButtonText: "Cancel",
  });

  if (result.isConfirmed) {
    router.delete(route("users.destroy", id), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("user deleted successfully", {
          theme: "colored",
          type: "success",
        });
        fetchData();
        widgetData.value[0].value = widgetData.value[0].value - 1;
      },
      onError: (errors) => {
        toast.error("An error occurred while deleting the user.", {
          theme: "colored",
          type: "error",
        });
      }
    });
  }
};

const detailUser = async (id) => {
  router.visit(route('users.show', id));
};

const editlUser = async (id) => {
  router.visit(route('users.edit', id));
};

const addNewUser = () => {
  fetchData();
  widgetData.value[0].value = widgetData.value[0].value + 1;
};
</script>
