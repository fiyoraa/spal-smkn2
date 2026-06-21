<script setup>
import { ref, computed, onUnmounted, nextTick, onMounted } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';
import FormPeminjaman from './FormPeminjaman.vue';
import { 
  Search, 
  Layers, 
  Clock, 
  CheckCircle2, 
  AlertCircle,
  FileCheck,
  ChevronRight,
  Info,
  Beaker, 
  Cpu, 
  FileText, 
  Database,
  QrCode
} from '@lucide/vue';

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  user: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['refresh-data']);

const catalogItems = computed(() => {
  return props.items || [];
});

const selectedItem = ref(null);
const searchQuery = ref('');
const filterOnlyAvailable = ref(false);

const showQrScanner = ref(false);
const isCameraActive = ref(false);
const isScanning = ref(false);
const cameraError = ref('');
let html5QrCode = null;

// Beep Web Audio API
const playBeep = () => {
  try {
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioCtx.createOscillator();
    const gainNode = audioCtx.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioCtx.destination);

    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(880, audioCtx.currentTime); // 880Hz beep
    gainNode.gain.setValueAtTime(0.04, audioCtx.currentTime); // low volume

    oscillator.start();
    oscillator.stop(audioCtx.currentTime + 0.08); // 80ms duration
  } catch (e) {
    console.warn('Audio Context failed:', e);
  }
};

// Haptic feedback API
const triggerVibrate = () => {
  if (navigator.vibrate) {
    navigator.vibrate(50);
  }
};

const toggleQrScanner = () => {
  if (showQrScanner.value) {
    stopPeminjamanScanner();
  } else {
    showQrScanner.value = true;
    cameraError.value = '';
    // Automatically try to start camera
    nextTick(() => {
      startPeminjamanScanner();
    });
  }
};

const startPeminjamanScanner = async () => {
  cameraError.value = '';
  isScanning.value = true;
  isCameraActive.value = false;
  
  await nextTick();
  
  html5QrCode = new Html5Qrcode("peminjaman-qr-reader");
  const config = { 
    fps: 10, 
    qrbox: (width, height) => {
      const minEdge = Math.min(width, height);
      const boxSize = Math.floor(minEdge * 0.7);
      return { width: boxSize, height: boxSize };
    }
  };
  
  html5QrCode.start(
    { facingMode: "environment" },
    config,
    (decodedText) => {
      handlePeminjamanScanSuccess(decodedText);
    },
    () => {
      // Ignore scanning error spam
    }
  )
  .then(() => {
    isCameraActive.value = true;
    isScanning.value = false;
  })
  .catch((err) => {
    console.error("Camera access error:", err);
    cameraError.value = "Kamera tidak dapat diakses. Silakan izinkan kamera di browser.";
    isScanning.value = false;
    isCameraActive.value = false;
    cleanupScanner();
  });
};

const cleanDecodedText = (text) => {
  let val = text.trim();
  if (val.includes('://') || val.includes('/')) {
    const parts = val.split('/');
    val = parts[parts.length - 1] || val;
  }
  if (val.startsWith("QR: ")) {
    val = val.replace("QR: ", "");
  }
  return val;
};

const handlePeminjamanScanSuccess = async (decodedText) => {
  playBeep();
  triggerVibrate();
  
  const equipmentId = cleanDecodedText(decodedText);
  
  try {
    const response = await fetch(`/api/equipment/${encodeURIComponent(equipmentId)}`);
    const result = await response.json();
    
    if (response.ok && result.status === 'success') {
      const item = {
        id: result.data.code,
        dbId: result.data.id,
        name: result.data.name,
        category: result.data.category.charAt(0).toUpperCase() + result.data.category.slice(1).toLowerCase(),
        status: result.data.status,
        loc: result.data.location,
        qr_code: result.data.qr_code
      };
      
      if (item.status === 'Tersedia') {
        selectItem(item);
        stopPeminjamanScanner();
      } else {
        cameraError.value = `Alat "${item.name}" ditemukan, tetapi status saat ini: ${item.status}.`;
        setTimeout(() => {
          cameraError.value = '';
        }, 4000);
      }
    } else {
      cameraError.value = result.message || `Alat dengan Kode "${equipmentId}" tidak ditemukan.`;
      setTimeout(() => {
        cameraError.value = '';
      }, 4000);
    }
  } catch (err) {
    console.error('Error fetching scanned equipment:', err);
    cameraError.value = 'Gagal memverifikasi alat dari server.';
    setTimeout(() => {
      cameraError.value = '';
    }, 4000);
  }
};

const stopPeminjamanScanner = () => {
  cleanupScanner();
  showQrScanner.value = false;
};

const cleanupScanner = () => {
  if (html5QrCode) {
    if (html5QrCode.isScanning) {
      html5QrCode.stop().then(() => {
        html5QrCode = null;
        isCameraActive.value = false;
      }).catch(err => {
        console.error("Failed to stop scanner:", err);
      });
    } else {
      html5QrCode = null;
      isCameraActive.value = false;
    }
  }
};

onUnmounted(() => {
  cleanupScanner();
});

const ambientSuggestions = computed(() => {
  return [
    { text: 'Praktikum Robotika hari ini? Butuh Solder Weller', value: 'Solder' },
    { text: 'Analisis Zat? Lihat Centrifuge', value: 'Centrifuge' },
    { text: 'Tampilkan Hanya yang Tersedia', value: 'TersediaOnly' }
  ];
});

const handleSelectSuggestion = (suggestion) => {
  if (suggestion.value === 'TersediaOnly') {
    filterOnlyAvailable.value = !filterOnlyAvailable.value;
  } else {
    searchQuery.value = suggestion.value;
  }
};

// Filter items
const filteredCatalog = computed(() => {
  return catalogItems.value.filter(item => {
    const matchesSearch = item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                          item.id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                          item.category.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    const matchesAvailability = !filterOnlyAvailable.value || item.status === 'Tersedia';
    return matchesSearch && matchesAvailability;
  });
});

// Group catalog items semantically by category (The Archival Index Aesthetic)
const groupedCatalog = computed(() => {
  const groups = {};
  filteredCatalog.value.forEach(item => {
    if (!groups[item.category]) {
      groups[item.category] = [];
    }
    groups[item.category].push(item);
  });
  return groups;
});

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

const selectItem = (item) => {
  if (item.status !== 'Tersedia') return;
  selectedItem.value = item;
  
  const formElement = document.getElementById('borrow-form-section');
  if (formElement) {
    formElement.scrollIntoView({ behavior: 'smooth' });
  }
};

const clearSelection = () => {
  selectedItem.value = null;
};

// Track submissions locally
const mySubmissions = ref([]);

const loadSubmissions = async () => {
  try {
    const response = await fetch(`/api/borrowings?user_id=${props.user.id}`);
    const result = await response.json();
    if (result.status === 'success') {
      mySubmissions.value = result.data.map(req => ({
        id: req.id,
        dbId: req.dbId,
        itemName: req.itemName,
        jumlah: req.quantity,
        tglPinjam: req.dateStart,
        tglKembali: req.dateEnd,
        status: req.status
      }));
    }
  } catch (err) {
    console.error('Error loading submissions:', err);
  }
};

onMounted(() => {
  loadSubmissions();
});

const handleFormSubmit = (formData) => {
  loadSubmissions();
  emit('refresh-data');
  selectedItem.value = null;
};

const getStatusClass = (status) => {
  switch (status.toLowerCase()) {
    case 'approved': return 'status-approved';
    case 'pending': return 'status-pending';
    case 'returned': return 'status-returned';
    case 'rejected': return 'status-rejected';
    default: return '';
  }
};
</script>

<template>
  <div class="peminjaman-user-page">
    <div class="page-intro">
      <h1 class="page-giant-title">Katalog & Reservasi</h1>
      <p class="page-subtitle">Pilih alat laboratorium dari index katalog di bawah untuk mengajukan peminjaman.</p>
    </div>

    <!-- MAIN GRID CONTAINER -->
    <div class="peminjaman-layout-grid">
      
      <!-- LEFT SECTION: CATALOG AREA (Archival Index Style) -->
      <div class="catalog-section">
        
        <!-- Ambient AI Suggestion & Search Block (Borderless) -->
        <div class="search-assistant-panel">
          <div class="search-wrapper-pill">
            <Search class="search-icon" />
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Cari alat berdasarkan nama atau kode..." 
              class="search-input"
            />
            <button @click="toggleQrScanner" class="btn-qr-scan" title="Scan QR Code" :class="{ 'active': showQrScanner }">
              <QrCode :size="16" />
            </button>
          </div>

          <!-- Ambient AI recommendation chips -->
          <div class="ambient-chips">
            <span class="ambient-label">Saran Pintar</span>
            <button 
              v-for="chip in ambientSuggestions" 
              :key="chip.text"
              class="chip-pill"
              :class="{ 'chip-active': (chip.value === 'TersediaOnly' && filterOnlyAvailable) || (chip.value !== 'TersediaOnly' && searchQuery === chip.value) }"
              @click="handleSelectSuggestion(chip)"
            >
              {{ chip.text }}
            </button>
          </div>
        </div>

        <!-- INLINE SCANNER VIEWFINDER (PROGRESIVE DISCLOSURE) -->
        <transition name="expand">
          <div v-show="showQrScanner" class="inline-scanner-panel">
            <div class="scanner-viewfinder-compact" :class="{ 'scanning': isCameraActive }">
              <div class="reticle-corner top-left"></div>
              <div class="reticle-corner top-right"></div>
              <div class="reticle-corner bottom-left"></div>
              <div class="reticle-corner bottom-right"></div>
              
              <!-- html5-qrcode video element destination -->
              <div v-show="isCameraActive" id="peminjaman-qr-reader"></div>

              <!-- Placeholder/Error -->
              <div v-if="!isCameraActive" class="scanner-placeholder">
                <p v-if="cameraError" class="scanner-error-text">{{ cameraError }}</p>
                <p v-else-if="isScanning" class="scanner-prompt-text">Mengakses Kamera...</p>
                <p v-else class="scanner-prompt-text">Akses Kamera diperlukan untuk memindai QR Code Alat.</p>
                <button v-if="!isScanning" @click="startPeminjamanScanner" class="btn-activate-camera-compact">
                  {{ cameraError ? 'Coba Lagi' : 'Aktifkan Kamera' }}
                </button>
              </div>
            </div>
          </div>
        </transition>

        <!-- Grouped Catalog Sections (The Archival Index Aesthetic - Rows instead of Cards) -->
        <div class="catalog-archive" v-if="Object.keys(groupedCatalog).length > 0">
          <div 
            v-for="(items, category) in groupedCatalog" 
            :key="category" 
            class="archive-group"
          >
            <!-- Large Graphic Category Typography (Instrument Serif Italic) -->
            <h2 class="category-giant-title">{{ category }}</h2>
            
            <div class="archive-list">
              <div 
                v-for="item in items" 
                :key="item.id" 
                class="archive-row"
                :class="{ 
                  'selected-row': selectedItem && selectedItem.id === item.id, 
                  'disabled-row': item.status !== 'Tersedia' 
                }"
                @click="selectItem(item)"
              >
                <!-- Essential Details View (Step 1 of Disclosure) -->
                <div class="row-main-content">
                  <div class="row-left">
                    <component :is="getCategoryIcon(item.category)" class="item-category-icon" />
                    <h4 class="item-name">{{ item.name }}</h4>
                  </div>

                  <div class="row-right">
                    <span class="status-indicator-dot" :class="item.status.toLowerCase()">
                      {{ item.status }}
                    </span>
                    <ChevronRight :size="16" class="row-chevron" />
                  </div>
                </div>

                <!-- Progressive Disclosure: Secondary details open on hover or active selection -->
                <div class="row-disclosure-panel">
                  <div class="disclosure-inner">
                    <span class="disclosure-detail"><strong>ID Referensi:</strong> {{ item.id }}</span>
                    <span class="disclosure-detail"><strong>Lokasi Penyimpanan:</strong> {{ item.loc }}</span>
                    <span class="disclosure-detail"><strong>Kategori:</strong> {{ item.category }}</span>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div class="empty-catalog-state" v-else>
          <Info :size="24" />
          <p>Alat tidak ditemukan. Silakan sesuaikan filter pencarian.</p>
        </div>
      </div>

      <!-- RIGHT SECTION: FORM & SUBMISSIONS -->
      <div id="borrow-form-section" class="form-section">
        
        <!-- Progressive Disclosure Form -->
        <FormPeminjaman 
          :selected-item="selectedItem"
          :user="user"
          @submit-success="handleFormSubmit"
          @cancel="clearSelection"
        />

        <!-- Secondary block: User requests history (Borderless, whitespace-based) -->
        <div class="my-loans-container">
          <div class="block-header">
            <FileCheck :size="16" class="header-icon" />
            <h3>Peminjaman Saya</h3>
          </div>
          
          <div class="requests-list" v-if="mySubmissions.length > 0">
            <div 
              v-for="sub in mySubmissions" 
              :key="sub.id" 
              class="request-list-row"
            >
              <div class="req-row-left">
                <span class="req-id-tag">{{ sub.id }}</span>
                <div class="req-details-text">
                  <h4>{{ sub.itemName }}</h4>
                  <p>Batas Pengembalian: {{ sub.tglKembali }}</p>
                </div>
              </div>
              <span class="req-status-badge" :class="getStatusClass(sub.status)">
                {{ sub.status === 'Approved' ? 'Disetujui' : sub.status === 'Returned' ? 'Kembali' : 'Pending' }}
              </span>
            </div>
          </div>
          <div class="empty-requests-state" v-else>
            <Clock :size="16" class="text-muted" />
            <p>Belum ada riwayat pengajuan peminjaman aktif.</p>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<style scoped>
.peminjaman-user-page {
  display: flex;
  flex-direction: column;
  gap: 48px;
  width: 100%;
}

.page-intro {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* Kinetic Typography - strong editorial style (DESIGN.md) */
.page-giant-title {
  font-family: var(--font-display); /* Instrument Serif */
  font-size: 3.5rem;
  font-weight: 400;
  font-style: italic;
  color: var(--color-text-primary);
  line-height: 1.1;
  letter-spacing: -1.5px;
}

.page-subtitle {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
}

.peminjaman-layout-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 48px;
  align-items: start;
}

.catalog-section {
  display: flex;
  flex-direction: column;
  gap: 56px;
}

/* SEARCH ASSISTANT & CHIPS (NO CARD BORDERS/SHADOWS) */
.search-assistant-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
  border-bottom: 1px solid rgba(30, 41, 59, 0.08);
  padding-bottom: 32px;
}

.search-wrapper-pill {
  display: flex;
  align-items: center;
  background: rgba(0, 0, 0, 0.015);
  border: 1.5px solid transparent;
  border-radius: 9999px; /* pill-shaped */
  padding: 4px 24px;
  transition: border-color 0.2s, background-color 0.2s;
}

.search-wrapper-pill:focus-within {
  background: #ffffff;
  border-color: var(--color-action-primary);
}

.search-icon {
  width: 16px;
  height: 16px;
  color: var(--color-text-secondary);
  margin-right: 12px;
}

.search-input {
  background: transparent;
  border: none;
  outline: none;
  font-family: var(--font-primary);
  font-size: 0.875rem;
  color: var(--color-text-primary);
  width: 100%;
  padding: 10px 0;
}

.ambient-chips {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  padding-left: 8px;
}

.ambient-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.8px;
}

.chip-pill {
  background: rgba(0, 0, 0, 0.02);
  border: none;
  padding: 6px 14px;
  font-family: var(--font-primary);
  font-weight: 600;
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  border-radius: 9999px; /* pill-shaped */
  cursor: pointer;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s, color 0.2s;
}

.chip-pill:hover {
  background: rgba(70, 112, 143, 0.06);
  color: var(--color-action-primary);
}

.chip-pill:active {
  transform: scale(0.98); /* Tactile */
}

.chip-pill.chip-active {
  background: var(--color-action-primary);
  color: white;
}

/* ARCHIVAL INDEX AESTHETIC (GROUPED DESIGN, FAINT BORDERS) */
.catalog-archive {
  display: flex;
  flex-direction: column;
  gap: 64px;
}

.archive-group {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Giant Category Title in Geist/Instrument Serif (No Inter) */
.category-giant-title {
  font-family: var(--font-display); /* Instrument Serif */
  font-size: 4.5rem;
  font-weight: 400;
  font-style: italic;
  letter-spacing: -2px;
  color: rgba(30, 41, 59, 0.08); /* Very oversized and faint as graphic anchor */
  line-height: 0.8;
  margin-bottom: -12px;
  pointer-events: none;
  user-select: none;
}

.archive-list {
  display: flex;
  flex-direction: column;
  border-top: 1px solid rgba(30, 41, 59, 0.04);
}

/* Minimal Rows instead of traditional Cards (Archival Index Aesthetic) */
.archive-row {
  display: flex;
  flex-direction: column;
  padding: 28px 8px;
  border-bottom: 1px solid rgba(30, 41, 59, 0.06);
  cursor: pointer;
  background: transparent;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s;
}

.archive-row:hover:not(.disabled-row) {
  background: rgba(70, 112, 143, 0.015);
  transform: scale(0.995); /* Indent slightly on hover */
}

.archive-row:active:not(.disabled-row) {
  transform: scale(0.98); /* Tactile click */
}

/* Row upper structure */
.row-main-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}

.row-left {
  display: flex;
  align-items: center;
  gap: 20px;
  flex: 1;
}

.item-category-icon {
  width: 20px;
  height: 20px;
  color: var(--color-text-secondary);
  opacity: 0.6;
}

.item-name {
  font-family: var(--font-number); /* Geist Grotesque, No Inter */
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-text-primary);
  letter-spacing: -0.2px;
}

.row-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.status-indicator-dot {
  font-size: 0.6875rem;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 9999px; /* pill-shaped */
}

/* Semantic only status colors - Nature Distilled */
.status-indicator-dot.tersedia {
  background: var(--color-success-light);
  color: var(--color-success);
}

.status-indicator-dot.dipinjam {
  background: var(--color-danger-light);
  color: var(--color-danger); /* Dark brick-red */
}

.status-indicator-dot.perawatan {
  background: var(--color-warning-light);
  color: var(--color-warning);
}

.row-chevron {
  color: var(--color-text-secondary);
  opacity: 0.4;
  transition: transform 0.2s;
}

.archive-row:hover .row-chevron {
  transform: translateX(4px);
  opacity: 0.8;
}

/* PROGRESSIVE DISCLOSURE: Details hidden by default, sliding down on hover/selection */
.row-disclosure-panel {
  max-height: 0;
  opacity: 0;
  overflow: hidden;
  transition: max-height 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease, padding 0.3s;
}

.archive-row:hover .row-disclosure-panel,
.archive-row.selected-row .row-disclosure-panel {
  max-height: 48px;
  opacity: 1;
  padding-top: 14px;
}

.disclosure-inner {
  display: flex;
  gap: 32px;
  padding-left: 40px; /* Aligns with name offset */
}

.disclosure-detail {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  font-weight: 500;
}

.disclosure-detail strong {
  color: var(--color-text-primary);
}

/* Selected row highlighted */
.archive-row.selected-row {
  background: rgba(70, 112, 143, 0.025);
  border-bottom-color: var(--color-action-primary);
}

.disabled-row {
  opacity: 0.4;
  cursor: not-allowed;
}

/* FORM & SUBMISSIONS SECTION (NO CARDS) */
.form-section {
  display: flex;
  flex-direction: column;
  gap: 40px;
}

.my-loans-container {
  padding-top: 32px;
  border-top: 1px solid rgba(30, 41, 59, 0.08);
}

.block-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 24px;
}

.block-header h3 {
  font-size: 0.9375rem;
  font-weight: 750;
  color: var(--color-text-primary);
}

.requests-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.request-list-row {
  background: transparent;
  border-bottom: 1px solid rgba(30, 41, 59, 0.04);
  padding: 16px 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.req-row-left {
  display: flex;
  align-items: center;
  gap: 16px;
  overflow: hidden;
  flex: 1;
}

.req-id-tag {
  font-family: var(--font-number); /* Geist */
  font-weight: 700;
  font-size: 0.6875rem;
  color: var(--color-action-primary);
  background: var(--color-primary-light);
  padding: 2px 8px;
  border-radius: 4px;
}

.req-details-text {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.req-details-text h4 {
  font-size: 0.8125rem;
  font-weight: 700;
  color: var(--color-text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.req-details-text p {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
}

.req-status-badge {
  font-size: 0.6875rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 9999px; /* pill-shaped */
}

.req-status-badge.status-approved {
  background: var(--color-success-light);
  color: var(--color-success);
}

.req-status-badge.status-pending {
  background: var(--color-warning-light);
  color: var(--color-warning);
}

.req-status-badge.status-returned {
  background: rgba(0, 0, 0, 0.02);
  color: var(--color-text-secondary);
}

.empty-requests-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  color: var(--color-text-secondary);
  gap: 8px;
  font-size: 0.75rem;
  padding: 24px 0;
}

.empty-catalog-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  color: var(--color-text-secondary);
  gap: 12px;
  font-size: 0.875rem;
  padding: 64px 16px;
}

/* QR SCANNER STYLES */
.btn-qr-scan {
  background: transparent;
  border: none;
  color: var(--color-text-secondary);
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: var(--transition-smooth);
}

.btn-qr-scan:hover,
.btn-qr-scan.active {
  background: rgba(0, 0, 0, 0.04);
  color: var(--color-action-primary);
}

.inline-scanner-panel {
  width: 100%;
  margin-top: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.scanner-viewfinder-compact {
  position: relative;
  width: 100%;
  height: 200px;
  background: #0a0f1d;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: var(--radius-xl);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: border-color 0.2s ease-out;
}

.scanner-viewfinder-compact.scanning {
  border-color: var(--color-action-primary);
}

.scanner-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 24px;
  text-align: center;
  z-index: 10;
}

.scanner-prompt-text {
  font-family: var(--font-primary);
  font-size: 0.8125rem;
  color: var(--color-text-secondary);
  font-weight: 500;
}

.scanner-error-text {
  font-family: var(--font-primary);
  font-size: 0.8125rem;
  color: var(--color-danger);
  font-weight: 500;
}

.btn-activate-camera-compact {
  background: var(--color-action-primary);
  color: white;
  border: none;
  font-family: var(--font-number), sans-serif;
  font-size: 0.8125rem;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 9999px; /* pill-shaped */
  cursor: pointer;
  transition: transform 0.2s, background-color 0.2s;
}

.btn-activate-camera-compact:hover {
  background: #355670;
}

.btn-activate-camera-compact:active {
  transform: scale(0.98);
}

#peminjaman-qr-reader {
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 5;
}

#peminjaman-qr-reader :deep(video) {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover !important;
}

#peminjaman-qr-reader :deep(span),
#peminjaman-qr-reader :deep(button),
#peminjaman-qr-reader :deep(select) {
  display: none !important;
}

/* ANIMATION HELPERS */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  max-height: 240px;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-8px);
}

@media (max-width: 1024px) {
  .peminjaman-layout-grid {
    grid-template-columns: 1fr;
    gap: 48px;
  }
}

@media (max-width: 640px) {
  .page-giant-title {
    font-size: 2.25rem;
  }
  
  .category-giant-title {
    font-size: 2.25rem;
    margin-bottom: 0;
  }
  
  .row-left {
    gap: 12px;
  }
  
  .item-name {
    font-size: 0.95rem;
  }
  
  .row-disclosure-panel {
    padding-top: 10px;
  }
  
  .disclosure-inner {
    flex-direction: column;
    gap: 6px;
    padding-left: 32px;
  }
  
  .archive-row {
    padding: 20px 8px;
  }
  
  .search-assistant-panel {
    padding-bottom: 20px;
    gap: 12px;
  }
  
  .search-wrapper-pill {
    padding: 2px 16px;
  }
  
  .ambient-chips {
    gap: 6px;
  }
}
</style>
