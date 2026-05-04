<script setup>
import { passwordValidator } from "@/@core/utils/validators";
import avatar1 from '@images/avatars/avatar-1.png';
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { toast } from "vue3-toastify";
import Layout from "../../layouts/blank.vue";

const props = defineProps({
  user: Object,
  roles: Object,
  role_current: Object
});

const isFormValid = ref(false);
const refForm = ref();
const isPasswordVisible = ref(false);
const refInputEl = ref()

const accountData = {
  avatarImg: avatar1
}

const form = useForm({
  name: props.user.name,
  username: props.user.username,
  email: props.user.email,
  phone: props.user.phone,
  role: props.role_current ? props.role_current.id : null,
  password: "",
});

const roleItems = computed(() =>
  props.roles
    ? Object.values(props.roles).map((role) => ({
      title: role.name,
      value: role.id,
    }))
    : []
);

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const formData = new FormData();
      formData.append("_method", "PUT");
      formData.append("name", form.name);
      formData.append("username", form.username);
      formData.append("email", form.email);
      formData.append("phone", form.phone);
      formData.append("role", form.role);
      

      if (refInputEl.value && refInputEl.value.files.length > 0) {
        formData.append("profile_photo_path", refInputEl.value.files[0]);
      } 

      router.post(route("users.update", props.user.id), formData, {
        preserveScroll: true,
        onSuccess: () => {
          toast.success("user updated successfully!");
        },
        onError: (errors) => {
          console.error(errors);
          toast.error("Failed to update user");
        },
        forceFormData: true
      });
    }
  });
};

const changeAvatar = file => {
  const fileReader = new FileReader();
  const { files } = file.target;

  if (files && files.length) {
    fileReader.readAsDataURL(files[0]);
    fileReader.onload = () => {
      if (typeof fileReader.result === 'string') {
        selectedAvatar.value = fileReader.result;
        form.profile_photo = files[0]; 
      }
    };
  }
}

const selectedAvatar = ref(props.user.profile_photo_path || accountData.avatarImg);

const resetAvatar = () => {
  selectedAvatar.value = props.user.profile_photo_path || accountData.avatarImg;
  form.profile_photo = null;
}
</script>

<template>

  <Head title="Edit User" />
  <Layout>
    <VCard>
      <VCardItem>
        <VCardTitle>Edit User</VCardTitle>
      </VCardItem>
      <VCardText>
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit" enctype="multipart/form-data">
          <VRow>
            <VCol cols="12">
              <VCardText class="d-flex p-[0px]">
                <!-- 👉 Avatar -->
                <VAvatar rounded size="100" class="me-6" :image="selectedAvatar" />
                <!-- 👉 Upload Photo -->
                <div class="d-flex flex-column justify-center gap-4">
                  <div class="d-flex flex-wrap gap-4">
                    <VBtn color="primary" size="small" @click="refInputEl?.click()">
                      <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                      <span class="d-none d-sm-block">Upload new photo</span>
                    </VBtn>

                    <input ref="refInputEl" type="file" name="profile_photo" accept=".jpeg,.png,.jpg,GIF" hidden
                      @input="changeAvatar">

                    <VBtn size="small" color="secondary" variant="tonal" @click="resetAvatar">
                      <span class="d-none d-sm-block">Reset</span>
                      <VIcon icon="tabler-refresh" class="d-sm-none" />
                    </VBtn>
                  </div>

                  <p class="text-body-1 mb-0">
                    Allowed JPG, GIF or PNG. Max size of 800K
                  </p>
                </div>
              </VCardText>
            </VCol>
            <VCol cols="12">
              <AppTextField v-model="form.name" :rules="[requiredValidator]" label="Full Name" placeholder="John Doe" />
            </VCol>
            <VCol cols="12">
              <AppTextField v-model="form.username" :rules="[requiredValidator]" label="Username"
                placeholder="Johndoe" />
            </VCol>
            <VCol cols="12">
              <AppTextField v-model="form.email" :rules="[requiredValidator, emailValidator]" label="Email"
                placeholder="johndoe@email.com" />
            </VCol>
            <VCol cols="12">
              <AppTextField v-model="form.password" label="New Password" :type="isPasswordVisible ? 'text' : 'password'"
                :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'" placeholder="Enter Password"
                :rules="form.password ? [passwordValidator] : []" @click:append-inner="isPasswordVisible = !isPasswordVisible" />
              <span class="text-caption text-muted">Leave empty if you don't want to change the password</span>
            </VCol>
            <VCol cols="12">
              <AppTextField v-model="form.phone" type="number" :rules="[requiredValidator]" label="Phone"
                placeholder="+1-541-754-3010" />
            </VCol>
            <VCol cols="12">
              <AppSelect v-model="form.role" label="Select Role" placeholder="Select Role" :rules="[requiredValidator]"
                :items="roleItems" />
            </VCol>
            <VCol cols="12">
              <VBtn type="submit" class="me-3">
                Save Changes
              </VBtn>
              <VBtn type="reset" variant="tonal" color="error" @click="$inertia.visit(route('users.index'))">
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </Layout>
</template>
