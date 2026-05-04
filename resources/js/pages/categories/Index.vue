<template>
  <Head title="Categories" />
  <Layout>
    <VCard>
      <VCardText
        class="d-flex align-center justify-space-between flex-wrap gap-4"
      >
        <div class="d-flex gap-2 align-center">
          <p class="text-body-1 mb-0">Show</p>
          <AppSelect
            :model-value="itemsPerPage"
            :items="[
              { value: 5, title: '5' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
              { value: -1, title: 'All' },
            ]"
            style="inline-size: 5.5rem"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>

        <div class="d-flex align-center gap-4 flex-wrap">
          <AppTextField
            v-model="search"
            placeholder="Search Category"
            style="inline-size: 15.625rem"
          />
          <Link :href="route('categories.create')">
            <VBtn density="default" prepend-icon="tabler-plus">
              Add Category
            </VBtn>
          </Link>
          <div v-if="selected.length > 0" class="d-flex gap-2 align-center">
            <VBtn
              icon
              color="error"
              size="small"
              @click="bulkDestroy"
              title="Delete selected"
            >
              <VIcon size="22" icon="tabler-trash" />
            </VBtn>
            <VBtn
              icon
              color="warning"
              size="small"
              @click="bulkUpdateStatus('draft')"
              title="Set draft"
            >
              <VIcon size="22" icon="tabler-file-pencil" />
            </VBtn>
            <VBtn
              icon
              color="success"
              size="small"
              @click="bulkUpdateStatus('published')"
              title="Set published"
            >
              <VIcon size="22" icon="tabler-check" />
            </VBtn>
          </div>
        </div>
      </VCardText>

      <VDivider />

      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        v-model:selected="selected"
        show-select
        :items-length="categoryData.total"
        :headers="headers"
        :items="categoryData.data"
        item-value="id"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <template #item.description="{ item }">
          <span>{{ item.description }}</span>
        </template>
        <template #item.actions="{ item }">
          <Link :href="route('categories.edit', item.id)">
            <VBtn icon size="small" color="medium-emphasis" variant="text">
              <VIcon size="22" icon="tabler-edit" />
            </VBtn>
          </Link>
          <VBtn
            icon
            size="small"
            color="medium-emphasis"
            variant="text"
            @click="destroy(item.id)"
          >
            <VIcon size="22" icon="tabler-trash" />
          </VBtn>
        </template>

        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="categoryData.total"
          />
        </template>
      </VDataTableServer>
    </VCard>
  </Layout>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import Swal from "sweetalert2";
import { ref, watch } from "vue";
import Layout from "../../layouts/blank.vue";

const props = defineProps({
  categoryData: Object,
  filters: Object,
});

const headers = [
  { title: "#", key: "data-table-select", sortable: false },
  { title: "NAME", key: "name", sortable: false },
  { title: "Description", key: "description", sortable: false },
  { title: "Actions", key: "actions", sortable: false },
];

const search = ref(props.filters.search || "");
const page = ref(props.categoryData.current_page);
const itemsPerPage = ref(props.categoryData.per_page);
const selected = ref([]);

const fetchData = () => {
  router.get(
    route("categories.index"),
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

const updateOptions = (options) => {
  page.value = options.page;
  itemsPerPage.value = options.itemsPerPage;
};

const destroy = (id) => {
  Swal.fire({
    title: "Are you sure?",
    text: "You won't be able to revert this",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#ea5455",
    cancelButtonColor: "#6CC9CF",
    confirmButtonText: "Yes, Proceed!",
    cancelButtonText: "Cancel",
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route("categories.destroy", { id }), {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire("Deleted!", "The category has been deleted.", "success");
        },
      });
    }
  });
};

// Delete all categories đang hiển thị
const destroyAll = () => {
  Swal.fire({
    title: "Delete all categories?",
    text: "This action cannot be undone!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#ea5455",
    cancelButtonColor: "#6CC9CF",
    confirmButtonText: "Yes, Delete All!",
    cancelButtonText: "Cancel",
  }).then((result) => {
    if (result.isConfirmed) {
      // Lấy danh sách ID của tất cả category hiển thị
      const ids = props.categoryData.data.map((item) => item.id);

      // Gọi API backend (cần có route hỗ trợ xoá nhiều)
      router.delete(route("categories.destroyAll"), {
        data: { ids }, // gửi mảng ID
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire("Deleted!", "All categories have been deleted.", "success");
        },
      });
    }
  });
};

function bulkDestroy() {
  if (selected.value.length === 0) return;
  Swal.fire({
    title: "Delete selected categories?",
    text: "You won't be able to revert this.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#ea5455",
    cancelButtonColor: "#6CC9CF",
    confirmButtonText: "Yes, delete!",
    cancelButtonText: "Cancel",
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(
        route("categories.bulkDestroy"),
        {
          ids: selected.value,
        },
        {
          preserveScroll: true,
          onSuccess: () => {
            selected.value = [];
            fetchData();
            Swal.fire({
              title: "Deleted!",
              text: "Selected categories deleted.",
              icon: "success",
              confirmButtonColor: "#34c38f",
            });
          },
        }
      );
    }
  });
}

function bulkUpdateStatus(status) {
  if (selected.value.length === 0) return;
  router.post(
    route("categories.bulkUpdateStatus"),
    {
      ids: selected.value,
      status,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        selected.value = [];
        fetchData();
        Swal.fire({
          title: "Success!",
          text: `Status updated to "${status}" for selected categories.`,
          icon: "success",
          confirmButtonColor: "#34c38f",
        });
      },
    }
  );
}
</script>
