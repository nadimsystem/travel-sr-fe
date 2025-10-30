 // === GOOGLE TRANSLATE ===
        function googleTranslateElementInit() {
            try {
                new google.translate.TranslateElement({
                    pageLanguage: 'id',
                    includedLanguages: 'id,en,zh-CN,ko,ja,ar,de,fr',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                    autoDisplay: false
                }, 'google_translate_element');
            } catch (error) {
                console.error("Google Translate failed to initialize:", error);
            }
        }

        function changeLanguage(langCode) {
            const googleTranslateSelect = document.querySelector('#google_translate_element select');
            if (googleTranslateSelect) {
                googleTranslateSelect.value = langCode;
                googleTranslateSelect.dispatchEvent(new Event('change'));
            } else {
                // Fallback jika elemen tidak ditemukan setelah beberapa saat
                setTimeout(() => {
                    const selectFallback = document.querySelector('#google_translate_element select');
                    if (selectFallback) {
                        selectFallback.value = langCode;
                        selectFallback.dispatchEvent(new Event('change'));
                    } else {
                        console.error("Google Translate select element still not found.");
                    }
                }, 500);
            }
            closeLangSelector();
        }

        function toggleLangSelector() {
            const selector = document.getElementById('language-selector');
            if (!selector) return;
            if (selector.classList.contains('opacity-100')) {
                closeLangSelector();
            } else {
                selector.classList.add('opacity-100', 'translate-y-0');
                selector.classList.remove('opacity-0', 'translate-y-10', 'pointer-events-none');
                closeWaSelector();
                closeWaSelector2();
            }
        }

        function closeLangSelector() {
            const selector = document.getElementById('language-selector');
            if (selector) {
                selector.classList.remove('opacity-100', 'translate-y-0');
                selector.classList.add('opacity-0', 'translate-y-10', 'pointer-events-none');
            }
        }

        // === WA SELECTORS ===
        function toggleWaSelector(event) {
            if (event) event.preventDefault();
            const selector = document.getElementById('wa-selector');
            if (!selector) return;
            if (selector.classList.contains('opacity-100')) {
                closeWaSelector();
            } else {
                selector.classList.add('opacity-100', 'translate-y-0');
                selector.classList.remove('opacity-0', 'translate-y-10', 'pointer-events-none');
                closeLangSelector();
                closeWaSelector2();
            }
        }

        function closeWaSelector() {
            const selector = document.getElementById('wa-selector');
            if (selector) {
                selector.classList.remove('opacity-100', 'translate-y-0');
                selector.classList.add('opacity-0', 'translate-y-10', 'pointer-events-none');
            }
        }

        function toggleWaSelector2(event) {
            if (event) event.preventDefault();
            const selector = document.getElementById('wa-selector-2');
            if (!selector) return;
            if (selector.classList.contains('opacity-100')) {
                closeWaSelector2();
            } else {
                selector.classList.add('opacity-100', 'translate-y-0');
                selector.classList.remove('opacity-0', 'translate-y-10', 'pointer-events-none');
                closeLangSelector();
                closeWaSelector();
            }
        }

        function closeWaSelector2() {
            const selector = document.getElementById('wa-selector-2');
            if (selector) {
                selector.classList.remove('opacity-100', 'translate-y-0');
                selector.classList.add('opacity-0', 'translate-y-10', 'pointer-events-none');
            }
        }

        // === MOBILE MENU ===
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu-dropdown');
            const button = document.querySelector('button[onclick="toggleMobileMenu()"]'); // Ambil tombolnya
            const isHidden = menu.classList.toggle('hidden');
            button.setAttribute('aria-expanded', !isHidden); // Update aria-expanded
        }

        function closeMobileMenu() {
            const menu = document.getElementById('mobile-menu-dropdown');
            const button = document.querySelector('button[onclick="toggleMobileMenu()"]');
            menu.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false'); // Set ke false saat ditutup
        }

        // === CONTACT FORM ===
        const shuttleWaNumber = '6282199991265'; // Nomor WA default form (Shuttle)

        function handleFormSubmit(event) {
            event.preventDefault();
            const name = document.getElementById('form-name').value;
            const phone = document.getElementById('form-phone').value;
            const message = document.getElementById('form-message').value;
            const text = `Halo Sutan Raya, saya ${name}.\nNomor WA: ${phone}\nPesan: ${message}`;
            const encodedText = encodeURIComponent(text);
            const whatsappURL = `https://wa.me/${shuttleWaNumber}?text=${encodedText}`; // Gunakan nomor WA shuttle
            window.open(whatsappURL, '_blank');
            document.getElementById('form-name').value = '';
            document.getElementById('form-phone').value = '';
            document.getElementById('form-message').value = '';
        }

        // === CONTACT TABS ===
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
                content.setAttribute('aria-hidden', 'true');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active-tab', 'border-sutan-gold', 'text-sutan-gold');
                btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-400');
                btn.setAttribute('aria-selected', 'false');
            });

            const activeContent = document.getElementById(`tab-content-${tabName}`);
            activeContent.classList.add('active');
            activeContent.setAttribute('aria-hidden', 'false');

            const activeBtn = document.getElementById(`tab-btn-${tabName}`);
            activeBtn.classList.add('active-tab', 'border-sutan-gold', 'text-sutan-gold');
            activeBtn.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-400');
            activeBtn.setAttribute('aria-selected', 'true');
        }

        // === ARMADA SCROLLER ===
        let scrollInterval = null;

        function startScroll() {
            const scroller = document.getElementById('imageScroller');
            if (!scroller || !scroller.firstElementChild) return; // Cek jika scroller ada dan punya anak
            clearInterval(scrollInterval);
            scrollInterval = setInterval(() => {
                if (scroller.scrollLeft >= scroller.scrollWidth / 2) {
                    scroller.scrollLeft = 0;
                } else {
                    scroller.scrollLeft += 1;
                }
            }, 30);
        }

        function stopScroll() {
            clearInterval(scrollInterval);
        }

        // === INTERSECTION OBSERVER (Animasi Scroll) ===
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // observer.unobserve(entry.target); // Hapus jika ingin animasi berulang saat scroll bolak-balik
                } else {
                    entry.target.classList.remove('visible'); // Tambahkan ini jika ingin animasi berulang
                }
            });
        }, {
            threshold: 0.1 // 10% elemen terlihat
        });

        // Inisialisasi setelah DOM siap
        document.addEventListener('DOMContentLoaded', () => {
            // Tab Kontak
            showTab('padang');

            // Scroller Armada
            const scroller = document.getElementById('imageScroller');
            if (scroller && scroller.children.length > 0) {
                // Duplikasi hanya jika belum ada (mencegah duplikasi ganda)
                if (scroller.scrollWidth <= scroller.clientWidth * 2) {
                    scroller.innerHTML += scroller.innerHTML;
                }
                startScroll();
            } else {
                console.warn("Image scroller not found or is empty.");
            }

            // Animasi Scroll
            const elementsToAnimate = document.querySelectorAll('.scroll-animate');
            elementsToAnimate.forEach(el => observer.observe(el));
        });