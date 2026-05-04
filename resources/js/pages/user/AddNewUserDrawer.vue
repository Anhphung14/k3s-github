<script setup>
import EventBus from "@/pages/events/eventBus";
import avatar1 from '@images/avatars/avatar-1.png';
import axios from "axios";
import { ref } from "vue";
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import { toast } from "vue3-toastify";
import { VForm } from "vuetify/components/VForm";

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  roles: Object
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'userData',
])
const users = ref([]);
const isFormValid = ref(false);
const refForm = ref();
const first_name = ref('');
const last_name = ref('');
const username = ref('');
const email = ref('');
const phone = ref('');
const role = ref();
const password = ref();
const refInputEl = ref();
const isPasswordVisible = ref(false);

const roleItems = computed(() => {
  return props.roles ? Object.values(props.roles).map(role => ({
    title: role.name,
    value: role.id
  })) : [];
});

const accountData = {
  avatarImg: avatar1
}

const accountDataLocal = ref(structuredClone(accountData))

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(async ({ valid }) => {
    if (valid) {
      const formData = new FormData();
      formData.append("first_name", first_name.value);
      formData.append("last_name", last_name.value);
      formData.append("username", username.value);
      formData.append("role", role.value);
      formData.append("phone", phone.value);
      formData.append("email", email.value);
      formData.append("password", password.value);

      if (refInputEl.value.files[0]) {
        formData.append("profile_photo_path", refInputEl.value.files[0]);
      }

      try {
        const response = await axios.post(route("users.store"), formData, {
          headers: {
            "Content-Type": "multipart/form-data"
          }
        });

        // Refresh user list
        await fetchUsers();
        
        // Show success message
        toast.success("user created successfully", {
          theme: "colored",
          type: "success",
        });

        // Emit events
        emit("userData");
        EventBus.emit("userCreated", response.data);

        // Close drawer and reset form
        emit("update:isDrawerOpen", false);
        refForm.value?.reset();
        refForm.value?.resetValidation();
      } catch (error) {
        // Show error message from server if available
        const errorMessage = error.response?.data?.message || "Error creating user. Please try again.";
        toast.error(errorMessage, {
          theme: "colored",
          type: "error",
        });
      }
    }
  });
};

const fetchUsers = async () => {
  try {
    const response = await axios.get(route("users.index"));
    users.value = response.data.data;
  } catch (error) {
    console.error("Error fetching user:", error);
  }
};

onMounted(() => {
  fetchUsers();
});

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

const changeAvatar = file => {
  const fileReader = new FileReader()
  const { files } = file.target
  if (files && files.length) {
    fileReader.readAsDataURL(files[0])
    fileReader.onload = () => {
      if (typeof fileReader.result === 'string')
        accountDataLocal.value.avatarImg = fileReader.result
    }
  }
}

const resetAvatar = () => {
  accountDataLocal.value.avatarImg = accountData.avatarImg
}

</script>

<template>
  <VNavigationDrawer data-allow-mismatch temporary :width="400" location="end" class="scrollable-content"
    :model-value="props.isDrawerOpen" @update:model-value="handleDrawerModelValueUpdate">
    <!-- 👉 Title -->
    <AppDrawerHeaderSection title="Add New User" @cancel="closeNavigationDrawer" />

    <VDivider />
    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12">
                <VCardText class="d-flex p-[0px]">
                  <!-- 👉 Avatar -->
                  <VAvatar rounded size="100" class="me-6" :image="accountDataLocal.avatarImg" />
                  <!-- 👉 Upload Photo -->
                  <div class="d-flex flex-column justify-center gap-4">
                    <div class="d-flex flex-wrap gap-4">
                      <VBtn color="primary" size="small" @click="refInputEl?.click()">
                        <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                        <span class="d-none d-sm-block">Upload new photo</span>
                      </VBtn>

                      <input ref="refInputEl" type="file" name="file" accept=".jpeg,.png,.jpg,GIF" hidden
                        @input="changeAvatar">

                      <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetAvatar">
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
              <!-- 👉 Full name -->
              <VCol cols="12">
                <AppTextField v-model="first_name" :rules="[requiredValidator]" label="First Name" placeholder="John" />
              </VCol>

              <VCol cols="12">
                <AppTextField v-model="last_name" :rules="[requiredValidator]" label="Last Name" placeholder="Doe" />
              </VCol>
              <!-- 👉 Username -->
              <VCol cols="12">
                <AppTextField v-model="username" :rules="[requiredValidator]" label="Username" placeholder="Johndoe" />
              </VCol>

              <!-- 👉 Email -->
              <VCol cols="12">
                <AppTextField v-model="email" :rules="[requiredValidator, emailValidator]" label="Email"
                  placeholder="johndoe@email.com" />
              </VCol>

              <!-- 👉 Password -->
              <VCol cols="12">
                <AppTextField v-model="password" label="Password" :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'" placeholder="Enter Password"
                  :rules="[requiredValidator, passwordValidator]" autocomplete="on"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible" />
              </VCol>

              <!-- 👉 Contact -->
              <VCol cols="12">
                <AppTextField v-model="phone" type="number" :rules="[requiredValidator]" label="Phone"
                  placeholder="+1-541-754-3010" />
              </VCol>

              <!-- 👉 role -->
              <VCol cols="12">
                <AppSelect v-model="role" label="Select Role" placeholder="Select Role" :rules="[requiredValidator]"
                  :items="roleItems" />
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn type="submit" class="me-3">
                  Submit
                </VBtn>
                <VBtn type="reset" variant="tonal" color="error" @click="closeNavigationDrawer">
                  Cancel
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
