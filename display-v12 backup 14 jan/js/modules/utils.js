export const utilsMixin = {
    methods: {
        showToast(title, icon = 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            Toast.fire({ icon: icon, title: title });
        },

        getDayName(dateStr) {
            if (!dateStr) return '';
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            return days[new Date(dateStr).getDay()];
        },

        getMonthName(idx) {
            const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            return months[idx] || '';
        },
        
        formatNumber(num) {
            return (num || 0).toLocaleString('id-ID');
        },

        parseAddress(str) {
            if (!str) return { text: '-', link: '' };
            const parts = str.split(',');
            if (parts.length > 1) {
                const lastPart = parts[parts.length - 1].trim();
                // Check if last part looks like a URL
                if (lastPart.startsWith('http') || lastPart.includes('maps.app.goo.gl') || lastPart.includes('google.com/maps')) {
                    const link = lastPart;
                    const text = parts.slice(0, -1).join(',').trim();
                    return { text, link };
                }
            }
            return { text: str.trim(), link: '' };
        },

        async postToApi(action, data) {
            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action, ...data })
                });
                return await res.json();
            } catch (e) {
                console.error("API Error", e);
                return { status: 'error', message: e.message };
            }
        },
        
        formatDateTime(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleString('id-ID', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        },

        formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n||0); },
        
        formatDate(d) { 
            if(!d) return '-'; 
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }; 
            return new Date(d).toLocaleDateString('id-ID', options); 
        },
        
        getWaLink(phone) {
            if (!phone) return '#';
            let p = phone.toString().replace(/\D/g, ''); // Remove non-digits
            if (p.startsWith('0')) {
                p = '62' + p.substring(1);
            }
            return `https://wa.me/${p}`;
        },

        copyWa(p) {
            const type = p.serviceType === 'Dropping' ? 'CHARTER' : 'TRAVEL';
            const txt = `*SUTAN RAYA - ${type}*\nJadwal: ${p.time}\nNama: ${p.passengerName}\nKursi: ${p.seatNumbers}\nHP: ${p.passengerPhone}\nJemput: ${p.pickupMapUrl||'-'} (${p.pickupAddress||'-'})\nAntar: ${p.dropoffAddress||'-'}`;
            navigator.clipboard.writeText(txt).then(() => alert("Data disalin ke Clipboard!"));
        },
        
        // CSS Helpers from App.js
        getVehicleStatusClass(s) { return s==='Tersedia'?'bg-green-100 text-green-700':(s==='On Trip'?'bg-blue-100 text-blue-700':(s==='Perbaikan'?'bg-red-100 text-red-700':'bg-gray-100')); },
        getDriverStatusClass(s) { return s==='Standby'?'bg-green-100 text-green-700':(s==='Jalan'?'bg-blue-100 text-blue-700':'bg-gray-200'); },
        getTripCardClass(s) { if(s==='On Trip') return 'border-blue-200 bg-blue-50/30'; if(s==='Tiba') return 'border-green-200 bg-green-50/30'; if(s==='Kendala') return 'border-red-200 bg-red-50/30'; return 'border-gray-200'; },
        getTripStatusBadge(s) { if(s==='On Trip') return 'bg-blue-500'; if(s==='Tiba') return 'bg-green-500'; if(s==='Kendala') return 'bg-red-500'; return 'bg-gray-400'; },
        
        getTripPassengerCount(trip) {
            if (!trip.passengers) return 0;
            let passengers = [];
            if (Array.isArray(trip.passengers)) {
                passengers = trip.passengers;
            } else if (typeof trip.passengers === 'object') {
                passengers = Object.values(trip.passengers);
            }
            return passengers.reduce((total, p) => total + (parseInt(p.seatCount) || 1), 0);
        },

        updateTime() { 
            const n=new Date(); 
            this.currentTime=n.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}); 
            this.currentDate=n.toLocaleDateString('id-ID',{weekday:'long',day:'numeric',month:'long'}); 
        },
    }
};
