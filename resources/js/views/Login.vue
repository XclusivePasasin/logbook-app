<template>
  <div class="login-container" :class="{ 'dark': isDark }">
    <n-card class="login-card">
      <div class="login-header">
        <h2>Logbook App</h2>
        <p>Inicia sesión para continuar</p>
      </div>
      
      <n-form
        ref="formRef"
        :model="formValue"
        :rules="rules"
        size="large"
      >
        <n-form-item path="email" label="Correo Electrónico">
          <n-input v-model:value="formValue.email" placeholder="correo@ejemplo.com" />
        </n-form-item>
        
        <n-form-item path="password" label="Contraseña">
          <n-input
            v-model:value="formValue.password"
            type="password"
            show-password-on="click"
            placeholder="********"
            @keydown.enter.prevent="handleLogin"
          />
        </n-form-item>
        
        <n-button
          type="primary"
          block
          :loading="loading"
          @click="handleLogin"
        >
          Iniciar Sesión
        </n-button>
      </n-form>
    </n-card>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useThemeStore } from '../stores/theme';
import { useMessage } from 'naive-ui';

const router = useRouter();
const authStore = useAuthStore();
const themeStore = useThemeStore();
const message = useMessage();

const isDark = computed(() => themeStore.isDark);

const formRef = ref(null);
const loading = ref(false);

const formValue = ref({
  email: '',
  password: '',
});

const rules = {
  email: [
    { required: true, message: 'Ingresa tu correo electrónico', trigger: 'blur' },
    { type: 'email', message: 'Correo electrónico inválido', trigger: 'blur' },
  ],
  password: [
    { required: true, message: 'Ingresa tu contraseña', trigger: 'blur' },
  ],
};

async function handleLogin() {
  try {
    await formRef.value?.validate();
    loading.value = true;
    
    await authStore.login(formValue.value);
    
    message.success('Bienvenido de nuevo');
    router.push({ name: 'dashboard' });
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.values(error.response.data.errors).flat().forEach(err => {
        message.error(err);
      });
    } else if (error.message) {
      // Form validation error
    } else {
      message.error('Error al iniciar sesión');
    }
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5f5f0 0%, #e8e8e0 100%);
  padding: 20px;
}

.login-container.dark {
  background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
}

.login-card {
  width: 100%;
  max-width: 400px;
}

.login-header {
  text-align: center;
  margin-bottom: 24px;
}

.login-header h2 {
  margin-bottom: 8px;
  color: #18a058;
}

.login-header p {
  color: #666;
}
</style>
