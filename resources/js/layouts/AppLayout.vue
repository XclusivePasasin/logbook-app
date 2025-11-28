<template>
  <n-layout has-sider position="absolute">
    <n-layout-sider
      bordered
      collapse-mode="width"
      :collapsed-width="64"
      :width="240"
      :collapsed="collapsed"
      show-trigger
      @collapse="collapsed = true"
      @expand="collapsed = false"
    >
      <div class="logo">
        <h2>Trip Logbook</h2>
      </div>
      <n-menu
        :collapsed="collapsed"
        :collapsed-width="64"
        :collapsed-icon-size="22"
        :options="menuOptions"
        :value="activeKey"
        @update:value="handleUpdateValue"
      />
    </n-layout-sider>
    <n-layout>
      <n-layout-header bordered>
        <div class="header-content">
          <n-space justify="end" align="center">
            <n-dropdown :options="userOptions" @select="handleUserSelect">
              <n-button text>
                {{ user?.name || 'User' }}
              </n-button>
            </n-dropdown>
          </n-space>
        </div>
      </n-layout-header>
      <n-layout-content content-style="padding: 24px;">
        <router-view></router-view>
      </n-layout-content>
    </n-layout>
  </n-layout>
</template>

<script setup>
import { h, ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { NIcon } from 'naive-ui';
import {
  HomeOutline as DashboardIcon,
  CarOutline as TripIcon,
  PersonOutline as DriverIcon,
  LogOutOutline as LogoutIcon
} from '@vicons/ionicons5';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const collapsed = ref(false);
const user = computed(() => authStore.user);

const activeKey = computed(() => route.name);

function renderIcon(icon) {
  return () => h(NIcon, null, { default: () => h(icon) });
}

const menuOptions = [
  {
    label: 'Dashboard',
    key: 'dashboard',
    icon: renderIcon(DashboardIcon)
  },
  {
    label: 'Viajes',
    key: 'trips',
    icon: renderIcon(TripIcon)
  },
  {
    label: 'Conductores',
    key: 'drivers',
    icon: renderIcon(DriverIcon)
  }
];

const userOptions = [
  {
    label: 'Cerrar Sesión',
    key: 'logout',
    icon: renderIcon(LogoutIcon)
  }
];

function handleUpdateValue(key) {
  router.push({ name: key });
}

async function handleUserSelect(key) {
  if (key === 'logout') {
    await authStore.logout();
    router.push({ name: 'login' });
  }
}
</script>

<style scoped>
.logo {
  height: 64px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.09);
}

.logo h2 {
  margin: 0;
  font-size: 18px;
  font-weight: bold;
  white-space: nowrap;
  overflow: hidden;
}

.header-content {
  height: 64px;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
}
</style>
