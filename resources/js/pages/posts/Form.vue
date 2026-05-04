<template>
  <Head :title="post_detail ? 'Edit Post' : 'Create Post'" />
  <Layout>
    <VCard>
      <VCardItem>
        <VCardTitle> {{ post_detail ? "Edit" : "Create" }} Post </VCardTitle>
      </VCardItem>
      <VCardText>
        <VForm
          @submit.prevent="
            post_detail && post_detail.id
              ? form.post(route('posts.update', post_detail.id))
              : form.post(route('posts.store'))
          "
        >
          <div class="mt-5">
            <label>Image</label>
            <VFileInput
              v-model="form.image"
              :rules="rules"
              label="Thumbnail"
              accept="image/png, image/jpeg, image/bmp"
              placeholder="Pick a thumbnail"
              prepend-icon="tabler-camera"
              multiple
            />
          </div>
          <div v-if="form.errors.image">{{ form.errors.image }}</div>
          <div v-if="imagePreview.length" class="mt-3">
            <img
              v-for="(src, idx) in imagePreview"
              :key="idx"
              :src="src"
              alt="Thumbnail Preview"
              style="max-width: 200px; max-height: 200px; margin-right: 8px"
            />
          </div>

          <AppTextField
            class="mt-5"
            v-model="form.title"
            label="Title"
            placeholder="Title"
          />
          <div v-if="form.errors.title">{{ form.errors.title }}</div>

          <AppTextField
            class="mt-5"
            v-model="form.slug"
            :error-messages="form.errors.slug"
            label="Slug*"
            type="text"
            placeholder="Slug"
          />
          <div v-if="form.errors.slug">{{ form.errors.slug }}</div>

          <AppSelect
            class="mt-5"
            v-model="form.categories"
            :error-messages="form.errors.categories"
            label="Categories*"
            :items="processCategories(props.categories)"
            multiple
            chips
            placeholder="Select categories"
          />
          <div v-if="form.errors.categories">{{ form.errors.categories }}</div>

          <AppSelect
            class="mt-5"
            v-model="form.tags"
            :error-messages="form.errors.tags"
            label="Tags"
            :items="processTags(props.tags)"
            multiple
            chips
            placeholder="Select tags"
          />
          <div v-if="form.errors.tags">{{ form.errors.tags }}</div>

          <AppTextField
            class="mt-5"
            v-model="form.author"
            label="Author"
            placeholder="Author"
            :readonly="true"
          />

          <div class="mt-5">
            <label class="mb-2 d-block">Content</label>
            <QuillEditor
              v-model:content="form.content"
              contentType="html"
              :options="quillOptions"
            />
            <div v-if="form.errors.content">
              {{ form.errors.content }}
            </div>
          </div>

          <div class="mt-5">
            <label class="mb-2 d-block">Status</label>
            <AppSelect
              v-model="form.status"
              :items="[
                { value: 'draft', title: 'Draft' },
                { value: 'published', title: 'Published' },
              ]"
              placeholder="Select Status"
            />
            <div v-if="form.errors.status">{{ form.errors.status }}</div>
          </div>

          <VBtn
            class="mt-5"
            type="submit"
            :disabled="form.processing"
            color="primary"
          >
            {{ post_detail ? "Update" : "Create" }}
          </VBtn>
        </VForm>
      </VCardText>
    </VCard>
  </Layout>
</template>

<script setup>
// filepath: f:\corsivalab\csl_laravel_starter_kit\resources\js\pages\posts\Form.vue
import AppSelect from "@/@core/components/app-form-elements/AppSelect.vue";
import AppTextField from "@/@core/components/app-form-elements/AppTextField.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";
import { onMounted, ref, watch } from "vue";
import Layout from "../../layouts/blank.vue";

const props = defineProps({
  post_detail: Object,
  categories: {
    type: Array,
    default: () => [],
  },
  author: {
    type: String,
    default: "",
  },
  tags: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  title: null,
  slug: null,
  description: null,
  content: null,
  status: null,
  categories: [],
  author: null,
  image: null,
  tags: [],
});

const rules = [
  (fileList) =>
    !fileList ||
    !fileList.length ||
    fileList[0].size < 1000000 ||
    "Thumbnail size should be less than 1 MB!",
];

const processCategories = (categories = []) => {
  return categories.map((category) => ({
    title: category.name,
    value: category.id,
  }));
};

const processTags = (tags = []) => {
  return tags.map((tag) => ({
    title: tag.name,
    value: tag.id,
  }));
};

const quillOptions = {
  theme: "snow",
  modules: {
    toolbar: [
      [{ font: [] }, { size: [] }],
      ["bold", "italic", "underline", "strike"],
      [{ color: [] }, { background: [] }],
      [{ script: "sub" }, { script: "super" }],
      [{ header: [1, 2, 3, 4, 5, 6, false] }],
      [
        { list: "ordered" },
        { list: "bullet" },
        { indent: "-1" },
        { indent: "+1" },
      ],
      [{ align: [] }],
      ["blockquote", "code-block"],
      ["link", "image", "video"],
      ["clean"],
    ],
    clipboard: {
      matchVisual: false,
    },
    history: {
      delay: 1000,
      maxStack: 100,
      userOnly: true,
    },
  },
};

onMounted(() => {
  if (props.post_detail) {
    form.title = props.post_detail.title;
    form.slug = props.post_detail.slug;
    form.description = props.post_detail.description;
    form.content = props.post_detail.content;
    form.status = props.post_detail.status;
    form.categories = props.post_detail.categories
      ? props.post_detail.categories.map((c) => c.id)
      : [];
    form.tags = props.post_detail.tags
      ? props.post_detail.tags.map((t) => t.id)
      : [];
    form.author = props.author;
  } else {
    form.author = props.author;
  }
});

const imagePreview = ref([]);

watch(
  () => form.image,
  (files) => {
    imagePreview.value = [];
    if (!files || files.length === 0) return;
    // Chỉ lấy file đầu tiên
    const file = Array.isArray(files) ? files[0] : files;
    if (typeof file === "string") {
      imagePreview.value.push(file);
    } else if (file instanceof File) {
      const reader = new FileReader();
      reader.onloadend = () => {
        imagePreview.value = [reader.result];
      };
      reader.readAsDataURL(file);
    }
  }
);
</script>
