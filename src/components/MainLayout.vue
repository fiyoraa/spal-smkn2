<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { 
  LayoutDashboard, 
  Package, 
  CalendarClock, 
  ShieldCheck, 
  Undo2, 
  History, 
  FileText, 
  UserX,
  Search,
  Bell,
  ChevronRight,
  LogOut,
  Settings,
  User,
  ArrowUpRight,
  X,
  Camera
} from '@lucide/vue';

// Navigation items for the Floating Navbar (Role-based)
const navItems = computed(() => {
  const role = props.user?.role || 'Siswa';
  if (role === 'Laboran') {
    return [
      { name: 'Dashboard', label: 'Dashboard', icon: LayoutDashboard },
      { name: 'Data Alat', label: 'Data Alat', icon: Package },
      { name: 'Approval', label: 'Approval', icon: ShieldCheck },
      { name: 'Pengembalian', label: 'Pengembalian', icon: Undo2 },
      { name: 'Riwayat', label: 'Riwayat', icon: History },
    ];
  } else {
    return [
      { name: 'Dashboard', label: 'Dashboard', icon: LayoutDashboard },
      { name: 'Peminjaman', label: 'Katalog', icon: Package },
      { name: 'Riwayat', label: 'Riwayat', icon: History },
    ];
  }
});

// All menu items for the Command Palette (Spotlight Search)
const menuItems = [
  { name: 'Dashboard', displayName: 'Dashboard', icon: LayoutDashboard },
  { name: 'Data Alat', displayName: 'Data Alat', icon: Package },
  { name: 'Peminjaman', displayName: 'Katalog (Peminjaman)', icon: CalendarClock },
  { name: 'Approval', displayName: 'Approval', icon: ShieldCheck },
  { name: 'Pengembalian', displayName: 'Pengembalian', icon: Undo2 },
  { name: 'Riwayat', displayName: 'Riwayat', icon: History },
  { name: 'Laporan', displayName: 'Laporan Statistik', icon: FileText },
  { name: 'Blacklist', displayName: 'Blacklist', icon: UserX },
];


const vFocus = {
  mounted: (el) => el.focus()
};

const props = defineProps({
  activeMenu: {
    type: String,
    default: 'Dashboard'
  },
  user: {
    type: Object,
    default: () => ({ name: 'Admin Laboran', email: 'laboran@school.id', role: 'Laboran' })
  }
});

const avatarUrl = computed(() => {
  if (props.user?.avatar) {
    return props.user.avatar;
  }
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(props.user?.name || 'User')}&background=46708F&color=ffffff&bold=true`;
});

const emit = defineEmits(['update:activeMenu', 'logout', 'update-user']);

const showProfileModal = ref(false);
const showSettingsModal = ref(false);

const profileName = ref('');
const profileEmail = ref('');
const profileAvatarPreview = ref('');
const fileInput = ref(null);

watch(() => props.user, (newVal) => {
  if (newVal) {
    profileName.value = newVal.name || '';
    profileEmail.value = newVal.email || '';
    profileAvatarPreview.value = newVal.avatar || '';
  }
}, { immediate: true, deep: true });

const openProfileModal = () => {
  profileName.value = props.user?.name || '';
  profileEmail.value = props.user?.email || '';
  profileAvatarPreview.value = props.user?.avatar || '';
  showProfileModal.value = true;
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleAvatarChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  
  if (file.size > 2 * 1024 * 1024) {
    alert('Ukuran file maksimal adalah 2MB.');
    return;
  }
  
  const reader = new FileReader();
  reader.onload = (event) => {
    profileAvatarPreview.value = event.target.result;
  };
  reader.readAsDataURL(file);
};

const computedAvatarPreview = computed(() => {
  if (profileAvatarPreview.value) {
    return profileAvatarPreview.value;
  }
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(profileName.value || 'User')}&background=46708F&color=ffffff&bold=true`;
});

const saveProfile = () => {
  if (!profileName.value.trim() || !profileEmail.value.trim()) return;
  const storedUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
  storedUser.name = profileName.value;
  storedUser.email = profileEmail.value;
  storedUser.avatar = profileAvatarPreview.value;
  localStorage.setItem('currentUser', JSON.stringify(storedUser));
  
  emit('update-user', { 
    ...props.user, 
    name: profileName.value, 
    email: profileEmail.value,
    avatar: profileAvatarPreview.value 
  });
  showProfileModal.value = false;
};

// Local settings state
const soundNotifications = ref(localStorage.getItem('sys_sound_notif') !== 'false');
const autoRefresh = ref(localStorage.getItem('sys_auto_refresh') !== 'false');
const fineRate = ref(parseInt(localStorage.getItem('sys_fine_rate') || '5000', 10));

const saveSettings = () => {
  localStorage.setItem('sys_sound_notif', soundNotifications.value ? 'true' : 'false');
  localStorage.setItem('sys_auto_refresh', autoRefresh.value ? 'true' : 'false');
  localStorage.setItem('sys_fine_rate', fineRate.value.toString());
  showSettingsModal.value = false;
};

// Dropdown states
const showNotifications = ref(false);
const showProfileDropdown = ref(false);

// Command Palette states
const showCommandPalette = ref(false);
const commandSearchQuery = ref('');
const selectedIndex = ref(0);

// Search Query (Header search)
const searchQuery = ref('');

// Unread notifications count
const showAllNotificationsModal = ref(false);

const initialNotifications = [
  { id: 1, text: 'Peminjaman Mikroskop oleh Budi Santoso menunggu Approval.', time: '5 menit lalu', type: 'pending', read: false },
  { id: 2, text: 'Alat Solder Listrik A-12 terlambat dikembalikan.', time: '2 jam lalu', type: 'warning', read: false },
  { id: 3, text: 'Laporan bulanan Laboratorium Fisika siap diunduh.', time: '1 hari lalu', type: 'info', read: false }
];

const storedNotifs = localStorage.getItem('sys_notifications');
const notifications = ref(storedNotifs ? JSON.parse(storedNotifs) : initialNotifications);

const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read).length;
});

const selectMenu = (menuName) => {
  emit('update:activeMenu', menuName);
  closeDropdowns();
  showCommandPalette.value = false;
};

const closeDropdowns = () => {
  showNotifications.value = false;
  showProfileDropdown.value = false;
};

const clearNotifications = () => {
  notifications.value.forEach(n => n.read = true);
  localStorage.setItem('sys_notifications', JSON.stringify(notifications.value));
};

const markAsRead = (id) => {
  const notif = notifications.value.find(n => n.id === id);
  if (notif) {
    notif.read = true;
    localStorage.setItem('sys_notifications', JSON.stringify(notifications.value));
  }
};

const getNotifDotStyle = (type) => {
  if (type === 'pending') return 'background: var(--color-primary);';
  if (type === 'warning') return 'background: var(--color-danger);';
  return 'background: var(--color-success);';
};

// Filtered Menu Items for Command Palette (Role-restricted)
const filteredMenuItems = computed(() => {
  const role = props.user?.role || 'Siswa';
  const query = commandSearchQuery.value.toLowerCase().trim();
  
  let items = menuItems;
  if (role !== 'Laboran') {
    // Non-laboran can only see Dashboard, Peminjaman (Katalog), and Riwayat
    items = menuItems.filter(item => 
      ['Dashboard', 'Peminjaman', 'Riwayat'].includes(item.name)
    );
  }
  
  if (!query) {
    return items;
  }
  return items.filter(item => 
    item.name.toLowerCase().includes(query) || 
    item.displayName.toLowerCase().includes(query)
  );
});

// Navigate Command Palette results
const nextItem = () => {
  if (filteredMenuItems.value.length === 0) return;
  selectedIndex.value = (selectedIndex.value + 1) % filteredMenuItems.value.length;
};

const prevItem = () => {
  if (filteredMenuItems.value.length === 0) return;
  selectedIndex.value = (selectedIndex.value - 1 + filteredMenuItems.value.length) % filteredMenuItems.value.length;
};

const selectCurrentItem = () => {
  if (filteredMenuItems.value.length === 0) return;
  const selected = filteredMenuItems.value[selectedIndex.value];
  selectMenu(selected.name);
};

// Listeners for shortcuts
const handleKeyDown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    showCommandPalette.value = !showCommandPalette.value;
    commandSearchQuery.value = '';
    selectedIndex.value = 0;
  } else if (e.key === 'Escape') {
    showCommandPalette.value = false;
  } else if (showCommandPalette.value) {
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      nextItem();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      prevItem();
    } else if (e.key === 'Enter') {
      e.preventDefault();
      selectCurrentItem();
    }
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
  <div class="layout-container">
    <!-- Click-away overlay -->
    <div 
      v-if="showNotifications || showProfileDropdown || showCommandPalette" 
      class="global-overlay" 
      @click="closeDropdowns(); showCommandPalette = false;"
    ></div>

    <!-- COMMAND PALETTE MODAL -->
    <div v-if="showCommandPalette" class="command-palette-overlay">
      <div class="command-palette-container" @click.stop>
        <div class="command-palette-header">
          <Search :size="18" class="command-palette-icon" />
          <input 
            type="text" 
            v-model="commandSearchQuery" 
            placeholder="Type a menu name to navigate..." 
            class="command-palette-input"
            ref="paletteInput"
            v-focus
          />
          <button @click="showCommandPalette = false" class="btn-close-palette" title="Tutup (Esc)">
            <X :size="16" />
          </button>
        </div>
        <div class="command-palette-results">
          <button 
            v-for="(item, index) in filteredMenuItems" 
            :key="item.name" 
            class="command-palette-item"
            :class="{ 'selected': index === selectedIndex }"
            @click="selectMenu(item.name)"
            @mouseenter="selectedIndex = index"
          >
            <div class="command-palette-item-left">
              <component :is="item.icon" class="command-palette-item-icon" />
              <span>{{ item.displayName }}</span>
            </div>
            <span class="command-palette-shortcut">↵ Enter</span>
          </button>
          <div v-if="filteredMenuItems.length === 0" class="command-palette-empty">
            No results found for "{{ commandSearchQuery }}"
          </div>
        </div>
      </div>
    </div>

    <!-- FLOATING TOP NAVIGATION -->
    <header class="floating-navbar">
      <div class="navbar-left">
        <div class="logo-container">
          <div class="logo-box">
            <Package class="logo-icon-svg" />
          </div>
          <span class="brand-name">SPAL <span class="text-blue">SMKN 2</span></span>
        </div>
      </div>

      <!-- Navigation links (Horizontal layout) -->
      <nav class="nav-links-horizontal">
        <button 
          v-for="item in navItems" 
          :key="item.name" 
          class="nav-tab-btn" 
          :class="{ 'active': activeMenu === item.name }"
          @click="selectMenu(item.name)"
        >
          <component :is="item.icon" class="nav-tab-icon" />
          <span class="nav-tab-label">{{ item.label }}</span>
        </button>
      </nav>

      <div class="navbar-right">
        <!-- Search Trigger for Command Palette -->
        <button @click="showCommandPalette = true; commandSearchQuery = ''; selectedIndex = 0;" class="search-spotlight-trigger" title="Search (Ctrl+K)">
          <Search :size="15" />
          <span class="spotlight-shortcut">⌘K</span>
        </button>

        <!-- Notification Bell -->
        <div class="nav-action-wrapper">
          <button 
            @click="showNotifications = !showNotifications; showProfileDropdown = false" 
            class="nav-action-btn"
            :class="{ 'active': showNotifications }"
          >
            <Bell class="bell-icon" />
            <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
          </button>

          <!-- Notifications Dropdown -->
          <transition name="dropdown-fade">
            <div v-if="showNotifications" class="notification-dropdown">
              <div class="dropdown-header">
                <h3>Notifikasi</h3>
                <button v-if="unreadCount > 0" @click="clearNotifications" class="btn-clear-notif">
                  Tandai dibaca
                </button>
              </div>
              <div class="dropdown-list">
                <div 
                  v-for="notif in notifications" 
                  :key="notif.id" 
                  class="notif-item"
                  :class="[notif.type, { 'read': notif.read }]"
                  @click="markAsRead(notif.id)"
                  style="cursor: pointer;"
                >
                  <div v-if="!notif.read" class="notif-indicator"></div>
                  <div class="notif-content">
                    <p class="notif-text" :style="notif.read ? 'opacity: 0.6; font-weight: normal;' : ''">{{ notif.text }}</p>
                    <span class="notif-time">{{ notif.time }}</span>
                  </div>
                </div>
              </div>
              <div class="dropdown-footer">
                <a href="#" class="btn-all-notif" @click.prevent="showAllNotificationsModal = true; closeDropdowns();">
                  Lihat Semua Notifikasi <ArrowUpRight :size="14" />
                </a>
              </div>
            </div>
          </transition>
        </div>

        <!-- Profile Menu -->
        <div class="nav-action-wrapper">
          <button 
            @click="showProfileDropdown = !showProfileDropdown; showNotifications = false" 
            class="profile-trigger"
            :class="{ 'active': showProfileDropdown }"
          >
            <div class="profile-avatar">
              <img :src="avatarUrl" alt="Avatar" />
            </div>
            <div class="profile-meta">
              <span class="profile-name">{{ (user?.name || 'User').split(' ')[0] }}</span>
            </div>
          </button>

          <!-- Profile Dropdown -->
          <transition name="dropdown-fade">
            <div v-if="showProfileDropdown" class="profile-dropdown">
              <div class="profile-dropdown-header">
                <p class="p-name">{{ user?.name || 'Pengguna' }}</p>
                <p class="p-email">{{ user?.email || '' }}</p>
                <span class="role-badge" style="font-size: 10px; font-weight: 700; background: var(--color-primary-light); color: var(--color-action-primary); padding: 2px 8px; border-radius: 9999px; margin-top: 4px; display: inline-block; text-transform: uppercase;">
                  {{ user?.role || 'Guest' }}
                </span>
              </div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-menu-list">
                <a href="#" class="dropdown-menu-link" @click.prevent="openProfileModal(); showProfileDropdown = false;">
                  <User :size="16" /> Profil Saya
                </a>
                <a href="#" class="dropdown-menu-link" @click.prevent="showSettingsModal = true; showProfileDropdown = false;">
                  <Settings :size="16" /> Pengaturan Sistem
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-menu-link text-danger" @click.prevent="emit('logout')">
                  <LogOut :size="16" /> Keluar Aplikasi
                </a>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </header>

    <!-- CONTENT STAGE -->
    <main class="content-stage-wrapper">
      <slot></slot>
    </main>

    <!-- MODAL PROFIL SAYA -->
    <transition name="fade">
      <div v-if="showProfileModal" class="modal-overlay-custom" @click.self="showProfileModal = false">
        <div class="modal-panel-custom glass-panel">
          <div class="modal-header-custom">
            <h3>Profil Saya</h3>
            <button @click="showProfileModal = false" class="btn-close-custom">
              <X :size="16" />
            </button>
          </div>
          
          <div class="modal-body-custom">
            <div class="profile-avatar-wrapper" @click="triggerFileInput" style="position: relative; margin: 0 auto 20px; width: 100px; height: 100px; cursor: pointer;">
              <div class="profile-avatar-large" style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 2px solid var(--color-primary); transition: var(--transition-smooth); margin: 0 auto;">
                <img :src="computedAvatarPreview" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;" />
              </div>
              <div class="avatar-edit-overlay" style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.45); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0; transition: var(--transition-smooth); color: white;">
                <Camera :size="18" />
              </div>
              <input type="file" ref="fileInput" accept="image/*" style="display: none;" @change="handleAvatarChange" />
            </div>
            
            <div class="form-group-custom">
              <label class="form-label-custom">Nama Lengkap</label>
              <input v-model="profileName" type="text" class="form-input-custom" placeholder="Masukkan nama..." />
            </div>
            
            <div class="form-group-custom">
              <label class="form-label-custom">Alamat Email</label>
              <input v-model="profileEmail" type="email" class="form-input-custom" placeholder="Masukkan email..." />
            </div>

            <div class="form-group-custom">
              <label class="form-label-custom">Peran Akun</label>
              <input :value="user?.role" type="text" class="form-input-custom readonly-input" readonly />
            </div>
          </div>
          
          <div class="modal-footer-custom">
            <button @click="showProfileModal = false" class="btn-cancel-custom">Batal</button>
            <button @click="saveProfile" class="btn-save-custom" :disabled="!profileName.trim() || !profileEmail.trim()">
              Simpan
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- MODAL PENGATURAN SISTEM -->
    <transition name="fade">
      <div v-if="showSettingsModal" class="modal-overlay-custom" @click.self="showSettingsModal = false">
        <div class="modal-panel-custom glass-panel" style="max-width: 420px;">
          <div class="modal-header-custom">
            <h3>Pengaturan Sistem</h3>
            <button @click="showSettingsModal = false" class="btn-close-custom">
              <X :size="16" />
            </button>
          </div>
          
          <div class="modal-body-custom">
            <div class="settings-option-item">
              <div class="settings-option-info">
                <h5>Notifikasi Suara</h5>
                <p>Aktifkan efek suara untuk peminjaman terlambat atau approval baru.</p>
              </div>
              <label class="switch-toggle">
                <input type="checkbox" v-model="soundNotifications" />
                <span class="slider-toggle"></span>
              </label>
            </div>
            
            <div class="settings-option-item">
              <div class="settings-option-info">
                <h5>Auto-refresh Data</h5>
                <p>Singkronisasi otomatis data inventaris dan transaksi setiap 30 detik.</p>
              </div>
              <label class="switch-toggle">
                <input type="checkbox" v-model="autoRefresh" />
                <span class="slider-toggle"></span>
              </label>
            </div>

            <div v-if="user?.role === 'Laboran'" class="form-group-custom" style="margin-top: 16px;">
              <label class="form-label-custom">Tarif Denda Terlambat (per hari)</label>
              <div class="input-currency-wrapper">
                <span class="currency-prefix">Rp</span>
                <input v-model.number="fineRate" type="number" class="form-input-custom" placeholder="5000" />
              </div>
              <p class="form-help-custom">Mengatur kalkulasi nilai denda otomatis bagi siswa/guru yang telat.</p>
            </div>
          </div>
          
          <div class="modal-footer-custom">
            <button @click="showSettingsModal = false" class="btn-cancel-custom">Batal</button>
            <button @click="saveSettings" class="btn-save-custom">
              Simpan
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- MODAL SEMUA NOTIFIKASI -->
    <transition name="fade">
      <div v-if="showAllNotificationsModal" class="modal-overlay-custom" @click.self="showAllNotificationsModal = false">
        <div class="modal-panel-custom glass-panel" style="max-width: 520px;">
          <div class="modal-header-custom">
            <h3>Semua Notifikasi</h3>
            <button @click="showAllNotificationsModal = false" class="btn-close-custom">
              <X :size="16" />
            </button>
          </div>
          
          <div class="modal-body-custom" style="max-height: 400px; overflow-y: auto;">
            <div v-if="notifications.length === 0" class="no-notifications-placeholder" style="text-align: center; padding: 32px; color: var(--text-secondary);">
              Tidak ada notifikasi saat ini.
            </div>
            <div v-else class="all-notifications-list" style="display: flex; flex-direction: column; gap: 12px;">
              <div 
                v-for="notif in notifications" 
                :key="notif.id" 
                class="modal-notif-item"
                :class="[notif.type, { 'read': notif.read }]"
                style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; border-radius: var(--radius-md); border: 1px solid rgba(0, 0, 0, 0.04); background: rgba(255, 255, 255, 0.5); position: relative;"
              >
                <!-- Indicator dot for unread -->
                <span 
                  v-if="!notif.read" 
                  class="modal-notif-dot"
                  :style="getNotifDotStyle(notif.type)"
                  style="width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-top: 6px; flex-shrink: 0;"
                ></span>
                <div class="modal-notif-content" style="flex: 1;">
                  <p class="modal-notif-text" :style="notif.read ? 'color: var(--text-secondary); opacity: 0.8;' : 'color: var(--text-primary); font-weight: 550;'" style="margin: 0; font-size: 0.875rem; line-height: 1.45;">
                    {{ notif.text }}
                  </p>
                  <span class="modal-notif-time" style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; display: inline-block;">
                    {{ notif.time }}
                  </span>
                </div>
                <button 
                  v-if="!notif.read" 
                  @click="markAsRead(notif.id)" 
                  class="btn-mark-single-read" 
                  style="background: transparent; border: none; font-size: 0.75rem; color: var(--color-primary); cursor: pointer; padding: 2px 6px; font-weight: 600;"
                >
                  Baca
                </button>
              </div>
            </div>
          </div>
          
          <div class="modal-footer-custom" style="display: flex; justify-content: space-between; align-items: center; width: 100%; gap: 12px;">
            <button 
              v-if="unreadCount > 0" 
              @click="clearNotifications" 
              class="btn-cancel-custom" 
              style="border-color: rgba(70,112,143,0.3); color: var(--color-primary); padding: 8px 16px; margin: 0;"
            >
              Tandai Semua Dibaca
            </button>
            <div style="flex: 1;"></div>
            <button @click="showAllNotificationsModal = false" class="btn-save-custom" style="padding: 8px 20px; margin: 0;">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* Main Layout Container */
.layout-container {
  min-height: 100vh;
  width: 100vw;
  position: relative;
  display: flex;
  flex-direction: column;
}

.global-overlay {
  position: fixed;
  inset: 0;
  z-index: 40;
  background: rgba(15, 23, 42, 0.01);
  backdrop-filter: blur(1px);
}

/* FLOATING NAVBAR (Rombak ke Archival Index) */
.floating-navbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  max-width: none;
  background: var(--color-bg-base); /* Menyatu dengan latar belakang */
  border: none;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05); /* Garis batas bawah 1px tipis */
  border-radius: 0; /* Hapus border radius */
  box-shadow: none; /* HAPUS SEMUA BAYANGAN */
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 22px 48px; /* Padding vertikal longgar agar bernapas */
  z-index: 100;
  transition: var(--transition-smooth);
}

.navbar-left {
  display: flex;
  align-items: center;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 10px;
}

.logo-box {
  width: 32px;
  height: 32px;
  background: transparent;
  border: 1.5px solid var(--color-primary);
  color: var(--color-primary);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-icon-svg {
  width: 16px;
  height: 16px;
}

.brand-name {
  font-family: var(--font-display);
  font-size: 26px; /* oversized */
  font-style: italic;
  font-weight: 500;
  color: var(--text-primary);
  letter-spacing: -0.01em;
  line-height: 1;
}

.text-blue {
  color: var(--color-primary);
  font-weight: 600;
  font-style: italic;
}

/* Horizontal tabs navigation (Hapus background pill kaku) */
.nav-links-horizontal {
  display: flex;
  align-items: center;
  gap: 32px; /* Jarak ekstra longgar */
}

.nav-tab-btn {
  background: transparent;
  border: none;
  font-family: var(--font-primary);
  font-size: 13.5px;
  font-weight: 500;
  color: var(--text-secondary);
  padding: 8px 0; /* Hanya padding vertikal, hilangkan highlight box background */
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  position: relative;
  transition: color 0.2s ease-out, transform 0.2s ease-out;
}

.nav-tab-icon {
  width: 15px;
  height: 15px;
  stroke-width: 2.2;
}

/* Kinetik Underline */
.nav-tab-btn::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background-color: var(--color-primary);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.nav-tab-btn:hover {
  color: var(--text-primary);
  transform: scale(1.02);
}

.nav-tab-btn:hover::after,
.nav-tab-btn.active::after {
  transform: scaleX(1);
}

.nav-tab-btn.active {
  color: var(--text-primary);
  font-weight: 600;
}

.nav-tab-btn:active {
  transform: scale(0.98); /* tactile click */
}

/* Spotlight search trigger button */
.search-spotlight-trigger {
  background: transparent;
  border: 1px solid rgba(0, 0, 0, 0.06);
  padding: 6px 12px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-primary);
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition-smooth);
}

.search-spotlight-trigger:hover {
  background: rgba(0, 0, 0, 0.02);
  border-color: rgba(0, 0, 0, 0.15);
  color: var(--text-primary);
  transform: translateY(-1px);
}

.search-spotlight-trigger:active {
  transform: translateY(0) scale(0.98); /* tactile click */
}

.spotlight-shortcut {
  font-size: 10px;
  background: rgba(0, 0, 0, 0.04);
  padding: 1px 6px;
  border-radius: 4px;
  color: var(--text-muted);
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

/* Nav Actions */
.nav-action-wrapper {
  position: relative;
}

.nav-action-btn {
  background: transparent;
  border: 1px solid rgba(0, 0, 0, 0.06);
  width: 36px;
  height: 36px;
  border-radius: 6px;
  cursor: pointer;
  color: var(--text-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: var(--transition-smooth);
}

.nav-action-btn:hover,
.nav-action-btn.active {
  background: rgba(0, 0, 0, 0.02);
  border-color: rgba(0, 0, 0, 0.15);
  color: var(--text-primary);
  transform: translateY(-1px);
}

.nav-action-btn:active {
  transform: translateY(0) scale(0.95); /* tactile press-in */
}

.bell-icon {
  width: 15px;
  height: 15px;
}

.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: var(--color-danger);
  color: white;
  font-size: 8.5px;
  font-weight: 700;
  min-width: 14px;
  height: 14px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1.5px solid var(--color-bg-base);
}

/* Profile Trigger button */
.profile-trigger {
  background: transparent;
  border: 1px solid rgba(0, 0, 0, 0.06);
  padding: 4px 12px 4px 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: var(--transition-smooth);
}

.profile-trigger:hover,
.profile-trigger.active {
  background: rgba(0, 0, 0, 0.02);
  border-color: rgba(0, 0, 0, 0.15);
  transform: translateY(-1px);
}

.profile-trigger:active {
  transform: translateY(0) scale(0.98);
}

.profile-avatar {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  overflow: hidden;
  border: 1.5px solid var(--color-bg-base);
}

.profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-name {
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-primary);
}

/* Notification Dropdown Panel */
.notification-dropdown {
  position: absolute;
  top: 44px;
  right: 0;
  width: 320px;
  background: #ffffff;
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-lg);
  box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.06);
  z-index: 100;
  overflow: hidden;
  transform-origin: top right;
}

.dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}

.dropdown-header h3 {
  font-size: 13.5px;
  font-weight: 700;
  color: var(--text-primary);
}

.btn-clear-notif {
  background: transparent;
  border: none;
  color: var(--color-primary);
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
}

.dropdown-list {
  max-height: 220px;
  overflow-y: auto;
}

.notif-item {
  display: flex;
  gap: 12px;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.03);
}

.notif-indicator {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  margin-top: 6px;
  flex-shrink: 0;
}

.notif-item.pending .notif-indicator { background: var(--color-primary); }
.notif-item.warning .notif-indicator { background: var(--color-danger); }
.notif-item.info .notif-indicator { background: var(--color-success); }

.notif-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.notif-text {
  font-size: 12px;
  line-height: 1.4;
  color: var(--text-primary);
}

.notif-time {
  font-size: 10px;
  color: var(--text-muted);
}

.dropdown-footer {
  padding: 10px;
  text-align: center;
  background: rgba(0, 0, 0, 0.005);
  border-top: 1px solid rgba(0, 0, 0, 0.04);
}

.btn-all-notif {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: var(--text-secondary);
  font-size: 11px;
  font-weight: 600;
  text-decoration: none;
}

.btn-all-notif:hover {
  color: var(--color-primary);
}

/* Profile Dropdown */
.profile-dropdown {
  position: absolute;
  top: 44px;
  right: 0;
  width: 190px;
  background: #ffffff;
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-lg);
  box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.06);
  z-index: 100;
  padding: 6px 0;
  transform-origin: top right;
}

.profile-dropdown-header {
  padding: 10px 16px;
}

.p-name {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-primary);
}

.p-email {
  font-size: 10.5px;
  color: var(--text-muted);
}

.dropdown-divider {
  height: 1px;
  background: rgba(0, 0, 0, 0.04);
  margin: 4px 0;
}

.dropdown-menu-link {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
  text-decoration: none;
  transition: var(--transition-fast);
}

.dropdown-menu-link:hover {
  background: var(--color-primary-light);
  color: var(--color-primary);
}

.dropdown-menu-link.text-danger {
  color: var(--color-danger);
}

.dropdown-menu-link.text-danger:hover {
  background: var(--color-danger-light);
}

/* CONTENT VIEWPORT WRAPPER */
.content-stage-wrapper {
  flex: 1;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding-top: 140px; /* whitespace relative to top navbar */
  padding-bottom: 48px;
  padding-left: 24px;
  padding-right: 24px;
  transition: var(--transition-smooth);
}

/* Custom Modal Overlays */
.modal-overlay-custom {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.2);
  backdrop-filter: blur(8px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.modal-panel-custom {
  width: 100%;
  max-width: 480px;
  background: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: var(--radius-xl);
  padding: 32px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
  animation: modalAppear 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes modalAppear {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.modal-header-custom {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header-custom h3 {
  font-family: var(--font-primary);
  font-size: 1.25rem;
  font-weight: 750;
  color: var(--text-primary);
}

.btn-close-custom {
  background: transparent;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  color: var(--text-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-close-custom:hover {
  background: rgba(0, 0, 0, 0.04);
  color: var(--text-primary);
}

.modal-body-custom {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Avatar Large style */
.profile-avatar-large {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  overflow: hidden;
  margin: 0 auto 16px auto;
  border: 2px solid var(--color-primary);
}

.profile-avatar-large img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-avatar-wrapper:hover .avatar-edit-overlay {
  opacity: 1 !important;
}

/* Custom form groups */
.form-group-custom {
  display: flex;
  flex-direction: column;
  gap: 6px;
  text-align: left;
}

.form-label-custom {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-input-custom {
  font-family: var(--font-primary);
  font-size: 0.875rem;
  color: var(--text-primary);
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: var(--radius-sm);
  padding: 10px 14px;
  outline: none;
  background: #ffffff;
  transition: var(--transition-fast);
}

.form-input-custom:focus {
  border-color: var(--color-primary);
}

.readonly-input {
  background: rgba(0, 0, 0, 0.02);
  color: var(--text-secondary);
  cursor: not-allowed;
}

.input-currency-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.currency-prefix {
  position: absolute;
  left: 14px;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-secondary);
  pointer-events: none;
}

.input-currency-wrapper .form-input-custom {
  padding-left: 36px;
  width: 100%;
}

.form-help-custom {
  font-size: 0.7125rem;
  color: var(--text-secondary);
}

/* Option Switch items */
.settings-option-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 12px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}

.settings-option-item:last-of-type {
  border-bottom: none;
}

.settings-option-info {
  text-align: left;
}

.settings-option-info h5 {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--text-primary);
}

.settings-option-info p {
  font-size: 0.75rem;
  color: var(--text-secondary);
  line-height: 1.4;
  margin-top: 2px;
}

/* Switch Toggle Slider */
.switch-toggle {
  position: relative;
  display: inline-block;
  width: 42px;
  height: 24px;
  flex-shrink: 0;
}

.switch-toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider-toggle {
  position: absolute;
  cursor: pointer;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.08);
  transition: .22s cubic-bezier(0.16, 1, 0.3, 1);
  border-radius: 34px;
}

.slider-toggle:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .22s cubic-bezier(0.16, 1, 0.3, 1);
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.switch-toggle input:checked + .slider-toggle {
  background-color: var(--color-primary);
}

.switch-toggle input:checked + .slider-toggle:before {
  transform: translateX(18px);
}

/* Footer buttons */
.modal-footer-custom {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 8px;
}

.btn-cancel-custom {
  background: transparent;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: var(--radius-sm);
  padding: 10px 18px;
  font-family: var(--font-primary);
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-cancel-custom:hover {
  background: rgba(0, 0, 0, 0.02);
  color: var(--text-primary);
}

.btn-save-custom {
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: var(--radius-sm);
  padding: 10px 20px;
  font-family: var(--font-primary);
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition-smooth);
}

.btn-save-custom:hover {
  background: #365670;
  box-shadow: 0 4px 12px rgba(70, 112, 143, 0.15);
}

.btn-save-custom:disabled {
  background: rgba(0, 0, 0, 0.04);
  color: var(--text-muted);
  cursor: not-allowed;
  box-shadow: none;
}

/* RESPONSIVE MEDIA QUERIES */
@media (max-width: 768px) {
  .floating-navbar {
    padding: 14px 20px;
  }
  
  .brand-name {
    font-size: 20px;
  }
  
  .nav-links-horizontal {
    position: fixed;
    bottom: 16px;
    left: 16px;
    right: 16px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(226, 232, 240, 0.6);
    border-radius: 20px;
    padding: 10px 14px;
    box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.15);
    z-index: 1000;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
    gap: 8px;
    justify-content: center;
  }
  
  .nav-tab-btn {
    flex-direction: column;
    gap: 4px;
    font-size: 10px;
    padding: 6px 0;
    align-items: center;
    justify-content: center;
    text-align: center;
    width: 100%;
  }
  
  .nav-tab-btn::after {
    display: none;
  }
  
  .nav-tab-btn.active {
    color: var(--color-primary);
  }
  
  .nav-tab-icon {
    width: 18px;
    height: 18px;
  }
  
  .nav-tab-label {
    font-size: 10px;
    font-weight: 600;
  }
  
  .search-spotlight-trigger {
    padding: 8px;
  }
  
  .spotlight-shortcut {
    display: none;
  }
  
  .content-stage-wrapper {
    padding-top: 100px;
    padding-bottom: 100px; /* Space for bottom nav bar */
    padding-left: 16px;
    padding-right: 16px;
  }
}

@media (max-width: 576px) {
  .modal-panel-custom {
    padding: 20px 16px;
    gap: 16px;
    border-radius: var(--radius-lg);
  }
  
  .modal-header-custom h3 {
    font-size: 1.125rem;
  }
  
  .profile-dropdown, .notification-dropdown {
    position: fixed;
    top: 70px;
    left: 16px;
    right: 16px;
    width: auto;
    max-width: none;
  }
  
  .command-palette-overlay {
    padding: 10vh 16px;
  }
}
</style>

<style>
/* Custom directive helper for focusing on palette mount */
</style>
