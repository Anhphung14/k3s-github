<template>
  <Head :title="tag_detail ? 'Edit Tag' : 'Create Tag'" />
  <Layout>
    <VCard>
      <VCardItem>
        <VCardTitle> {{ tag_detail ? "Edit" : "Create" }} Tag </VCardTitle>
      </VCardItem>
      <VCardText>
        <VForm
          @submit.prevent="
            tag_detail && tag_detail.id
              ? form.post(route('tags.update', tag_detail.id))
              : form.post(route('tags.store'))
          "
        >
          <AppTextField
            class="mt-5"
            v-model="form.name"
            label="Name"
            placeholder="Tag name"
          />
          <div v-if="form.errors.name">{{ form.errors.name }}</div>

          <AppTextField
            class="mt-5"
            v-model="form.slug"
            :error-messages="form.errors.slug"
            label="Slug"
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

          <div class="mt-5">
            <label class="mb-2 d-block">Status</label>
            <AppSelect
              v-model="form.status"
              :items="[
                { value: 'active', title: 'Active' },
                { value: 'inactive', title: 'Inactive' },
              ]"
              placeholder="Select Status"
            />
            <div v-if="form.errors.status">{{ form.errors.status }}</div>
          </div>

          <div class="mt-5">
            <label class="mb-2 d-block">Color</label>
            <VuePickColors
              :model-value="form.color"
              @change="form.color = $event"
            />
          </div>
          <div v-if="form.errors.color">{{ form.errors.color }}</div>

          <VBtn
            class="mt-5"
            type="submit"
            :disabled="form.processing"
            color="primary"
          >
            {{ tag_detail ? "Update" : "Create" }}
          </VBtn>
        </VForm>
      </VCardText>
    </VCard>
  </Layout>
</template>

<script setup>
import AppSelect from "@/@core/components/app-form-elements/AppSelect.vue";
import AppTextField from "@/@core/components/app-form-elements/AppTextField.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";
import VuePickColors from "vue-pick-colors";
import Layout from "../../layouts/blank.vue";

const props = defineProps({
  tag_detail: Object,
});

const form = useForm({
  name: null,
  slug: null,
  description: null,
  status: null,
  color: "#1976d2",
});

onMounted(() => {
  if (props.tag_detail) {
    form.name = props.tag_detail.name;
    form.slug = props.tag_detail.slug;
    form.description = props.tag_detail.description;
    form.status = props.tag_detail.status;
    form.color = props.tag_detail.color ?? "#1976d2";
  }
});
</script>
