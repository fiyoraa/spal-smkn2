<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Search, Download } from '@lucide/vue';

const props = defineProps({
  loansData: {
    type: Array,
    default: () => []
  }
});

const searchQuery = ref('');
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

const filteredLoans = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  if (!query) return props.loansData;
  return props.loansData.filter(item => 
    item.userName.toLowerCase().includes(query) ||
    item.itemName.toLowerCase().includes(query) ||
    item.id.toLowerCase().includes(query)
  );
});

const getStatusClass = (status) => {
  switch (status.toLowerCase()) {
    case 'terlambat':
      return 'status-terlambat';
    case 'selesai':
      return 'status-selesai';
    case 'dipinjam':
      return 'status-dipinjam';
    default:
      return '';
  }
};
</script>

<template>
  <div class="riwayat-container">
    <div class="view-header">
      <div>
        <h2>Riwayat Aktivitas & Peminjaman</h2>
        <p class="subtitle">Daftar log riwayat lengkap seluruh kegiatan laboratorium dari waktu ke waktu.</p>
      </div>
      <button class="btn-secondary">
        <Download :size="14" /> Ekspor Log (.xlsx)
      </button>
    </div>

    <!-- Search bar & filter (Whitespace separated) -->
    <div class="search-section">
      <div class="search-wrapper">
        <Search class="search-icon" :size="16" />
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Cari berdasarkan ID Pinjam, nama siswa, atau nama alat..." 
          class="search-input"
        />
      </div>
    </div>

    <!-- DESKTOP TABLE VIEW -->
    <div v-if="!isMobile" class="desktop-only table-card">
      <table class="modern-table">
        <thead>
          <tr>
            <th>ID Pinjam</th>
            <th>Nama Peminjam</th>
            <th>Alat Laboratorium</th>
            <th>Tanggal Pinjam</th>
            <th>Batas Pengembalian</th>
            <th>Denda</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="loan in filteredLoans" :key="loan.id">
            <td class="td-mono td-id">{{ loan.id }}</td>
            <td>
              <div class="user-info">
                <span class="user-name">{{ loan.userName }}</span>
                <span class="user-class">{{ loan.className }}</span>
              </div>
            </td>
            <td class="td-bold">{{ loan.itemName }}</td>
            <td class="td-mono">{{ loan.dateOut }}</td>
            <td class="td-mono">{{ loan.dateDue }}</td>
            <td class="td-mono" :class="{ 'text-danger-bold': loan.fine !== '-' }">{{ loan.fine }}</td>
            <td>
              <span class="badge-status" :class="getStatusClass(loan.status)">
                {{ loan.status }}
              </span>
            </td>
          </tr>
          <tr v-if="filteredLoans.length === 0">
            <td colspan="7" class="empty-row">
              Tidak ditemukan data riwayat untuk "{{ searchQuery }}"
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- MOBILE CARDS VIEW -->
    <div v-else class="mobile-only mobile-cards-list">
      <div v-for="loan in filteredLoans" :key="loan.id" class="mobile-loan-card glass-panel">
        <div class="card-header">
          <span class="loan-id td-mono">{{ loan.id }}</span>
          <span class="badge-status" :class="getStatusClass(loan.status)">
            {{ loan.status }}
          </span>
        </div>
        <div class="card-body">
          <h3 class="item-name">{{ loan.itemName }}</h3>
          <div class="borrower-info">
            <span class="borrower-name">{{ loan.userName }}</span>
            <span class="borrower-class">&bull; {{ loan.className }}</span>
          </div>
          <div class="date-row">
            <div class="date-item">
              <span class="date-label">Pinjam</span>
              <span class="date-val td-mono">{{ loan.dateOut }}</span>
            </div>
            <div class="date-item">
              <span class="date-label">Batas Kembali</span>
              <span class="date-val td-mono" :class="{ 'text-danger-bold': loan.status.toLowerCase() === 'terlambat' }">
                {{ loan.dateDue }}
              </span>
            </div>
          </div>
          <div v-if="loan.fine !== '-'" class="fine-box">
            <span class="fine-label">Denda Aktif</span>
            <span class="fine-val text-danger-bold">{{ loan.fine }}</span>
          </div>
        </div>
      </div>
      <div v-if="filteredLoans.length === 0" class="empty-row-mobile">
        Tidak ditemukan data riwayat untuk "{{ searchQuery }}"
      </div>
    </div>
  </div>
</template>

<style scoped>
.riwayat-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.view-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.view-header h2 {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-primary);
}

.view-header .subtitle {
  font-size: 13.5px;
  color: var(--text-secondary);
  margin-top: 2px;
}

.search-section {
  display: flex;
  align-items: center;
}

.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  max-width: 480px;
}

.search-icon {
  position: absolute;
  left: 14px;
  color: var(--text-muted);
}

.search-input {
  width: 100%;
  background: transparent;
  border: 1px solid rgba(0, 0, 0, 0.06);
  padding: 10px 14px 10px 40px;
  border-radius: 8px;
  font-family: var(--font-primary);
  font-size: 13.5px;
  color: var(--text-primary);
  outline: none;
  transition: var(--transition-smooth);
}

.search-input:focus {
  border-color: var(--color-primary);
  background: white;
}

/* Card layout wrapper (Hapus shadow berat & border radius raksasa) */
.table-card {
  background: transparent;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  margin-top: 12px;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  color: var(--text-primary);
}

.user-class {
  font-size: 11px;
  color: var(--text-muted);
  font-weight: 500;
}

/* Badge Status: Pill shaped, bold but small, muted colors */
.badge-status {
  display: inline-flex;
  font-size: 10px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 50px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  border: none;
}

.status-terlambat {
  background: rgba(192, 92, 62, 0.08);
  color: #C05C3E; /* Muted terracotta/brick red */
}

.status-selesai {
  background: rgba(16, 185, 129, 0.08);
  color: #10B981; /* Muted Green */
}

.status-dipinjam {
  background: rgba(70, 112, 143, 0.08);
  color: #46708F; /* Muted Steel Blue */
}

.empty-row {
  text-align: center;
  padding: 40px;
  color: var(--text-muted);
  font-size: 13.5px;
}

.text-danger-bold {
  color: #C05C3E !important;
  font-weight: 700;
}

.btn-secondary {
  background: transparent;
  border: 1px solid rgba(0, 0, 0, 0.08);
  color: var(--text-primary);
  font-family: var(--font-primary);
  font-size: 12.5px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: var(--transition-smooth);
}

.btn-secondary:hover {
  background: rgba(0, 0, 0, 0.02);
  border-color: rgba(0, 0, 0, 0.15);
}

.btn-secondary:active {
  transform: scale(0.98);
}

/* Mobile specific styling */
.mobile-only.mobile-cards-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 12px;
}

.mobile-loan-card {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur-webkit);
  border-radius: var(--radius-lg);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  box-shadow: var(--shadow-soft);
  transition: var(--transition-smooth);
}

.mobile-loan-card:hover, .mobile-loan-card:active {
  border-color: var(--color-action-primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-hover);
}

.mobile-loan-card .card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  padding-bottom: 10px;
}

.mobile-loan-card .loan-id {
  font-size: 11px;
  font-weight: 700;
  color: var(--color-action-primary);
  background: rgba(70, 112, 143, 0.08);
  padding: 4px 10px;
  border-radius: 6px;
  letter-spacing: 0.5px;
}

.mobile-loan-card .item-name {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.25;
}

.mobile-loan-card .borrower-info {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  color: var(--text-secondary);
}

.mobile-loan-card .borrower-name {
  font-weight: 600;
  color: var(--text-primary);
}

.mobile-loan-card .date-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  background: rgba(0, 0, 0, 0.02);
  padding: 12px 16px;
  border-radius: var(--radius-sm);
}

.mobile-loan-card .date-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.mobile-loan-card .date-label {
  font-size: 9px;
  text-transform: uppercase;
  color: var(--text-muted);
  font-weight: 700;
  letter-spacing: 0.5px;
}

.mobile-loan-card .date-val {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-primary);
}

.mobile-loan-card .fine-box {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid rgba(0, 0, 0, 0.04);
  padding-top: 12px;
}

.mobile-loan-card .fine-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
}

.mobile-loan-card .fine-val {
  font-size: 13px;
}

.empty-row-mobile {
  text-align: center;
  padding: 48px 24px;
  color: var(--text-muted);
  font-size: 13.5px;
}
</style>
