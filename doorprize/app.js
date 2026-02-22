const API_URL = 'api.php';
let currentTab = 'active'; // 'active' or 'trash'

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('couponForm')) {
        fetchCoupons();
        document.getElementById('couponForm').addEventListener('submit', handleFormSubmit);
        document.getElementById('addCouponForm').addEventListener('submit', handleAddCouponSubmit);
        
        // Default Date: Today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('departureDate').value = today;
        document.getElementById('modalDepartureDate').value = today;

        // Dark Mode Toggle
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                if (document.documentElement.classList.contains('dark')) {
                    localStorage.setItem('theme', 'dark');
                } else {
                    localStorage.setItem('theme', 'light');
                }
            });
        }
        // Auto-format coupon inputs (Pad to 6 digits)
        const couponInput = document.getElementById('couponNumber');
        const modalCouponInput = document.getElementById('modalCouponNumber');
        
        function padCouponValue(input) {
            let val = input.value.trim();
            // If it's a number (and not empty), pad it
            if (val && /^\d+$/.test(val)) {
                input.value = val.padStart(6, '0');
            }
        }

        if (couponInput) {
            couponInput.addEventListener('blur', function() { padCouponValue(this); });
        }
        if (modalCouponInput) {
            modalCouponInput.addEventListener('blur', function() { padCouponValue(this); });
        }
    }
});

let allCoupons = [];

async function fetchCoupons() {
    const action = currentTab === 'active' ? 'get_coupons' : 'get_trash';
    try {
        const response = await fetch(`${API_URL}?action=${action}`);
        allCoupons = await response.json();
        renderGroupedTable(allCoupons);
    } catch (error) {
        console.error('Error fetching coupons:', error);
    }
}

function switchTab(tab) {
    currentTab = tab;
    
    // Update UI
    const tabActive = document.getElementById('tabActive');
    const tabTrash = document.getElementById('tabTrash');
    
    if (tab === 'active') {
        tabActive.className = 'pb-2 px-4 border-b-2 border-blue-600 font-semibold text-blue-600 dark:text-blue-400 transition-colors';
        tabTrash.className = 'pb-2 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors';
    } else {
        tabTrash.className = 'pb-2 px-4 border-b-2 border-red-600 font-semibold text-red-600 dark:text-red-400 transition-colors';
        tabActive.className = 'pb-2 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors';
    }

    fetchCoupons();
}

function renderGroupedTable(coupons) {
    const tbody = document.getElementById('couponTableBody');
    const emptyState = document.getElementById('emptyState');
    
    if (!tbody) return;

    // Group by Name + Phone
    const groups = {};
    coupons.forEach(c => {
        const key = `${c.name}|${c.phone}`;
        if (!groups[key]) {
            groups[key] = {
                name: c.name,
                phone: c.phone,
                coupons: []
            };
        }
        groups[key].coupons.push(c);
    });

    const sortedGroups = Object.values(groups).sort((a, b) => b.coupons.length - a.coupons.length);

    // Update Stats
    const totalPeopleSpan = document.getElementById('totalPeople');
    const totalCouponsSpan = document.getElementById('totalCoupons');
    if (totalPeopleSpan) totalPeopleSpan.textContent = sortedGroups.length;
    if (totalCouponsSpan) totalCouponsSpan.textContent = coupons.length;

    tbody.innerHTML = '';
    
    if (sortedGroups.length === 0) {
        tbody.parentElement.parentElement.classList.add('hidden'); // Hide table container
        emptyState.classList.remove('hidden');
        return;
    } else {
        tbody.parentElement.parentElement.classList.remove('hidden');
        emptyState.classList.add('hidden');
    }

    sortedGroups.forEach(group => {
        // Main Row
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group border-b border-gray-100 dark:border-gray-700';
        
        const couponListHtml = group.coupons.map(c => `
            <div class="inline-flex items-center justify-between py-1 px-2 bg-gray-50 dark:bg-gray-700 rounded text-xs border border-gray-100 dark:border-gray-600">
                <span class="font-mono ${c.is_winner == 1 ? 'text-yellow-600 dark:text-yellow-400 font-bold' : 'text-gray-600 dark:text-gray-300'}">
                    ${c.coupon_number} ${c.is_winner == 1 ? '👑' : ''}
                </span>
                <span class="text-gray-400 dark:text-gray-500 ml-2 border-l border-gray-200 dark:border-gray-600 pl-2">${c.departure_date || '-'}</span>
                ${currentTab === 'active' 
                    ? `<button onclick="deleteCoupon(${c.id})" class="text-red-400 hover:text-red-600 ml-2" title="Hapus ke Sampah">×</button>`
                    : `<div class="flex ml-2 gap-1">
                        <button onclick="restoreCoupon(${c.id})" class="text-green-500 hover:text-green-600" title="Pulihkan">↩</button>
                        <button onclick="forceDeleteCoupon(${c.id})" class="text-red-500 hover:text-red-700" title="Hapus Permanen">🗑</button>
                       </div>`
                }
            </div>
        `).join('');

        tr.innerHTML = `
            <td class="px-6 py-4 align-top">
                <div class="font-semibold text-gray-900 dark:text-white">${group.name}</div>
            </td>
            <td class="px-6 py-4 align-top text-gray-500 dark:text-gray-400">
                ${group.phone}
            </td>
            <td class="px-6 py-4 align-top text-center">
                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    ${group.coupons.length}
                </span>
            </td>
            <td class="px-6 py-4 align-top">
                <div class="flex flex-wrap gap-2">
                    ${couponListHtml}
                </div>
            </td>
            <td class="px-6 py-4 align-top text-right">
                ${currentTab === 'active' ? `
                <button onclick="openAddCouponModal('${group.name}', '${group.phone}')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 px-3 py-1.5 rounded-lg transition-colors">
                    + Tambah Kupon
                </button>
                ` : '<span class="text-xs text-gray-400 italic">Terhapus</span>'}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// --- Form Handlers ---

async function handleFormSubmit(e) {
    e.preventDefault();
    const data = {
        coupon_number: document.getElementById('couponNumber').value,
        name: document.getElementById('name').value,
        phone: document.getElementById('phone').value,
        departure_date: document.getElementById('departureDate').value
    };
    await saveCoupon(data);
    document.getElementById('couponForm').reset();
}

async function handleAddCouponSubmit(e) {
    e.preventDefault();
    const data = {
        coupon_number: document.getElementById('modalCouponNumber').value,
        name: document.getElementById('modalNameInput').value,
        phone: document.getElementById('modalPhoneInput').value,
        departure_date: document.getElementById('modalDepartureDate').value
    };
    await saveCoupon(data);
    closeModal();
    document.getElementById('modalCouponNumber').value = '';
}

async function saveCoupon(data) {
    try {
        const response = await fetch(`${API_URL}?action=save_coupon`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            fetchCoupons(); // Refresh table
        } else {
            alert('Gagal menyimpan: ' + result.message);
        }
    } catch (error) {
        console.error('Error saving coupon:', error);
        alert('Terjadi kesalahan sistem.');
    }
}

async function deleteCoupon(id) {
    if (!confirm('Pindahkan kupon ini ke Sampah?')) return;

    try {
        const response = await fetch(`${API_URL}?action=delete_coupon`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            fetchCoupons();
        } else {
            alert('Gagal menghapus: ' + result.message);
        }
    } catch (error) {
        console.error('Error deleting coupon:', error);
    }
}

async function restoreCoupon(id) {
    if (!confirm('Pulihkan kupon ini?')) return;

    try {
        const response = await fetch(`${API_URL}?action=restore_coupon`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            fetchCoupons();
        } else {
            alert('Gagal memulihkan: ' + result.message);
        }
    } catch (error) {
        console.error('Error restoring coupon:', error);
    }
}

async function forceDeleteCoupon(id) {
    if (!confirm('PERINGATAN: Kupon akan dihapus PERMANEN dan tidak bisa dikembalikan. Lanjutkan?')) return;

    try {
        const response = await fetch(`${API_URL}?action=force_delete_coupon`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            fetchCoupons();
        } else {
            alert('Gagal menghapus permanen: ' + result.message);
        }
    } catch (error) {
        console.error('Error force deleting coupon:', error);
    }
}

// --- Modal Logic ---

window.openAddCouponModal = function(name, phone) {
    document.getElementById('modalPersonName').textContent = name;
    document.getElementById('modalPersonPhone').textContent = phone;
    document.getElementById('modalNameInput').value = name;
    document.getElementById('modalPhoneInput').value = phone;
    
    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('modalDepartureDate').value = today;

    document.getElementById('addCouponModal').classList.remove('hidden');
    document.getElementById('modalCouponNumber').focus();
};

window.closeModal = function() {
    document.getElementById('addCouponModal').classList.add('hidden');
};
