<template>
  <Head :title="category_detail ? 'Edit Category' : 'Create Category'" />
  <Layout>
    <VCard>
      <VCardItem>
        <VCardTitle>
          {{ category_detail ? "Edit" : "Create" }} Category
        </VCardTitle>
      </VCardItem>
      <VCardText>
        <VForm
          @submit.prevent="
            category_detail && category_detail.id
              ? form.post(route('categories.update', category_detail.id))
              : form.post(route('categories.store'))
          "
        >
          <AppTextField v-model="form.name" label="Name" placeholder="Name" />
          <div v-if="form.errors.name">{{ form.errors.name }}</div>

          <AppTextField
            class="mt-5"
            v-model="form.slug"
            :error-messages="form.errors.slug"
            label="Slug*"
            type="text"
            placeholder="Slug"
          />
          <div v-if="form.errors.slug">{{ form.errors.slug }}</div>

          <AppTextField
            class="mt-5"
            v-model="form.description"
            label="Description"
            placeholder="Description"
          />
          <div v-if="form.errors.description">
            {{ form.errors.description }}
          </div>

          <VBtn
            class="mt-5"
            type="submit"
            :disabled="form.processing"
            color="primary"
          >
            {{ category_detail ? "Update" : "Create" }}
          </VBtn>
        </VForm>
      </VCardText>
    </VCard>
  </Layout>
</template>

<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";
import Layout from "../../layouts/blank.vue";

const props = defineProps({
  category_detail: Object,
});

const form = useForm({
  name: null,
  slug: null,
  description: null,
});

onMounted(() => {
  if (props.category_detail) {
    form.name = props.category_detail.name;
    form.slug = props.category_detail.slug;
    form.description = props.category_detail.description;
  }
});
</script>
