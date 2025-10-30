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
                    const waNumber = "6282199996500"; // Nomor Admin untuk form (sesuaikan jika perlu)
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

        /**
         * Fungsi init Google Translate
         * HARUS berada di scope global (window) agar bisa dipanggil oleh script Google
         */
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'id',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, 'google_translate_element');
        }