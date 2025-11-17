const { createApp } = Vue;

        createApp({
            data() {
                return {
                    isMobileMenuOpen: false,
                    isWaSelectorOpen: false,
                    isWaSelector2Open: false, // For mobile bottom bar
                    isLangSelectorOpen: false,
                    activeTab: 'padang', // For contact tabs
                    form: { // For contact form
                        name: '',
                        phone: '',
                        message: ''
                    },
                    // [PERUBAHAN] Properti untuk scroller baru
                    scrollAnimationId: null,
                    originalScrollWidth: 0,
                    scrollSpeed: 1, // Kecepatan scroll (1px per frame)
                    isScrollingPaused: false, // Status untuk mouse hover
                    observer: null // For scroll animations
                };
            },
            methods: {
                // --- Navigasi & Popup ---
                toggleMobileMenu() {
                    this.isMobileMenuOpen = !this.isMobileMenuOpen;
                    this.isWaSelectorOpen = false;
                    this.isWaSelector2Open = false;
                    this.isLangSelectorOpen = false;
                },
                closeMobileMenu() {
                    this.isMobileMenuOpen = false;
                },
                toggleWaSelector(event) {
                    if (event) event.preventDefault();
                    this.isWaSelectorOpen = !this.isWaSelectorOpen;
                    this.isMobileMenuOpen = false;
                    this.isWaSelector2Open = false;
                    this.isLangSelectorOpen = false;
                },
                toggleWaSelector2(event) { // For mobile
                    if (event) event.preventDefault();
                    this.isWaSelector2Open = !this.isWaSelector2Open;
                    this.isMobileMenuOpen = false;
                    this.isWaSelectorOpen = false;
                    this.isLangSelectorOpen = false;
                },
                toggleLangSelector() {
                    this.isLangSelectorOpen = !this.isLangSelectorOpen;
                    this.isMobileMenuOpen = false;
                    this.isWaSelectorOpen = false;
                    this.isWaSelector2Open = false;
                },
                closeAllPopups() {
                    this.isMobileMenuOpen = false;
                    this.isWaSelectorOpen = false;
                    this.isWaSelector2Open = false;
                    this.isLangSelectorOpen = false;
                },

                // --- Tab Kontak ---
                showTab(tabName) {
                    this.activeTab = tabName;
                },

                // --- Form Kontak ---
                handleFormSubmit() {
                    const waNumber = "6282199991265"; // Nomor Admin untuk form (sesuaikan jika perlu)
                    let message = `Halo Admin Sutan Raya,\n\n`;
                    message += `Nama: ${this.form.name}\n`;
                    message += `No. WA: ${this.form.phone}\n`;
                    message += `Pesan: ${this.form.message}\n\n`;
                    message += `Saya menghubungi dari form di website.`;

                    const encodedMessage = encodeURIComponent(message);
                    const waUrl = `https://wa.me/${waNumber}?text=${encodedMessage}`;
                    
                    window.open(waUrl, '_blank');
                    
                    // Reset form
                    this.form.name = '';
                    this.form.phone = '';
                    this.form.message = '';
                },

                // --- Pengganti Bahasa ---
                changeLanguage(lang) {
                    const cookieName = 'googtrans';
                    const cookieValue = '/auto/' + lang;
                    const domain = '.' + window.location.hostname.split('.').slice(-2).join('.'); // Get root domain
                    
                    document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=${domain}`;
                    document.cookie = `${cookieName}=${cookieValue}; path=/; domain=${domain}`;
                    
                    this.isLangSelectorOpen = false;
                    location.reload();
                },

                // --- [PERUBAHAN] Scroller Galeri Armada (Continuous) ---
                initContinuousScroller() {
                    const scroller = document.getElementById('imageScroller');
                    if (!scroller) return;

                    // 1. Simpan lebar original
                    this.originalScrollWidth = scroller.scrollWidth;
                    
                    // 2. Duplikasi gambar untuk efek loop tak terbatas
                    const images = Array.from(scroller.children);
                    images.forEach(img => {
                        scroller.appendChild(img.cloneNode(true));
                    });
                    
                    // 3. Mulai scroll
                    this.startScroll();
                },

                startScroll() { // Dipanggil oleh @mouseleave
                    this.isScrollingPaused = false;
                    const scroller = document.getElementById('imageScroller');
                    if (!scroller) return;

                    const step = () => {
                        // Berhenti jika di-pause (saat mouse hover)
                        if (this.isScrollingPaused) return; 

                        scroller.scrollLeft += this.scrollSpeed;
                        
                        // Cek jika sudah scroll melewati set gambar pertama
                        if (scroller.scrollLeft >= this.originalScrollWidth) {
                            // Lompat kembali ke awal tanpa animasi
                            scroller.scrollLeft = 0;
                        }
                        
                        // Minta frame animasi berikutnya
                        this.scrollAnimationId = requestAnimationFrame(step);
                    };
  
                    // Batalkan frame sebelumnya (jika ada) dan mulai yang baru
                    if (this.scrollAnimationId) {
                        cancelAnimationFrame(this.scrollAnimationId);
                    }
                    this.scrollAnimationId = requestAnimationFrame(step);
                },

                stopScroll() { // Dipanggil oleh @mouseenter
                    this.isScrollingPaused = true;
                    // Batalkan frame animasi saat di-pause
                    if (this.scrollAnimationId) {
                        cancelAnimationFrame(this.scrollAnimationId);
                        this.scrollAnimationId = null;
                    }
                },
                // --- End [PERUBAHAN] ---

                // --- Animasi Scroll ---
                initScrollAnimation() {
                    const elements = document.querySelectorAll('.scroll-animate');
                    if (!('IntersectionObserver' in window)) {
                        // Fallback untuk browser lama: langsung tampilkan semua
                        elements.forEach(el => el.classList.add('visible'));
                        return;
                    }
                    
                    this.observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            // [PERUBAHAN DI SINI]
                            if (entry.isIntersecting) {
                                // Saat elemen masuk ke layar, tambahkan kelas .visible
                                entry.target.classList.add('visible');
                            } else {
                                // Saat elemen keluar dari layar, hapus kelas .visible
                                entry.target.classList.remove('visible');
                            }
                        });
                    }, {
                        threshold: 0.1 // 10% elemen terlihat
                    });

                    elements.forEach(el => this.observer.observe(el));
                }
            },
            mounted() {
                this.initScrollAnimation();
                this.initContinuousScroller(); // [PERUBAHAN] Panggil inisiasi scroller baru

                // Listener untuk menutup popup saat klik di luar
                document.addEventListener('click', (event) => {
                    const clickedOutside = 
                        !event.target.closest('#mobile-menu-dropdown') &&
                        !event.target.closest('button[aria-controls="mobile-menu-dropdown"]') &&
                        !event.target.closest('#wa-selector') &&
                        !event.target.closest('a[href="#"][@click.prevent="toggleWaSelector"]') && 
                        !event.target.closest('#wa-selector-2') &&
                        !event.target.closest('a[href="#"][@click.prevent="toggleWaSelector2"]') && 
                        !event.target.closest('#language-selector') &&
                        !event.target.closest('.language-toggle');

                    if (clickedOutside) {
                        this.closeAllPopups();
                    }
                });
            },
            beforeUnmount() {
                // Bersihkan observer dan interval saat komponen dihancurkan
                if (this.observer) {
                    this.observer.disconnect();
                }
                this.stopScroll(); // [PERUBAHAN] Panggil stopScroll untuk membersihkan requestAnimationFrame
            }
        }).mount('#app');

     // === GOOGLE TRANSLATE (LOGIKA BARU YANG ROBUST) ===

/**
 * (BARU) Variabel yang Anda minta untuk memaksa region.
 * Hapus (comment out) baris ini atau set ke null untuk mengaktifkan
 * deteksi bahasa otomatis berdasarkan browser/device.
 */
const forceRegion = 'ID';

/**
 * (BARU) Daftar bahasa yang didukung oleh widget Anda.
 * Ini harus SAMA PERSIS dengan 'includedLanguages' di init.
 */
const supportedLanguages = ['id', 'en', 'zh-CN', 'ko', 'ja', 'ar', 'de', 'fr'];

/**
 * (BARU) Helper Function: Pemicu perubahan bahasa yang robust (tahan banting).
 * Ini akan terus mencoba menemukan <select> widget sampai berhasil atau timeout.
 */
function triggerLanguageChange(langCode) {
    // Pastikan bahasa ada di daftar
    if (!supportedLanguages.includes(langCode)) {
        console.warn(`Bahasa ${langCode} tidak ada dalam daftar supportedLanguages.`);
        return;
    }

    let attempt = 0;
    const maxAttempts = 10; // Coba maksimal 10 kali (total 5 detik)
    const intervalTime = 500; // Coba setiap 0.5 detik

    // Hapus interval lama jika ada (mencegah double-click atau race condition)
    if (window.googleTranslateInterval) {
        clearInterval(window.googleTranslateInterval);
    }

    window.googleTranslateInterval = setInterval(() => {
        // Cari elemen <select> yang dibuat oleh Google
        const selectElement = document.querySelector('#google_translate_element select');
        
        if (selectElement) {
            // (BERHASIL) Elemen ditemukan
            clearInterval(window.googleTranslateInterval); // Hentikan percobaan
            window.googleTranslateInterval = null; // Hapus ID interval

            if (selectElement.value !== langCode) {
                selectElement.value = langCode;
                // Memicu event 'change' agar script Google Translate bekerja
                selectElement.dispatchEvent(new Event('change'));
                console.log(`Google Translate berhasil di-switch ke ${langCode}.`);
            } else {
                console.log(`Google Translate sudah di ${langCode}.`);
            }
        } else {
            // (GAGAL) Elemen belum siap, coba lagi
            attempt++;
            console.log(`Mencoba switch ke ${langCode} (Percobaan ${attempt})... Widget belum siap.`);
            if (attempt >= maxAttempts) {
                clearInterval(window.googleTranslateInterval); // Hentikan percobaan
                window.googleTranslateInterval = null;
                console.error('Gagal menemukan elemen <select> Google Translate setelah 5 detik.');
            }
        }
    }, intervalTime);
}

/**
 * (BARU) Fungsi untuk auto-switch berdasarkan bahasa browser.
 * Hanya berjalan jika forceRegion tidak di-set ke 'ID'.
 */
function autoSwitchLanguage() {
    // 1. Cek variabel paksa
    if (forceRegion === 'ID') {
        console.log('Region dipaksa ke ID. Google Translate tidak akan auto-switch.');
        return; // Berhenti
    }

    // 2. Deteksi bahasa browser (misal: 'en' dari 'en-US')
    const userLang = (navigator.language || navigator.userLanguage).split('-')[0];
    
    // 3. Cek cookie dulu. Jika user pernah memilih bahasa, jangan di-override.
    const cookieLang = document.cookie.split('; ').find(row => row.startsWith('googtrans='));
    if (cookieLang && cookieLang !== '/id/id') {
        console.log('Cookie terjemahan (pilihan user) ditemukan. Auto-switch dibatalkan.');
        return;
    }

    // 4. Cek jika bahasa didukung dan bukan bahasa default (id)
    if (supportedLanguages.includes(userLang) && userLang !== 'id') {
        console.log(`Bahasa browser terdeteksi: ${userLang}. Mencoba auto-switch...`);
        // Panggil helper robust untuk switch
        triggerLanguageChange(userLang);
    } else {
        console.log(`Bahasa browser (${userLang}) tidak didukung atau sudah ID. Tidak ada auto-switch.`);
    }
}

/**
 * (Fungsi Asli Anda - DIMODIFIKASI)
 * Fungsi init Google Translate. HARUS berada di scope global (wadtiindow).
 */
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'id',
        includedLanguages: supportedLanguages.join(','), // Ambil dari variabel
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');

    // (BARU) Panggil fungsi auto-switch SETELAH widget di-init.
    // Kita beri jeda sedikit agar 'google_translate_element' sempat ter-render
    // sebelum kita mulai mencari <select> di dalam autoSwitchLanguage -> triggerLanguageChange
    setTimeout(autoSwitchLanguage, 500); 
}

/**
 * (Fungsi Anda yang Lain - DIMODIFIKASI)
 * Ini adalah fungsi yang dipanggil oleh tombol-tombol bendera (pilihan manual).
 * Sekarang menggunakan helper 'triggerLanguageChange' yang robust.
 */
function changeLanguage(langCode) {
    triggerLanguageChange(langCode);
    closeLangSelector(); // Tutup pop-up bahasa setelah diklik
}