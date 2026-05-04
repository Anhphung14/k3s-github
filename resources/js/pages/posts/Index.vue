<template>
  <Head title="Posts" />
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
            placeholder="Search Post"
            style="inline-size: 22rem"
          />
          <AppSelect
            v-model="author"
            :items="authorOptions"
            style="inline-size: 12rem"
            clearable
            placeholder="Filter by author"
          />
          <AppSelect
            v-model="categoryFilter"
            :items="categoryOptions"
            multiple
            chips
            style="inline-size: 12rem"
            clearable
            placeholder="Filter by categories"
          />
          <AppSelect
            v-model="statusFilter"
            :items="statusOptions"
            multiple
            chips
            style="inline-size: 12rem"
            clearable
            placeholder="Filter by status"
          />
          <AppDateTimePicker
            v-model="date"
            style="inline-size: 12rem"
            placeholder="Select date"
          />
          <Link :href="route('posts.create')">
            <VBtn density="default" prepend-icon="tabler-plus"> Add Post </VBtn>
          </Link>
        </div>
      </VCardText>

      <VDivider />

      <VDataTableServer
        :headers="headers"
        :items="postData.data"
        item-value="id"
        v-model:selected="selected"
        show-select
        class="text-no-wrap"
      >
        <template #item.thumbnail="{ item }">
          <img
            v-if="item.thumbnail"
            :src="getThumbnailUrl(item.thumbnail)"
            alt="Thumbnail"
            style="max-width: 100px; max-height: 100px; border-radius: 8px"
          />
          <span v-else>No Image</span>
        </template>
        <template #item.title="{ item }">
          <span>{{ item.title }}</span>
        </template>
        <template #item.categories="{ item }">
          <span>{{ item.categories }}</span>
        </template>
        <template #item.tags="{ item }">
          <span
            v-for="tag in item.tags"
            :key="tag.id"
            :style="{
              background: tag.color || '#eee',
              color: '#fff',
              padding: '4px 12px',
              borderRadius: '12px',
              display: 'inline-block',
              fontWeight: 'bold',
              marginRight: '6px',
            }"
          >
            {{ tag.name }}
          </span>
        </template>
        <template #item.author="{ item }">
          <span>{{ item.author }}</span>
        </template>
        <template #item.status="{ item }">
          <VChip
            :label="false"
            :color="item.status === 'published' ? 'success' : 'error'"
          >
            {{ item.status }}
          </VChip>
        </template>
        <template #item.published_at="{ item }">
          <span>{{
            new Date(item.created_at).toLocaleDateString("vi-VN")
          }}</span>
        </template>
        <template #item.actions="{ item }">
          <Link :href="route('posts.show', item.slug)">
            <VBtn icon size="small" color="medium-emphasis" variant="text">
              <VIcon size="22" icon="tabler-eye" />
            </VBtn>
          </Link>
          <Link :href="route('posts.edit', item.id)">
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
            :total-items="postData.total"
          />
        </template>
      </VDataTableServer>
    </VCard>
  </Layout>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { ref, watch } from "vue";
import Layout from "../../layouts/blank.vue";

const props = defineProps({
  postData: Object,
  filters: Object,
  authors: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [],
  },
  statusOptions: {
    type: Array,
    default: () => [
      { title: "Published", value: "published" },
      { title: "Draft", value: "draft" },
      { title: "Archived", value: "archived" },
    ],
  },
});

const headers = [
  { title: "#", key: "data-table-select", sortable: false },
  { title: "Thumbnail", key: "thumbnail", sortable: false },
  { title: "Title", key: "title", sortable: false },
  { title: "Categories", key: "categories", sortable: false },
  { title: "Tags", key: "tags", sortable: false },
  { title: "Author", key: "author", sortable: false },
  { title: "Published At", key: "published_at", sortable: false },
  { title: "Status", key: "status", sortable: false },
  { title: "Actions", key: "actions", sortable: false },
];

const search = ref(props.filters.search || "");
const author = ref(props.filters.author || "");
const statusFilter = ref(props.filters.status || []);
const categoryFilter = ref(props.filters.categories || []);
const date = ref(props.filters.created_from || null); // single date
const selected = ref([]);

const authorOptions = props.authors.map((user) => ({
  title: user.name,
  value: user.id,
}));

const categoryOptions = props.categories.map((cat) => ({
  title: cat.name,
  value: cat.id,
}));

const statusOptions = props.statusOptions ?? [
  { title: "Published", value: "published" },
  { title: "Draft", value: "draft" },
  { title: "Archived", value: "archived" },
];

const page = ref(props.postData.current_page);
const itemsPerPage = ref(props.postData.per_page);

const fetchData = () => {
  router.get(
    route("posts.index"),
    {
      search: search.value,
      author: author.value,
      categories: categoryFilter.value,
      status: statusFilter.value,
      page: page.value,
      paginate: itemsPerPage.value,
      created_from: date.value,
      created_to: date.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

watch(search, debounce(fetchData, 300));
watch(
  [author, categoryFilter, statusFilter, page, itemsPerPage, date],
  fetchData
);

const getThumbnailUrl = (path) => {
  if (!path) return "";
  if (!/^https?:\/\//.test(path)) {
    return `/storage/${path.replace(/^public\//, "")}`;
  }
  return path;
};
</script>
