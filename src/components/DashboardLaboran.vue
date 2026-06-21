<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
  Wrench, 
  CheckCircle, 
  Clock, 
  CalendarCheck,
  TrendingUp,
  Sliders,
  AlertTriangle,
  ChevronDown,
  ChevronUp,
  Package,
  Calendar,
  AlertCircle
} from '@lucide/vue';

const props = defineProps({
  user: {
    type: Object,
    default: () => ({ name: 'Pengguna', email: '', role: 'Siswa' })
  },
  activeLoans: {
    type: Array,
    default: () => []
  },
  items: {
    type: Array,
    default: () => []
  },
  blacklist: {
    type: Array,
    default: () => []
  },
  approvals: {
    type: Array,
    default: () => []
  },
  transactions: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['refresh-data']);

const loanSearchQuery = ref('');
const showAllLoans = ref(false);
const isMobile = ref(false);

const checkIfMobile = () => {
  isMobile.value = window.innerWidth <= 768;
};

onMounted(() => {
  checkIfMobile();
  window.addEventListener('resize', checkIfMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkIfMobile);
});

const filteredActiveLoans = computed(() => {
  if (!loanSearchQuery.value.trim()) {
    return props.activeLoans;
  }
  const q = loanSearchQuery.value.toLowerCase().trim();
  return props.activeLoans.filter(l => 
    (l.userName && l.userName.toLowerCase().includes(q)) || 
    (l.itemName && l.itemName.toLowerCase().includes(q)) ||
    (l.id && l.id.toLowerCase().includes(q))
  );
});

const greetingMessage = computed(() => {
  const hr = new Date().getHours();
  if (hr < 12) return 'Selamat Pagi';
  if (hr < 15) return 'Selamat Siang';
  if (hr < 18) return 'Selamat Sore';
  return 'Selamat Malam';
});

// Computed values for stats
const totalAlat = computed(() => props.items.length);
const totalDipinjam = computed(() => props.activeLoans.length);
const pendingApprovalsCount = computed(() => props.approvals.filter(a => a.status === 'Pending').length);
const blacklistCount = computed(() => props.blacklist.length);

// Calculate total fine for current user (Guru / Siswa)
const totalDenda = computed(() => {
  let sum = 0;
  props.activeLoans.forEach(loan => {
    if (loan.fine && loan.fine !== '-') {
      const num = parseInt(loan.fine.replace(/[^0-9]/g, ''), 10);
      if (!isNaN(num)) sum += num;
    }
  });
  return sum;
});

const formatRupiah = (number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(number);
};

// Weekly chart data calculated dynamically from the database
const chartData = computed(() => {
  const counts = { 'Sen': 0, 'Sel': 0, 'Rab': 0, 'Kam': 0, 'Jum': 0, 'Sab': 0 };
  
  if (props.transactions && props.transactions.length > 0) {
    props.transactions.forEach(trx => {
      if (trx.dateOut) {
        const date = new Date(trx.dateOut);
        if (!isNaN(date.getTime())) {
          const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
          const dayName = days[date.getDay()];
          if (counts[dayName] !== undefined) {
            counts[dayName]++;
          }
        }
      }
    });
  }
  
  return [
    { name: 'Sen', value: counts['Sen'] },
    { name: 'Sel', value: counts['Sel'] },
    { name: 'Rab', value: counts['Rab'] },
    { name: 'Kam', value: counts['Kam'] },
    { name: 'Jum', value: counts['Jum'] },
    { name: 'Sab', value: counts['Sab'] },
  ];
});

const maxChartValue = computed(() => {
  const vals = chartData.value.map(b => b.value);
  const max = Math.max(...vals);
  return max > 0 ? max : 1;
});

const showChart = ref(false);

const getInitials = (name) => {
  if (!name) return '?';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.slice(0, 2).toUpperCase();
};

const getItemCategoryColor = (itemName) => {
  const name = itemName.toLowerCase();
  if (name.includes('laptop') || name.includes('switch') || name.includes('router') || name.includes('access point') || name.includes('server') || name.includes('pc')) {
    return 'avatar-blue';
  } else if (name.includes('proyektor') || name.includes('monitor') || name.includes('tv') || name.includes('display')) {
    return 'avatar-purple';
  } else if (name.includes('kamera') || name.includes('webcam') || name.includes('handycam') || name.includes('capture') || name.includes('gimbal')) {
    return 'avatar-amber';
  } else {
    return 'avatar-green';
  }
};
</script>

<template>
  <div class="dashboard-laboran" :class="{ 'is-mobile-dashboard': isMobile }">
    
    <!-- DESKTOP VIEW FOR DASHBOARD -->
    <template v-if="!isMobile">
      <!-- 1. LABORAN (ADMIN) DASHBOARD -->
      <template v-if="user.role === 'Laboran'">
        <!-- Welcome Greetings Banner -->
        <div class="dashboard-welcome-card glass-panel">
          <div class="welcome-card-left">
            <span class="welcome-greeting">{{ greetingMessage }}, {{ user.name }}!</span>
            <h1 class="welcome-title">Dasbor Pemantauan SPAL</h1>
            <p class="welcome-subtitle">
              Hari ini terdapat <strong class="text-blue">{{ totalDipinjam }} alat</strong> sedang dipinjam and 
              <strong class="text-amber">{{ pendingApprovalsCount }} pengajuan</strong> menunggu approval Anda.
            </p>
          </div>
        </div>

        <!-- Quick Metrics Grid -->
        <div class="stats-row-grid">
          <div class="stat-card glass-panel stat-total">
            <div class="stat-card-header">
              <div class="stat-icon-box bg-blue-light text-blue">
                <Package :size="18" />
              </div>
              <span class="stat-card-title">Total Inventaris Alat</span>
            </div>
            <div class="stat-card-body">
              <h2 class="stat-card-value">{{ totalAlat }}</h2>
              <span class="stat-card-desc">Item terdaftar di database</span>
            </div>
          </div>

          <div class="stat-card glass-panel stat-borrowed">
            <div class="stat-card-header">
              <div class="stat-icon-box bg-green-light text-green">
                <CalendarCheck :size="18" />
              </div>
              <span class="stat-card-title">Sedang Dipinjam</span>
            </div>
            <div class="stat-card-body">
              <h2 class="stat-card-value">{{ totalDipinjam }}</h2>
              <span class="stat-card-desc">Alat aktif di luar lab</span>
            </div>
          </div>

          <div class="stat-card glass-panel stat-pending" :class="{ 'highlight-glow': pendingApprovalsCount > 0 }">
            <div class="stat-card-header">
              <div class="stat-icon-box bg-amber-light text-amber">
                <AlertCircle :size="18" />
              </div>
              <span class="stat-card-title">Persetujuan Menunggu</span>
            </div>
            <div class="stat-card-body">
              <h2 class="stat-card-value">{{ pendingApprovalsCount }}</h2>
              <span class="stat-card-desc" v-if="pendingApprovalsCount > 0">Perlu tindakan verifikasi</span>
              <span class="stat-card-desc" v-else>Semua pengajuan diproses</span>
            </div>
          </div>

          <div class="stat-card glass-panel stat-blacklist">
            <div class="stat-card-header">
              <div class="stat-icon-box bg-red-light text-red">
                <AlertTriangle :size="18" />
              </div>
              <span class="stat-card-title">Akses Ditangguhkan</span>
            </div>
            <div class="stat-card-body">
              <h2 class="stat-card-value">{{ blacklistCount }}</h2>
              <span class="stat-card-desc">Siswa diblokir / sanksi</span>
            </div>
          </div>
        </div>

        <!-- Main Panels Layout Grid -->
        <!-- Main Panels Layout Grid -->
        <div class="main-dashboard-grid">
          
          <!-- Left Panel: Active Loans & Chart -->
          <div class="col-span-3 flex-column-container">
            
            <!-- Active Loans -->
            <div class="panel-card glass-panel">
              <div class="panel-card-header">
                <div>
                  <h3 class="panel-card-title-serif">Peminjaman Aktif Saat Ini</h3>
                  <p class="panel-card-subtitle">Daftar guru dan siswa yang meminjam inventaris laboratorium.</p>
                </div>
                
                <div class="panel-header-actions">
                  <!-- Search Bar inside active loans card -->
                  <input 
                    v-model="loanSearchQuery" 
                    type="text" 
                    placeholder="Cari nama atau alat..." 
                    class="search-bar-compact" 
                  />
                  <span class="loans-count-pill">{{ filteredActiveLoans.length }} Sesi</span>
                </div>
              </div>

              <div class="loans-list-wrapper" v-if="filteredActiveLoans.length > 0">
                <div 
                  v-for="loan in filteredActiveLoans.slice(0, showAllLoans ? undefined : 5)" 
                  :key="loan.id" 
                  class="loan-row-item"
                >
                  <div class="loan-row-left">
                    <div class="borrower-avatar-circle">
                      {{ getInitials(loan.userName) }}
                    </div>
                    <div class="loan-row-details">
                      <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <h4 class="loan-borrower-name">{{ loan.userName }}</h4>
                        <span class="loan-trx-code">{{ loan.id }}</span>
                      </div>
                      <p class="loan-item-name">{{ loan.itemName }}</p>
                    </div>
                  </div>
                  
                  <div class="loan-row-right">
                    <div class="loan-due-info">
                      <span class="due-label">Batas Kembali</span>
                      <span class="due-date-value" :class="{ 'late-text': loan.status === 'Terlambat' }">
                        {{ loan.dateDue }}
                      </span>
                    </div>
                    
                    <span class="loan-badge" :class="loan.status.toLowerCase()">
                      {{ loan.status }}
                    </span>
                  </div>
                </div>
                
                <!-- Expand/Collapse Button if > 5 active loans -->
                <button 
                  v-if="filteredActiveLoans.length > 5" 
                  @click="showAllLoans = !showAllLoans" 
                  class="btn-expand-list"
                >
                  {{ showAllLoans ? 'Tampilkan Lebih Sedikit' : `Lihat Semua (${filteredActiveLoans.length})` }}
                </button>
              </div>
              
              <div v-else class="empty-list-state">
                <CheckCircle :size="32" class="text-green" />
                <p>Tidak ada peminjaman aktif saat ini.</p>
              </div>
            </div>

            <!-- Weekly Visualisation Section (Always Visible) -->
            <div class="chart-dashboard-panel glass-panel">
              <div class="chart-panel-header">
                <div>
                  <h3 class="panel-card-title-serif" style="margin-bottom: 2px;">Visualisasi Transaksi Lab</h3>
                  <p class="panel-card-subtitle">Frekuensi aktivitas peminjaman mingguan.</p>
                </div>
              </div>
              
              <div class="chart-area-visual">
                <div class="chart-y-axis-vals">
                  <span>{{ maxChartValue }}</span>
                  <span>{{ Math.round(maxChartValue / 2) }}</span>
                  <span>0</span>
                </div>

                <div class="chart-canvas-wrapper">
                  <div class="chart-grid-lines">
                    <div class="grid-line-dash"></div>
                    <div class="grid-line-dash"></div>
                    <div class="grid-line-dash"></div>
                  </div>

                  <div class="bars-container-flex">
                    <div v-for="bar in chartData" :key="bar.name" class="bar-column-wrapper">
                      <div class="bar-visual-container">
                        <div 
                          class="bar-filled-pill" 
                          :style="{ height: `${(bar.value / maxChartValue) * 100}%` }"
                          :title="`Jumlah: ${bar.value}`"
                        >
                          <span class="visual-tooltip">{{ bar.value }} Transaksi</span>
                        </div>
                      </div>
                      <span class="bar-column-label">{{ bar.name }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="chart-legend-row">
                <span class="legend-dot"></span>
                <span>Jumlah transaksi peminjaman mingguan</span>
              </div>
            </div>

          </div>

          <!-- Right Panel: Alerts & Notifications -->
          <div class="right-column-panel col-span-1">
            
            <!-- System Alert for Late Returns -->
            <div class="alert-box-card warning-alert" v-if="activeLoans.some(l => l.status === 'Terlambat')">
              <div class="alert-box-header">
                <AlertTriangle class="alert-box-icon text-red" :size="16" />
                <h4>Keterlambatan Peminjaman</h4>
              </div>
              <p class="alert-box-body">
                Terdeteksi <strong>{{ activeLoans.filter(l => l.status === 'Terlambat').length }} peminjaman</strong> terlambat! Sistem denda otomatis aktif.
              </p>
            </div>
            
            <!-- Quick Tasks / Navigation Shortcut Card -->
            <div class="action-steps-card glass-panel">
              <h3 class="panel-card-title">Tugas Cepat</h3>
              <div class="action-steps-list">
                <div class="step-item" @click="$emit('change-menu', 'Approval')">
                  <div class="step-bullet" :class="{ 'has-pending': pendingApprovalsCount > 0 }">
                    <Clock :size="12" />
                  </div>
                  <div class="step-content">
                    <h5>Persetujuan Peminjaman</h5>
                    <p v-if="pendingApprovalsCount > 0">Terdapat {{ pendingApprovalsCount }} pengajuan baru</p>
                    <p v-else>Semua pengajuan diproses</p>
                  </div>
                </div>

                <div class="step-item" @click="$emit('change-menu', 'Pengembalian')">
                  <div class="step-bullet">
                    <Wrench :size="12" />
                  </div>
                  <div class="step-content">
                    <h5>Scan Pengembalian</h5>
                    <p>Verifikasi pengembalian alat</p>
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>
      </template>

      <!-- 2. GURU & SISWA DASHBOARD -->
      <template v-else>
        <!-- Welcome Greetings Banner -->
        <div class="dashboard-welcome-card glass-panel">
          <div class="welcome-card-left">
            <span class="welcome-greeting">{{ greetingMessage }}, {{ user.name }}!</span>
            <h1 class="welcome-title">Portal Peminjaman Alat</h1>
            <p class="welcome-subtitle">
              Anda masuk sebagai <strong class="text-blue">{{ user.role }} SPAL</strong>. Cari alat yang Anda butuhkan dan ajukan peminjaman dengan mudah.
            </p>
          </div>
          <div class="welcome-card-right">
            <button @click="$emit('change-menu', 'Peminjaman')" class="btn-primary-action">
              <Package :size="16" /> Cari & Pinjam Alat
            </button>
          </div>
        </div>

        <!-- Quick Stats Grid for Guru/Siswa -->
        <div class="stats-row-grid three-columns">
          <div class="stat-card glass-panel stat-borrowed">
            <div class="stat-card-header">
              <div class="stat-icon-box bg-green-light text-green">
                <CalendarCheck :size="18" />
              </div>
              <span class="stat-card-title">Alat Dipinjam Saya</span>
            </div>
            <div class="stat-card-body">
              <h2 class="stat-card-value">{{ totalDipinjam }}</h2>
              <span class="stat-card-desc">Sedang aktif digunakan</span>
            </div>
          </div>

          <div class="stat-card glass-panel stat-pending">
            <div class="stat-card-header">
              <div class="stat-icon-box bg-amber-light text-amber">
                <Clock :size="18" />
              </div>
              <span class="stat-card-title">Pengajuan Pending</span>
            </div>
            <div class="stat-card-body">
              <h2 class="stat-card-value">{{ approvals.length }}</h2>
              <span class="stat-card-desc">Menunggu approval laboran</span>
            </div>
          </div>

          <div class="stat-card glass-panel stat-blacklist" :class="{ 'highlight-glow-red': totalDenda > 0 }">
            <div class="stat-card-header">
              <div class="stat-icon-box bg-red-light text-red">
                <AlertCircle :size="18" />
              </div>
              <span class="stat-card-title">Total Denda Saya</span>
            </div>
            <div class="stat-card-body">
              <h2 class="stat-card-value" :class="{ 'red-text-giant': totalDenda > 0 }">
                {{ totalDenda > 0 ? formatRupiah(totalDenda) : 'Rp 0' }}
              </h2>
              <span class="stat-card-desc" v-if="totalDenda > 0">Ada peminjaman terlambat!</span>
              <span class="stat-card-desc" v-else>Bebas tanggungan denda</span>
            </div>
          </div>
        </div>

        <!-- Main Panels for Guru/Siswa -->
        <!-- Main Panels for Guru/Siswa -->
        <div class="main-dashboard-grid two-columns">
          
          <!-- Left Panel: Active Loans -->
          <div class="panel-card glass-panel col-span-2">
            <div class="panel-card-header">
              <div>
                <h3 class="panel-card-title-serif">Peminjaman Aktif Saya</h3>
                <p class="panel-card-subtitle">Alat laboratorium yang sedang Anda pinjam saat ini.</p>
              </div>
            </div>

            <div class="loans-list-wrapper" v-if="activeLoans.length > 0">
              <div v-for="loan in activeLoans" :key="loan.id" class="loan-row-item" :class="{ 'loan-row-late': loan.status.toLowerCase() === 'terlambat' }">
                <div class="loan-row-left">
                  <div class="borrower-avatar-circle" :class="getItemCategoryColor(loan.itemName)">
                    {{ loan.itemName.slice(0, 2).toUpperCase() }}
                  </div>
                  <div class="loan-row-details">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                      <h4 class="loan-borrower-name">{{ loan.itemName }}</h4>
                      <span class="loan-trx-code">{{ loan.id }}</span>
                    </div>
                    <p class="loan-item-name">Batas Waktu Pengembalian: <strong>{{ loan.dateDue }}</strong></p>
                  </div>
                </div>
                
                <div class="loan-row-right">
                  <span v-if="loan.fine && loan.fine !== '-'" class="fine-warning-badge">
                    Denda: {{ loan.fine }}
                  </span>
                  <span class="loan-badge" :class="loan.status.toLowerCase()">
                    {{ loan.status }}
                  </span>
                </div>
              </div>
            </div>
            <div class="empty-list-state" v-else>
              <Package :size="32" class="text-muted" style="opacity: 0.5;" />
              <p>Anda tidak memiliki peminjaman alat yang aktif saat ini.</p>
            </div>
          </div>

          <!-- Right Panel: Pending Request Status -->
          <div class="panel-card glass-panel col-span-2" v-if="approvals.length > 0">
            <div class="panel-card-header">
              <div>
                <h3 class="panel-card-title-serif">Status Pengajuan Terbaru</h3>
                <p class="panel-card-subtitle">Status verifikasi pengajuan peminjaman alat Anda.</p>
              </div>
            </div>

            <div class="loans-list-wrapper">
              <div v-for="req in approvals" :key="req.id" class="loan-row-item">
                <div class="loan-row-left">
                  <div class="borrower-avatar-circle" :class="getItemCategoryColor(req.itemName)">
                    {{ req.itemName.slice(0, 2).toUpperCase() }}
                  </div>
                  <div class="loan-row-details">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                      <h4 class="loan-borrower-name">{{ req.itemName }}</h4>
                      <span class="loan-trx-code">{{ req.id }}</span>
                    </div>
                    <p class="loan-item-name">Tanggal Diajukan: {{ req.dateReq }} &bull; Jumlah: {{ req.quantity }} unit</p>
                  </div>
                </div>
                
                <div class="loan-row-right">
                  <span class="loan-badge" :class="req.status.toLowerCase()">
                    {{ req.status === 'Pending' ? 'Menunggu' : req.status === 'Approved' ? 'Disetujui' : 'Ditolak' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </template>
    </template>

    <!-- MOBILE VIEW FOR DASHBOARD -->
    <template v-else>
      <div class="mobile-dashboard-container">
        
        <!-- Welcome Greetings Banner (Compact) -->
        <div class="mobile-welcome-banner glass-panel">
          <span class="mobile-welcome-greeting">{{ greetingMessage }}, {{ user.name }}!</span>
          <h1 class="mobile-welcome-title" style="font-size: 22px; font-weight: 750; font-family: var(--font-primary);">
            {{ user.role === 'Laboran' ? 'Dasbor Pemantauan' : 'Portal Peminjaman' }}
          </h1>
          <p class="mobile-welcome-subtitle" style="font-size: 12px; color: var(--color-text-secondary); margin-top: 4px;">
            <template v-if="user.role === 'Laboran'">
              Terdapat <strong>{{ totalDipinjam }} alat</strong> dipinjam & <strong>{{ pendingApprovalsCount }} pengajuan</strong> menunggu approval.
            </template>
            <template v-else>
              Anda masuk sebagai <strong>{{ user.role }} SPAL</strong>. Cari dan pinjam alat dengan mudah.
            </template>
          </p>
          <div v-if="user.role !== 'Laboran'" class="mobile-banner-action" style="margin-top: 12px;">
            <button @click="$emit('change-menu', 'Peminjaman')" class="btn-primary-action" style="width: 100%; justify-content: center;">
              <Package :size="14" /> Cari & Pinjam Alat
            </button>
          </div>
        </div>

        <!-- Metrics Grid (Compact 2x2) -->
        <div class="mobile-metrics-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px;">
          <!-- Metric 1: Total Alat (Laboran) or Alat Dipinjam Saya (Siswa) -->
          <div class="mobile-metric-card glass-panel" style="padding: 14px; display: flex; flex-direction: column; gap: 8px;">
            <div class="metric-card-header" style="display: flex; align-items: center; gap: 8px;">
              <div class="metric-icon bg-blue-light text-blue" style="width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                <Package :size="14" />
              </div>
              <span style="font-size: 11px; font-weight: 700; color: var(--color-text-secondary);">
                {{ user.role === 'Laboran' ? 'Total Inventaris' : 'Dipinjam Saya' }}
              </span>
            </div>
            <div>
              <h2 style="font-family: var(--font-number); font-size: 26px; font-weight: 800; line-height: 1;">
                {{ user.role === 'Laboran' ? totalAlat : totalDipinjam }}
              </h2>
            </div>
          </div>

          <!-- Metric 2: Sedang Dipinjam (Laboran) or Pending (Siswa) -->
          <div class="mobile-metric-card glass-panel" style="padding: 14px; display: flex; flex-direction: column; gap: 8px;">
            <div class="metric-card-header" style="display: flex; align-items: center; gap: 8px;">
              <div class="metric-icon bg-green-light text-green" style="width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                <CalendarCheck :size="14" />
              </div>
              <span style="font-size: 11px; font-weight: 700; color: var(--color-text-secondary);">
                {{ user.role === 'Laboran' ? 'Sedang Dipinjam' : 'Pending' }}
              </span>
            </div>
            <div>
              <h2 style="font-family: var(--font-number); font-size: 26px; font-weight: 800; line-height: 1;">
                {{ user.role === 'Laboran' ? totalDipinjam : approvals.length }}
              </h2>
            </div>
          </div>

          <!-- Metric 3: Pending Approvals (Laboran) or Denda Saya (Siswa) -->
          <div 
            class="mobile-metric-card glass-panel" 
            :class="{ 'highlight-glow': user.role === 'Laboran' && pendingApprovalsCount > 0, 'highlight-glow-red': user.role !== 'Laboran' && totalDenda > 0 }"
            style="padding: 14px; display: flex; flex-direction: column; gap: 8px;"
          >
            <div class="metric-card-header" style="display: flex; align-items: center; gap: 8px;">
              <div class="metric-icon bg-amber-light text-amber" style="width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                <AlertCircle :size="14" />
              </div>
              <span style="font-size: 11px; font-weight: 700; color: var(--color-text-secondary);">
                {{ user.role === 'Laboran' ? 'Approval Pending' : 'Denda Saya' }}
              </span>
            </div>
            <div>
              <h2 
                style="font-family: var(--font-number); font-size: 24px; font-weight: 800; line-height: 1;"
                :class="{ 'red-text-giant': user.role !== 'Laboran' && totalDenda > 0 }"
              >
                <template v-if="user.role === 'Laboran'">{{ pendingApprovalsCount }}</template>
                <template v-else>{{ totalDenda > 0 ? formatRupiah(totalDenda) : 'Rp 0' }}</template>
              </h2>
            </div>
          </div>

          <!-- Metric 4: Akses Ditangguhkan (Laboran Only) -->
          <div 
            v-if="user.role === 'Laboran'" 
            class="mobile-metric-card glass-panel" 
            style="padding: 14px; display: flex; flex-direction: column; gap: 8px;"
          >
            <div class="metric-card-header" style="display: flex; align-items: center; gap: 8px;">
              <div class="metric-icon bg-red-light text-red" style="width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                <AlertTriangle :size="14" />
              </div>
              <span style="font-size: 11px; font-weight: 700; color: var(--color-text-secondary);">
                Ditangguhkan
              </span>
            </div>
            <div>
              <h2 style="font-family: var(--font-number); font-size: 26px; font-weight: 800; line-height: 1;">
                {{ blacklistCount }}
              </h2>
            </div>
          </div>
        </div>

        <!-- Late Return Alert for Mobile -->
        <div 
          v-if="user.role === 'Laboran' && activeLoans.some(l => l.status === 'Terlambat')" 
          class="mobile-warning-alert glass-panel"
          style="margin-top: 16px; padding: 14px; background: rgba(239, 68, 68, 0.06); border: 1.5px solid rgba(239, 68, 68, 0.15); border-radius: 12px; display: flex; align-items: flex-start; gap: 10px;"
        >
          <AlertTriangle class="text-red" :size="16" style="flex-shrink: 0; margin-top: 2px;" />
          <div>
            <h4 style="font-size: 12.5px; font-weight: 700; color: #b91c1c;">Ada Keterlambatan</h4>
            <p style="font-size: 11px; color: #7f1d1d; line-height: 1.35; margin-top: 2px;">
              Terdeteksi <strong>{{ activeLoans.filter(l => l.status === 'Terlambat').length }} peminjaman</strong> terlambat!
            </p>
          </div>
        </div>

        <!-- Active Loans List for Mobile -->
        <div class="mobile-section-card glass-panel" style="margin-top: 16px; padding: 18px; display: flex; flex-direction: column; gap: 14px;">
          <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0, 0, 0, 0.04); padding-bottom: 12px;">
            <h3 style="font-family: var(--font-primary); font-size: 14px; font-weight: 750; color: var(--color-text-primary);">
              {{ user.role === 'Laboran' ? 'Peminjaman Aktif' : 'Peminjaman Aktif Saya' }}
            </h3>
            <span class="loans-count-pill" style="font-size: 10px; padding: 3px 8px;">
              {{ user.role === 'Laboran' ? filteredActiveLoans.length : activeLoans.length }} Sesi
            </span>
          </div>

          <div v-if="user.role === 'Laboran'">
            <div v-if="filteredActiveLoans.length > 0" class="mobile-loans-list" style="display: flex; flex-direction: column; gap: 10px;">
              <div 
                v-for="loan in filteredActiveLoans.slice(0, showAllLoans ? undefined : 3)" 
                :key="loan.id" 
                style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: rgba(0, 0, 0, 0.015); border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.02);"
              >
                <div style="display: flex; align-items: center; gap: 10px;">
                  <div class="borrower-avatar-circle" style="width: 32px; height: 32px; font-size: 11px;">
                    {{ getInitials(loan.userName) }}
                  </div>
                  <div style="display: flex; flex-direction: column; gap: 2px;">
                    <h5 style="font-size: 12px; font-weight: 700; color: var(--color-text-primary);">{{ loan.userName }}</h5>
                    <p style="font-size: 11px; color: var(--color-text-secondary);">{{ loan.itemName }}</p>
                  </div>
                </div>
                <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                  <span style="font-size: 10px; font-weight: 750;" :class="{ 'late-text': loan.status === 'Terlambat' }">
                    {{ loan.dateDue }}
                  </span>
                  <span class="loan-badge" :class="loan.status.toLowerCase()" style="font-size: 8px; padding: 2px 6px;">
                    {{ loan.status }}
                  </span>
                </div>
              </div>
              <button 
                v-if="filteredActiveLoans.length > 3" 
                @click="showAllLoans = !showAllLoans" 
                class="btn-expand-list"
                style="font-size: 11px; padding: 6px; border-radius: 8px; width: 100%;"
              >
                {{ showAllLoans ? 'Sembunyikan' : `Lihat Semua (${filteredActiveLoans.length})` }}
              </button>
            </div>
            <div v-else class="mobile-empty-state" style="padding: 24px 12px;">
              <CheckCircle :size="24" class="text-green" />
              <p style="font-size: 12px;">Tidak ada peminjaman aktif saat ini.</p>
            </div>
          </div>

          <div v-else>
            <div v-if="activeLoans.length > 0" class="mobile-loans-list" style="display: flex; flex-direction: column; gap: 10px;">
              <div 
                v-for="loan in activeLoans" 
                :key="loan.id" 
                style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: rgba(0, 0, 0, 0.015); border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.02);"
              >
                <div style="display: flex; align-items: center; gap: 10px;">
                  <div class="borrower-avatar-circle category-avatar" style="width: 32px; height: 32px; font-size: 11px;">
                    {{ loan.itemName.slice(0, 2).toUpperCase() }}
                  </div>
                  <div style="display: flex; flex-direction: column; gap: 2px;">
                    <h5 style="font-size: 12px; font-weight: 700; color: var(--color-text-primary);">{{ loan.itemName }}</h5>
                    <p style="font-size: 11px; color: var(--color-text-secondary);">Batas: {{ loan.dateDue }}</p>
                  </div>
                </div>
                <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                  <span v-if="loan.fine && loan.fine !== '-'" class="fine-warning-badge" style="font-size: 9px; padding: 2px 6px;">
                    {{ loan.fine }}
                  </span>
                  <span class="loan-badge" :class="loan.status.toLowerCase()" style="font-size: 8px; padding: 2px 6px;">
                    {{ loan.status }}
                  </span>
                </div>
              </div>
            </div>
            <div class="mobile-empty-state" style="padding: 24px 12px;">
              <Package :size="24" class="text-muted" style="opacity: 0.5;" />
              <p style="font-size: 12px;">Anda tidak memiliki peminjaman aktif.</p>
            </div>
          </div>
        </div>

        <!-- Pending Submissions (Siswa Only) -->
        <div v-if="user.role !== 'Laboran' && approvals.length > 0" class="mobile-section-card glass-panel" style="margin-top: 16px; padding: 18px; display: flex; flex-direction: column; gap: 14px;">
          <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0, 0, 0, 0.04); padding-bottom: 12px;">
            <h3 style="font-family: var(--font-primary); font-size: 14px; font-weight: 750; color: var(--color-text-primary);">
              Status Pengajuan Terbaru
            </h3>
          </div>
          <div class="mobile-loans-list" style="display: flex; flex-direction: column; gap: 10px;">
            <div 
              v-for="req in approvals" 
              :key="req.id" 
              style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: rgba(0, 0, 0, 0.015); border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.02);"
            >
              <div style="display: flex; align-items: center; gap: 10px;">
                <div class="borrower-avatar-circle category-avatar pending-avatar" style="width: 32px; height: 32px; font-size: 11px;">
                  {{ req.itemName.slice(0, 2).toUpperCase() }}
                </div>
                <div style="display: flex; flex-direction: column; gap: 2px;">
                  <h5 style="font-size: 12px; font-weight: 700; color: var(--color-text-primary);">{{ req.itemName }}</h5>
                  <p style="font-size: 11px; color: var(--color-text-secondary);">Diajukan: {{ req.dateReq }}</p>
                </div>
              </div>
              <div>
                <span class="loan-badge" :class="req.status.toLowerCase()" style="font-size: 8px; padding: 2px 6px;">
                  {{ req.status === 'Pending' ? 'Menunggu' : req.status === 'Approved' ? 'Disetujui' : 'Ditolak' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Weekly visual chart for Mobile (Laboran Only) -->
        <div v-if="user.role === 'Laboran'" class="mobile-section-card glass-panel" style="margin-top: 16px; padding: 18px; display: flex; flex-direction: column; gap: 14px;">
          <div>
            <h3 style="font-family: var(--font-primary); font-size: 14px; font-weight: 750; color: var(--color-text-primary);">
              Visualisasi Transaksi Lab
            </h3>
            <p style="font-size: 11px; color: var(--color-text-secondary); margin-top: 2px;">Frekuensi aktivitas peminjaman mingguan.</p>
          </div>

          <!-- Compact mobile chart -->
          <div class="mobile-chart-area" style="display: flex; gap: 10px; height: 140px; margin-top: 10px; position: relative;">
            <div class="chart-y-axis-vals" style="display: flex; flex-direction: column; justify-content: space-between; font-size: 10px; text-align: right; width: 18px; padding-bottom: 2px;">
              <span>{{ maxChartValue }}</span>
              <span>0</span>
            </div>
            <div class="chart-canvas-wrapper" style="flex: 1; height: 100%; position: relative;">
              <div class="chart-grid-lines" style="position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: space-between; pointer-events: none;">
                <div class="grid-line-dash"></div>
                <div class="grid-line-dash"></div>
              </div>
              <div class="bars-container-flex" style="position: absolute; inset: 0; display: flex; justify-content: space-around; align-items: flex-end;">
                <div v-for="bar in chartData" :key="bar.name" style="display: flex; flex-direction: column; align-items: center; gap: 6px; height: 100%; justify-content: flex-end; width: 30px;">
                  <div class="bar-visual-container" style="flex: 1; width: 10px; display: flex; align-items: flex-end; justify-content: center; background: rgba(0, 0, 0, 0.02); border-radius: 9999px;">
                    <div 
                      class="bar-filled-pill" 
                      :style="{ height: `${(bar.value / maxChartValue) * 100}%` }"
                      style="width: 10px; min-height: 4px;"
                    ></div>
                  </div>
                  <span style="font-size: 10px; font-weight: 600; color: var(--color-text-secondary);">{{ bar.name }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Tasks for Mobile (Laboran Only) -->
        <div v-if="user.role === 'Laboran'" class="mobile-section-card glass-panel" style="margin-top: 16px; padding: 18px; display: flex; flex-direction: column; gap: 14px; margin-bottom: 80px;">
          <h3 style="font-family: var(--font-primary); font-size: 14px; font-weight: 750; color: var(--color-text-primary);">
            Tugas Cepat
          </h3>
          <div class="action-steps-list" style="margin-top: 0; display: flex; flex-direction: column; gap: 12px;">
            <div 
              @click="$emit('change-menu', 'Approval')" 
              class="step-item" 
              style="padding: 12px; background: rgba(0, 0, 0, 0.015); border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.02);"
            >
              <div class="step-bullet" :class="{ 'has-pending': pendingApprovalsCount > 0 }" style="width: 20px; height: 20px;">
                <Clock :size="10" />
              </div>
              <div class="step-content">
                <h5 style="font-size: 12.5px;">Persetujuan Peminjaman</h5>
                <p style="font-size: 11px;">Terdapat {{ pendingApprovalsCount }} pengajuan baru</p>
              </div>
            </div>
            <div 
              @click="$emit('change-menu', 'Pengembalian')" 
              class="step-item" 
              style="padding: 12px; background: rgba(0, 0, 0, 0.015); border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.02);"
            >
              <div class="step-bullet" style="width: 20px; height: 20px;">
                <Wrench :size="10" />
              </div>
              <div class="step-content">
                <h5 style="font-size: 12.5px;">Scan Pengembalian</h5>
                <p style="font-size: 11px;">Verifikasi pengembalian alat</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </template>
  </div>
</template>

<style scoped>
.dashboard-laboran {
  display: flex !important;
  flex-direction: column !important;
  gap: 32px !important;
  width: 100% !important;
}

/* Glassmorphism Panel base */
.glass-panel {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur-webkit);
  border-radius: var(--radius-lg);
  padding: 24px;
  box-shadow: var(--shadow-soft);
  transition: var(--transition-smooth);
}

.glass-panel:hover {
  border-color: var(--color-action-primary);
  box-shadow: var(--shadow-hover);
}

/* Welcome Greetings Banner */
.dashboard-welcome-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  border-radius: var(--radius-xl);
  padding: 32px;
}

.welcome-card-left {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.welcome-greeting {
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--color-action-primary);
}

.welcome-title {
  font-family: var(--font-display);
  font-size: 2.5rem;
  font-style: italic;
  font-weight: 400;
  color: var(--color-text-primary);
  line-height: 1.1;
  letter-spacing: -0.5px;
}

.welcome-subtitle {
  font-size: 0.95rem;
  color: var(--color-text-secondary);
  line-height: 1.5;
}

.text-blue {
  color: var(--color-action-primary);
  font-weight: 600;
}

.text-amber {
  color: var(--color-warning);
  font-weight: 600;
}

.welcome-card-right {
  display: flex;
  align-items: center;
}

.live-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(16, 185, 129, 0.08);
  color: #10B981;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 6px 14px;
  border-radius: 9999px;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.pulse-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10B981;
  position: relative;
}

.pulse-dot::after {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  border: 2px solid rgba(16, 185, 129, 0.4);
  animation: pulse-ring 1.5s cubic-bezier(0.215, 0.610, 0.355, 1) infinite;
}

@keyframes pulse-ring {
  0% { transform: scale(0.5); opacity: 1; }
  100% { transform: scale(1.5); opacity: 0; }
}

/* Quick Metrics Grid */
.stats-row-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.stats-row-grid.three-columns {
  grid-template-columns: repeat(3, 1fr);
}

.stat-card {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 24px;
}

.stat-card-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.stat-icon-box {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-blue-light {
  background: rgba(70, 112, 143, 0.08);
}
.bg-green-light {
  background: rgba(16, 185, 129, 0.08);
}
.bg-amber-light {
  background: rgba(245, 158, 11, 0.08);
}
.bg-red-light {
  background: rgba(239, 68, 68, 0.08);
}

.text-blue {
  color: var(--color-action-primary);
}
.text-green {
  color: #10B981;
}
.text-amber {
  color: #f59e0b;
}
.text-red {
  color: #ef4444;
}

.stat-card-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-text-secondary);
}

.stat-card-body {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: auto;
}

.stat-card-value {
  font-family: var(--font-number);
  font-size: 2.25rem;
  font-weight: 800;
  color: var(--color-text-primary);
  line-height: 1;
}

.stat-card-desc {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
}

.highlight-glow {
  border-color: rgba(245, 158, 11, 0.4);
  box-shadow: 0 4px 20px rgba(245, 158, 11, 0.08);
}

.highlight-glow-red {
  border-color: rgba(239, 68, 68, 0.4);
  box-shadow: 0 4px 20px rgba(239, 68, 68, 0.08);
}

.red-text-giant {
  color: #ef4444;
}

/* Main Panels Layout Grid */
.main-dashboard-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

.main-dashboard-grid.two-columns {
  grid-template-columns: repeat(4, 1fr);
}

.col-span-3 {
  grid-column: span 3;
}

.col-span-2 {
  grid-column: span 2;
}

.col-span-1 {
  grid-column: span 1;
}

/* Flex layout helper for left side grouping */
.flex-column-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.right-column-panel {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.panel-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  padding-bottom: 16px;
  gap: 16px;
  flex-wrap: wrap;
}

.panel-card-title-serif {
  font-family: var(--font-display);
  font-size: 1.5rem;
  font-style: italic;
  font-weight: 400;
  color: var(--color-text-primary);
  margin-bottom: 4px;
}

.panel-card-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: 16px;
}

.panel-card-subtitle {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
}

.panel-header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.search-bar-compact {
  border: 1px solid rgba(0, 0, 0, 0.08);
  background: rgba(255, 255, 255, 0.5);
  border-radius: var(--radius-sm);
  padding: 6px 12px;
  font-family: var(--font-primary);
  font-size: 0.8125rem;
  color: var(--color-text-primary);
  outline: none;
  transition: var(--transition-fast);
  width: 180px;
}

.search-bar-compact:focus {
  border-color: var(--color-action-primary);
  background: white;
}

.loans-count-pill {
  background: rgba(70, 112, 143, 0.06);
  color: var(--color-action-primary);
  font-size: 0.75rem;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 9999px;
  white-space: nowrap;
}

/* Loan Rows */
.loans-list-wrapper {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.loan-row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: rgba(255, 255, 255, 0.4);
  border: 1px solid rgba(0, 0, 0, 0.03);
  border-radius: var(--radius-md);
  transition: var(--transition-smooth);
}

.loan-row-item:hover {
  background: white;
  border-color: var(--glass-border);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
}

.loan-row-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

/* Borrower Avatar initials circles */
.borrower-avatar-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(70, 112, 143, 0.08);
  color: var(--color-action-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-primary);
  font-size: 0.8125rem;
  font-weight: 700;
  flex-shrink: 0;
  border: 1.5px solid rgba(70, 112, 143, 0.15);
}

.category-avatar {
  background: rgba(16, 185, 129, 0.08);
  color: #10B981;
  border-color: rgba(16, 185, 129, 0.15);
}

.pending-avatar {
  background: rgba(245, 158, 11, 0.08);
  color: #f59e0b;
  border-color: rgba(245, 158, 11, 0.15);
}

.loan-trx-code {
  font-family: var(--font-number);
  font-size: 0.6875rem;
  font-weight: 700;
  color: var(--color-action-primary);
  background: rgba(70, 112, 143, 0.08);
  padding: 3px 8px;
  border-radius: 4px;
  letter-spacing: 0.5px;
  display: inline-block;
  vertical-align: middle;
}

.loan-row-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.loan-borrower-name {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--color-text-primary);
  line-height: 1.2;
}

.loan-item-name {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
}

.loan-row-right {
  display: flex;
  align-items: center;
  gap: 24px;
}

.loan-due-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.due-label {
  font-size: 0.6875rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.due-date-value {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-secondary);
}

.due-date-value.late-text {
  color: #ef4444;
  font-weight: 700;
}

.loan-badge {
  font-size: 0.6875rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 9999px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.loan-badge.dipinjam,
.loan-badge.approved {
  background: rgba(16, 185, 129, 0.08);
  color: #10B981;
}

.loan-badge.terlambat,
.loan-badge.rejected {
  background: rgba(239, 68, 68, 0.08);
  color: #ef4444;
}

.loan-badge.pending {
  background: rgba(245, 158, 11, 0.08);
  color: #f59e0b;
}

.fine-warning-badge {
  background: rgba(239, 68, 68, 0.08);
  color: #ef4444;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: var(--radius-sm);
  white-space: nowrap;
}

.loan-row-item.loan-row-late {
  background: rgba(239, 68, 68, 0.02);
  border-color: rgba(239, 68, 68, 0.1);
}

.loan-row-item.loan-row-late:hover {
  background: rgba(239, 68, 68, 0.04);
  border-color: rgba(239, 68, 68, 0.2);
}

/* Category dynamic avatar coloring */
.borrower-avatar-circle.avatar-blue {
  background: rgba(70, 112, 143, 0.08);
  color: var(--color-action-primary);
  border-color: rgba(70, 112, 143, 0.15);
}
.borrower-avatar-circle.avatar-purple {
  background: rgba(139, 92, 246, 0.08);
  color: #8b5cf6;
  border-color: rgba(139, 92, 246, 0.15);
}
.borrower-avatar-circle.avatar-amber {
  background: rgba(245, 158, 11, 0.08);
  color: #f59e0b;
  border-color: rgba(245, 158, 11, 0.15);
}
.borrower-avatar-circle.avatar-green {
  background: rgba(16, 185, 129, 0.08);
  color: #10B981;
  border-color: rgba(16, 185, 129, 0.15);
}

.btn-expand-list {
  background: transparent;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: var(--radius-sm);
  padding: 10px;
  color: var(--color-action-primary);
  font-family: var(--font-primary);
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition-smooth);
  text-align: center;
  margin-top: 8px;
}

.btn-expand-list:hover {
  background: rgba(70, 112, 143, 0.04);
  border-color: var(--color-action-primary);
}

.empty-list-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 48px 24px;
  color: var(--color-text-secondary);
  font-size: 0.875rem;
  text-align: center;
}

/* System Alerts */
.alert-box-card {
  border-radius: var(--radius-lg);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.warning-alert {
  background: rgba(239, 68, 68, 0.06);
  border: 1px solid rgba(239, 68, 68, 0.15);
}

.alert-box-header {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #b91c1c;
}

.alert-box-header h4 {
  font-size: 0.875rem;
  font-weight: 700;
}

.alert-box-icon {
  flex-shrink: 0;
}

.alert-box-body {
  font-size: 0.8125rem;
  color: #7f1d1d;
  line-height: 1.4;
}

/* Quick Navigation / Tasks */
.action-steps-card {
  padding: 24px;
}

.action-steps-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 16px;
}

.step-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  cursor: pointer;
  padding: 10px;
  border-radius: var(--radius-sm);
  transition: var(--transition-smooth);
}

.step-item:hover {
  background: rgba(70, 112, 143, 0.04);
}

.step-bullet {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(70, 112, 143, 0.08);
  color: var(--color-action-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  position: relative;
}

.step-bullet.has-pending::after {
  content: '';
  position: absolute;
  top: -2px;
  right: -2px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #f59e0b;
  border: 2px solid white;
}

.step-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.step-content h5 {
  font-size: 0.8125rem;
  font-weight: 700;
  color: var(--color-text-primary);
}

.step-content p {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
}

/* Chart Panel Styles */
.chart-dashboard-panel {
  padding: 24px 32px;
}

.chart-panel-header {
  margin-bottom: 32px;
}

.chart-area-visual {
  display: flex;
  gap: 16px;
  height: 220px;
  position: relative;
}

.chart-y-axis-vals {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 180px;
  font-family: var(--font-number);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-secondary);
  text-align: right;
  width: 24px;
  padding-bottom: 2px;
}

.chart-canvas-wrapper {
  flex: 1;
  height: 180px;
  position: relative;
}

.chart-grid-lines {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  pointer-events: none;
}

.grid-line-dash {
  width: 100%;
  height: 1px;
  background-image: linear-gradient(90deg, rgba(0, 0, 0, 0.04) 50%, transparent 50%);
  background-size: 8px 1px;
}

.bars-container-flex {
  position: absolute;
  inset: 0;
  display: flex;
  justify-content: space-around;
  align-items: flex-end;
  z-index: 1;
}

.bar-column-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  height: 100%;
  justify-content: flex-end;
  width: 50px;
}

.bar-visual-container {
  flex: 1;
  width: 16px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  background: rgba(0, 0, 0, 0.02);
  border-radius: 9999px;
  margin: 0 auto;
  position: relative;
  padding-bottom: 2px;
}

.bar-filled-pill {
  width: 16px;
  background: linear-gradient(180deg, var(--color-action-primary) 0%, #365670 100%);
  border-radius: 9999px;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: pointer;
  position: relative;
  min-height: 4px;
}

.bar-filled-pill:hover {
  background: #2D4D65;
  filter: drop-shadow(0 2px 8px rgba(70, 112, 143, 0.25));
}

.visual-tooltip {
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translate(-50%, -8px) scale(0.9);
  background: var(--color-text-primary);
  color: white;
  font-size: 0.6875rem;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 4px;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: var(--transition-smooth);
  z-index: 10;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.bar-filled-pill:hover .visual-tooltip {
  opacity: 1;
  transform: translate(-50%, -8px) scale(1);
}

.bar-column-label {
  font-family: var(--font-primary);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-secondary);
}

.chart-legend-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 24px;
  font-size: 0.75rem;
  color: var(--color-text-secondary);
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--color-action-primary);
}

/* User role actions / buttons */
.btn-primary-action {
  background: var(--color-action-primary);
  color: white;
  border: none;
  border-radius: var(--radius-sm);
  padding: 10px 20px;
  font-family: var(--font-primary);
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: var(--transition-smooth);
}

.btn-primary-action:hover {
  background: #365670;
  transform: translateY(-1px);
  box-shadow: var(--shadow-hover);
}

.btn-primary-action:active {
  transform: translateY(0);
}

/* Responsive details */
@media (max-width: 1024px) {
  .stats-row-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .stats-row-grid.three-columns {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .main-dashboard-grid, .main-dashboard-grid.two-columns {
    grid-template-columns: 1fr;
  }
  
  .col-span-3, .col-span-2, .col-span-1 {
    grid-column: span 1;
  }
}

@media (max-width: 640px) {
  .dashboard-welcome-card {
    flex-direction: column;
    align-items: flex-start;
    padding: 24px;
  }
  
  .stats-row-grid, .stats-row-grid.three-columns {
    grid-template-columns: 1fr;
  }
  
  .welcome-title {
    font-size: 2rem;
  }
  
  .panel-card-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .panel-header-actions {
    width: 100%;
    justify-content: space-between;
  }
  
  .search-bar-compact {
    width: 100%;
    flex: 1;
  }
}
</style>
