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
                    observer: null, // For scroll animations
                    
                    // [BARU] Doorprize Stats
                    doorprizeStats: {
                        participants: 0,
                        coupons: 0
                    },
                    isContactPopupOpen: false // [BARU] Popup Kontak
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
                    this.isContactPopupOpen = false;
                },
                toggleContactPopup(event) {
                    if (event) event.preventDefault();
                    this.isContactPopupOpen = !this.isContactPopupOpen;
                    this.isMobileMenuOpen = false;
                    this.isWaSelectorOpen = false;
                    this.isWaSelector2Open = false;
                    this.isLangSelectorOpen = false;
                },
                closeAllPopups() {
                    this.isMobileMenuOpen = false;
                    this.isWaSelectorOpen = false;
                    this.isWaSelector2Open = false;
                    this.isLangSelectorOpen = false;
                    this.isContactPopupOpen = false;
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
                // --- Pengganti Bahasa ---
                changeLanguage(lang) {
                    // Panggil fungsi global dari js/translate.js
                    if (typeof window.changeLanguage === 'function') {
                        window.changeLanguage(lang);
                    } else {
                        // Fallback jika js/translate.js belum load
                        localStorage.setItem('sutanraya_lang', lang);
                        location.reload();
                    }
                    this.isLangSelectorOpen = false;
                },

                // --- [BARU] Fetch Doorprize Stats ---
                async fetchDoorprizeStats() {
                    try {
                        const response = await fetch('doorprize/api.php?action=get_stats');
                        const res = await response.json();
                        if (res.status === 'success') {
                            this.doorprizeStats.participants = res.data.total_participants;
                            this.doorprizeStats.coupons = res.data.total_coupons;
                        }
                    } catch (error) {
                        console.error('Gagal mengambil data doorprize:', error);
                    }
                },

                // --- [PERUBAHAN] Scroller Galeri Armada (Continuous) ---
                initContinuousScroller() {
                    const scroller = document.getElementById('imageScroller');
                    if (!scroller) return;

                    // Force scroll behavior to auto to prevent smooth scrolling interference
                    scroller.style.scrollBehavior = 'auto';

                    // 1. Duplikasi gambar untuk efek loop tak terbatas
                    const images = Array.from(scroller.children);
                    images.forEach(img => {
                        scroller.appendChild(img.cloneNode(true));
                    });

                    // 2. [VISUAL PERFECT] Tunggu SEMUA gambar clone termuat sempurna sebelum hitung width
                    const allImages = Array.from(scroller.querySelectorAll('img'));
                    const imagePromises = allImages.map(img => {
                        if (img.complete) return Promise.resolve();
                        return new Promise(resolve => {
                            img.onload = resolve;
                            img.onerror = resolve; 
                        });
                    });

                    // [SAFETY] Fallback jika ada gambar yang hang loading (terutama di Safari mobile)
                    const timeoutPromise = new Promise(resolve => setTimeout(resolve, 3000));

                    Promise.race([Promise.all(imagePromises), timeoutPromise]).then(() => {
                         // Tunggu nextTick Vue + sedikit buffer render agar width akurat 100%
                        this.$nextTick(() => {
                           // Re-calculate width just to be sure
                           this.originalScrollWidth = scroller.scrollWidth / 2;
                           this.startScroll();
                        });
                    });
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
                this.initContinuousScroller(); // Self-wait logic inside
                this.fetchDoorprizeStats(); // [BARU] Ambil data doorprize

                // Listener untuk menutup popup saat klik di luar
                document.addEventListener('click', (event) => {
                    const clickedOutside = 
                        !event.target.closest('#mobile-menu-dropdown') &&
                        !event.target.closest('button[aria-controls="mobile-menu-dropdown"]') &&
                        !event.target.closest('#wa-selector') &&
                        !event.target.closest('#wa-toggle-desktop') && 
                        !event.target.closest('#wa-selector-2') &&
                        !event.target.closest('#wa-toggle-mobile') && 
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
