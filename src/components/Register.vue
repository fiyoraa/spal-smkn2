<script setup>
import { ref } from 'vue';
import { User, Mail, Lock, ArrowRight, Package } from '@lucide/vue';

const emit = defineEmits(['register-success', 'switch-view']);

const name = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const role = ref('Siswa');
const isLoading = ref(false);
const errorMessage = ref('');

const handleRegister = async () => {
  if (!name.value || !email.value || !password.value || !confirmPassword.value) {
    errorMessage.value = 'Silakan lengkapi semua kolom pendaftaran.';
    return;
  }
  
  if (password.value !== confirmPassword.value) {
    errorMessage.value = 'Konfirmasi kata sandi tidak cocok.';
    return;
  }
  
  isLoading.value = true;
  errorMessage.value = '';
  
  try {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        name: name.value,
        email: email.value,
        password: password.value,
        role: role.value
      })
    });
    
    const result = await response.json();
    if (response.ok && result.status === 'success') {
      emit('register-success', result.data);
    } else {
      errorMessage.value = result.message || 'Pendaftaran gagal. Silakan coba lagi.';
    }
  } catch (err) {
    console.error('Registration error:', err);
    errorMessage.value = 'Terjadi kesalahan koneksi ke server.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="register-viewport">
    <!-- Asymmetric Form Panel (1/3 Width) -->
    <div class="register-form-panel">
      <div class="brand-header">
        <div class="logo-box">
          <Package class="logo-icon" />
        </div>
        <span class="brand-text">SPAL <i>SMKN 2</i></span>
      </div>

      <div class="form-intro">
        <h1 class="greeting">Daftar Akun.</h1>
        <p class="sub-greeting">Mulai mengelola inventarisasi laboratorium dan administrasi peminjaman sekarang.</p>
      </div>

      <form @submit.prevent="handleRegister" class="auth-form">
        <div v-if="errorMessage" class="error-banner">
          {{ errorMessage }}
        </div>

        <!-- Name Input Group -->
        <div class="input-group">
          <label for="name" class="input-label">Nama Lengkap</label>
          <div class="input-wrapper">
            <User class="input-icon" :size="16" />
            <input 
              id="name"
              type="text" 
              v-model="name" 
              placeholder="Alexandra Dev" 
              class="tactile-input"
              required 
            />
          </div>
        </div>

        <!-- Role Input Group -->
        <div class="input-group">
          <label for="role" class="input-label">Peran Pengguna</label>
          <div class="input-wrapper">
            <User class="input-icon" :size="16" />
            <select id="role" v-model="role" class="tactile-input" style="appearance: none; -webkit-appearance: none; padding-right: 24px; cursor: pointer; background: transparent; width: 100%; border: none; border-bottom: 1px solid rgba(0, 0, 0, 0.1);" required>
              <option value="Siswa" style="color: black;">Siswa</option>
              <option value="Guru" style="color: black;">Guru</option>
              <option value="Laboran" style="color: black;">Laboran</option>
            </select>
          </div>
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
              placeholder="siswa@school.id" 
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
              type="password" 
              v-model="password" 
              placeholder="••••••••" 
              class="tactile-input"
              required 
            />
          </div>
        </div>

        <!-- Confirm Password Input Group -->
        <div class="input-group">
          <label for="confirmPassword" class="input-label">Konfirmasi Kata Sandi</label>
          <div class="input-wrapper">
            <Lock class="input-icon" :size="16" />
            <input 
              id="confirmPassword"
              type="password" 
              v-model="confirmPassword" 
              placeholder="••••••••" 
              class="tactile-input"
              required 
            />
          </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <button type="submit" class="btn-submit" :disabled="isLoading">
            <span>{{ isLoading ? 'Mendaftar...' : 'Buat Akun' }}</span>
            <ArrowRight v-if="!isLoading" :size="16" />
          </button>
        </div>
      </form>

      <div class="auth-footer">
        <span>Sudah memiliki akun?</span>
        <button class="btn-link" @click="emit('switch-view', 'Login')">Masuk sekarang</button>
      </div>
    </div>

    <!-- Massive Whitespace Panel (2/3 Width) -->
    <div class="whitespace-panel">
      <div class="watermark-container">
        <span class="watermark-text">SPAL Portal</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.register-viewport {
  display: flex;
  width: 100vw;
  height: 100vh;
  background-color: var(--color-bg-base);
  overflow: hidden;
  font-family: var(--font-primary);
}

.register-form-panel {
  width: 35%;
  min-width: 400px;
  height: 100%;
  padding: 40px 80px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  z-index: 10;
}

.brand-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 32px;
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
  margin-bottom: 28px;
}

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
  gap: 20px;
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

.tactile-input {
  width: 100%;
  background: transparent;
  border: none;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  padding: 8px 0 8px 24px;
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
  margin-top: 28px;
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

.whitespace-panel {
  flex: 1;
  height: 100%;
  border-left: 1px solid rgba(0, 0, 0, 0.03);
  display: flex;
  align-items: flex-end;
  justify-content: flex-end;
  padding: 60px;
  background: var(--color-bg-base);
}

.watermark-container {
  opacity: 0.05;
  user-select: none;
}

.watermark-text {
  font-family: var(--font-display);
  font-size: 72px;
  font-style: italic;
  font-weight: 400;
  color: var(--text-primary);
  letter-spacing: -0.02em;
}

@media (max-width: 900px) {
  .register-form-panel {
    width: 100%;
    min-width: 0;
    padding: 40px;
  }
  
  .whitespace-panel {
    display: none;
  }
}
</style>
