<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "../../layouts/blank.vue";
import UserBioPanel from '@/pages/user/UserBioPanel.vue';

const props = defineProps({
  user: Object
})
</script>

<template>
  <Head title="Detail User" />
  <Layout>
    <VRow v-if="props.user">
      <VCol cols="12">
        <UserBioPanel :user-data="props.user" />
      </VCol>
    </VRow>
  </Layout>
</template>
