<template>
  <n-layout has-sider style="height: 100vh">
    <!-- Mobile Drawer -->
    <n-drawer v-model:show="mobileMenuOpen" :width="240" placement="left">
      <n-drawer-content title="Logbook" :native-scrollbar="false">
        <n-menu
          :options="menuOptions"
          :value="activeKey"
          @update:value="handleMobileMenuSelect"
        />
      </n-drawer-content>
    </n-drawer>

    <!-- Desktop Sidebar -->
    <n-layout-sider
      v-if="!isMobile"
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
        <h2 v-if="!collapsed">Logbook</h2>
        <h2 v-else>LB</h2>
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
      <n-layout-header bordered class="header">
        <div class="header-content">
          <div class="header-left">
            <n-button v-if="isMobile" text @click="mobileMenuOpen = true" style="margin-right: 12px">
              <template #icon>
                <n-icon size="24">
                  <menu-icon />
                </n-icon>
              </template>
            </n-button>
            <h3 v-if="isMobile" style="margin: 0;">Logbook</h3>
          </div>
          <div class="user-info">
            <n-button circle quaternary @click="toggleTheme" style="margin-right: 12px">
              <template #icon>
                <n-icon>
                  <moon-icon v-if="isDark" />
                  <sunny-icon v-else />
                </n-icon>
              </template>
            </n-button>
            <n-dropdown :options="userOptions" @select="handleUserSelect">
              <n-button text>
                {{ isMobile ? '' : authStore.user?.name || 'Usuario' }}
                <template #icon v-if="isMobile">
                  <n-icon>
                    <person-circle-icon />
                  </n-icon>
                </template>
              </n-button>
            </n-dropdown>
          </div>
        </div>
      </n-layout-header>
      <n-layout-content :content-style="isMobile ? 'padding: 12px;' : 'padding: 24px;'">
        <router-view />
      </n-layout-content>
    </n-layout>
  </n-layout>
</template>

<script setup>
import { ref, computed, h } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { NIcon } from 'naive-ui';
import {
  HomeOutline as DashboardIcon,
  CarOutline as CarIcon,
  PersonOutline as PersonIcon,
  PeopleOutline as PeopleIcon,
  DocumentTextOutline as ReportIcon,
  LogOutOutline as LogoutIcon,
  MoonOutline as MoonIcon,
  SunnyOutline as SunnyIcon,
  MenuOutline as MenuIcon,
  PersonCircleOutline as PersonCircleIcon,
} from '@vicons/ionicons5';
import { useThemeStore } from '../stores/theme';
import { useBreakpoints, breakpointsTailwind } from '@vueuse/core';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const themeStore = useThemeStore();
const breakpoints = useBreakpoints(breakpointsTailwind);

const collapsed = ref(false);
const mobileMenuOpen = ref(false);
const isMobile = breakpoints.smaller('md');

const isDark = computed(() => themeStore.isDark);
const toggleTheme = themeStore.toggleTheme;

const activeKey = computed(() => route.name);

function renderIcon(icon) {
  return () => h(NIcon, null, { default: () => h(icon) });
}

const menuOptions = computed(() => {
  const options = [
    {
      label: 'Dashboard',
      key: 'dashboard',
      icon: renderIcon(DashboardIcon),
    },
    {
      label: 'Bitácora',
      key: 'trips.index',
      icon: renderIcon(CarIcon),
    },
    {
      label: 'Conductores',
      key: 'drivers.index',
      icon: renderIcon(PersonIcon),
    },
    {
      label: 'Reportes',
      key: 'reports',
      icon: renderIcon(ReportIcon),
    },
  ];

  if (authStore.isAdmin) {
    options.push({
      label: 'Usuarios',
      key: 'users.index',
      icon: renderIcon(PeopleIcon),
    });
  }

  return options;
});

const userOptions = [
  {
    label: 'Cerrar Sesión',
    key: 'logout',
    icon: renderIcon(LogoutIcon),
  },
];

function handleUpdateValue(key) {
  router.push({ name: key });
}

function handleMobileMenuSelect(key) {
  mobileMenuOpen.value = false;
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
  border-bottom: 1px solid #efeff5;
}

.header {
  height: 64px;
  padding: 0 16px;
  display: flex;
  align-items: center;
}

.header-content {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
}
</style>
