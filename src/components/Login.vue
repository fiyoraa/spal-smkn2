<script setup>
import { ref } from 'vue';
import { Mail, Lock, ArrowRight, Package, Eye, EyeOff } from '@lucide/vue';

const emit = defineEmits(['login-success', 'switch-view']);

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const isLoading = ref(false);
const errorMessage = ref('');

const handleLogin = async () => {
  if (!email.value || !password.value) {
    errorMessage.value = 'Silakan isi email dan kata sandi Anda.';
    return;
  }
  
  isLoading.value = true;
  errorMessage.value = '';
  
  try {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value
      })
    });
    
    const result = await response.json();
    if (response.ok && result.status === 'success') {
      emit('login-success', result.data);
    } else {
      errorMessage.value = result.message || 'Email atau kata sandi Anda salah.';
    }
  } catch (err) {
    console.error('Login error:', err);
    errorMessage.value = 'Terjadi kesalahan koneksi ke server.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="login-viewport">
    <!-- Left Form Panel (Minimalist, Legger, Breathable) -->
    <div class="login-form-panel">
      <div class="brand-header">
        <div class="logo-box">
          <Package class="logo-icon" />
        </div>
        <span class="brand-text">SPAL <i>SMKN 2</i></span>
      </div>

      <div class="form-intro">
        <h1 class="greeting">Selamat Datang.</h1>
        <p class="sub-greeting">Silakan masuk untuk mengelola inventarisasi alat laboratorium Anda.</p>
      </div>

      <form @submit.prevent="handleLogin" class="auth-form">
        <div v-if="errorMessage" class="error-banner">
          {{ errorMessage }}
        </div>

        <!-- Email Input Group -->
        <div class="input-group">
          <label for="email" class="input-label">Email Pengguna</label>
          <div class="input-wrapper">
            <Mail class="input-icon" :size="16" />
            <input 
              id="email"
              type="email" 
              v-model="email" 
              placeholder="laboran@school.id" 
              class="tactile-input"
              required 
            />
          </div>
        </div>

        <!-- Password Input Group -->
        <div class="input-group">
          <label for="password" class="input-label">Kata Sandi</label>
          <div class="input-wrapper">
            <Lock class="input-icon" :size="16" />
            <input 
              id="password"
              :type="showPassword ? 'text' : 'password'" 
              v-model="password" 
              placeholder="••••••••" 
              class="tactile-input"
              style="padding-right: 32px;"
              required 
            />
            <button 
              type="button" 
              @click="showPassword = !showPassword" 
              class="password-toggle-btn"
              title="Tampilkan/Sembunyikan Kata Sandi"
            >
              <Eye v-if="!showPassword" :size="16" />
              <EyeOff v-else :size="16" />
            </button>
          </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <button type="submit" class="btn-submit" :disabled="isLoading">
            <span>{{ isLoading ? 'Memproses...' : 'Masuk Aplikasi' }}</span>
            <ArrowRight v-if="!isLoading" :size="16" />
          </button>
        </div>
      </form>

      <div class="auth-footer">
        <span>Belum terdaftar?</span>
        <button class="btn-link" @click="emit('switch-view', 'Register')">Buat akun baru</button>
      </div>
    </div>

    <!-- Massive Showcase Panel (65% Width) - Premium Editorial Design -->
    <div class="whitespace-panel">
      <div class="showcase-content">
        <div class="showcase-meta">PORTAL RESMI SEKOLAH</div>
        <h2 class="showcase-title">Sistem Peminjaman & Pengembalian Alat Laboratorium SMKN 2 Palembang</h2>
        <p class="showcase-desc">Sistem ini ditujukan untuk mempermudah pencatatan, inventarisasi, serta pengelolaan transaksi peminjaman dan pengembalian alat praktik di laboratorium komputer SMKN 2 Palembang secara tertib dan transparan.</p>
      </div>
      <div class="watermark-container">
        <span class="watermark-text">SPAL SMKN 2</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-viewport {
  display: flex;
  width: 100vw;
  height: 100vh;
  background-color: var(--color-bg-base);
  overflow: hidden;
  font-family: var(--font-primary);
}

/* Asymmetric form alignment: 35% of screen width */
.login-form-panel {
  width: 35%;
  min-width: 400px;
  height: 100%;
  padding: 60px 80px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  z-index: 10;
}

.brand-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 48px;
}

.logo-box {
  width: 28px;
  height: 28px;
  border: 1.5px solid var(--color-primary);
  color: var(--color-primary);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-icon {
  width: 14px;
  height: 14px;
}

.brand-text {
  font-family: var(--font-display);
  font-size: 20px;
  font-style: italic;
  font-weight: 500;
  color: var(--text-primary);
}

.brand-text i {
  font-weight: 600;
  color: var(--color-primary);
}

.form-intro {
  margin-bottom: 36px;
}

/* Giant Serif Greeting */
.greeting {
  font-family: var(--font-display);
  font-size: 48px;
  font-style: italic;
  font-weight: 400;
  color: var(--color-text-primary);
  margin-bottom: 8px;
  line-height: 1.1;
  letter-spacing: -0.02em;
}

.sub-greeting {
  font-size: 13.5px;
  color: var(--text-secondary);
  line-height: 1.45;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.error-banner {
  background: rgba(239, 68, 68, 0.05);
  border: 1px solid rgba(239, 68, 68, 0.15);
  color: var(--color-danger);
  padding: 10px 14px;
  border-radius: 6px;
  font-size: 12px;
  line-height: 1.4;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.input-label {
  font-size: 11.5px;
  font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 0;
  color: var(--text-muted);
  pointer-events: none;
  transition: color 0.2s ease-out;
}

/* Tactile Input: Bottom border only, transitions on focus */
.tactile-input {
  width: 100%;
  background: transparent;
  border: none;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  padding: 10px 0 10px 24px;
  font-family: var(--font-primary);
  font-size: 14px;
  color: var(--text-primary);
  outline: none;
  transition: border-bottom-color 0.2s ease-out, border-bottom-width 0.2s ease-out;
}

.tactile-input:focus {
  border-bottom: 2px solid var(--color-primary);
}

.tactile-input:focus + .input-icon, 
.input-wrapper:focus-within .input-icon {
  color: var(--color-primary);
}

.form-actions {
  margin-top: 12px;
}

.btn-submit {
  width: 100%;
  background: var(--color-primary);
  color: white;
  border: none;
  font-family: var(--font-primary);
  font-size: 13.5px;
  font-weight: 600;
  padding: 12px 20px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.2s ease-out, transform 0.2s ease-out;
}

.btn-submit:hover:not(:disabled) {
  background: #3b6582;
}

.btn-submit:active:not(:disabled) {
  transform: scale(0.98);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.auth-footer {
  margin-top: 36px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--text-secondary);
}

.btn-link {
  background: transparent;
  border: none;
  color: var(--color-primary);
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  font-family: var(--font-primary);
  transition: color 0.15s ease;
}

.btn-link:hover {
  text-decoration: underline;
  color: #3b6582;
}

/* Massive Showcase Panel (65% Width) */
.whitespace-panel {
  flex: 1;
  height: 100%;
  border-left: 1px solid rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 80px;
  background: #F4F3EF; /* Creamy off-white background matching the dashboard */
  position: relative;
  overflow: hidden;
}

.showcase-content {
  max-width: 520px;
  margin-top: auto;
  margin-bottom: auto;
  z-index: 5;
}

.showcase-meta {
  font-size: 11px;
  font-weight: 750;
  color: var(--color-primary);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 24px;
}

.showcase-title {
  font-family: var(--font-display);
  font-size: 3rem;
  font-weight: 400;
  color: var(--color-text-primary);
  line-height: 1.15;
  letter-spacing: -1px;
  margin-bottom: 24px;
}

.showcase-title i {
  font-style: italic;
  font-weight: 400;
}

.showcase-desc {
  font-size: 13.5px;
  line-height: 1.6;
  color: var(--color-text-secondary);
  margin-bottom: 0;
}

.watermark-container {
  position: absolute;
  bottom: 40px;
  right: 60px;
  opacity: 0.03;
  user-select: none;
  z-index: 1;
}

.watermark-text {
  font-family: var(--font-display);
  font-size: 90px;
  font-style: italic;
  font-weight: 400;
  color: var(--color-text-primary);
  letter-spacing: -0.02em;
}

.password-toggle-btn {
  position: absolute;
  right: 0;
  background: transparent;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px 0;
  transition: color 0.15s ease;
}

.password-toggle-btn:hover {
  color: var(--color-primary);
}

@media (max-width: 900px) {
  .login-form-panel {
    width: 100%;
    min-width: 0;
    padding: 40px;
  }
  
  .whitespace-panel {
    display: none;
  }
}
</style>
