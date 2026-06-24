<script setup>
import { ref, computed, onUnmounted, nextTick, onMounted } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';
import { 
  QrCode, 
  UploadCloud, 
  AlertTriangle, 
  CheckCircle, 
  Camera,
  RefreshCw,
  Image
} from '@lucide/vue';

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['refresh-data']);

const activeLoansForReturn = ref([]);

const loadActiveLoans = async () => {
  try {
    const response = await fetch('/api/transactions?status=Dipinjam');
    const result = await response.json();
    if (result.status === 'success') {
      activeLoansForReturn.value = result.data.map(trx => {
        // Compute delayDays
        const dueDate = new Date(trx.dateDue);
        const today = new Date();
        let delayDays = 0;
        if (today > dueDate) {
          delayDays = Math.floor((today - dueDate) / (1000 * 60 * 60 * 24));
        }
        return {
          id: trx.id, // transaction code
          itemId: trx.equipmentCode,
          user: trx.userName,
          item: trx.itemName,
          dateDue: trx.dateDue,
          delayDays: delayDays,
          initialCond: 'Baik'
        };
      });
    }
  } catch (err) {
    console.error('Error fetching active loans:', err);
  }
};

onMounted(() => {
  loadActiveLoans();
});

const selectedLoanId = ref('');
const isScanning = ref(false);
const isScanCompleted = ref(false);
const dendaVerified = ref(false);

const isCameraActive = ref(false);
const cameraError = ref('');
let html5QrCode = null;

const selectedLoan = computed(() => {
  return activeLoansForReturn.value.find(l => l.id === selectedLoanId.value) || null;
});

// Condition form states
const returnCond = ref('Baik');
const returnNotes = ref('');
const isLeftDrag = ref(false);
const isRightDrag = ref(false);
const isSubmitSuccess = ref(false);

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

const startCamera = async () => {
  cameraError.value = '';
  isScanning.value = true;
  isCameraActive.value = false;
  
  await nextTick();
  
  html5QrCode = new Html5Qrcode("qr-reader");
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
      handleScanSuccess(decodedText);
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

const handleScanSuccess = (decodedText) => {
  playBeep();
  triggerVibrate();
  
  const scanVal = cleanDecodedText(decodedText);
  const found = activeLoansForReturn.value.find(l => 
    l.id.toLowerCase() === scanVal.toLowerCase() ||
    (l.itemId && l.itemId.toLowerCase() === scanVal.toLowerCase()) ||
    l.item.toLowerCase() === scanVal.toLowerCase()
  );
  
  if (found) {
    selectedLoanId.value = found.id;
    isScanCompleted.value = true;
    cleanupScanner();
  } else {
    cameraError.value = `ID/Kode Alat "${scanVal}" tidak ditemukan dalam daftar aktif peminjaman.`;
    setTimeout(() => {
      cameraError.value = '';
    }, 4000);
  }
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

// Calculate denda: Rp 10.000/day
const totalDenda = computed(() => {
  if (!selectedLoan.value) return 0;
  return selectedLoan.value.delayDays * 10000;
});

const formatRupiah = (number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(number);
};

const handleReturnSubmit = async () => {
  if (!selectedLoan.value) return;
  
  try {
    const response = await fetch('/api/borrowing/return', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        transaction_code: selectedLoan.value.id,
        status_condition: returnCond.value,
        notes: returnNotes.value || 'Catatan verifikasi pengembalian.',
        laboran_id: props.user.id
      })
    });
    
    const result = await response.json();
    if (response.ok && result.status === 'success') {
      isSubmitSuccess.value = true;
      setTimeout(() => {
        isSubmitSuccess.value = false;
        activeLoansForReturn.value = activeLoansForReturn.value.filter(l => l.id !== selectedLoanId.value);
        selectedLoanId.value = '';
        isScanCompleted.value = false;
        dendaVerified.value = false;
        returnNotes.value = '';
        returnCond.value = 'Baik';
        loadActiveLoans();
        emit('refresh-data');
      }, 2000);
    } else {
      alert(result.message || 'Gagal menyimpan pengembalian.');
    }
  } catch (err) {
    console.error('Error returning equipment:', err);
    alert('Terjadi kesalahan koneksi saat memproses pengembalian.');
  }
};

// Reset scan screen to repeat process
const handleResetScanner = () => {
  isScanCompleted.value = false;
  dendaVerified.value = false;
  selectedLoanId.value = '';
  returnCond.value = 'Baik';
  returnNotes.value = '';
  nextTick(() => {
    startCamera();
  });
};

onUnmounted(() => {
  cleanupScanner();
});
</script>

<template>
  <div class="pengembalian-alat-container">
    
    <!-- Success Alert Overlay -->
    <transition name="fade">
      <div v-if="isSubmitSuccess" class="success-overlay">
        <div class="success-modal">
          <div class="success-icon-badge">
            <CheckCircle :size="28" />
          </div>
          <h3>Pengembalian Berhasil</h3>
          <p>Status peminjaman alat telah berhasil diselesaikan dan dicatat secara permanen.</p>
        </div>
      </div>
    </transition>

    <!-- LANGKAH 1: CAMERA SCANNER (CENTERED, MINIMAL, DEEP WHITESPACE) -->
    <div v-if="!isScanCompleted" class="step-one-wrapper">
      <div class="scanner-hero">
        <h2>Validasi Pengembalian Alat</h2>
        <p>Arahkan kode QR peminjaman ke arah kamera pemindai aktif.</p>
      </div>

      <div class="scanner-viewport-wrapper">
        <div class="scanner-viewfinder" :class="{ 'scanning': isCameraActive }">
          <div class="reticle-corner top-left"></div>
          <div class="reticle-corner top-right"></div>
          <div class="reticle-corner bottom-left"></div>
          <div class="reticle-corner bottom-right"></div>
          
          <!-- html5-qrcode video element destination -->
          <div id="qr-reader" :style="{ visibility: isCameraActive ? 'visible' : 'hidden', position: isCameraActive ? 'relative' : 'absolute', width: '100%', height: '100%' }"></div>

          <!-- Video placeholder / status prompt -->
          <div v-if="!isCameraActive" class="viewport-content">
            <Camera :size="32" class="viewport-icon text-secondary" v-if="!isScanning" />
            <RefreshCw :size="32" class="viewport-icon text-action animate-spin-slow" v-if="isScanning" />
            
            <p v-if="cameraError" class="viewport-text error-text">{{ cameraError }}</p>
            <p v-else-if="isScanning" class="viewport-text">Mengakses Kamera...</p>
            <p v-else class="viewport-text">Kamera dinonaktifkan</p>
            
            <button v-if="!isScanning" @click="startCamera" class="btn-activate-camera">
              {{ cameraError ? 'Coba Lagi' : 'Aktifkan Kamera' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- LANGKAH 2: FORM SPLIT-PANE & HITL CONTROL (SLIDES DOWN ON SUCCESSFUL SCAN) -->
    <transition name="slide-down">
      <div v-if="isScanCompleted && selectedLoan" class="step-two-wrapper">
        
        <!-- Header to show return progress and allows resetting back to scan step -->
        <div class="step-two-header">
          <div>
            <span class="back-link-pill" @click="handleResetScanner">← Ulangi Pemindaian</span>
            <h2>Detail Verifikasi Pengembalian</h2>
            <p>Sistem memverifikasi QR Code: <strong>{{ selectedLoan.id }}</strong> (Peminjam: {{ selectedLoan.user }})</p>
          </div>
        </div>

        <!-- HITL LATENESS FINE SECTION -->
        <div v-if="selectedLoan.delayDays > 0" class="fine-hitl-card">
          <div class="fine-header">
            <div class="fine-warning-badge">
              <AlertTriangle :size="18" />
            </div>
            <div>
              <h3>Keterlambatan Terdeteksi</h3>
              <p>Alat dikembalikan terlambat {{ selectedLoan.delayDays }} hari dari tenggat waktu.</p>
            </div>
          </div>
          
          <div class="fine-body">
            <div class="fine-display-group">
              <span class="fine-meta-label">Total Denda Akumulasi</span>
              <div class="fine-giant-value">{{ formatRupiah(totalDenda) }}</div>
            </div>
            
            <div class="fine-hitl-controls">
              <button 
                @click="dendaVerified = !dendaVerified" 
                class="btn-verify-denda"
                :class="{ 'verified': dendaVerified }"
              >
                <span v-if="!dendaVerified">Verifikasi & Setujui Denda</span>
                <span v-else>Denda Disetujui ✓</span>
              </button>
            </div>
          </div>
        </div>

        <!-- SPLIT-PANE CONDITIONS & FILES -->
        <div class="split-pane-layout">
          
          <!-- LEFT PANE: INITIAL CONDITION -->
          <div class="pane-card">
            <div class="pane-title">
              <span class="pane-dot dot-initial"></span>
              <h3>Kondisi Awal (Serah Terima)</h3>
            </div>
            
            <div class="pane-fields">
              <div class="field-pill">
                <span class="field-lbl">Nama Alat</span>
                <span class="field-val">{{ selectedLoan.item }}</span>
              </div>
              <div class="field-pill">
                <span class="field-lbl">Kondisi Serah Terima</span>
                <span class="field-val status-badge-good">{{ selectedLoan.initialCond }}</span>
              </div>
            </div>

            <!-- Initial photo if condition is not perfect -->
            <transition name="fade">
              <div v-if="returnCond !== 'Sangat Baik' && returnCond !== 'Baik'" class="preview-upload-zone">
                <Image :size="24" class="upload-icon-dimmed" />
                <span>Foto Awal Peminjaman</span>
                <p class="upload-filename">Tidak ada foto serah terima awal</p>
              </div>
            </transition>
          </div>

          <!-- RIGHT PANE: RETURN CONDITION (EDITABLE) -->
          <div class="pane-card">
            <div class="pane-title">
              <span class="pane-dot dot-return"></span>
              <h3>Kondisi Pengembalian (Fisik Alat)</h3>
            </div>

            <div class="pane-fields">
              <div class="select-group">
                <label class="select-label">Kondisi Fisik Saat Ini</label>
                <select v-model="returnCond" class="form-select-pill">
                  <option value="Sangat Baik">Sangat Baik</option>
                  <option value="Baik">Baik</option>
                  <option value="Cukup">Cukup</option>
                  <option value="Rusak Ringan">Rusak Ringan</option>
                  <option value="Rusak Berat">Rusak Berat</option>
                </select>
              </div>
            </div>

            <!-- CONDITIONAL PHOTO UPLOAD & TEXTAREA (Progressive Disclosure) -->
            <transition name="fade">
              <div v-if="returnCond !== 'Sangat Baik' && returnCond !== 'Baik'" class="conditional-inputs">
                <div class="upload-zone-drop">
                  <UploadCloud :size="28" class="upload-icon-action" />
                  <span>Unggah Foto Bukti Fisik Kerusakan</span>
                  <p class="upload-instruction">Seret dan lepas foto ke sini, atau klik untuk memilih file</p>
                </div>

                <div class="textarea-group">
                  <label class="textarea-label">Catatan Detail Kerusakan</label>
                  <textarea 
                    v-model="returnNotes" 
                    placeholder="Tuliskan deskripsi kerusakan alat secara mendetail..." 
                    class="form-textarea-pill"
                    rows="3"
                  ></textarea>
                </div>
              </div>
            </transition>
          </div>
          
        </div>

        <!-- MAIN SUBMIT ACTION ROW -->
        <div class="submit-action-row">
          <button 
            @click="handleReturnSubmit" 
            class="btn-confirm-return"
            :disabled="selectedLoan.delayDays > 0 && !dendaVerified"
          >
            <span v-if="selectedLoan.delayDays > 0 && !dendaVerified">
              Harap Verifikasi & Setujui Denda Dahulu
            </span>
            <span v-else>
              Setujui Pengembalian
            </span>
          </button>
        </div>

      </div>
    </transition>

  </div>
</template>

<style scoped>
.pengembalian-alat-container {
  width: 100%;
  min-height: calc(100vh - 120px);
  display: flex;
  flex-direction: column;
  color: var(--color-text-primary);
}

/* STEP 1: SCANNER HERO & LAYOUT (CENTERED WITH DEEP WHITESPACE) */
.step-one-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 24px;
  max-width: 620px;
  margin: 0 auto;
  width: 100%;
  text-align: center;
}

.scanner-hero h2 {
  font-size: 2rem;
  font-weight: 800;
  color: var(--color-text-primary);
  letter-spacing: -0.5px;
}

.scanner-hero p {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  margin-top: 8px;
  margin-bottom: 40px;
}

.scanner-viewport-wrapper {
  width: 100%;
  display: flex;
  justify-content: center;
  margin-bottom: 40px;
}

.scanner-viewfinder {
  position: relative;
  width: 100%;
  max-width: 480px;
  height: 280px;
  background: #0a0f1d;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: var(--radius-xl);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: border-color 0.2s ease-out;
  z-index: 10;
}

.scanner-viewfinder.scanning {
  border-color: var(--color-action-primary);
}

/* Reticle Frames */
.reticle-corner {
  position: absolute;
  width: 24px;
  height: 24px;
  border: 3px solid transparent;
  pointer-events: none;
  z-index: 15;
}

.top-left {
  top: 24px;
  left: 24px;
  border-top-color: rgba(255, 255, 255, 0.25);
  border-left-color: rgba(255, 255, 255, 0.25);
}

.top-right {
  top: 24px;
  right: 24px;
  border-top-color: rgba(255, 255, 255, 0.25);
  border-right-color: rgba(255, 255, 255, 0.25);
}

.bottom-left {
  bottom: 24px;
  left: 24px;
  border-bottom-color: rgba(255, 255, 255, 0.25);
  border-left-color: rgba(255, 255, 255, 0.25);
}

.bottom-right {
  bottom: 24px;
  right: 24px;
  border-bottom-color: rgba(255, 255, 255, 0.25);
  border-right-color: rgba(255, 255, 255, 0.25);
}

.scanner-viewfinder.scanning .top-left { border-top-color: var(--color-action-primary); border-left-color: var(--color-action-primary); }
.scanner-viewfinder.scanning .top-right { border-top-color: var(--color-action-primary); border-right-color: var(--color-action-primary); }
.scanner-viewfinder.scanning .bottom-left { border-bottom-color: var(--color-action-primary); border-left-color: var(--color-action-primary); }
.scanner-viewfinder.scanning .bottom-right { border-bottom-color: var(--color-action-primary); border-right-color: var(--color-action-primary); }

.viewport-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 32px;
  z-index: 10;
}

.viewport-icon {
  color: var(--color-text-secondary);
  opacity: 0.7;
}

.viewport-icon.text-action {
  color: var(--color-action-primary);
  opacity: 1;
}

.viewport-text {
  font-family: var(--font-primary);
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-secondary);
}

.viewport-text.error-text {
  color: var(--color-danger);
}

.btn-activate-camera {
  background: var(--color-action-primary);
  color: white;
  border: none;
  font-family: var(--font-number), sans-serif;
  font-size: 0.875rem;
  font-weight: 600;
  padding: 12px 24px;
  border-radius: 9999px; /* pill-shaped */
  cursor: pointer;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s;
  box-shadow: none;
}

.btn-activate-camera:hover {
  background: #355670;
}

.btn-activate-camera:active {
  transform: scale(0.98);
}

#qr-reader {
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 5;
}

#qr-reader :deep(video) {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover !important;
}

#qr-reader :deep(span),
#qr-reader :deep(button),
#qr-reader :deep(select) {
  display: none !important;
}

/* STEP 2: DETAIL SUMMARY LAYOUT */
.step-two-wrapper {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.step-two-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.back-link-pill {
  display: inline-block;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-action-primary);
  background: var(--color-primary-light);
  padding: 6px 14px;
  border-radius: 9999px; /* pill-shaped */
  cursor: pointer;
  margin-bottom: 12px;
  transition: transform 0.2s, background-color 0.2s;
}

.back-link-pill:hover {
  background: rgba(70, 112, 143, 0.08);
}

.back-link-pill:active {
  transform: scale(0.98);
}

.step-two-header h2 {
  font-size: 1.75rem;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.step-two-header p {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  margin-top: 4px;
}

/* HITL LATENESS FINE CARD */
.fine-hitl-card {
  background: rgba(254, 242, 242, 0.75);
  border-radius: var(--radius-xl);
  padding: 32px;
  box-shadow: var(--shadow-soft);
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.fine-header {
  display: flex;
  gap: 16px;
  align-items: center;
}

.fine-warning-badge {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(239, 68, 68, 0.08);
  color: var(--color-danger);
  display: flex;
  align-items: center;
  justify-content: center;
}

.fine-header h3 {
  font-size: 1.125rem;
  font-weight: 750;
  color: #991b1b;
}

.fine-header p {
  font-size: 0.8125rem;
  color: #b91c1c;
  margin-top: 2px;
}

.fine-body {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 24px;
}

.fine-display-group {
  display: flex;
  flex-direction: column;
}

.fine-meta-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #b91c1c;
  letter-spacing: 0.8px;
  margin-bottom: 4px;
}

/* Extremely large typographic display using Instrument Serif (No VibeCode Neon) */
.fine-giant-value {
  font-family: var(--font-display);
  font-size: 5rem;
  font-weight: 400;
  font-style: italic;
  color: var(--color-danger);
  line-height: 1;
  letter-spacing: -2px;
}

.fine-hitl-controls {
  display: flex;
  align-items: center;
}

.btn-verify-denda {
  background: transparent;
  border: 1.5px solid var(--color-danger);
  color: var(--color-danger);
  font-family: var(--font-primary);
  font-size: 0.875rem;
  font-weight: 700;
  padding: 12px 28px;
  border-radius: 9999px; /* pill-shaped */
  cursor: pointer;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s, border-color 0.2s, color 0.2s;
}

.btn-verify-denda:hover {
  background: var(--color-danger);
  color: white;
}

.btn-verify-denda:active {
  transform: scale(0.98); /* Tactile */
}

.btn-verify-denda.verified {
  background: var(--color-success);
  border-color: var(--color-success);
  color: white;
  cursor: default;
}

.btn-verify-denda.verified:hover {
  background: var(--color-success);
  color: white;
}

/* SPLIT-PANE GRID SYSTEM */
.split-pane-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
}

.pane-card {
  background: var(--color-surface);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  padding: 32px;
  box-shadow: var(--shadow-soft);
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.pane-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.pane-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.dot-initial {
  background: var(--color-action-primary);
}

.dot-return {
  background: var(--color-success);
}

.pane-title h3 {
  font-size: 1rem;
  font-weight: 750;
  color: var(--color-text-primary);
}

.pane-fields {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.field-pill {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(0, 0, 0, 0.015);
  padding: 14px 20px;
  border-radius: 9999px; /* pill-shaped */
  font-size: 0.8125rem;
}

.field-lbl {
  font-weight: 500;
  color: var(--color-text-secondary);
}

.field-val {
  font-weight: 600;
  color: var(--color-text-primary);
}

.status-badge-good {
  background: var(--color-primary-light);
  color: var(--color-action-primary);
  padding: 4px 12px;
  border-radius: 9999px; /* pill-shaped */
  font-weight: 700;
}

/* Upload zone representation for initial condition */
.preview-upload-zone {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: rgba(0, 0, 0, 0.015);
  border: 1px solid rgba(148, 163, 184, 0.12);
  border-radius: var(--radius-lg);
  gap: 8px;
  text-align: center;
}

.upload-icon-dimmed {
  color: var(--color-text-secondary);
}

.preview-upload-zone span {
  font-size: 0.8125rem;
  font-weight: 600;
}

.upload-filename {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
}

/* EDITABLE RETURN PANEL */
.select-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.select-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-text-secondary);
  padding-left: 8px;
}

.form-select-pill {
  background: rgba(255, 255, 255, 0.8);
  border: none;
  padding: 14px 20px;
  border-radius: 9999px; /* pill-shaped */
  font-family: var(--font-primary);
  font-size: 0.875rem;
  outline: none;
  cursor: pointer;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s;
}

.form-select-pill:focus {
  background: #ffffff;
}

.form-select-pill:active {
  transform: scale(0.98); /* Tactile */
}

/* CONDITIONAL DISCLOSURE */
.conditional-inputs {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.upload-zone-drop {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px 20px;
  background: rgba(255, 255, 255, 0.5);
  border: 1.5px dashed rgba(70, 112, 143, 0.2);
  border-radius: var(--radius-lg);
  cursor: pointer;
  gap: 10px;
  text-align: center;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s, background-color 0.2s;
}

.upload-zone-drop:hover {
  background: #ffffff;
  border-color: var(--color-action-primary);
}

.upload-zone-drop:active {
  transform: scale(0.98);
}

.upload-icon-action {
  color: var(--color-action-primary);
}

.upload-zone-drop span {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-text-primary);
}

.upload-instruction {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
}

.textarea-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.textarea-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-text-secondary);
  padding-left: 8px;
}

.form-textarea-pill {
  background: rgba(255, 255, 255, 0.8);
  border: none;
  padding: 16px 20px;
  border-radius: var(--radius-lg);
  font-family: var(--font-primary);
  font-size: 0.875rem;
  outline: none;
  resize: vertical;
  line-height: 1.5;
  transition: background-color 0.2s;
}

.form-textarea-pill:focus {
  background: #ffffff;
}

/* CONFIRM RETURN ACTION */
.submit-action-row {
  display: flex;
  justify-content: flex-end;
  margin-top: 16px;
}

.btn-confirm-return {
  background: var(--color-success);
  color: white;
  border: none;
  font-family: var(--font-primary);
  font-size: 0.9375rem;
  font-weight: 600;
  padding: 16px 36px;
  border-radius: 9999px; /* pill-shaped */
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.12);
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s, box-shadow 0.2s;
}

.btn-confirm-return:hover:not(:disabled) {
  background: #059669;
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.18);
}

.btn-confirm-return:active:not(:disabled) {
  transform: scale(0.98); /* Tactile Press */
}

.btn-confirm-return:disabled {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
  border: 1px dashed rgba(16, 185, 129, 0.3);
  opacity: 0.8;
  cursor: not-allowed;
  box-shadow: none;
}

/* SUCCESS MODAL & OVERLAY */
.success-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.15);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
}

.success-modal {
  width: 90%;
  max-width: 360px;
  background: #ffffff;
  padding: 32px 24px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  border-radius: var(--radius-xl);
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
}

.success-icon-badge {
  width: 54px;
  height: 54px;
  border-radius: 50%;
  background: var(--color-success-light);
  color: var(--color-success);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.success-modal h3 {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: 8px;
}

.success-modal p {
  font-size: 0.8125rem;
  color: var(--color-text-secondary);
  line-height: 1.4;
}

/* ANIMATIONS */
@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin-slow {
  animation: spin-slow 3s linear infinite;
}

/* Transitions */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-24px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .split-pane-layout {
    grid-template-columns: 1fr;
    gap: 24px;
  }
}
</style>
