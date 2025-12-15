/**
 * Ticket Printer Utility
 * Mengubah visual tiket HTML menjadi PDF menggunakan html2pdf.js
 */

function generateTicketPDF(elementId, filename) {
    const originalElement = document.getElementById(elementId);
    
    if (!originalElement) {
        console.error("Ticket element not found:", elementId);
        alert("Gagal menemukan elemen tiket untuk dicetak.");
        return;
    }

    // 1. VISIBLE FLASH STRATEGY (Improved with Absolute Positioning)
    const container = document.createElement('div');
    container.style.position = 'absolute'; // Gunakan absolute agar ikut flow tapi di atas
    container.style.top = window.scrollY + 'px'; // Sesuaikan dengan scroll user saat ini
    container.style.left = '0';
    container.style.width = '100%';
    container.style.height = '100vh';
    container.style.zIndex = '99999';
    container.style.background = 'rgba(255,255,255, 1)'; // Putih penuh
    container.style.display = 'flex';
    container.style.alignItems = 'center';
    container.style.justifyContent = 'center';
    
    // 2. Clone Element
    const clone = originalElement.cloneNode(true);
    
    // Reset Style Clone
    clone.style.position = 'relative';
    clone.style.display = 'block';
    clone.style.visibility = 'visible';
    clone.style.opacity = '1';
    clone.style.transform = 'none';
    clone.style.zIndex = '100000';
    clone.style.left = 'auto';
    clone.style.top = 'auto';
    clone.style.margin = 'auto'; // Center
    
    // Mobile Portrait Fix
    clone.style.width = '375px'; 
    clone.style.maxWidth = '375px';
    clone.style.height = 'auto'; 
    clone.style.boxShadow = 'none'; 
    clone.style.backgroundColor = '#ffffff'; // Explicit White
    
    // Clean utility classes
    clone.classList.remove('opacity-0', 'pointer-events-none', 'fixed', 'absolute', 'inset-0', 'z-[-100]', 'hidden', 'w-full', 'max-w-sm', 'shadow-2xl');
    clone.classList.add('block', 'visible', 'opacity-100'); 

    container.appendChild(clone);
    document.body.appendChild(container);

    console.log("Generating Flash PDF for:", filename);

    // 3. Render dengan Delay (Increased to ensure paint)
    setTimeout(() => {
        const opt = {
            margin:       0,
            filename:     filename,
            image:        { type: 'jpeg', quality: 0.98 }, 
            html2canvas:  { 
                scale: 3, 
                useCORS: true,
                allowTaint: true, // Allow tainted canvas just in case
                logging: false,
                scrollY: 0,
                x: 0,
                y: 0,
                width: 375, // Explicit capture width
                windowWidth: 375
            },
            jsPDF:        { unit: 'mm', format: [100, 190], orientation: 'portrait' } 
        };

        html2pdf()
            .set(opt)
            .from(clone) // Gunakan clone langsung yang sudah di dalam container visible
            .save()
            .then(() => {
                console.log("PDF Generated Successfully");
                if (document.body.contains(container)) {
                    document.body.removeChild(container);
                }
            })
            .catch(err => {
                console.error("PDF Generation Error:", err);
                if (document.body.contains(container)) {
                    document.body.removeChild(container);
                }
                alert("Gagal cetak PDF. Coba lagi.");
            });
    }, 500); // 500ms delay untuk memastikan gambar/font terload
}
