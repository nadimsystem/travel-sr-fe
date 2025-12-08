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

    // 1. Clone element untuk manipulasi aman
    const clone = originalElement.cloneNode(true);

    // 2. Setup container sementara agar style tetap jalan tapi posisi terkontrol
    // Kita buat container di luar viewport atau di atas segalanya
    const container = document.createElement('div');
    container.style.position = 'absolute';
    container.style.top = '0';
    container.style.left = '0';
    container.style.width = '400px'; // Paksa lebar sesuai desain tiket
    container.style.zIndex = '-9999'; // Sembunyikan dari user
    container.style.background = 'white'; // Pastikan background putih
    
    // Masukkan clone ke container
    container.appendChild(clone);
    document.body.appendChild(container);

    // 3. Konfigurasi html2pdf
    const opt = {
        margin:       0,
        filename:     filename,
        image:        { type: 'jpeg', quality: 1.0 }, // Max quality
        html2canvas:  { 
            scale: 2, // Retina scale
            useCORS: true, 
            logging: true,
            scrollY: 0,
            windowWidth: 1200 // Simulate desktop to ensure styles load
        },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    console.log("Generating PDF for:", filename);

    // 4. Generate & Cleanup
    html2pdf()
        .set(opt)
        .from(clone)
        .save()
        .then(() => {
            console.log("PDF Generated Successfully");
            document.body.removeChild(container); // Hapus elemen sementara
        })
        .catch(err => {
            console.error("PDF Generation Error:", err);
            alert("Terjadi kesalahan saat membuat PDF.");
            if (document.body.contains(container)) {
                document.body.removeChild(container);
            }
        });
}
