<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import QRCode from 'qrcode';
import MainLayout from './components/MainLayout.vue';
import DashboardLaboran from './components/DashboardLaboran.vue';
import PeminjamanUser from './components/PeminjamanUser.vue';
import PengembalianAlat from './components/PengembalianAlat.vue';
import ApprovalPeminjaman from './components/ApprovalPeminjaman.vue';
import Login from './components/Login.vue';
import Register from './components/Register.vue';
import RiwayatPeminjaman from './components/RiwayatPeminjaman.vue';
import { 
  TrendingUp, 
  Users, 
  AlertTriangle, 
  CheckCircle2, 
  Plus, 
  ArrowUpRight, 
  Activity, 
  FileText, 
  Wrench, 
  UserX,
  FileSpreadsheet,
  Download,
  Calendar,
  Layers,
  ArrowRight,
  ShieldCheck,
  Check,
  X,
  Clock,
  ExternalLink,
  Beaker,
  Cpu,
  Database,
  QrCode
} from '@lucide/vue';

// Active menu state
const activeMenu = ref(localStorage.getItem('activeMenu') || 'Dashboard');
watch(activeMenu, (newVal) => {
  localStorage.setItem('activeMenu', newVal);
});

// Auth states
const isLoggedIn = ref(localStorage.getItem('isLoggedIn') === 'true');
const authView = ref('Login');
const currentUser = ref(JSON.parse(localStorage.getItem('currentUser') || 'null'));

watch(isLoggedIn, (newVal) => {
  localStorage.setItem('isLoggedIn', newVal ? 'true' : 'false');
  if (!newVal) {
    localStorage.removeItem('currentUser');
    currentUser.value = null;
    activeMenu.value = 'Dashboard';
  }
});

const allTransactions = ref([]);

const loadData = async () => {
  if (!isLoggedIn.value || !currentUser.value) return;
  
  // 1. Fetch equipments
  try {
    const eqResp = await fetch('/api/equipment');
    const eqResult = await eqResp.json();
    if (eqResult.status === 'success') {
      const itemsList = [];
      for (const item of eqResult.data) {
        let qr = item.qr_code;
        if (!qr || qr.startsWith('data:image/svg+xml')) {
          try {
            qr = await QRCode.toDataURL(item.code);
          } catch (qrErr) {
            console.error('Local QR generation error:', qrErr);
          }
        }
        itemsList.push({
          id: item.code,
          dbId: item.id,
          name: item.name,
          category: item.category.charAt(0).toUpperCase() + item.category.slice(1).toLowerCase(),
          status: item.status,
          loc: item.location,
          qr_code: qr
        });
      }
      items.value = itemsList;
    }
  } catch (err) {
    console.error('Error fetching equipment:', err);
  }
  
  // 2. Fetch approvals (for Laboran) or my submissions (for Guru/Siswa)
  try {
    const url = currentUser.value.role === 'Laboran' 
      ? '/api/borrowings' 
      : `/api/borrowings?user_id=${currentUser.value.id}`;
    
    const appResp = await fetch(url);
    const appResult = await appResp.json();
    if (appResult.status === 'success') {
      approvals.value = appResult.data;
    }
  } catch (err) {
    console.error('Error fetching approvals:', err);
  }
  
  // 3. Fetch transactions
  try {
    const url = currentUser.value.role === 'Laboran'
      ? '/api/transactions'
      : `/api/transactions?user_id=${currentUser.value.id}`;
    const trxResp = await fetch(url);
    const trxResult = await trxResp.json();
    if (trxResult.status === 'success') {
      loans.value = trxResult.data.filter(trx => trx.status === 'Dipinjam' || trx.status === 'Terlambat');
      allTransactions.value = trxResult.data;
    }
  } catch (err) {
    console.error('Error fetching transactions:', err);
  }

  // 4. Fetch blacklist (only for Laboran)
  if (currentUser.value.role === 'Laboran') {
    try {
      const blResp = await fetch('/api/blacklist');
      const blResult = await blResp.json();
      if (blResult.status === 'success') {
        blacklist.value = blResult.data;
      }
    } catch (err) {
      console.error('Error fetching blacklist:', err);
    }
  }
};

onMounted(() => {
  if (isLoggedIn.value) {
    if (!currentUser.value) {
      isLoggedIn.value = false;
    } else {
      loadData();
    }
  }
});

const handleLoginSuccess = (user) => {
  currentUser.value = user;
  localStorage.setItem('currentUser', JSON.stringify(user));
  isLoggedIn.value = true;
  activeMenu.value = 'Dashboard';
  loadData();
};

const handleLogout = () => {
  isLoggedIn.value = false;
};

// Search query reference
const searchFilter = ref('');

// Data for Alat (Equipment) - Dynamically loaded from DB
const items = ref([]);

const getCategoryIcon = (category) => {
  switch (category) {
    case 'Biologi':
    case 'Kimia':
    case 'Biokimia':
      return Beaker;
    case 'Elektronika':
      return Cpu;
    case 'Analisis':
      return FileText;
    default:
      return Database;
  }
};

// Data for Peminjaman (Loans) - Dynamically loaded from DB
const loans = ref([]);

// Data for Approval - Dynamically loaded from DB
const approvals = ref([]);

// Data for Blacklist - Dynamically loaded from DB
const blacklist = ref([]);

// Log of approvals processed during the session
const approvalHistory = ref([]);

// Action Handlers
const handleApprove = (id) => {
  const index = approvals.value.findIndex(a => a.id === id);
  if (index !== -1) {
    const item = approvals.value[index];
    approvalHistory.value.push({ ...item, action: 'Approved', processedAt: 'Baru saja' });
    approvals.value.splice(index, 1);
  }
};

const handleReject = (id) => {
  const index = approvals.value.findIndex(a => a.id === id);
  if (index !== -1) {
    const item = approvals.value[index];
    approvalHistory.value.push({ ...item, action: 'Rejected', processedAt: 'Baru saja' });
    approvals.value.splice(index, 1);
  }
};

// Input state for new loan
const newReturnBarcode = ref('');
const returnSuccessMessage = ref('');
const handleReturnSubmit = () => {
  if (newReturnBarcode.value.trim() === '') return;
  returnSuccessMessage.value = `Alat dengan ID "${newReturnBarcode.value}" berhasil diverifikasi dan dikembalikan!`;
  newReturnBarcode.value = '';
  setTimeout(() => {
    returnSuccessMessage.value = '';
  }, 3000);
};

// Report Categories (computed dynamically from database transactions)
const reportStats = computed(() => {
  const monthsList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  
  const monthlyData = {};
  monthsList.forEach(m => {
    monthlyData[m] = { loans: 0, returns: 0 };
  });

  allTransactions.value.forEach(trx => {
    if (trx.dateOut) {
      const dOut = new Date(trx.dateOut);
      if (!isNaN(dOut.getTime())) {
        const monthName = monthsList[dOut.getMonth()];
        monthlyData[monthName].loans++;
      }
    }
    if (trx.dateReturned) {
      const dRet = new Date(trx.dateReturned);
      if (!isNaN(dRet.getTime())) {
        const monthName = monthsList[dRet.getMonth()];
        monthlyData[monthName].returns++;
      }
    }
  });

  // Collect months that have activity
  const result = [];
  monthsList.forEach(m => {
    if (monthlyData[m].loans > 0 || monthlyData[m].returns > 0) {
      result.push({
        month: m,
        loans: monthlyData[m].loans,
        returns: monthlyData[m].returns
      });
    }
  });

  if (result.length === 0) {
    return monthsList.slice(0, 6).map(m => ({ month: m, loans: 0, returns: 0 }));
  }
  
  return result;
});

// Recent Lab Activities - Computed dynamically from database transactions
const activities = computed(() => {
  return allTransactions.value.slice(0, 10).map((trx, index) => {
    let text = '';
    let type = '';
    if (trx.status === 'Kembali') {
      text = `${trx.userName} mengembalikan ${trx.itemName}`;
      type = 'return';
    } else if (trx.status === 'Terlambat') {
      text = `Pengembalian ${trx.itemName} oleh ${trx.userName} terlambat`;
      type = 'warning';
    } else {
      text = `${trx.userName} meminjam ${trx.itemName}`;
      type = 'borrow';
    }
    
    return {
      id: index,
      type: type,
      text: text,
      time: trx.dateOut
    };
  });
});

// Tambah Alat Baru States
const showAddModal = ref(false);
const newItemName = ref('');
const newItemCategory = ref('laptop');
const newItemLoc = ref('');
const generatedQrPreview = ref('');
const generatedCode = ref('');

// Zoom QR Code States
const showZoomQrModal = ref(false);
const zoomQrItem = ref(null);
const handleZoomQr = (item) => {
  zoomQrItem.value = item;
  showZoomQrModal.value = true;
};

const handleSaveNewItem = async () => {
  try {
    const response = await fetch('/api/equipment', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        name: newItemName.value,
        category: newItemCategory.value,
        location: newItemLoc.value
      })
    });
    
    const result = await response.json();
    if (result.status === 'success') {
      let qr = result.data.qr_code;
      if (!qr || qr.startsWith('data:image/svg+xml')) {
        try {
          qr = await QRCode.toDataURL(result.data.code);
        } catch (qrErr) {
          console.error('Local QR generation error on save:', qrErr);
        }
      }
      items.value.push({
        id: result.data.code,
        dbId: result.data.id,
        name: result.data.name,
        category: result.data.category.charAt(0).toUpperCase() + result.data.category.slice(1).toLowerCase(),
        status: 'Tersedia',
        loc: result.data.location,
        qr_code: qr
      });
      
      generatedCode.value = result.data.code;
      generatedQrPreview.value = qr;
      
      setTimeout(() => {
        showAddModal.value = false;
        newItemName.value = '';
        newItemCategory.value = 'laptop';
        newItemLoc.value = '';
        generatedQrPreview.value = '';
        generatedCode.value = '';
      }, 1800);
    } else {
      alert(result.message || 'Gagal menyimpan alat.');
    }
  } catch (error) {
    console.error('Error saving new equipment:', error);
    // Offline / Standalone Mock Fallback
    const randomId = Math.floor(Math.random() * 900) + 100;
    const mockCode = `SMKN2-LAB-${newItemCategory.value.toUpperCase()}-2026-0${randomId}`;
    
    try {
      const mockQr = await QRCode.toDataURL(mockCode);
      items.value.push({
        id: mockCode,
        dbId: null,
        name: newItemName.value,
        category: newItemCategory.value.charAt(0).toUpperCase() + newItemCategory.value.slice(1).toLowerCase(),
        status: 'Tersedia',
        loc: newItemLoc.value,
        qr_code: mockQr
      });
      
      generatedCode.value = mockCode;
      generatedQrPreview.value = mockQr;
    } catch (qrErr) {
      console.error('Error generating QR:', qrErr);
      const mockQrFallback = `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect width="200" height="200" fill="white"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="12">QR: ${mockCode}</text></svg>`;
      items.value.push({
        id: mockCode,
        dbId: null,
        name: newItemName.value,
        category: newItemCategory.value.charAt(0).toUpperCase() + newItemCategory.value.slice(1).toLowerCase(),
        status: 'Tersedia',
        loc: newItemLoc.value,
        qr_code: mockQrFallback
      });
      generatedCode.value = mockCode;
      generatedQrPreview.value = mockQrFallback;
    }
    
    setTimeout(() => {
      showAddModal.value = false;
      newItemName.value = '';
      newItemCategory.value = 'laptop';
      newItemLoc.value = '';
      generatedQrPreview.value = '';
      generatedCode.value = '';
    }, 1800);
  }
};

// Edit Equipment Modal States
const showEditModal = ref(false);
const editItemDbId = ref(null);
const editItemName = ref('');
const editItemCategory = ref('laptop');
const editItemLoc = ref('');
const editItemStatus = ref('Tersedia');

const handleEditClick = (item) => {
  editItemDbId.value = item.dbId;
  editItemName.value = item.name;
  editItemCategory.value = item.category.toLowerCase();
  editItemLoc.value = item.loc;
  editItemStatus.value = item.status;
  showEditModal.value = true;
};

const handleSaveEditItem = async () => {
  try {
    const response = await fetch(`/api/equipment/${editItemDbId.value}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        name: editItemName.value,
        category: editItemCategory.value,
        location: editItemLoc.value,
        status: editItemStatus.value
      })
    });
    
    const result = await response.json();
    if (result.status === 'success') {
      let qr = result.data.qr_code;
      if (!qr || qr.startsWith('data:image/svg+xml')) {
        try {
          qr = await QRCode.toDataURL(result.data.code);
        } catch (qrErr) {
          console.error('Local QR generation error on update:', qrErr);
        }
      }
      const idx = items.value.findIndex(item => item.dbId === editItemDbId.value);
      if (idx !== -1) {
        items.value[idx] = {
          id: result.data.code,
          dbId: result.data.id,
          name: result.data.name,
          category: result.data.category.charAt(0).toUpperCase() + result.data.category.slice(1).toLowerCase(),
          status: result.data.status,
          loc: result.data.location,
          qr_code: qr
        };
      }
      showEditModal.value = false;
      loadData();
    } else {
      alert(result.message || 'Gagal mengubah alat.');
    }
  } catch (error) {
    console.error('Error updating equipment:', error);
    alert('Terjadi kesalahan koneksi.');
  }
};

const handleDeleteItem = async (item) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus alat "${item.name}"?`)) {
    return;
  }
  try {
    const response = await fetch(`/api/equipment/${item.dbId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json'
      }
    });
    
    const result = await response.json();
    if (result.status === 'success') {
      loadData();
    } else {
      alert(result.message || 'Gagal menghapus alat.');
    }
  } catch (error) {
    console.error('Error deleting equipment:', error);
    alert('Terjadi kesalahan koneksi.');
  }
};
</script>

<template>
  <div v-if="!isLoggedIn">
    <Login 
      v-if="authView === 'Login'" 
      @login-success="user => handleLoginSuccess(user)" 
      @switch-view="view => authView = view" 
    />
    <Register 
      v-else 
      @register-success="user => handleLoginSuccess(user)" 
      @switch-view="view => authView = view" 
    />
  </div>
  <template v-else>
    <MainLayout :user="currentUser" v-model:activeMenu="activeMenu" @logout="handleLogout" @update-user="user => currentUser = user">
    
    <!-- DASHBOARD VIEW -->
    <div v-if="activeMenu === 'Dashboard'" class="view-content fade-in">
      <DashboardLaboran 
        :user="currentUser" 
        :activeLoans="loans" 
        :items="items" 
        :blacklist="blacklist" 
        :approvals="approvals" 
        :transactions="allTransactions"
        @refresh-data="loadData" 
        @change-menu="val => activeMenu = val"
      />
    </div>

    <!-- DATA ALAT VIEW (No Emojis, Clean category icons) -->
    <div v-else-if="activeMenu === 'Data Alat'" class="view-content fade-in">
      <div class="view-header">
        <div>
          <h2>Data Alat & Inventaris Lab</h2>
          <p class="subtitle">Daftar seluruh alat sains, perkakas listrik, dan instrumen laboratorium.</p>
        </div>
        <button @click="showAddModal = true" class="btn-primary">
          <Plus :size="16" /> Tambah Alat Baru
        </button>
      </div>

      <!-- Filters -->
      <div class="filters-bar glass-panel">
        <div class="search-box">
          <input type="text" placeholder="Filter nama alat atau kode..." class="filter-input" />
        </div>
        <div class="filter-options">
          <span class="filter-label">Kategori:</span>
          <select class="filter-select">
            <option>Semua Kategori</option>
            <option>Biologi</option>
            <option>Kimia</option>
            <option>Elektronika</option>
          </select>
        </div>
      </div>

      <!-- Grid of Equipments (Lucide icons instead of emojis) -->
      <div class="equipment-grid">
        <div v-for="item in items" :key="item.id" class="equipment-card glass-panel">
          <div class="card-image-box">
            <div class="card-icon-circle">
              <component :is="getCategoryIcon(item.category)" class="card-icon-svg" />
            </div>
            <span class="status-badge" :class="item.status.toLowerCase()">
              {{ item.status }}
            </span>
            <!-- Quick View QR Badge -->
            <button v-if="item.qr_code" @click="handleZoomQr(item)" class="qr-thumbnail-badge" title="Tampilkan QR Code">
              <QrCode :size="14" />
            </button>
          </div>
          <div class="card-body">
            <span class="card-category">{{ item.category }}</span>
            <h4 class="card-title">{{ item.name }}</h4>
            <div class="card-details">
              <p><span>ID:</span> {{ item.id }}</p>
              <p><span>Lokasi:</span> {{ item.loc }}</p>
            </div>
            <div class="card-actions">
              <button class="btn-secondary-sm" @click="handleEditClick(item)">Ubah</button>
              <button class="btn-danger-sm" @click="handleDeleteItem(item)">Hapus</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- PEMINJAMAN VIEW -->
    <div v-else-if="activeMenu === 'Peminjaman'" class="view-content fade-in">
      <PeminjamanUser :items="items" :user="currentUser" @refresh-data="loadData" />
    </div>

    <!-- APPROVAL VIEW -->
    <div v-else-if="activeMenu === 'Approval'" class="view-content fade-in">
      <ApprovalPeminjaman :user="currentUser" @refresh-data="loadData" />
    </div>

    <!-- PENGEMBALIAN VIEW -->
    <div v-else-if="activeMenu === 'Pengembalian'" class="view-content fade-in">
      <PengembalianAlat :user="currentUser" @refresh-data="loadData" />
    </div>

    <!-- RIWAYAT VIEW -->
    <div v-else-if="activeMenu === 'Riwayat'" class="view-content fade-in">
      <RiwayatPeminjaman :loansData="allTransactions" />
    </div>

    <!-- LAPORAN VIEW -->
    <div v-else-if="activeMenu === 'Laporan'" class="view-content fade-in">
      <div class="view-header">
        <div>
          <h2>Laporan Statistik Laboratorium</h2>
          <p class="subtitle">Visualisasi performa peminjaman alat, aktivitas laboratorium, dan tren bulanan.</p>
        </div>
        <div class="btn-group">
          <button class="btn-secondary"><FileText :size="14" /> Cetak PDF</button>
          <button class="btn-primary"><FileSpreadsheet :size="14" /> Ekspor Excel</button>
        </div>
      </div>

      <div class="reports-grid">
        <div class="report-main-card glass-panel">
          <h3>Statistik Kunjungan & Peminjaman (Semester Ini)</h3>
          <p class="report-card-subtitle">Perbandingan peminjaman aktif vs pengembalian per bulan.</p>
          
          <div class="chart-mockup-container">
            <div v-for="stat in reportStats" :key="stat.month" class="chart-bar-group">
              <span class="chart-bar-label">{{ stat.month }}</span>
              <div class="chart-bars">
                <div class="chart-bar borrow-bar" :style="`height: ${stat.loans * 1.2}px`" :title="`Peminjaman: ${stat.loans}`"></div>
                <div class="chart-bar return-bar" :style="`height: ${stat.returns * 1.2}px`" :title="`Pengembalian: ${stat.returns}`"></div>
              </div>
            </div>
          </div>
          <div class="chart-legend">
            <div class="legend-item"><span class="legend-dot borrow"></span> Peminjaman</div>
            <div class="legend-item"><span class="legend-dot return"></span> Pengembalian</div>
          </div>
        </div>

        <div class="report-side-column">
          <div class="mini-report-card glass-panel">
            <h4>Alat Paling Sering Dipinjam</h4>
            <div class="top-list">
              <div class="top-list-item">
                <span class="list-rank">1</span>
                <span class="list-name">Mikroskop Olympus CX23</span>
                <span class="list-count">48 Kali</span>
              </div>
              <div class="top-list-item">
                <span class="list-rank">2</span>
                <span class="list-name">Solder Listrik Weller WE1010</span>
                <span class="list-count">36 Kali</span>
              </div>
              <div class="top-list-item">
                <span class="list-rank">3</span>
                <span class="list-name">Oscilloscope Digital Rigol</span>
                <span class="list-count">24 Kali</span>
              </div>
            </div>
          </div>

          <div class="mini-report-card glass-panel">
            <h4>Tingkat Pengembalian Tepat Waktu</h4>
            <div class="percentage-display">
              <div class="percentage-circle">
                <span class="pct-val">96.4%</span>
                <span class="pct-sub">Tepat Waktu</span>
              </div>
              <p class="pct-desc">Sebagian besar siswa patuh mengembalikan alat sesuai tanggal batas waktu yang disepakati.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Table of Equipment Utilization (Archival Index Style) -->
      <div class="report-table-section" style="margin-top: 32px;">
        <h3 style="font-size: 15px; font-weight: 750; margin-bottom: 16px; color: var(--text-primary);">Rincian Utilisasi Inventaris</h3>
        <div class="table-card" style="border-top: 1px solid rgba(0, 0, 0, 0.05);">
          <table class="modern-table">
            <thead>
              <tr>
                <th>Kode Alat</th>
                <th>Nama Alat</th>
                <th>Kategori</th>
                <th>Total Pinjam</th>
                <th>Kondisi Terakhir</th>
                <th>Utilisasi</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="td-mono td-id">ALT-101</td>
                <td class="td-bold">Mikroskop Olympus CX23</td>
                <td>Biologi</td>
                <td class="td-mono">48 Kali</td>
                <td>Sangat Baik</td>
                <td>
                  <span class="badge-status status-selesai">Tinggi</span>
                </td>
              </tr>
              <tr>
                <td class="td-mono td-id">ALT-103</td>
                <td class="td-bold">Solder Listrik Weller WE1010</td>
                <td>Elektronika</td>
                <td class="td-mono">36 Kali</td>
                <td>Baik</td>
                <td>
                  <span class="badge-status status-selesai">Tinggi</span>
                </td>
              </tr>
              <tr>
                <td class="td-mono td-id">ALT-102</td>
                <td class="td-bold">Oscilloscope Digital Rigol</td>
                <td>Elektronika</td>
                <td class="td-mono">24 Kali</td>
                <td>Baik</td>
                <td>
                  <span class="badge-status status-dipinjam">Sedang</span>
                </td>
              </tr>
              <tr>
                <td class="td-mono td-id">ALT-106</td>
                <td class="td-bold">Multimeter Fluke 115</td>
                <td>Elektronika</td>
                <td class="td-mono">18 Kali</td>
                <td>Cukup</td>
                <td>
                  <span class="badge-status status-dipinjam">Sedang</span>
                </td>
              </tr>
              <tr>
                <td class="td-mono td-id">ALT-104</td>
                <td class="td-bold">Gelas Kimia Pyrex 500ml</td>
                <td>Kimia</td>
                <td class="td-mono">12 Kali</td>
                <td>Perawatan</td>
                <td>
                  <span class="badge-status status-terlambat">Rendah</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- BLACKLIST VIEW -->
    <div v-else-if="activeMenu === 'Blacklist'" class="view-content fade-in">
      <div class="view-header">
        <div>
          <h2>Daftar Hitam Pengguna (Blacklist)</h2>
          <p class="subtitle">Siswa yang hak peminjamannya ditangguhkan akibat pelanggaran regulasi laboratorium.</p>
        </div>
        <button class="btn-danger-action">
          <Plus :size="16" /> Tambah Pelanggaran
        </button>
      </div>

      <div class="blacklist-grid">
        <div v-for="bl in blacklist" :key="bl.id" class="blacklist-card glass-panel">
          <div class="bl-badge">ACCESS SUSPENDED</div>
          <div class="bl-avatar">
            <span>{{ bl.name.charAt(0) }}</span>
          </div>
          <h3 class="bl-name">{{ bl.name }}</h3>
          <p class="bl-class">{{ bl.className }}</p>
          <div class="bl-reason-box">
            <span>Alasan Penangguhan:</span>
            <p>{{ bl.reason }}</p>
          </div>
          <div class="bl-footer">
            <span class="bl-date">Ditambahkan: {{ bl.dateAdded }}</span>
            <button class="btn-revocation">Pulihkan Akses</button>
          </div>
        </div>
      </div>
    </div>

    </MainLayout>

  <!-- MODAL TAMBAH ALAT BARU (Cardless, minimal, 1px border) -->
  <transition name="fade">
    <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
      <div class="modal-panel">
        <div class="modal-header">
          <h3>Tambah Alat Baru</h3>
          <button @click="showAddModal = false" class="btn-close">
            <X :size="16" />
          </button>
        </div>
        
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Nama Alat</label>
            <input v-model="newItemName" type="text" placeholder="Masukkan nama alat..." class="form-input" />
          </div>
          
          <div class="form-group">
            <label class="form-label">Kategori</label>
            <select v-model="newItemCategory" class="form-select">
              <option value="laptop">Laptop</option>
              <option value="proyektor">Proyektor</option>
              <option value="kamera">Kamera</option>
              <option value="tripod">Tripod</option>
            </select>
          </div>
          
          <div class="form-group">
            <label class="form-label">Lokasi Penyimpanan</label>
            <input v-model="newItemLoc" type="text" placeholder="Contoh: Lemari A1..." class="form-input" />
          </div>

          <!-- Auto-generated QR preview area -->
          <transition name="fade">
            <div v-if="generatedQrPreview" class="qr-preview-section">
              <span class="preview-label">QR Code: {{ generatedCode }}</span>
              <div class="qr-code-box" style="width: 160px; height: 160px; margin: 0 auto;">
                <img :src="generatedQrPreview" alt="QR Code" style="width: 140px; height: 140px; object-fit: contain;" />
              </div>
            </div>
          </transition>
        </div>
        
        <div class="modal-footer">
          <button @click="showAddModal = false" class="btn-cancel">Batal</button>
          <button @click="handleSaveNewItem" class="btn-save" :disabled="!newItemName.trim() || !newItemLoc.trim()">
            Simpan
          </button>
        </div>
      </div>
    </div>
  </transition>

  <!-- MODAL ZOOM QR CODE (Cardless, minimal, 1px border) -->
  <transition name="fade">
    <div v-if="showZoomQrModal && zoomQrItem" class="modal-overlay" @click.self="showZoomQrModal = false">
      <div class="modal-panel" style="max-width: 380px; text-align: center;">
        <div class="modal-header">
          <h3>QR Code Alat</h3>
          <button @click="showZoomQrModal = false" class="btn-close">
            <X :size="16" />
          </button>
        </div>
        <div class="modal-body" style="display: flex; flex-direction: column; align-items: center; gap: 16px; padding: 24px 16px;">
          <h4 style="font-weight: 700; color: var(--color-text-primary); margin: 0;">{{ zoomQrItem.name }}</h4>
          <span style="font-family: var(--font-number); font-size: 0.8125rem; font-weight: 700; color: var(--color-action-primary); background: var(--color-primary-light); padding: 4px 12px; border-radius: 9999px;">
            {{ zoomQrItem.id }}
          </span>
          <div class="qr-code-box" style="width: 264px; height: 264px; margin: 0 auto;">
            <img :src="zoomQrItem.qr_code" alt="QR Code" style="width: 240px; height: 240px; object-fit: contain;" />
          </div>
          <p style="font-size: 0.75rem; color: var(--color-text-secondary); line-height: 1.4; margin: 0;">
            Pindai kode QR ini menggunakan kamera laptop/HP untuk reservasi atau verifikasi pengembalian.
          </p>
        </div>
      </div>
    </div>
  </transition>

  <!-- MODAL UBAH ALAT (Cardless, minimal, 1px border) -->
  <transition name="fade">
    <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
      <div class="modal-panel">
        <div class="modal-header">
          <h3>Ubah Data Alat</h3>
          <button @click="showEditModal = false" class="btn-close">
            <X :size="16" />
          </button>
        </div>
        
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Nama Alat</label>
            <input v-model="editItemName" type="text" placeholder="Masukkan nama alat..." class="form-input" />
          </div>
          
          <div class="form-group">
            <label class="form-label">Kategori</label>
            <select v-model="editItemCategory" class="form-select">
              <option value="laptop">Laptop</option>
              <option value="proyektor">Proyektor</option>
              <option value="kamera">Kamera</option>
              <option value="tripod">Tripod</option>
            </select>
          </div>
          
          <div class="form-group">
            <label class="form-label">Lokasi Penyimpanan</label>
            <input v-model="editItemLoc" type="text" placeholder="Contoh: Lemari A1..." class="form-input" />
          </div>

          <div class="form-group">
            <label class="form-label">Status</label>
            <select v-model="editItemStatus" class="form-select">
              <option value="Tersedia">Tersedia</option>
              <option value="Dipinjam">Dipinjam</option>
              <option value="Perawatan">Perawatan</option>
            </select>
          </div>
        </div>
        
        <div class="modal-footer">
          <button @click="showEditModal = false" class="btn-cancel">Batal</button>
          <button @click="handleSaveEditItem" class="btn-save" :disabled="!editItemName.trim() || !editItemLoc.trim()">
            Simpan Perubahan
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>
</template>

<style>
/* Clean corporate CSS animations */
.fade-in {
  animation: fadeIn 0.2s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Header layout styles */
.welcome-header {
  margin-bottom: 24px;
}

.welcome-title {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 6px;
}

.welcome-subtitle {
  font-size: 13.5px;
  color: var(--text-secondary);
  line-height: 1.45;
}

.view-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  gap: 16px;
  flex-wrap: wrap;
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

.glass-panel {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur-webkit);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-soft);
  transition: var(--transition-smooth);
}

/* Dashboard Panels Layout */
.dashboard-panels-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
  gap: 24px;
}

.panel-card {
  padding: 24px;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.03);
  padding-bottom: 12px;
}

.panel-header-small {
  margin-bottom: 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.03);
  padding-bottom: 10px;
}

.panel-header-small h3 {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-primary);
}

.panel-title-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
}

.panel-title-wrapper h3 {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-primary);
}

.panel-icon {
  width: 18px;
  height: 18px;
  color: var(--color-primary);
}

.badge-live {
  background: var(--color-danger-light);
  color: var(--color-danger);
  font-size: 10.5px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 50px;
  border: 1px solid rgba(239, 68, 68, 0.1);
}

.activities-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.activity-item {
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.activity-bullet {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  margin-top: 6px;
  flex-shrink: 0;
}

.activity-bullet.borrow { background: var(--color-primary); }
.activity-bullet.return { background: var(--color-success); }
.activity-bullet.approve { background: var(--color-warning); }
.activity-bullet.warning { background: var(--color-danger); }

.activity-details {
  display: flex;
  flex-direction: column;
}

.activity-text {
  font-size: 13px;
  line-height: 1.4;
  color: var(--text-primary);
}

.activity-time {
  font-size: 10.5px;
  color: var(--text-muted);
}

/* Filters Bar */
.filters-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 20px;
  margin-bottom: 20px;
  gap: 16px;
  flex-wrap: wrap;
}

.filter-input {
  background: rgba(255, 255, 255, 0.6);
  border: 1px solid rgba(148, 163, 184, 0.25);
  padding: 8px 14px;
  border-radius: var(--radius-md);
  font-family: var(--font-primary);
  font-size: 13px;
  outline: none;
  min-width: 240px;
  transition: var(--transition-fast);
}

.filter-input:focus {
  background: #ffffff;
  border-color: var(--color-primary);
}

.filter-options {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-secondary);
}

.filter-select {
  background: rgba(255, 255, 255, 0.6);
  border: 1px solid rgba(148, 163, 184, 0.25);
  padding: 8px 14px;
  border-radius: var(--radius-md);
  font-family: var(--font-primary);
  font-size: 13px;
  outline: none;
  cursor: pointer;
  min-width: 140px;
  transition: var(--transition-fast);
}

.filter-select:focus {
  background: #ffffff;
  border-color: var(--color-primary);
}

/* Equipment Grid Cards (Clean look, no emojis) */
.equipment-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 20px;
}

.equipment-card {
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.card-image-box {
  background: #f8fafc;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.card-icon-circle {
  width: 44px;
  height: 44px;
  background: var(--color-primary-light);
  color: var(--color-primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-icon-svg {
  width: 18px;
  height: 18px;
}

.status-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  font-size: 9.5px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 50px;
  text-transform: uppercase;
  border: 1.5px solid currentColor;
}

.status-badge.tersedia {
  background: var(--color-success-light);
  color: var(--color-success);
}

.status-badge.dipinjam {
  background: var(--color-warning-light);
  color: var(--color-warning);
}

.status-badge.perawatan {
  background: var(--color-danger-light);
  color: var(--color-danger);
}

.card-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.card-category {
  font-size: 10px;
  font-weight: 700;
  color: var(--color-primary);
  text-transform: uppercase;
}

.card-title {
  font-size: 14.5px;
  font-weight: 700;
  color: var(--text-primary);
  margin-top: 2px;
  margin-bottom: 10px;
  line-height: 1.3;
}

.card-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 12px;
  color: var(--text-secondary);
  background: #f8fafc;
  padding: 8px 12px;
  border-radius: var(--radius-md);
  margin-bottom: 14px;
}

.card-details span {
  font-weight: 700;
  color: var(--text-muted);
}

.card-actions {
  display: flex;
  gap: 8px;
  margin-top: auto;
}

/* Button UI variants (Flat corporate blue) */
.btn-primary {
  background: var(--color-primary);
  color: white;
  border: none;
  font-family: var(--font-primary);
  font-size: 12.5px;
  font-weight: 600;
  padding: 10px 16px;
  border-radius: var(--radius-md);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 2px 6px rgba(70, 130, 180, 0.15);
  transition: var(--transition-fast);
}

.btn-primary:hover {
  background: #3b719c;
}

.btn-primary-lg {
  background: var(--color-primary);
  color: white;
  border: none;
  font-family: var(--font-primary);
  font-size: 14px;
  font-weight: 600;
  padding: 12px 24px;
  border-radius: var(--radius-md);
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(70, 130, 180, 0.15);
  transition: var(--transition-fast);
  width: 100%;
  text-align: center;
}

.btn-primary-lg:hover {
  background: #3b719c;
}

.btn-secondary {
  background: #ffffff;
  border: 1px solid rgba(148, 163, 184, 0.25);
  color: var(--text-primary);
  font-family: var(--font-primary);
  font-size: 12.5px;
  font-weight: 600;
  padding: 10px 16px;
  border-radius: var(--radius-md);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: var(--transition-fast);
}

.btn-secondary:hover {
  background: #f8fafc;
  border-color: var(--color-primary);
}

.btn-primary-sm {
  flex: 1;
  background: var(--color-primary);
  color: white;
  border: none;
  font-family: var(--font-primary);
  font-size: 12px;
  font-weight: 600;
  padding: 8px 12px;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-primary-sm:hover:not(:disabled) {
  background: #3b719c;
}

.btn-primary-sm:disabled {
  background: var(--text-muted);
  opacity: 0.35;
  cursor: not-allowed;
}

.btn-secondary-sm {
  background: #ffffff;
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: var(--text-secondary);
  font-family: var(--font-primary);
  font-size: 12px;
  font-weight: 600;
  padding: 8px 12px;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-secondary-sm:hover {
  background: #f8fafc;
  color: var(--text-primary);
}

.btn-danger-sm {
  background: #ffffff;
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: var(--color-danger);
  font-family: var(--font-primary);
  font-size: 12px;
  font-weight: 600;
  padding: 8px 12px;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-danger-sm:hover {
  background: var(--color-danger-light);
  color: var(--color-danger);
  border-color: var(--color-danger);
}

.equipment-card:hover {
  box-shadow: var(--shadow-hover);
  border-color: rgba(70, 130, 180, 0.2);
}


.td-id {
  font-family: monospace;
  font-weight: 700;
  color: var(--color-primary);
}

.td-bold {
  font-weight: 600;
  color: var(--text-primary);
}

.badge-status {
  display: inline-flex;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 50px;
  border: 1.5px solid currentColor;
}

.badge-status.dipinjam {
  background: var(--color-warning-light);
  color: var(--color-warning);
}

.badge-status.terlambat {
  background: var(--color-danger-light);
  color: var(--color-danger);
}

.btn-action-verify {
  background: var(--color-primary-light);
  border: 1px solid rgba(70, 130, 180, 0.15);
  color: var(--color-primary);
  font-size: 11.5px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-action-verify:hover {
  background: var(--color-primary);
  color: white;
}

.text-danger-bold {
  color: var(--color-danger);
  font-weight: 700;
}

/* Approval Panel */
.approval-split-layout {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 24px;
}

.approval-queue {
  padding: 24px;
}

.approval-history-panel {
  padding: 24px;
}

.queue-list,
.history-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.req-card {
  background: #f8fafc;
  border: 1px solid rgba(148, 163, 184, 0.15);
  border-radius: var(--radius-lg);
  padding: 16px;
  transition: var(--transition-smooth);
}

.req-card:hover {
  background: #ffffff;
  box-shadow: var(--shadow-hover);
  border-color: rgba(70, 130, 180, 0.25);
}

.req-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.req-id {
  font-size: 10.5px;
  font-weight: 700;
  color: var(--color-primary);
  background: var(--color-primary-light);
  padding: 2px 6px;
  border-radius: 4px;
}

.req-date {
  font-size: 10.5px;
  color: var(--text-muted);
}

.req-card-body h4 {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 4px;
}

.req-user {
  font-size: 12px;
  color: var(--text-secondary);
}

.req-reason {
  font-size: 12px;
  color: var(--text-muted);
  margin-top: 4px;
  border-left: 2px solid rgba(70, 130, 180, 0.2);
  padding-left: 8px;
}

.req-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 10px;
  border-top: 1px solid rgba(0, 0, 0, 0.02);
  padding-top: 8px;
}

.btn-approve-action {
  background: var(--color-success-light);
  border: 1px solid rgba(16, 185, 129, 0.15);
  color: var(--color-success);
  font-size: 11.5px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-approve-action:hover {
  background: var(--color-success);
  color: white;
}

.btn-reject-action {
  background: var(--color-danger-light);
  border: 1px solid rgba(239, 68, 68, 0.15);
  color: var(--color-danger);
  font-size: 11.5px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-reject-action:hover {
  background: var(--color-danger);
  color: white;
}

.history-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  background: rgba(255, 255, 255, 0.4);
  border: 1px solid rgba(241, 245, 249, 0.9);
  border-radius: var(--radius-md);
  transition: var(--transition-fast);
}

.history-card.approved {
  border-left: 3px solid var(--color-success);
}

.history-card.rejected {
  border-left: 3px solid var(--color-danger);
}

.hist-status-badge {
  font-size: 9px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
  text-transform: uppercase;
}

.hist-status-badge.approved {
  background: var(--color-success-light);
  color: var(--color-success);
}

.hist-status-badge.rejected {
  background: var(--color-danger-light);
  color: var(--color-danger);
}

.hist-details {
  flex: 1;
  margin-left: 10px;
}

.hist-details h4 {
  font-size: 12.5px;
  color: var(--text-primary);
  font-weight: 600;
}

.hist-sub {
  font-size: 11px;
  color: var(--text-secondary);
}

.hist-time {
  font-size: 9.5px;
  color: var(--text-muted);
}

.empty-state-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 32px 16px;
  background: #f8fafc;
  border: 1.5px dashed rgba(148, 163, 184, 0.2);
  border-radius: var(--radius-lg);
  color: var(--text-secondary);
  gap: 8px;
  font-size: 13px;
}

.empty-state-small {
  text-align: center;
  padding: 24px 12px;
  color: var(--text-muted);
  font-size: 11.5px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

/* Timeline Activity */
.timeline-container {
  padding: 24px;
  position: relative;
}

.timeline-container::before {
  content: "";
  position: absolute;
  top: 24px;
  bottom: 24px;
  left: 40px;
  width: 2px;
  background: rgba(0, 0, 0, 0.02);
}

.timeline-item {
  position: relative;
  padding-left: 40px;
  margin-bottom: 24px;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.time-node {
  position: absolute;
  left: 11px;
  top: 4px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: white;
  border: 2px solid var(--text-muted);
  z-index: 2;
}

.time-node.success { border-color: var(--color-success); }
.time-node.primary { border-color: var(--color-primary); }
.time-node.warning { border-color: var(--color-warning); }
.time-node.danger { border-color: var(--color-danger); }

.time-stamp {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-muted);
}

.timeline-body {
  background: rgba(255, 255, 255, 0.4);
  border: 1px solid rgba(241, 245, 249, 0.9);
  border-radius: var(--radius-lg);
  padding: 14px;
  margin-top: 6px;
  transition: var(--transition-smooth);
}

.timeline-body h4 {
  font-size: 13.5px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 2px;
}

.timeline-body p {
  font-size: 12.5px;
  color: var(--text-secondary);
  line-height: 1.4;
}

.timeline-item:hover .timeline-body {
  background: #ffffff;
  box-shadow: var(--shadow-hover);
  border-color: rgba(70, 130, 180, 0.2);
}

/* Reports view */
.reports-grid {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 24px;
}

.report-main-card {
  padding: 24px;
}

.report-main-card h3 {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-primary);
}

.report-card-subtitle {
  font-size: 12px;
  color: var(--text-secondary);
  margin-bottom: 20px;
}

.chart-mockup-container {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  height: 200px;
  border-bottom: 1.5px solid rgba(0, 0, 0, 0.03);
  padding-bottom: 8px;
  margin-bottom: 14px;
}

.chart-bar-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
}

.chart-bar-label {
  font-size: 10.5px;
  color: var(--text-muted);
  margin-top: 6px;
  font-weight: 600;
}

.chart-bars {
  display: flex;
  align-items: flex-end;
  gap: 4px;
  height: 160px;
}

.chart-bar {
  width: 12px;
  border-radius: 2px 2px 0 0;
  transition: var(--transition-smooth);
}

.borrow-bar {
  background: var(--color-primary);
}

.return-bar {
  background: var(--color-success);
}

.chart-legend {
  display: flex;
  gap: 16px;
  justify-content: center;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 600;
  color: var(--text-secondary);
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.legend-dot.borrow { background: var(--color-primary); }
.legend-dot.return { background: var(--color-success); }

.report-side-column {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.mini-report-card {
  padding: 20px;
}

.mini-report-card h4 {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 12px;
}

.top-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.top-list-item {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  background: #f8fafc;
  border-radius: var(--radius-md);
  border: 1px solid rgba(148, 163, 184, 0.15);
}

.list-rank {
  width: 20px;
  height: 20px;
  background: var(--color-primary-light);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10.5px;
  font-weight: 700;
  color: var(--color-primary);
  margin-right: 10px;
}

.list-name {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-primary);
  flex: 1;
}

.list-count {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-muted);
}

.percentage-display {
  display: flex;
  align-items: center;
  gap: 16px;
}

.percentage-circle {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  border: 5px solid var(--color-success-light);
  border-top-color: var(--color-success);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.pct-val {
  font-size: 13.5px;
  font-weight: 750;
  color: var(--color-success);
}

.pct-sub {
  font-size: 7.5px;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
}

.pct-desc {
  font-size: 12px;
  color: var(--text-secondary);
  line-height: 1.4;
  flex: 1;
}

/* Blacklist Card */
.blacklist-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 20px;
}

.blacklist-card {
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.bl-badge {
  position: absolute;
  top: 10px;
  right: -28px;
  background: var(--color-danger);
  color: white;
  font-size: 7.5px;
  font-weight: 700;
  padding: 3px 28px;
  transform: rotate(45deg);
  letter-spacing: 0.5px;
}

.bl-avatar {
  width: 54px;
  height: 54px;
  background: var(--color-danger-light);
  border: 2px solid rgba(239, 68, 68, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  font-weight: 700;
  color: var(--color-danger);
  margin-bottom: 12px;
}

.bl-name {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-primary);
}

.bl-class {
  font-size: 11.5px;
  color: var(--text-muted);
  margin-top: 2px;
}

.bl-reason-box {
  background: rgba(239, 68, 68, 0.015);
  border: 1px solid rgba(239, 68, 68, 0.08);
  padding: 10px;
  border-radius: var(--radius-md);
  margin-top: 14px;
  margin-bottom: 16px;
  width: 100%;
}

.bl-reason-box span {
  font-size: 10px;
  font-weight: 700;
  color: var(--color-danger);
  text-transform: uppercase;
}

.bl-reason-box p {
  font-size: 11.5px;
  line-height: 1.4;
  color: var(--text-secondary);
  margin-top: 2px;
}

.bl-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  margin-top: auto;
  border-top: 1px solid rgba(0, 0, 0, 0.02);
  padding-top: 12px;
}

.bl-date {
  font-size: 10.5px;
  color: var(--text-muted);
}

.btn-revocation {
  background: rgba(16, 185, 129, 0.06);
  border: 1px solid rgba(16, 185, 129, 0.15);
  color: var(--color-success);
  font-size: 10.5px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-revocation:hover {
  background: var(--color-success);
  color: white;
}

.btn-danger-action {
  background: var(--color-danger);
  color: white;
  border: none;
  font-family: var(--font-primary);
  font-size: 12.5px;
  font-weight: 600;
  padding: 10px 16px;
  border-radius: var(--radius-md);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 2px 6px rgba(239, 68, 68, 0.15);
  transition: var(--transition-fast);
}

.btn-danger-action:hover {
  background: #dc2626;
}

.blacklist-card:hover {
  box-shadow: var(--shadow-hover);
  border-color: rgba(239, 68, 68, 0.15);
}

/* Quick View QR Badge styles */
.qr-thumbnail-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-secondary);
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s, color 0.2s;
  z-index: 10;
}

.qr-thumbnail-badge:hover {
  background: #ffffff;
  color: var(--color-action-primary);
  transform: scale(1.08);
}

.qr-thumbnail-badge:active {
  transform: scale(0.95);
}

@media (max-width: 1024px) {
  .approval-split-layout {
    grid-template-columns: 1fr;
  }
  
  .reports-grid {
    grid-template-columns: 1fr;
  }
}

/* Spacing layout adjustment */
.equipment-grid {
  gap: 28px !important;
}

.reports-grid {
  gap: 28px !important;
}

/* Modal overlays */
.modal-overlay {
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

.modal-panel {
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

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  font-family: var(--font-primary);
  font-size: 1.25rem;
  font-weight: 750;
  color: var(--text-primary);
}

.btn-close {
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

.btn-close:hover {
  background: rgba(0, 0, 0, 0.04);
  color: var(--text-primary);
}

.modal-body {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 8px;
}

.btn-cancel {
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

.btn-cancel:hover {
  background: rgba(0, 0, 0, 0.02);
  color: var(--text-primary);
}

.btn-save {
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

.btn-save:hover:not(:disabled) {
  background: #365670;
  box-shadow: 0 4px 12px rgba(70, 112, 143, 0.15);
}

.btn-save:disabled {
  background: rgba(0, 0, 0, 0.04);
  color: var(--text-muted);
  cursor: not-allowed;
  box-shadow: none;
}

/* Modals Form Styling Improvements */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-align: left;
  width: 100%;
}

.form-label {
  font-family: var(--font-primary);
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-input,
.form-select {
  font-family: var(--font-primary);
  font-size: 0.875rem;
  color: var(--text-primary);
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: var(--radius-sm);
  padding: 10px 14px;
  outline: none;
  background: #ffffff;
  transition: var(--transition-fast);
  width: 100%;
}

.form-input:focus,
.form-select:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(70, 112, 143, 0.08);
}

.form-input::placeholder {
  color: var(--text-muted);
  font-size: 0.875rem;
}

.qr-preview-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  margin-top: 12px;
  padding: 16px;
  background: var(--color-primary-light);
  border-radius: var(--radius-lg);
  border: 1px dashed rgba(70, 112, 143, 0.2);
}

.preview-label {
  font-family: var(--font-number);
  font-size: 0.8125rem;
  font-weight: 700;
  color: var(--color-primary);
}

.qr-code-box {
  padding: 12px;
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: var(--radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 576px) {
  .modal-panel {
    padding: 20px 16px;
    gap: 16px;
    border-radius: var(--radius-lg);
  }
  
  .modal-header h3 {
    font-size: 1.125rem;
  }
  
  .view-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .view-header .btn-primary, 
  .view-header .btn-secondary, 
  .view-header .btn-danger-action,
  .view-header .btn-group {
    width: 100%;
  }
  
  .view-header .btn-primary, 
  .view-header .btn-secondary, 
  .view-header .btn-danger-action {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .view-header .btn-group {
    display: flex;
    gap: 8px;
  }

  .view-header .btn-group .btn-primary,
  .view-header .btn-group .btn-secondary {
    flex: 1;
  }
  
  .filters-bar {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
  
  .filter-input {
    min-width: 0;
    width: 100%;
  }
  
  .filter-options {
    justify-content: space-between;
    width: 100%;
  }
  
  .filter-select {
    flex: 1;
    min-width: 0;
  }
}
</style>

