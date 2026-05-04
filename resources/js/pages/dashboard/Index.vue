<template>
  <Head title="Home" />
  <Layout>
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row">
        <!-- Welcome Message -->
        <div class="col-xl-12 mb-4 col-lg-5 col-12">
          <div class="card">
            <div class="d-flex align-items-center row">
              <div class="col-7">
                <div class="card-body text-nowrap">
                  <h5 class="card-title">Welcome! 🎉</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import Layout from "../../layouts/blank.vue";
import { Link, Head } from "@inertiajs/vue3";
</script>
