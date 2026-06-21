<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
  CheckCircle2, 
  XCircle, 
  Clock, 
  User, 
  Wrench, 
  Calendar,
  AlertTriangle,
  Info,
  ChevronRight,
  X
} from '@lucide/vue';

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['refresh-data']);

const pendingApprovals = ref([]);
const processedApprovals = ref([]);

const selectedRequestId = ref('');
const isSlideOverOpen = ref(false);

// Rejection states
const isRejectFormOpen = ref(false);
const rejectReason = ref('');

// Mobile responsive states
const isMobile = ref(false);
const mobileActiveTab = ref('pending');

const selectedRequest = computed(() => {
  return pendingApprovals.value.find(r => r.id === selectedRequestId.value) || null;
});

const checkIfMobile = () => {
  isMobile.value = window.innerWidth <= 768;
};

const loadPendingApprovals = async () => {
  try {
    const response = await fetch('/api/borrowings?status=Pending');
    const result = await response.json();
    if (result.status === 'success') {
      pendingApprovals.value = result.data.map(req => ({
        id: req.id,
        dbId: req.dbId,
        userId: req.userId,
        userName: req.userName,
        className: req.className,
        itemName: req.itemName,
        reason: req.reason,
        dateReq: req.dateReq,
        trustText: 'Sedang memuat data kepercayaan...',
        trustLevel: 'good'
      }));
      
      // Fetch trust histories dynamically
      pendingApprovals.value.forEach(async (req, idx) => {
        try {
          const resp = await fetch(`/api/transactions?user_id=${req.userId}`);
          const res = await resp.json();
          if (res.status === 'success') {
            const lateCount = res.data.filter(t => t.status === 'Terlambat').length;
            if (lateCount === 0) {
              pendingApprovals.value[idx].trustText = 'Siswa/Guru ini belum pernah terlambat.';
              pendingApprovals.value[idx].trustLevel = 'good';
            } else if (lateCount === 1) {
              pendingApprovals.value[idx].trustText = 'Pernah terlambat 1 kali.';
              pendingApprovals.value[idx].trustLevel = 'warning';
            } else {
              pendingApprovals.value[idx].trustText = `Pernah terlambat ${lateCount} kali.`;
              pendingApprovals.value[idx].trustLevel = 'danger';
            }
          }
        } catch (e) {
          pendingApprovals.value[idx].trustText = 'Tidak ada riwayat keterlambatan.';
        }
      });
    }
  } catch (err) {
    console.error('Error fetching pending approvals:', err);
  }
};

onMounted(() => {
  loadPendingApprovals();
  checkIfMobile();
  window.addEventListener('resize', checkIfMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkIfMobile);
});


const handleSelectRequest = (id) => {
  selectedRequestId.value = id;
  isSlideOverOpen.value = true;
  isRejectFormOpen.value = false;
  rejectReason.value = '';
};

const handleCloseSlideOver = () => {
  isSlideOverOpen.value = false;
  setTimeout(() => {
    selectedRequestId.value = '';
  }, 300);
};

const handleApproveAction = async () => {
  if (!selectedRequest.value) return;
  
  const req = selectedRequest.value;
  try {
    const response = await fetch(`/api/borrowing/approve/${req.dbId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        laboran_id: props.user.id
      })
    });
    
    const result = await response.json();
    if (response.ok && result.status === 'success') {
      processedApprovals.value.unshift({
        ...req,
        status: 'Disetujui',
        processedTime: 'Baru saja'
      });
      pendingApprovals.value = pendingApprovals.value.filter(r => r.id !== req.id);
      emit('refresh-data');
      handleCloseSlideOver();
    } else {
      alert(result.message || 'Gagal menyetujui pengajuan.');
    }
  } catch (err) {
    console.error('Error approving request:', err);
  }
};

const handleRejectClick = () => {
  if (!isRejectFormOpen.value) {
    isRejectFormOpen.value = true;
  } else {
    submitRejection();
  }
};

const submitRejection = async () => {
  if (!rejectReason.value.trim() || !selectedRequest.value) return;
  
  const req = selectedRequest.value;
  try {
    const response = await fetch(`/api/borrowing/reject/${req.dbId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    const result = await response.json();
    if (response.ok && result.status === 'success') {
      processedApprovals.value.unshift({
        ...req,
        status: 'Ditolak',
        processedTime: 'Baru saja',
        rejectReason: rejectReason.value
      });
      pendingApprovals.value = pendingApprovals.value.filter(r => r.id !== req.id);
      emit('refresh-data');
      handleCloseSlideOver();
    } else {
      alert(result.message || 'Gagal menolak pengajuan.');
    }
  } catch (err) {
    console.error('Error rejecting request:', err);
  }
};
</script>

<template>
  <div class="approval-container" :class="{ 'is-mobile-device': isMobile }">
    
    <!-- DESKTOP LAYOUT -->
    <template v-if="!isMobile">
      <div class="approval-main-view">
        <div class="view-header-description">
          <h2>Permintaan Persetujuan</h2>
          <p>Kelola dan setujui peminjaman alat laboratorium secara manual.</p>
        </div>

        <div class="split-columns">
          
          <!-- PENDING LIST -->
          <div class="list-section">
            <div class="section-title-wrapper">
              <h3>Menunggu Aksi ({{ pendingApprovals.length }})</h3>
            </div>

            <div v-if="pendingApprovals.length > 0" class="clean-list">
              <div 
                v-for="req in pendingApprovals" 
                :key="req.id" 
                class="list-item"
                :class="{ 'item-selected': selectedRequestId === req.id }"
                @click="handleSelectRequest(req.id)"
              >
                <div class="item-meta">
                  <span class="item-borrower">{{ req.userName }}</span>
                  <span class="item-date">{{ req.dateReq }}</span>
                </div>
                <div class="item-body">
                  <span class="item-equipment">{{ req.itemName }}</span>
                  <ChevronRight :size="16" class="chevron-icon" />
                </div>
              </div>
            </div>

            <div v-else class="empty-state">
              <CheckCircle2 :size="32" class="text-success" />
              <p>Semua permintaan peminjaman telah selesai diproses.</p>
            </div>
          </div>

          <!-- RECENTLY PROCESSED HISTORY -->
          <div class="history-section">
            <div class="section-title-wrapper">
              <h3>Selesai Diproses Sesi Ini</h3>
            </div>

            <div v-if="processedApprovals.length > 0" class="history-list">
              <div 
                v-for="hist in processedApprovals" 
                :key="hist.id" 
                class="history-card"
              >
                <div class="hist-header">
                  <span class="hist-borrower">{{ hist.userName }}</span>
                  <span 
                    class="badge-pill" 
                    :class="hist.status === 'Disetujui' ? 'badge-approved' : 'badge-rejected'"
                  >
                    {{ hist.status }}
                  </span>
                </div>
                
                <div class="hist-body">
                  <span class="hist-equipment">{{ hist.itemName }}</span>
                  <span class="hist-time">Diproses: {{ hist.processedTime }}</span>
                </div>

                <div v-if="hist.rejectReason" class="hist-reason">
                  <strong>Alasan Tolak:</strong> "{{ hist.rejectReason }}"
                </div>
              </div>
            </div>

            <div v-else class="empty-state-history">
              <Clock :size="24" class="text-muted" />
              <p>Aksi persetujuan Anda akan direkam di sini.</p>
            </div>
          </div>

        </div>
      </div>

      <!-- RIGHT SIDE: SLIDE-OVER DETAIL PANEL -->
      <transition name="slide-panel">
        <div v-if="isSlideOverOpen && selectedRequest" class="slide-over-overlay" @click.self="handleCloseSlideOver">
          <div class="slide-over-panel">
            
            <div class="panel-header">
              <button @click="handleCloseSlideOver" class="btn-close-panel">
                <X :size="20" />
              </button>
              <span class="panel-tag">{{ selectedRequest.id }}</span>
              <h2>Detail Pengajuan</h2>
            </div>

            <div class="panel-content">
              <!-- Details Grid -->
              <div class="detail-group">
                <span class="detail-label">Nama Peminjam</span>
                <span class="detail-value text-bold">{{ selectedRequest.userName }}</span>
                <span class="detail-sub-value">{{ selectedRequest.className }}</span>
              </div>

              <div class="detail-group">
                <span class="detail-label">Alat yang Diajukan</span>
                <span class="detail-value">{{ selectedRequest.itemName }}</span>
              </div>

              <div class="detail-group">
                <span class="detail-label">Tanggal Pengajuan</span>
                <span class="detail-value">{{ selectedRequest.dateReq }}</span>
              </div>

              <div class="detail-group">
                <span class="detail-label">Alasan Keperluan</span>
                <p class="detail-paragraph">"{{ selectedRequest.reason }}"</p>
              </div>

              <!-- HITL CONTEXTUAL TRUST INDICATOR -->
              <div 
                class="trust-indicator-card"
                :class="{ 
                  'trust-level-good': selectedRequest.trustLevel === 'good',
                  'trust-level-warning': selectedRequest.trustLevel === 'warning',
                  'trust-level-danger': selectedRequest.trustLevel === 'danger'
                }"
              >
                <div class="trust-header">
                  <Info :size="16" class="trust-info-icon" />
                  <span class="trust-label">Indikator Kepercayaan</span>
                </div>
                <p 
                  class="trust-text"
                  :class="{ 
                    'trust-good': selectedRequest.trustLevel === 'good',
                    'trust-warning': selectedRequest.trustLevel === 'warning',
                    'trust-danger': selectedRequest.trustLevel === 'danger'
                  }"
                >
                  {{ selectedRequest.trustText }}
                </p>
              </div>

              <!-- CONDITIONAL REJECTION INPUT (Progressive Disclosure) -->
              <transition name="expand">
                <div v-if="isRejectFormOpen" class="reject-form-area">
                  <label class="textarea-label">Alasan Penolakan (Wajib)</label>
                  <textarea 
                    v-model="rejectReason"
                    placeholder="Tuliskan mengapa pengajuan ini ditolak..."
                    class="minimalist-textarea"
                    rows="3"
                  ></textarea>
                </div>
              </transition>
            </div>

            <!-- BOTTOM ACTIONS -->
            <div class="panel-footer">
              <div class="actions-row">
                <button 
                  v-if="!isRejectFormOpen"
                  @click="handleRejectClick" 
                  class="btn-action-reject"
                >
                  Tolak
                </button>
                
                <div v-else class="rejection-confirm-group">
                  <button 
                    @click="isRejectFormOpen = false" 
                    class="btn-action-cancel"
                  >
                    Batal
                  </button>
                  <button 
                    @click="submitRejection" 
                    class="btn-action-confirm-reject"
                    :disabled="!rejectReason.trim()"
                  >
                    Kirim Penolakan
                  </button>
                </div>

                <button 
                  v-if="!isRejectFormOpen"
                  @click="handleApproveAction" 
                  class="btn-action-approve"
                >
                  Setujui
                </button>
              </div>
            </div>

          </div>
        </div>
      </transition>
    </template>

    <!-- MOBILE LAYOUT -->
    <template v-else>
      <div class="mobile-approval-view">
        <div class="mobile-view-header">
          <h2>Persetujuan Alat</h2>
          <p class="subtitle">Kelola pengajuan laboratorium di smartphone Anda.</p>
        </div>

        <!-- Tab Toggle for Pending vs Processed on Mobile -->
        <div class="mobile-tabs-bar">
          <button 
            @click="mobileActiveTab = 'pending'" 
            class="mobile-tab-btn"
            :class="{ 'active': mobileActiveTab === 'pending' }"
          >
            Menunggu ({{ pendingApprovals.length }})
          </button>
          <button 
            @click="mobileActiveTab = 'history'" 
            class="mobile-tab-btn"
            :class="{ 'active': mobileActiveTab === 'history' }"
          >
            Riwayat Sesi ({{ processedApprovals.length }})
          </button>
        </div>

        <!-- Mobile Pending List Content -->
        <div v-if="mobileActiveTab === 'pending'" class="mobile-tab-content">
          <div v-if="pendingApprovals.length > 0" class="mobile-pending-cards">
            <div 
              v-for="req in pendingApprovals" 
              :key="req.id" 
              class="mobile-req-card glass-panel"
              @click="handleSelectRequest(req.id)"
            >
              <div class="req-card-header">
                <span class="req-id">{{ req.id }}</span>
                <span class="req-date">{{ req.dateReq }}</span>
              </div>
              <div class="req-card-body">
                <h4 class="req-borrower">{{ req.userName }}</h4>
                <p class="req-class">{{ req.className }}</p>
                <div class="req-eq-badge">
                  <Wrench :size="12" class="wrench-icon" />
                  <span>{{ req.itemName }}</span>
                </div>
              </div>
              <div class="req-card-footer">
                <span class="tap-detail-hint">Ketuk untuk detail & keputusan &rarr;</span>
              </div>
            </div>
          </div>
          <div v-else class="mobile-empty-state">
            <CheckCircle2 :size="40" class="text-success" />
            <p>Semua permintaan peminjaman selesai diproses!</p>
          </div>
        </div>

        <!-- Mobile History List Content -->
        <div v-else class="mobile-tab-content">
          <div v-if="processedApprovals.length > 0" class="mobile-history-cards">
            <div 
              v-for="hist in processedApprovals" 
              :key="hist.id" 
              class="mobile-hist-card glass-panel"
            >
              <div class="hist-card-header">
                <span class="hist-borrower">{{ hist.userName }}</span>
                <span 
                  class="badge-status-pill" 
                  :class="hist.status === 'Disetujui' ? 'status-approved' : 'status-rejected'"
                >
                  {{ hist.status }}
                </span>
              </div>
              <div class="hist-card-body">
                <span class="hist-item-title">{{ hist.itemName }}</span>
                <span class="hist-processed-time">Waktu: {{ hist.processedTime }}</span>
                <p v-if="hist.rejectReason" class="hist-reject-text">
                  <strong>Alasan Tolak:</strong> "{{ hist.rejectReason }}"
                </p>
              </div>
            </div>
          </div>
          <div v-else class="mobile-empty-state">
            <Clock :size="32" class="text-muted" />
            <p>Belum ada pengajuan yang diproses pada sesi ini.</p>
          </div>
        </div>

        <!-- MOBILE DETAIL BOTTOM DRAWER (Full screen modal on smaller screens) -->
        <transition name="drawer-slide">
          <div v-if="isSlideOverOpen && selectedRequest" class="mobile-drawer-overlay" @click.self="handleCloseSlideOver">
            <div class="mobile-drawer-panel">
              <div class="drawer-header">
                <div class="drawer-drag-handle"></div>
                <button @click="handleCloseSlideOver" class="btn-drawer-close">
                  <X :size="20" />
                </button>
                <span class="drawer-tag">{{ selectedRequest.id }}</span>
                <h3>Detail Pengajuan</h3>
              </div>

              <div class="drawer-content">
                <div class="drawer-info-group">
                  <span class="info-label">Nama Peminjam</span>
                  <span class="info-val text-bold">{{ selectedRequest.userName }}</span>
                  <span class="info-sub-val">{{ selectedRequest.className }}</span>
                </div>

                <div class="drawer-info-group">
                  <span class="info-label">Alat Laboratorium</span>
                  <span class="info-val">{{ selectedRequest.itemName }}</span>
                </div>

                <div class="drawer-info-group">
                  <span class="info-label">Tanggal Pengajuan</span>
                  <span class="info-val">{{ selectedRequest.dateReq }}</span>
                </div>

                <div class="drawer-info-group">
                  <span class="info-label">Alasan Keperluan</span>
                  <p class="info-desc">"{{ selectedRequest.reason }}"</p>
                </div>

                <!-- Trust Indicator for Mobile -->
                <div 
                  class="mobile-trust-card"
                  :class="{ 
                    'trust-level-good': selectedRequest.trustLevel === 'good',
                    'trust-level-warning': selectedRequest.trustLevel === 'warning',
                    'trust-level-danger': selectedRequest.trustLevel === 'danger'
                  }"
                >
                  <div class="trust-title-row">
                    <Info :size="14" />
                    <span>Indikator Kepercayaan</span>
                  </div>
                  <p class="trust-msg">{{ selectedRequest.trustText }}</p>
                </div>

                <!-- Reject Reason Form for Mobile -->
                <div v-if="isRejectFormOpen" class="mobile-reject-form">
                  <label class="mobile-textarea-label">Alasan Penolakan (Wajib)</label>
                  <textarea 
                    v-model="rejectReason"
                    placeholder="Tuliskan alasan penolakan di sini..."
                    class="mobile-textarea"
                    rows="3"
                  ></textarea>
                </div>
              </div>

              <!-- STICKY BOTTOM ACTIONS FOR MOBILE -->
              <div class="drawer-footer">
                <div class="mobile-actions-row">
                  <button 
                    v-if="!isRejectFormOpen"
                    @click="handleRejectClick" 
                    class="btn-mobile-reject"
                  >
                    Tolak
                  </button>
                  
                  <div v-else class="mobile-rejection-confirm">
                    <button 
                      @click="isRejectFormOpen = false" 
                      class="btn-mobile-cancel"
                    >
                      Batal
                    </button>
                    <button 
                      @click="submitRejection" 
                      class="btn-mobile-confirm-reject"
                      :disabled="!rejectReason.trim()"
                    >
                      Kirim Tolak
                    </button>
                  </div>

                  <button 
                    v-if="!isRejectFormOpen"
                    @click="handleApproveAction" 
                    class="btn-mobile-approve"
                  >
                    Setujui
                  </button>
                </div>
              </div>

            </div>
          </div>
        </transition>

      </div>
    </template>

  </div>
</template>

<style scoped>
.approval-container {
  width: 100%;
  display: flex;
  position: relative;
  min-height: calc(100vh - 120px);
}

.approval-main-view {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 48px; /* Masif whitespace */
}

.view-header-description h2 {
  font-family: var(--font-display);
  font-style: italic;
  font-size: 2.5rem;
  font-weight: 400;
  color: var(--color-text-primary);
  letter-spacing: -0.02em;
  line-height: 1.1;
}

.view-header-description p {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  margin-top: 8px;
}

.split-columns {
  display: grid;
  grid-template-columns: 1.3fr 1fr; /* Asymmetric layout */
  gap: 64px; /* Massive whitespace */
  width: 100%;
}

.section-title-wrapper {
  margin-bottom: 24px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding-bottom: 12px;
}

.section-title-wrapper h3 {
  font-family: var(--font-number), sans-serif;
  font-size: 0.875rem;
  font-weight: 600;
  letter-spacing: -0.01em;
  color: var(--color-text-secondary);
}

/* LIST VIEW STYLES (CARDLESS) */
.clean-list {
  display: flex;
  flex-direction: column;
}

.list-item {
  background: transparent;
  border: none;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  border-radius: 0;
  padding: 24px 8px;
  cursor: pointer;
  transition: background-color 0.15s ease-out, border-color 0.15s ease-out, transform 0.15s ease-out;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.list-item:hover {
  background: rgba(0, 0, 0, 0.015);
}

.list-item:active {
  transform: scale(0.995); /* Tactile */
}

.list-item.item-selected {
  background: rgba(70, 112, 143, 0.03);
  border-bottom-color: var(--color-action-primary);
}

.item-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.item-borrower {
  font-family: var(--font-primary);
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-primary);
  letter-spacing: -0.01em;
}

.item-date {
  font-family: var(--font-number), monospace;
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  font-weight: 500;
}

.item-body {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.item-equipment {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  font-weight: 500;
}

.chevron-icon {
  color: var(--color-text-secondary);
  opacity: 0.3;
  transition: transform 0.15s ease-out, opacity 0.15s ease-out;
}

.list-item:hover .chevron-icon,
.list-item.item-selected .chevron-icon {
  transform: translateX(4px);
  opacity: 0.8;
  color: var(--color-action-primary);
}

/* HISTORY VIEW (CARDLESS) */
.history-list {
  display: flex;
  flex-direction: column;
}

.history-card {
  background: transparent;
  border: none;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  border-radius: 0;
  padding: 20px 8px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.hist-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.hist-borrower {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-text-primary);
}

.badge-pill {
  font-family: var(--font-number), sans-serif;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 9999px;
  letter-spacing: -0.01em;
}

.badge-approved {
  background: var(--color-success-light);
  color: var(--color-success);
}

.badge-rejected {
  background: var(--color-danger-light);
  color: var(--color-danger);
}

.hist-body {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.75rem;
  color: var(--color-text-secondary);
}

.hist-equipment {
  font-weight: 500;
}

.hist-time {
  font-family: var(--font-number), monospace;
  font-size: 0.75rem;
  opacity: 0.8;
}

.hist-reason {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  font-style: italic;
  margin-top: 4px;
  padding-left: 8px;
  border-left: 1px solid rgba(0, 0, 0, 0.1);
}

/* EMPTY STATES */
.empty-state {
  padding: 80px 24px;
  text-align: left;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 12px;
  border: none;
  background: transparent;
}

.empty-state p {
  font-size: 0.9375rem;
  color: var(--color-text-secondary);
}

.empty-state-history {
  padding: 60px 0;
  text-align: left;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 12px;
  border: none;
  background: transparent;
}

.empty-state-history p {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
}

/* SLIDE-OVER PANEL STYLES (CARDLESS & ASYMMETRIC) */
.slide-over-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.05); /* very soft light dim */
  backdrop-filter: blur(4px);
  z-index: 500;
  display: flex;
  justify-content: flex-end;
}

.slide-over-panel {
  width: 100%;
  max-width: 480px;
  height: 100%;
  background: var(--color-bg-base); /* merges with body bg */
  border-left: 1px solid rgba(0, 0, 0, 0.08); /* clean separator */
  box-shadow: none; /* absolute zero shadow */
  display: flex;
  flex-direction: column;
  padding: 48px;
  overflow-y: auto;
  position: relative;
}

.panel-header {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  margin-bottom: 36px;
  position: relative;
}

.btn-close-panel {
  position: absolute;
  top: 0;
  right: 0;
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
  transition: background-color 0.15s ease-out, transform 0.15s ease-out;
}

.btn-close-panel:hover {
  background: rgba(0, 0, 0, 0.04);
}

.btn-close-panel:active {
  transform: scale(0.95);
}

.panel-tag {
  font-family: var(--font-number), monospace;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  color: var(--color-action-primary);
  background: rgba(70, 112, 143, 0.06);
  padding: 4px 12px;
  border-radius: 9999px;
  margin-bottom: 12px;
}

.panel-header h2 {
  font-family: var(--font-display);
  font-style: italic;
  font-size: 2.25rem;
  font-weight: 400;
  color: var(--color-text-primary);
  letter-spacing: -0.02em;
  line-height: 1.1;
  margin-top: 4px;
}

.panel-content {
  display: flex;
  flex-direction: column;
  gap: 32px;
  margin-bottom: 32px;
}

.detail-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-label {
  font-family: var(--font-number), sans-serif;
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--color-text-secondary);
}

.detail-value {
  font-size: 0.9375rem;
  font-weight: 500;
  color: var(--color-text-primary);
}

.detail-value.text-bold {
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.detail-sub-value {
  font-size: 0.8125rem;
  color: var(--color-text-secondary);
  margin-top: -4px;
}

.detail-paragraph {
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--color-text-primary);
  font-style: italic;
  background: transparent;
  padding: 0;
  border-radius: 0;
}

/* TRUST INDICATOR */
.trust-indicator-card {
  background: transparent;
  border: none;
  border-left: 2px solid rgba(0, 0, 0, 0.08);
  border-radius: 0;
  padding: 8px 0 8px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.trust-indicator-card.trust-level-good {
  border-left-color: var(--color-text-secondary);
}

.trust-indicator-card.trust-level-warning {
  border-left-color: #c2410c;
}

.trust-indicator-card.trust-level-danger {
  border-left-color: var(--color-danger);
}

.trust-header {
  display: flex;
  align-items: center;
  gap: 8px;
}

.trust-info-icon {
  color: var(--color-text-secondary);
  opacity: 0.6;
}

.trust-label {
  font-family: var(--font-number), sans-serif;
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--color-text-secondary);
}

.trust-text {
  font-size: 0.875rem;
  font-weight: 500;
  line-height: 1.4;
}

.trust-good {
  color: var(--color-text-primary);
}

.trust-warning {
  color: #c2410c;
}

.trust-danger {
  color: var(--color-danger);
}

/* REJECT TEXTAREA */
.reject-form-area {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.textarea-label {
  font-family: var(--font-number), sans-serif;
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--color-text-secondary);
}

.minimalist-textarea {
  background: transparent;
  border: none;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  padding: 8px 0;
  border-radius: 0;
  font-family: var(--font-primary);
  font-size: 0.9375rem;
  color: var(--color-text-primary);
  outline: none;
  resize: vertical;
  line-height: 1.5;
  transition: border-color 0.2s ease-out;
}

.minimalist-textarea:focus {
  border-color: var(--color-action-primary);
  box-shadow: 0 1px 0 var(--color-action-primary); /* underline thickening transition */
}

/* PANEL FOOTER ACTIONS */
.panel-footer {
  margin-top: auto;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  padding-top: 24px;
}

.actions-row {
  display: flex;
  gap: 16px;
  justify-content: space-between;
}

.btn-action-approve {
  flex: 1;
  background: var(--color-action-primary);
  color: white;
  border: none;
  font-family: var(--font-number), sans-serif;
  font-size: 0.9375rem;
  font-weight: 700;
  padding: 14px 28px;
  border-radius: 9999px;
  cursor: pointer;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s;
}

.btn-action-approve:hover {
  background: #355670;
}

.btn-action-approve:active {
  transform: scale(0.98);
}

.btn-action-reject {
  flex: 1;
  background: transparent;
  color: var(--color-text-secondary);
  border: 1px solid rgba(0, 0, 0, 0.08);
  font-family: var(--font-number), sans-serif;
  font-size: 0.9375rem;
  font-weight: 600;
  padding: 14px 28px;
  border-radius: 9999px;
  cursor: pointer;
  transition: var(--transition-smooth);
}

.btn-action-reject:hover {
  color: var(--color-danger);
  border-color: var(--color-danger);
  background: rgba(239, 68, 68, 0.02);
}

.btn-action-reject:active {
  transform: scale(0.98);
}

.rejection-confirm-group {
  display: flex;
  gap: 12px;
  flex: 1;
}

.btn-action-cancel {
  flex: 1;
  background: transparent;
  color: var(--color-text-secondary);
  border: 1px solid rgba(0, 0, 0, 0.08);
  font-family: var(--font-number), sans-serif;
  font-size: 0.8125rem;
  font-weight: 600;
  padding: 12px 18px;
  border-radius: 9999px;
  cursor: pointer;
  transition: var(--transition-smooth);
}

.btn-action-cancel:hover {
  background: rgba(0, 0, 0, 0.02);
  color: var(--color-text-primary);
  border-color: rgba(0, 0, 0, 0.15);
}

.btn-action-cancel:active {
  transform: scale(0.98);
}

.btn-action-confirm-reject {
  flex: 1.5;
  background: var(--color-danger);
  color: white;
  border: none;
  font-family: var(--font-number), sans-serif;
  font-size: 0.8125rem;
  font-weight: 700;
  padding: 12px 18px;
  border-radius: 9999px;
  cursor: pointer;
  transition: var(--transition-smooth);
}

.btn-action-confirm-reject:hover:not(:disabled) {
  background: #b91c1c;
}

.btn-action-confirm-reject:active:not(:disabled) {
  transform: scale(0.98);
}

.btn-action-confirm-reject:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* ANIMATIONS & TRANSITIONS */
.slide-panel-enter-active,
.slide-panel-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-panel-enter-from,
.slide-panel-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.expand-enter-active,
.expand-leave-active {
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  max-height: 200px;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-8px);
}

@media (max-width: 768px) {
  .split-columns {
    grid-template-columns: 1fr;
    gap: 40px;
  }
}

/* MOBILE CODES STYLING (ISOLATED) */
.mobile-approval-view {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
}

.mobile-view-header h2 {
  font-family: var(--font-display);
  font-style: italic;
  font-size: 2rem;
  font-weight: 400;
  color: var(--color-text-primary);
  letter-spacing: -0.02em;
}

.mobile-view-header .subtitle {
  font-size: 0.8125rem;
  color: var(--color-text-secondary);
  margin-top: 4px;
}

.mobile-tabs-bar {
  display: flex;
  background: rgba(0, 0, 0, 0.03);
  padding: 4px;
  border-radius: 12px;
  gap: 4px;
}

.mobile-tab-btn {
  flex: 1;
  background: transparent;
  border: none;
  font-family: var(--font-primary);
  font-size: 12px;
  font-weight: 600;
  color: var(--color-text-secondary);
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: var(--transition-smooth);
  text-align: center;
  outline: none;
}

.mobile-tab-btn.active {
  background: #ffffff;
  color: var(--color-action-primary);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.mobile-pending-cards, .mobile-history-cards {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.mobile-req-card {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-lg);
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  box-shadow: var(--shadow-soft);
  cursor: pointer;
  transition: var(--transition-smooth);
}

.mobile-req-card:active {
  transform: scale(0.98);
}

.req-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.req-id {
  font-family: var(--font-number);
  font-size: 10px;
  font-weight: 700;
  color: var(--color-action-primary);
  background: rgba(70, 112, 143, 0.06);
  padding: 3px 8px;
  border-radius: 4px;
}

.req-date {
  font-family: var(--font-number);
  font-size: 11px;
  color: var(--color-text-secondary);
}

.req-card-body {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.req-borrower {
  font-size: 14px;
  font-weight: 700;
  color: var(--color-text-primary);
}

.req-class {
  font-size: 11px;
  color: var(--color-text-secondary);
  margin-top: -2px;
}

.req-eq-badge {
  align-self: flex-start;
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(0, 0, 0, 0.02);
  border: 1.5px solid rgba(0, 0, 0, 0.03);
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  color: var(--color-text-primary);
  margin-top: 6px;
}

.wrench-icon {
  color: var(--color-action-primary);
}

.req-card-footer {
  border-top: 1px solid rgba(0, 0, 0, 0.03);
  padding-top: 8px;
  text-align: right;
}

.tap-detail-hint {
  font-size: 11px;
  font-weight: 600;
  color: var(--color-action-primary);
}

.mobile-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 48px 24px;
  text-align: center;
  color: var(--color-text-secondary);
}

.mobile-empty-state p {
  font-size: 13px;
  font-weight: 550;
}

/* MOBILE HIST CARD */
.mobile-hist-card {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-lg);
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.hist-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.hist-borrower {
  font-size: 13.5px;
  font-weight: 700;
  color: var(--color-text-primary);
}

.badge-status-pill {
  font-size: 9px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 20px;
  text-transform: uppercase;
}

.status-approved {
  background: var(--color-success-light);
  color: var(--color-success);
}

.status-rejected {
  background: var(--color-danger-light);
  color: var(--color-danger);
}

.hist-card-body {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.hist-item-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-primary);
}

.hist-processed-time {
  font-family: var(--font-number);
  font-size: 10.5px;
  color: var(--color-text-secondary);
}

.hist-reject-text {
  font-size: 11px;
  color: var(--color-text-secondary);
  font-style: italic;
  margin-top: 4px;
  padding-left: 8px;
  border-left: 2px solid rgba(0, 0, 0, 0.08);
}

/* MOBILE DRAWER OVERLAY & PANEL */
.mobile-drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.3);
  backdrop-filter: blur(4px);
  z-index: 2000;
  display: flex;
  justify-content: flex-end;
  align-items: flex-end;
}

.mobile-drawer-panel {
  width: 100%;
  height: 90%;
  background: #ffffff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.08);
}

.drawer-header {
  padding: 20px 24px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.drawer-drag-handle {
  width: 36px;
  height: 4px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 999px;
  align-self: center;
  margin-bottom: 12px;
}

.btn-drawer-close {
  position: absolute;
  top: 24px;
  right: 24px;
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
  background: rgba(0, 0, 0, 0.03);
}

.drawer-tag {
  font-family: var(--font-number);
  font-size: 10px;
  font-weight: 700;
  color: var(--color-action-primary);
  background: rgba(70, 112, 143, 0.08);
  padding: 3px 10px;
  border-radius: 20px;
  margin-bottom: 6px;
}

.drawer-header h3 {
  font-family: var(--font-primary);
  font-size: 18px;
  font-weight: 750;
  color: var(--color-text-primary);
}

.drawer-content {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.drawer-info-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--color-text-secondary);
}

.info-val {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-primary);
}

.info-val.text-bold {
  font-size: 18px;
  font-weight: 800;
}

.info-sub-val {
  font-size: 12px;
  color: var(--color-text-secondary);
  margin-top: -2px;
}

.info-desc {
  font-size: 13.5px;
  line-height: 1.5;
  font-style: italic;
  color: var(--color-text-primary);
  background: rgba(0, 0, 0, 0.015);
  padding: 10px 14px;
  border-radius: 8px;
  border-left: 2px solid rgba(0, 0, 0, 0.06);
}

/* MOBILE TRUST CARD */
.mobile-trust-card {
  border-left: 2px solid rgba(0, 0, 0, 0.1);
  padding: 6px 0 6px 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.mobile-trust-card.trust-level-good { border-left-color: var(--color-text-secondary); }
.mobile-trust-card.trust-level-warning { border-left-color: #c2410c; }
.mobile-trust-card.trust-level-danger { border-left-color: var(--color-danger); }

.trust-title-row {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--color-text-secondary);
}

.trust-msg {
  font-size: 12px;
  font-weight: 600;
  line-height: 1.35;
}

.mobile-trust-card.trust-level-good .trust-msg { color: var(--color-text-primary); }
.mobile-trust-card.trust-level-warning .trust-msg { color: #c2410c; }
.mobile-trust-card.trust-level-danger .trust-msg { color: var(--color-danger); }

/* MOBILE REJECT FORM */
.mobile-reject-form {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 8px;
}

.mobile-textarea-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--color-text-secondary);
}

.mobile-textarea {
  background: transparent;
  border: none;
  border-bottom: 1.5px solid rgba(0, 0, 0, 0.1);
  font-family: var(--font-primary);
  font-size: 13.5px;
  color: var(--color-text-primary);
  outline: none;
  resize: none;
  padding: 6px 0;
  transition: border-color 0.2s;
}

.mobile-textarea:focus {
  border-color: var(--color-action-primary);
}

/* MOBILE DRAWER FOOTER */
.drawer-footer {
  padding: 16px 24px 32px 24px;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  background: #ffffff;
}

.mobile-actions-row {
  display: flex;
  gap: 12px;
  width: 100%;
}

.btn-mobile-approve {
  flex: 1;
  background: var(--color-action-primary);
  color: white;
  border: none;
  font-family: var(--font-primary);
  font-size: 14px;
  font-weight: 700;
  padding: 12px;
  border-radius: 20px;
  cursor: pointer;
  transition: var(--transition-smooth);
}

.btn-mobile-reject {
  flex: 1;
  background: transparent;
  color: var(--color-text-secondary);
  border: 1px solid rgba(0, 0, 0, 0.1);
  font-family: var(--font-primary);
  font-size: 14px;
  font-weight: 600;
  padding: 12px;
  border-radius: 20px;
  cursor: pointer;
  transition: var(--transition-smooth);
}

.btn-mobile-approve:active {
  background: #355670;
  transform: scale(0.98);
}

.btn-mobile-reject:active {
  color: var(--color-danger);
  border-color: var(--color-danger);
  background: rgba(239, 68, 68, 0.02);
  transform: scale(0.98);
}

.mobile-rejection-confirm {
  display: flex;
  gap: 8px;
  flex: 1;
}

.btn-mobile-cancel {
  flex: 1;
  background: transparent;
  color: var(--color-text-secondary);
  border: 1px solid rgba(0, 0, 0, 0.1);
  font-family: var(--font-primary);
  font-size: 12px;
  font-weight: 600;
  padding: 10px;
  border-radius: 20px;
  cursor: pointer;
}

.btn-mobile-confirm-reject {
  flex: 1.5;
  background: var(--color-danger);
  color: white;
  border: none;
  font-family: var(--font-primary);
  font-size: 12px;
  font-weight: 700;
  padding: 10px;
  border-radius: 20px;
  cursor: pointer;
}

.btn-mobile-confirm-reject:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* DRAWER ANIMATION FOR OUT OVERLAY */
.drawer-slide-enter-active,
.drawer-slide-leave-active {
  transition: opacity 0.25s ease-out;
}

.drawer-slide-enter-from,
.drawer-slide-leave-to {
  opacity: 0;
}

.drawer-slide-enter-active .mobile-drawer-panel,
.drawer-slide-leave-active .mobile-drawer-panel {
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.drawer-slide-enter-from .mobile-drawer-panel,
.drawer-slide-leave-to .mobile-drawer-panel {
  transform: translateY(100%);
}
</style>
