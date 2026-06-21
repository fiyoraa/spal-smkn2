<script setup>
import { ref, watch, computed } from 'vue';
import { 
  Calendar, 
  CheckCircle,
  Bookmark,
  ChevronLeft,
  ChevronRight,
  Info
} from '@lucide/vue';

const props = defineProps({
  selectedItem: {
    type: Object,
    default: null
  },
  user: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['submit-success', 'cancel']);

const form = ref({
  itemName: '',
  itemId: '',
  jumlah: 1,
  tglPinjam: '',
  tglKembali: '',
  keperluan: ''
});

// Calendar logic for June 2026
const currentYear = 2026;
const currentMonth = 5; // June
const monthName = 'Juni 2026';

const daysInMonth = 30;
const startDayOfWeek = 1; // 1st of June 2026 is Monday

const calendarDays = computed(() => {
  const days = [];
  // Dummy slots for offset
  for (let i = 1; i < startDayOfWeek; i++) {
    days.push({ dayNum: '', dateStr: '', status: 'empty' });
  }
  for (let d = 1; d <= daysInMonth; d++) {
    const dayStr = d < 10 ? `0${d}` : `${d}`;
    const dateStr = `2026-06-${dayStr}`;
    
    // Simulate some busy dates (e.g. 19 and 20 are busy)
    let status = 'Tersedia';
    if (d === 19 || d === 20) {
      status = 'Dipinjam';
    }
    
    days.push({
      dayNum: d,
      dateStr,
      status
    });
  }
  return days;
});

const handleDayClick = (dayObj) => {
  if (dayObj.status === 'Dipinjam' || !dayObj.dayNum) return;
  
  const dateStr = dayObj.dateStr;
  
  if (!form.value.tglPinjam || (form.value.tglPinjam && form.value.tglKembali)) {
    form.value.tglPinjam = dateStr;
    form.value.tglKembali = '';
  } else if (form.value.tglPinjam && !form.value.tglKembali) {
    if (new Date(dateStr) >= new Date(form.value.tglPinjam)) {
      form.value.tglKembali = dateStr;
    } else {
      form.value.tglPinjam = dateStr;
    }
  }
};

const isDateSelected = (dateStr) => {
  return form.value.tglPinjam === dateStr || form.value.tglKembali === dateStr;
};

const isDateInRange = (dateStr) => {
  if (!form.value.tglPinjam || !form.value.tglKembali) return false;
  const curr = new Date(dateStr);
  const start = new Date(form.value.tglPinjam);
  const end = new Date(form.value.tglKembali);
  return curr > start && curr < end;
};

const isStepOneCompleted = computed(() => {
  return form.value.tglPinjam !== '' && form.value.tglKembali !== '';
});

watch(() => props.selectedItem, (newVal) => {
  if (newVal) {
    form.value.itemName = newVal.name;
    form.value.itemId = newVal.id;
    form.value.tglPinjam = '';
    form.value.tglKembali = '';
    form.value.keperluan = '';
  }
}, { immediate: true });

const isSubmitting = ref(false);
const showSuccess = ref(false);

const submitForm = async () => {
  if (!form.value.itemName || !form.value.tglPinjam || !form.value.tglKembali || !form.value.keperluan) {
    alert('Harap lengkapi alasan keperluan peminjaman!');
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    const response = await fetch('/api/borrowing/request', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        user_id: props.user.id,
        equipment_id: props.selectedItem.dbId,
        quantity: form.value.jumlah,
        reason: form.value.keperluan,
        date_start: form.value.tglPinjam,
        date_end: form.value.tglKembali
      })
    });
    
    const result = await response.json();
    if (response.ok && result.status === 'success') {
      showSuccess.value = true;
      
      const successData = {
        id: `PEM-${result.data.id}`,
        dbId: result.data.id,
        itemName: form.value.itemName,
        itemId: form.value.itemId,
        jumlah: form.value.jumlah,
        tglPinjam: form.value.tglPinjam,
        tglKembali: form.value.tglKembali,
        keperluan: form.value.keperluan,
        status: 'Pending'
      };
      
      setTimeout(() => {
        showSuccess.value = false;
        form.value = {
          itemName: '',
          itemId: '',
          jumlah: 1,
          tglPinjam: '',
          tglKembali: '',
          keperluan: ''
        };
        emit('submit-success', successData);
        emit('cancel'); // Clear selection
      }, 2000);
    } else {
      alert(result.message || 'Gagal mengajukan peminjaman.');
    }
  } catch (err) {
    console.error('Error submitting borrowing request:', err);
    alert('Terjadi kesalahan koneksi saat mengirim permintaan.');
  } finally {
    isSubmitting.value = false;
  }
};

const formatDateReadable = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
  <div class="form-peminjaman-container">
    
    <!-- Success Alert Overlay -->
    <transition name="fade">
      <div v-if="showSuccess" class="success-overlay">
        <div class="success-modal">
          <div class="success-icon-badge">
            <CheckCircle :size="28" />
          </div>
          <h3>Pengajuan Terkirim</h3>
          <p>Permintaan peminjaman Anda sedang diproses oleh Laboran.</p>
        </div>
      </div>
    </transition>

    <!-- IF NO ITEM SELECTED STATE -->
    <div v-if="!selectedItem" class="form-empty-state">
      <Bookmark :size="24" class="empty-icon" />
      <h3>Formulir Reservasi</h3>
      <p>Pilih instrumen yang tersedia dari katalog index di sebelah kiri untuk mengisi form peminjaman.</p>
    </div>

    <!-- IF SELECTED ITEM STATE (NO OUTSIDE CARD CONTAINER, JUST SPACE) -->
    <div v-else class="form-flow-wrapper">
      
      <!-- HEADER -->
      <div class="form-header">
        <div class="selected-meta">
          <span class="item-tag">{{ selectedItem.id }}</span>
          <h2>Ajukan Reservasi</h2>
          <p class="item-title-desc">{{ selectedItem.name }} &bull; {{ selectedItem.loc }}</p>
        </div>
      </div>

      <!-- PROGRESSIVE DISCLOSURE STEP 1: INTERACTIVE AVAILABILITY CALENDAR -->
      <div class="calendar-step">
        <div class="step-label">
          <span class="step-num">1</span>
          <div>
            <h3>Jadwal Peminjaman</h3>
            <p>Pilih rentang tanggal mulai dan pengembalian.</p>
          </div>
        </div>

        <!-- Custom Calendar Box (Borderless, only thin boundary line) -->
        <div class="custom-calendar-box">
          <div class="calendar-month-header">
            <h4>{{ monthName }}</h4>
          </div>
          
          <div class="calendar-weekdays">
            <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
          </div>

          <div class="calendar-days-grid">
            <button 
              v-for="(day, idx) in calendarDays" 
              :key="idx"
              class="cal-day-btn"
              :class="{
                'cal-empty': !day.dayNum,
                'cal-booked': day.status === 'Dipinjam',
                'cal-selected': isDateSelected(day.dateStr),
                'cal-in-range': day.dayNum && isDateInRange(day.dateStr)
              }"
              :disabled="day.status === 'Dipinjam' || !day.dayNum"
              @click="handleDayClick(day)"
            >
              {{ day.dayNum }}
            </button>
          </div>

          <div class="calendar-legend">
            <div class="legend-item"><span class="leg-dot leg-avail"></span><span>Tersedia</span></div>
            <div class="legend-item"><span class="leg-dot leg-booked"></span><span>Dipinjam</span></div>
          </div>
        </div>

        <!-- Selected Dates Summary -->
        <div class="dates-summary" v-if="form.tglPinjam">
          <div class="summary-pill">
            <span class="sum-lbl">Mulai</span>
            <span class="sum-val">{{ formatDateReadable(form.tglPinjam) }}</span>
          </div>
          <div class="summary-pill" v-if="form.tglKembali">
            <span class="sum-lbl">Kembali</span>
            <span class="sum-val">{{ formatDateReadable(form.tglKembali) }}</span>
          </div>
        </div>
      </div>

      <!-- PROGRESSIVE DISCLOSURE STEP 2: DETAILS (SLIDES DOWN) -->
      <transition name="slide-down">
        <div v-if="isStepOneCompleted" class="form-inputs-step">
          <div class="step-label border-top-divider">
            <span class="step-num">2</span>
            <div>
              <h3>Tujuan & Unit</h3>
              <p>Rincian alasan peminjaman dan kuantitas unit.</p>
            </div>
          </div>

          <div class="form-fields">
            <!-- Unit Selector -->
            <div class="input-field-group">
              <label class="field-label">Jumlah Unit</label>
              <input 
                type="number" 
                v-model="form.jumlah" 
                min="1" 
                max="5"
                required 
                class="form-input-pill"
              />
            </div>

            <!-- Purpose text area -->
            <div class="input-field-group">
              <label class="field-label">Detail Tujuan Praktikum</label>
              <textarea 
                v-model="form.keperluan" 
                placeholder="Tuliskan tujuan peminjaman alat secara spesifik..."
                required
                class="form-textarea-pill"
                rows="3"
              ></textarea>
            </div>
          </div>

          <!-- Bottom Actions (Tactile Digital Clicks) -->
          <div class="action-footer">
            <button type="button" @click="emit('cancel')" class="btn-cancel-pill">
              Batal
            </button>
            <button 
              @click="submitForm" 
              class="btn-submit-pill" 
              :disabled="isSubmitting"
            >
              <span v-if="!isSubmitting">Ajukan Peminjaman</span>
              <span v-else>Memproses...</span>
            </button>
          </div>
        </div>
      </transition>

    </div>
  </div>
</template>

<style scoped>
.form-peminjaman-container {
  width: 100%;
}

/* NO CARD CONTAINER LOOK (Archival Index Style) */
.form-empty-state {
  border: 1px dashed rgba(30, 41, 59, 0.08);
  border-radius: var(--radius-xl);
  padding: 64px 32px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.empty-icon {
  color: var(--color-text-secondary);
  opacity: 0.4;
}

.form-empty-state h3 {
  font-size: 1.125rem;
  font-weight: 800;
  color: var(--color-text-primary);
}

.form-empty-state p {
  font-size: 0.8125rem;
  color: var(--color-text-secondary);
  line-height: 1.5;
  max-width: 260px;
}

.form-flow-wrapper {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.form-header {
  border-bottom: 1px solid rgba(30, 41, 59, 0.08);
  padding-bottom: 24px;
}

.item-tag {
  font-family: var(--font-number); /* Geist */
  font-size: 0.6875rem;
  font-weight: 700;
  color: var(--color-action-primary);
  background: var(--color-primary-light);
  padding: 4px 12px;
  border-radius: 9999px; /* pill-shaped */
  display: inline-block;
  margin-bottom: 8px;
}

.selected-meta h2 {
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.item-title-desc {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  margin-top: 2px;
}

/* STEP HEADER DISPLAY */
.step-label {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  margin-bottom: 20px;
}

.step-num {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--color-action-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  flex-shrink: 0;
  margin-top: 2px;
}

.step-label h3 {
  font-size: 0.9375rem;
  font-weight: 750;
  color: var(--color-text-primary);
}

.step-label p {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  margin-top: 2px;
}

.border-top-divider {
  border-top: 1px solid rgba(30, 41, 59, 0.08);
  padding-top: 32px;
}

/* CUSTOM VISUAL CALENDAR (NO HEAVY SHADOWS/CARDS) */
.custom-calendar-box {
  background: rgba(0, 0, 0, 0.01);
  border: 1px solid rgba(30, 41, 59, 0.04);
  border-radius: var(--radius-lg);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.calendar-month-header {
  text-align: center;
  font-weight: 700;
  font-size: 0.875rem;
  color: var(--color-text-primary);
}

.calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  font-size: 0.6875rem;
  font-weight: 700;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.calendar-days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 6px;
}

.cal-day-btn {
  background: transparent;
  border: none;
  font-family: var(--font-primary);
  font-size: 0.8125rem;
  font-weight: 550;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--color-text-primary);
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s, color 0.2s;
}

.cal-day-btn:hover:not(.cal-empty):not(.cal-booked) {
  background: rgba(70, 112, 143, 0.05);
  color: var(--color-action-primary);
}

.cal-day-btn:active:not(.cal-empty):not(.cal-booked) {
  transform: scale(0.95);
}

.cal-empty {
  cursor: default;
  pointer-events: none;
}

.cal-booked {
  background: rgba(239, 68, 68, 0.03);
  color: #94a3b8;
  text-decoration: line-through;
  cursor: not-allowed;
}

.cal-selected {
  background: var(--color-action-primary) !important;
  color: white !important;
  font-weight: 700;
}

.cal-in-range {
  background: var(--color-primary-light);
  border-radius: var(--radius-sm);
  color: var(--color-action-primary);
}

.calendar-legend {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin-top: 8px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.6875rem;
  color: var(--color-text-secondary);
}

.leg-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.leg-avail {
  background: #cbd5e1;
}

.leg-booked {
  background: rgba(239, 68, 68, 0.2);
}

/* SELECTED DATES PILLS */
.dates-summary {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

.summary-pill {
  flex: 1;
  background: rgba(0, 0, 0, 0.015);
  padding: 10px 16px;
  border-radius: 9999px; /* pill-shaped */
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.75rem;
}

.sum-lbl {
  color: var(--color-text-secondary);
  font-weight: 500;
}

.sum-val {
  color: var(--color-text-primary);
  font-weight: 700;
}

/* STEP 2 INPUT DETAILS */
.form-fields {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 24px;
}

.input-field-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-text-secondary);
  padding-left: 8px;
}

/* Form controls with Focus Underline Glow (no shadow boxes) */
.form-input-pill {
  background: rgba(0, 0, 0, 0.015);
  border: 1px solid rgba(30, 41, 59, 0.05);
  padding: 14px 20px;
  border-radius: 9999px; /* pill-shaped */
  font-family: var(--font-primary);
  font-size: 0.875rem;
  outline: none;
  transition: background-color 0.2s, border-color 0.2s, box-shadow 0.2s;
}

.form-input-pill:focus {
  background: #ffffff;
  border-color: var(--color-action-primary);
  box-shadow: 0 0 0 3px rgba(70, 112, 143, 0.08); /* Underline focus glow wrapper */
}

.form-textarea-pill {
  background: rgba(0, 0, 0, 0.015);
  border: 1px solid rgba(30, 41, 59, 0.05);
  padding: 16px 20px;
  border-radius: var(--radius-lg);
  font-family: var(--font-primary);
  font-size: 0.875rem;
  outline: none;
  resize: vertical;
  line-height: 1.5;
  transition: background-color 0.2s, border-color 0.2s, box-shadow 0.2s;
}

.form-textarea-pill:focus {
  background: #ffffff;
  border-color: var(--color-action-primary);
  box-shadow: 0 0 0 3px rgba(70, 112, 143, 0.08); /* Underline focus glow wrapper */
}

/* FOOTER ACTIONS */
.action-footer {
  display: flex;
  gap: 12px;
}

.btn-cancel-pill {
  flex: 1;
  background: transparent;
  border: 1.5px solid rgba(30, 41, 59, 0.15);
  font-family: var(--font-primary);
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--color-text-primary);
  padding: 14px;
  border-radius: 9999px; /* pill-shaped */
  cursor: pointer;
  transition: transform 0.2s, background-color 0.2s;
}

.btn-cancel-pill:hover {
  background: rgba(0, 0, 0, 0.02);
}

.btn-cancel-pill:active {
  transform: scale(0.98);
}

.btn-submit-pill {
  flex: 2;
  background: var(--color-action-primary);
  color: white;
  border: none;
  font-family: var(--font-primary);
  font-size: 0.875rem;
  font-weight: 700;
  padding: 14px 28px;
  border-radius: 9999px; /* pill-shaped */
  cursor: pointer;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-submit-pill:hover:not(:disabled) {
  background: #355670;
}

.btn-submit-pill:active:not(:disabled) {
  transform: scale(0.98); /* Tactile click response */
}

.btn-submit-pill:disabled {
  background: #e2e8f0;
  color: #94a3b8;
  opacity: 0.55;
  cursor: not-allowed;
}

/* SUCCESS PANEL OVERLAY */
.success-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  border-radius: var(--radius-xl);
}

.success-modal {
  width: 90%;
  max-width: 300px;
  background: #ffffff;
  padding: 32px 24px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  border-radius: var(--radius-xl);
}

.success-icon-badge {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: var(--color-success-light);
  color: var(--color-success);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.success-modal h3 {
  font-size: 0.9375rem;
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
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  max-height: 400px;
  overflow: hidden;
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-20px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease-out;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 480px) {
  .dates-summary {
    flex-direction: column;
    gap: 8px;
  }
  
  .summary-pill {
    padding: 8px 12px;
  }
  
  .custom-calendar-box {
    padding: 12px;
  }
  
  .calendar-weekdays span {
    font-size: 0.625rem;
  }
  
  .cal-day-btn {
    height: 30px;
    font-size: 0.75rem;
  }
  
  .action-footer {
    flex-direction: column-reverse;
    gap: 8px;
  }

  .btn-cancel-pill, .btn-submit-pill {
    width: 100%;
    flex: none;
  }
}
</style>
