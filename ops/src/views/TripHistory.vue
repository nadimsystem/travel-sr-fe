<template>
  <div class="space-y-6">
    <!-- Header & Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
      <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Riwayat Trip & Penggajian</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Kelola riwayat keberangkatan armada dan gaji supir</p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <!-- Quick Filters -->
        <div class="flex bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
             <button @click="setDateFilter('today')" class="px-3 py-1.5 text-xs font-medium rounded-md transition-all" :class="activeFilter === 'today' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">Hari Ini</button>
             <button @click="setDateFilter('week')" class="px-3 py-1.5 text-xs font-medium rounded-md transition-all" :class="activeFilter === 'week' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">Minggu Ini</button>
             <button @click="setDateFilter('month')" class="px-3 py-1.5 text-xs font-medium rounded-md transition-all" :class="activeFilter === 'month' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">Bulan Ini</button>
        </div>

        <!-- Search -->
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="bi bi-search"></i></span>
            <input type="text" v-model="searchQuery" placeholder="Cari Supir / Rute..." class="pl-9 pr-4 py-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block dark:bg-slate-800 dark:border-slate-700 dark:placeholder-gray-400 dark:text-white">
        </div>

        <div class="h-6 w-px bg-slate-300 dark:bg-slate-700 mx-1"></div>

        <input type="date" v-model="filter.startDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2 dark:bg-slate-800 dark:border-slate-700 dark:placeholder-gray-400 dark:text-white">
        <span class="text-slate-400">-</span>
        <input type="date" v-model="filter.endDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2 dark:bg-slate-800 dark:border-slate-700 dark:placeholder-gray-400 dark:text-white">
        
        <button @click="loadTrips" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 flex items-center gap-2">
            <i class="bi bi-arrow-repeat"></i>
        </button>
        <!-- Batch Print Button -->
        <button v-if="selectedTrips.length > 0" @click="printBatchStruk" class="text-white bg-slate-700 hover:bg-slate-800 focus:ring-4 focus:ring-slate-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-slate-700 dark:hover:bg-slate-700 flex items-center gap-2 animate-bounce-in">
             <i class="bi bi-printer"></i> Cetak ({{ selectedTrips.length }})
        </button>
      </div>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
             <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Trip</div>
             <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ trips.length }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
             <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Penumpang</div>
             <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ totalPassengers }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
             <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Estimasi Gaji Supir</div>
             <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ formatCurrency(totalPayroll) }}</div>
        </div>
    </div>


    <div class="border-b border-slate-200 dark:border-slate-800 mb-4">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
            <li class="mr-2">
                <button @click="activeTab = 'daily'" :class="activeTab === 'daily' ? 'border-blue-600 text-blue-600 dark:text-blue-500' : 'border-transparent text-slate-500 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300'" class="inline-block p-4 border-b-2 rounded-t-lg transition-all">
                    <i class="bi bi-list-ul mr-2"></i> Mode Harian (Detail)
                </button>
            </li>
            <li class="mr-2">
                <button @click="activeTab = 'recap'" :class="activeTab === 'recap' ? 'border-blue-600 text-blue-600 dark:text-blue-500' : 'border-transparent text-slate-500 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300'" class="inline-block p-4 border-b-2 rounded-t-lg transition-all">
                    <i class="bi bi-people mr-2"></i> Mode Per Supir (Rekap)
                </button>
            </li>
        </ul>
    </div>


    <div v-if="activeTab === 'daily'" class="flex gap-2 mb-4">
        <button @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-slate-800 text-white' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'" class="px-3 py-1.5 text-xs font-medium rounded-full transition-colors shadow-sm">
            Semua
        </button>
        <button @click="statusFilter = 'pending'" :class="statusFilter === 'pending' ? 'bg-yellow-500 text-white' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'" class="px-3 py-1.5 text-xs font-medium rounded-full transition-colors shadow-sm">
            <i class="bi bi-hourglass-split me-1"></i> Belum Dibayar
        </button>
        <button @click="statusFilter = 'paid'" :class="statusFilter === 'paid' ? 'bg-green-600 text-white' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'" class="px-3 py-1.5 text-xs font-medium rounded-full transition-colors shadow-sm">
            <i class="bi bi-check-circle-fill me-1"></i> Sudah Dibayar
        </button>
    </div>

    <!-- DAILY VIEW -->
    <div v-if="activeTab === 'daily'" class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-800 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="p-4">
                            <div class="flex items-center">
                                <input id="checkbox-all" type="checkbox" @change="toggleAllSelection" :checked="isAllSelected" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="checkbox-all" class="sr-only">checkbox</label>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">Waktu</th>
                        <th scope="col" class="px-6 py-3">Rute</th>
                        <th scope="col" class="px-6 py-3">Armada / Supir</th>
                        <th scope="col" class="px-6 py-3 text-center">Penumpang</th>
                        <th scope="col" class="px-6 py-3 text-right">Gaji Supir</th>
                        <th scope="col" class="px-6 py-3 text-center">Status</th>
                        <th scope="col" class="px-6 py-3">Catatan</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="isLoading" class="bg-white border-b dark:bg-slate-900 dark:border-slate-800">
                        <td colspan="9" class="px-6 py-10 text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            <p class="mt-2 text-slate-500">Memuat data...</p>
                        </td>
                    </tr>
                    <tr v-else-if="filteredTrips.length === 0" class="bg-white border-b dark:bg-slate-900 dark:border-slate-800">
                        <td colspan="9" class="px-6 py-10 text-center text-slate-500">
                            <i class="bi bi-inbox text-4xl mb-2 block text-slate-300"></i>
                            Tidak ada data trip.
                        </td>
                    </tr>
                    <tr v-for="trip in filteredTrips" :key="trip.id" class="bg-white border-b dark:bg-slate-900 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="w-4 p-4">
                            <div class="flex items-center">
                                <input :id="'checkbox-table-' + trip.id" type="checkbox" v-model="selectedTrips" :value="trip" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label :for="'checkbox-table-' + trip.id" class="sr-only">checkbox</label>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-white whitespace-nowrap">
                            <div class="text-xs text-slate-400">{{ formatDate(trip.date) }}</div>
                            <div class="text-base font-bold">{{ formatTime(trip.time) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                                    {{ trip.routeConfig?.origin || '?' }} 
                                    <i class="bi bi-arrow-right mx-1"></i> 
                                    {{ trip.routeConfig?.destination || '?' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800 dark:text-white">{{ trip.fleet?.name || '-' }} ({{ trip.fleet?.plateNumber || '' }})</div>
                            <div class="text-xs text-slate-500">{{ trip.driver?.name || 'No Driver' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-slate-100 text-slate-800 text-xs font-bold px-2.5 py-0.5 rounded dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ trip.passengerCount }} Org
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ formatCurrency(trip.payrollCalculated) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div v-if="trip.status === 'Selesai'" class="flex flex-col items-center gap-1">
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                                    <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                </span>
                                <button v-if="trip.payroll_proof_image" @click="viewProof(trip.payroll_proof_image)" class="text-[10px] text-blue-600 hover:text-blue-800 underline cursor-pointer">
                                    <i class="bi bi-image me-1"></i> Bukti TF
                                </button>
                            </div>
                            <span v-else class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">
                                <i class="bi bi-hourglass-split me-1"></i> Pending
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs italic text-slate-500 max-w-[150px] truncate">
                            {{ trip.note || '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button v-if="trip.status !== 'Selesai'" @click="openPayrollModal(trip)" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-2 dark:bg-blue-500 dark:hover:bg-blue-600 focus:outline-none dark:focus:ring-blue-800 shadow-sm transition-all hover:shadow-md">
                                <i class="bi bi-cash-coin me-1"></i> Proses Gaji
                            </button>
                            <button v-else @click="printStruk(trip)" class="text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 focus:ring-4 focus:outline-none focus:ring-slate-300 font-medium rounded-lg text-xs px-3 py-2 dark:bg-slate-900 dark:text-white dark:border-slate-700 dark:hover:bg-slate-700 dark:hover:border-slate-700 dark:focus:ring-slate-700 shadow-sm">
                                <i class="bi bi-printer me-1"></i> Struk
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RECAP / BATCH VIEW -->
    <div v-if="activeTab === 'recap'" class="space-y-6">
        <div v-for="group in groupedTrips" :key="group.driverName" class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                <!-- Group Header -->
                <div class="p-4 flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-4 border-b border-gray-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold dark:bg-blue-900 dark:text-blue-300">
                            {{ getInitials(group.driverName) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white text-lg uppercase">{{ group.driverName }}</h3>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ group.trips.length }} Trip Total 
                                <span v-if="group.pendingCount > 0" class="text-orange-500 font-bold">({{ group.pendingCount }} Pending)</span>
                                <span v-else class="text-green-500 font-bold">(Lunas Semua)</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right mt-2 md:mt-0">
                        <div class="text-[10px] text-slate-400 uppercase font-bold">Total Pending</div>
                         <!-- Dynamic Total based on selection or Default Pending Amount -->
                        <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ formatCurrency(getSelectedCount(group) > 0 ? calculateSelectedTotal(group) : group.pendingAmount) }}
                        </div>
                        <div class="text-xs text-slate-400" v-if="getSelectedCount(group) > 0">
                            (Terpilih: {{ getSelectedCount(group) }} Trip)
                        </div>
                        
                        <button 
                            v-if="group.pendingCount > 0"
                            @click="handleGroupPayment(group)"
                            class="mt-1 px-4 py-1.5 rounded-lg text-xs font-bold transition-colors shadow-sm"
                            :class="getSelectedCount(group) > 0 ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white'"
                        >
                            <i class="bi" :class="getSelectedCount(group) > 0 ? 'bi-check2-all' : 'bi-cash-stack'"></i>
                            {{ getSelectedCount(group) > 0 ? `Bayar (${getSelectedCount(group)})` : 'Bayar Semua Pending' }}
                        </button>
                    </div>
                </div>

                <!-- Trips Table -->
                <!-- Trips Table: Pending (Always Visible) -->
                <div class="overflow-x-auto mb-4" v-if="group.pendingTrips.length > 0">
                    <h4 class="px-4 py-2 text-xs font-bold text-orange-600 bg-orange-50 dark:bg-orange-900/20 dark:text-orange-400 border-b border-orange-100 dark:border-orange-900/30">
                        PENDING ({{ group.pendingTrips.length }})
                    </h4>
                    <table class="w-full text-left border-collapse text-slate-500 dark:text-slate-400">
                        <thead>
                            <tr class="text-[10px] text-slate-400 uppercase border-b border-slate-50 dark:border-slate-800">
                                <th class="py-2 pl-2 w-8">
                                    <input type="checkbox" 
                                        :checked="isGroupAllSelected(group)"
                                        @change="toggleGroupSelection(group)"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-slate-800 dark:border-slate-700 dark:focus:ring-blue-600 dark:ring-offset-gray-800 cursor-pointer">
                                </th>
                                <th class="py-2">Tanggal</th>
                                <th scope="col" class="px-6 py-2">Rute</th>
                                <th scope="col" class="px-6 py-2">Armada</th>
                                <th scope="col" class="px-6 py-2 text-center">Penumpang</th>
                                <th scope="col" class="px-6 py-2 text-right">Nominal</th>
                                <th scope="col" class="px-6 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-slate-600 dark:text-slate-300">
                            <tr v-for="trip in group.pendingTrips" :key="trip.id" 
                                class="border-b border-slate-50 dark:border-slate-800 last:border-0 hover:bg-slate-50/50 dark:hover:bg-slate-700/50 transition-colors"
                                :class="{'bg-blue-50/30 dark:bg-blue-900/20': isTripSelected(trip.id)}">
                                <td class="py-3 pl-2">
                                     <input type="checkbox" 
                                        :checked="isTripSelected(trip.id)"
                                        @change="toggleTripSelection(trip.id)"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-slate-800 dark:border-slate-700 dark:focus:ring-blue-600 dark:ring-offset-gray-800 cursor-pointer">
                                </td>
                                <td class="px-6 py-3">
                                    <div class="font-bold text-slate-700 dark:text-white">{{ formatDate(trip.date) }}</div>
                                    <div class="text-xs text-slate-400">{{ formatTime(trip.time) }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    {{ trip.routeConfig?.origin }} <i class="bi bi-arrow-right mx-1 text-slate-300 dark:text-slate-500"></i> {{ trip.routeConfig?.destination }}
                                </td>
                                <td class="px-6 py-3">
                                    <div>{{ trip.fleet?.name }} <span class="text-xs text-slate-400">({{ trip.fleet?.plateNumber }})</span></div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    {{ trip.passengerCount }}
                                </td>
                                <td class="px-6 py-3 text-right font-mono font-bold text-slate-700 dark:text-slate-300">
                                    Rp {{ formatCurrency(trip.payrollCalculated) }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <button @click="openPayrollModal(trip)" class="px-2 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/50 dark:text-emerald-300 dark:hover:bg-emerald-900 transition">
                                        Bayar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Trips Table: Paid (Accordion) -->
                <div v-if="group.paidTrips.length > 0 || group.paidGroups.length === 0" class="border-t border-slate-100 dark:border-slate-800">
                    <button @click="toggleDriver(group.driverName)" 
                        class="w-full flex items-center justify-between px-4 py-3 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-700 transition-colors text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">
                        <span><i class="bi" :class="isDriverExpanded(group.driverName) ? 'bi-chevron-down' : 'bi-chevron-right'"></i> Riwayat Pembayaran ({{ group.paidTrips.length }})</span>
                        <span v-if="!isDriverExpanded(group.driverName)" class="text-[10px] text-slate-400">Klik untuk melihat</span>
                    </button>
                    
                    <div v-show="isDriverExpanded(group.driverName)" class="bg-slate-50/50 dark:bg-slate-900/30 p-2 animate-fade-in-up">
                         
                         <!-- Month Shortcuts / Global Filter Helpers -->
                         <div class="flex gap-2 mb-2 px-2 overflow-x-auto pb-2">
                            <button @click="setMonthFilter(0)" class="whitespace-nowrap px-3 py-1 rounded bg-white border border-slate-200 text-xs text-slate-600 hover:bg-blue-50 hover:text-blue-600 shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                Bulan Ini
                            </button>
                            <button @click="setMonthFilter(-1)" class="whitespace-nowrap px-3 py-1 rounded bg-white border border-slate-200 text-xs text-slate-600 hover:bg-blue-50 hover:text-blue-600 shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                Bulan Lalu
                            </button>
                            <button @click="setMonthFilter(-2)" class="whitespace-nowrap px-3 py-1 rounded bg-white border border-slate-200 text-xs text-slate-600 hover:bg-blue-50 hover:text-blue-600 shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                2 Bulan Lalu
                            </button>
                         </div>

                         <!-- Month Groups -->
                         <div v-for="mGroup in group.paidGroups" :key="mGroup.key" class="mb-2 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
                            <button @click="toggleMonth(mGroup.uniqueKey)" class="w-full flex justify-between items-center px-4 py-2 bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    <i class="bi" :class="isMonthExpanded(mGroup.uniqueKey) ? 'bi-caret-down-fill' : 'bi-caret-right-fill'"></i> {{ mGroup.label }}
                                </span>
                                <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold">
                                    Rp {{ formatCurrency(mGroup.total) }}
                                </span>
                            </button>

                            <div v-show="isMonthExpanded(mGroup.uniqueKey)" class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-slate-500 dark:text-slate-400">
                                    <thead>
                                        <tr class="text-[10px] text-slate-400 uppercase border-b border-slate-100 dark:border-slate-800 bg-slate-100/30">
                                            <th class="py-2 pl-4">Tanggal</th>
                                            <th scope="col" class="px-6 py-2">Rute</th>
                                            <th scope="col" class="px-6 py-2">Armada</th>
                                            <th scope="col" class="px-6 py-2 text-center">Penumpang</th>
                                            <th scope="col" class="px-6 py-2 text-right">Nominal</th>
                                            <th scope="col" class="px-6 py-2 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm text-slate-500 dark:text-slate-400 opacity-75">
                                        <tr v-for="trip in mGroup.trips" :key="trip.id" class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/30 transition-colors">
                                            <td class="px-6 py-3 pl-4">
                                                <div class="font-bold">{{ formatDate(trip.date) }}</div>
                                                <div class="text-xs text-slate-400">{{ formatTime(trip.time) }}</div>
                                            </td>
                                            <td class="px-6 py-3">
                                                {{ trip.routeConfig?.origin }} <i class="bi bi-arrow-right mx-1 text-slate-300"></i> {{ trip.routeConfig?.destination }}
                                            </td>
                                            <td class="px-6 py-3">
                                                <div>{{ trip.fleet?.name }} <span class="text-xs text-slate-400">({{ trip.fleet?.plateNumber }})</span></div>
                                            </td>
                                            <td class="px-6 py-3 text-center">
                                                {{ trip.passengerCount }}
                                            </td>
                                            <td class="px-6 py-3 text-right font-mono font-bold text-green-600 dark:text-green-400">
                                                Rp {{ formatCurrency(trip.payrollCalculated) }} <i class="bi bi-check-circle-fill ml-1 text-[10px]"></i>
                                            </td>
                                            <td class="px-6 py-3 text-center">
                                                <button @click="printStruk(trip)" class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 transition">
                                                    <i class="bi bi-printer"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                         </div>
                    </div>
                </div>
        </div>
        
        <div v-if="groupedTrips.length === 0" class="text-center py-10 text-slate-400">
            <i class="bi bi-check-circle text-4xl mb-2 block"></i>
            Semua supir sudah lunas untuk periode ini.
        </div>
    </div>

    <!-- Payroll Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md overflow-hidden animate-fade-in-up">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">
                    {{ selectedBatch ? 'Pembayaran Massal (Mingguan)' : 'Konfirmasi Gaji Supir' }}
                </h3>
                <button @click="cancelModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <div class="p-6 space-y-4">
                <!-- SINGLE TRIP SUMMARY -->
                <div v-if="selectedTrip" class="bg-blue-50 dark:bg-slate-800/50 p-4 rounded-xl space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Rute</span>
                        <span class="font-bold text-slate-800 dark:text-white">{{ selectedTrip?.routeConfig?.origin }} - {{ selectedTrip?.routeConfig?.destination }}</span>
                    </div>
                     <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Supir</span>
                        <span class="font-bold text-slate-800 dark:text-white">{{ selectedTrip?.driver?.name }}</span>
                    </div>
                     <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Jumlah Penumpang</span>
                        <span class="font-bold text-slate-800 dark:text-white">{{ selectedTrip?.passengerCount }} Orang</span>
                    </div>
                </div>

                <!-- BATCH SUMMARY -->
                <div v-if="selectedBatch" class="bg-blue-50 dark:bg-slate-800/50 p-4 rounded-xl space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Supir</span>
                        <span class="font-bold text-slate-800 dark:text-white">{{ selectedBatch?.driverName }}</span>
                    </div>
                     <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Total Trip Pending</span>
                        <span class="font-bold text-slate-800 dark:text-white">{{ selectedBatch?.trips.length }} Trip</span>
                    </div>
                     <div class="flex justify-between text-sm border-t border-blue-200 dark:border-slate-700 pt-2 mt-2">
                        <span class="text-slate-500 dark:text-slate-400">Total Gaji</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ formatCurrency(selectedBatch?.totalAmount) }}</span>
                    </div>
                </div>

                <!-- Calculation Rule (Single Trip Only) -->
                <div v-if="selectedTrip" class="border-t border-slate-100 dark:border-slate-800 pt-4">
                    <p class="text-xs text-slate-500 mb-2">Perhitungan (Bisa Diedit):</p>
                    <div class="relative">
                         <span class="absolute left-3 top-3.5 text-slate-500 font-bold">Rp</span>
                         <input type="number" v-model="payrollInput" class="w-full pl-10 pr-4 py-3 bg-white border border-slate-300 rounded-lg text-lg font-bold text-emerald-600 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="0">
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2 italic">
                        *Otomatis: {{ selectedTrip?.passengerCount > 6 ? '> 6 (Full)' : '1 - 6 (Reguler)' }}. Ubah manual untuk Carter/Dropping.
                    </p>
                </div>

                <!-- Payment Method -->
                <div class="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-4">
                     <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Metode Pembayaran</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="paymentMethod" value="cash" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Cash / Tunai</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="paymentMethod" value="transfer" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Transfer</span>
                            </label>
                        </div>
                     </div>
                     
                     <div v-show="paymentMethod === 'transfer'">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Bukti Transfer (Wajib)</label>
                        <input type="file" ref="fileInputRef" @change="onFileSelected" class="block w-full text-sm text-slate-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-xs file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100 dark:file:bg-slate-700 dark:file:text-slate-200
                        "/>
                         <p v-if="paymentProof" class="mt-2 text-xs text-green-600 dark:text-green-400">
                            <i class="bi bi-check-circle-fill"></i> File dipilih: {{ paymentProof.name }}
                        </p>
                     </div>
                     
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Catatan (Opsional)</label>
                         <textarea v-model="note" rows="2" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-800 dark:border-slate-700 dark:placeholder-gray-400 dark:text-white" placeholder="No Rekening / Keterangan tambahan..."></textarea>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button @click="cancelModal" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button @click="selectedBatch ? confirmBatchPayroll() : confirmPayroll()" :disabled="isProcessing" class="px-4 py-2 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
                        <i v-if="isProcessing" class="bi bi-arrow-repeat animate-spin"></i>
                        {{ isProcessing ? 'Memproses...' : 'Konfirmasi Pembayaran' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import Swal from 'sweetalert2';

const trips = ref([]);
const users = ref([]); // For filter if needed
const isLoading = ref(false);
const activeTab = ref('daily');
const statusFilter = ref('all'); // all, pending, paid
const selectedBatch = ref(null); // For batch payment

// Helper to get initials
const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const filter = ref({
    startDate: '',
    endDate: ''
});
const activeFilter = ref('month');
const searchQuery = ref('');

// Refs for modal
const showModal = ref(false);
const selectedTrip = ref(null);
const payrollInput = ref(0);
const paymentMethod = ref('cash');
const note = ref('');
const paymentProof = ref(null);
const fileInputRef = ref(null);
const isProcessing = ref(false);

const setDateFilter = (type) => {
    activeFilter.value = type;
    const today = new Date();
    const start = new Date(today);
    
    if (type === 'today') {
        // start is today
    } else if (type === 'week') {
        const day = today.getDay() || 7; // Get current day number, ensure Sunday is 7
        if (day !== 1) {
             start.setHours(-24 * (day - 1));
        }
    } else if (type === 'month') {
        start.setDate(1);
    }
    
    filter.value.startDate = start.toISOString().split('T')[0];
    filter.value.endDate = today.toISOString().split('T')[0];
    loadTrips();
};

const formatDate = (dateStr) => {
   if (!dateStr) return '-';
   return new Date(dateStr).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
};

const formatTime = (timeStr) => {
    if(!timeStr) return '-';
    return timeStr.substring(0, 5); 
};

const loadTrips = async () => {
    isLoading.value = true;
    try {
        const res = await fetch(`api.php?action=get_trip_history&startDate=${filter.value.startDate}&endDate=${filter.value.endDate}`);
        const data = await res.json();
        if(data.status === 'success') {
            trips.value = data.data;
        } else {
            trips.value = [];
        }
    } catch (e) {
        console.error("Error loading trips", e);
    } finally {
        isLoading.value = false;
    }
};

const filteredTrips = computed(() => {
    let t = trips.value;
    const q = searchQuery.value.toLowerCase();
    
    if (q) {
        t = t.filter(trip => {
            const driver = trip.driver?.name?.toLowerCase() || '';
            const fleet = trip.fleet?.name?.toLowerCase() || '';
            const plate = trip.fleet?.plateNumber?.toLowerCase() || '';
            const origin = trip.routeConfig?.origin?.toLowerCase() || '';
            const dest = trip.routeConfig?.destination?.toLowerCase() || '';
            
            return driver.includes(q) || fleet.includes(q) || plate.includes(q) || 
                   origin.includes(q) || dest.includes(q);
        });
    }
    
    // Status Filter
    if (statusFilter.value !== 'all') {
        t = t.filter(trip => {
            if (statusFilter.value === 'pending') return trip.status !== 'Selesai';
            if (statusFilter.value === 'paid') return trip.status === 'Selesai';
            return true;
        });
    }
    
    return t;
});

const groupedTrips = computed(() => {
    // Show ALL trips for history, but separate them visually/logic
    // const pendingTrips = trips.value.filter(t => t.status !== 'Selesai');
    // User wants to see paid trips too in sub-table
    
    const allTrips = trips.value;
    
    // Filter by search query if exists
    const q = searchQuery.value.toLowerCase();
    
    const groups = {};
    const currentMonthKey = new Date().toISOString().substring(0, 7); // YYYY-MM

    allTrips.forEach(trip => {
        const driverName = trip.driver?.name || 'Unknown Driver';
        
        // Skip if search query matches driver name
        if (q && !driverName.toLowerCase().includes(q)) {
            return;
        }

        if (!groups[driverName]) {
            groups[driverName] = {
                driverName,
                trips: [], 
                pendingTrips: [],
                paidTrips: [],
                paidGroups: [], // Grouped by Month
                totalAmount: 0,
                pendingCount: 0,
                pendingAmount: 0,
                showPaid: false 
            };
        }
        groups[driverName].trips.push(trip);
        
        const amount = parseFloat(trip.payrollCalculated || 0);
        groups[driverName].totalAmount += amount;
        
        if (trip.status !== 'Selesai') {
            groups[driverName].pendingCount++;
            groups[driverName].pendingAmount += amount;
            groups[driverName].pendingTrips.push(trip);
        } else {
            groups[driverName].paidTrips.push(trip);
        }
    });
    
    // Process Paid Groups
    Object.values(groups).forEach(group => {
        const monthMap = {};
        
        group.paidTrips.forEach(trip => {
            const date = new Date(trip.date);
            const key = trip.date.substring(0, 7); // YYYY-MM
            const label = date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
            
            if (!monthMap[key]) {
                monthMap[key] = {
                    key,
                    label,
                    trips: [],
                    total: 0,
                    yearMonth: key,
                    uniqueKey: `${group.driverName}-${key}` // For global expansion tracking
                };
            }
            monthMap[key].trips.push(trip);
            monthMap[key].total += parseFloat(trip.payrollCalculated || 0);
        });
        
        // Convert map to array and sort desc
        group.paidGroups = Object.values(monthMap).sort((a, b) => b.key.localeCompare(a.key));
    });

    return Object.values(groups);
});

// --- Expansion State (Reactivity Fix) ---
const expandedDrivers = ref({});
const expandedMonths = ref({});

const isDriverExpanded = (driverName) => expandedDrivers.value[driverName] || false;
const toggleDriver = (driverName) => {
    expandedDrivers.value[driverName] = !expandedDrivers.value[driverName];
};

const isMonthExpanded = (uniqueKey) => expandedMonths.value[uniqueKey] || false;
const toggleMonth = (uniqueKey) => {
    expandedMonths.value[uniqueKey] = !expandedMonths.value[uniqueKey];
};

// Initialize default open for current month when trips change
watch(trips, () => {
    const currentMonthKey = new Date().toISOString().substring(0, 7);
    
    trips.value.forEach(trip => {
         if (trip.status === 'Selesai') {
             const key = trip.date.substring(0, 7);
             const driverName = trip.driver?.name || 'Unknown Driver';
             
             if (key === currentMonthKey) {
                 const uniqueKey = `${driverName}-${key}`;
                 if (!expandedMonths.value[uniqueKey]) {
                     expandedMonths.value[uniqueKey] = true;
                 }
             }
         }
    });
}, { deep: true, immediate: true });


const totalPassengers = computed(() => trips.value.reduce((acc, trip) => acc + parseInt(trip.passengerCount || 0), 0));
const totalPayroll = computed(() => trips.value.reduce((acc, trip) => acc + parseFloat(trip.payrollCalculated || 0), 0));

const formatCurrency = (val) => {
    if (!val) return '0';
    return parseInt(val).toLocaleString('id-ID');
};

const openPayrollModal = (trip) => {
    selectedTrip.value = trip;
    selectedBatch.value = null; // Clear batch
    payrollInput.value = parseInt(trip.payrollCalculated); // Default calc
    paymentMethod.value = 'cash';
    note.value = '';
    paymentProof.value = null;
    showModal.value = true;
};

const openBatchModal = (group) => {
    selectedBatch.value = group;
    selectedTrip.value = null; // Clear single
    payrollInput.value = group.totalAmount; 
    paymentMethod.value = 'cash';
    note.value = '';
    paymentProof.value = null;
    showModal.value = true;
};

// Handle file selection
const onFileSelected = (event) => {
    const files = event.target.files;
    if (files.length > 0) {
        paymentProof.value = files[0];
        console.log('File Selected:', paymentProof.value);
    } else {
        paymentProof.value = null;
    }
};

const confirmPayroll = async () => {
    if (paymentMethod.value === 'transfer') {
        // Fallback check if @change didn't fire
         if (!paymentProof.value && fileInputRef.value && fileInputRef.value.files.length > 0) {
            paymentProof.value = fileInputRef.value.files[0];
        }
        
        if (!paymentProof.value) {
            Swal.fire('Error', 'Harap upload bukti transfer', 'warning');
            return;
        }
    }
    
    isProcessing.value = true;
    try {
        const formData = new FormData();
        formData.append('action', 'finish_trip_payroll');
        formData.append('id', selectedTrip.value.id);
        formData.append('amount', payrollInput.value);
        formData.append('method', paymentMethod.value);
        formData.append('notes', note.value);
        
        if (paymentMethod.value === 'transfer' && paymentProof.value) {
            formData.append('proof', paymentProof.value);
        }
        
        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            Swal.fire('Berhasil!', 'Gaji supir telah dicatat dan trip diselesaikan.', 'success');
            
            // Optimistic UI Update: Find and update trip status locally immediately
            if (activeTab.value === 'trips') {
                const tripIndex = trips.value.findIndex(t => t.id === selectedTrip.value.id);
                if (tripIndex !== -1) {
                    trips.value[tripIndex].status = 'Selesai'; 
                    // This should automatically move it from Pending to Finished filter if computed properly
                }
            } else {
                // Determine driver group and remove from pending
                // Since this view is complex, reloading might be safest, but let's try to update the specific trip
                const trip = trips.value.find(t => t.id === selectedTrip.value.id);
                if (trip) trip.status = 'Selesai';
            }

            cancelModal();
            cancelModal();
            loadTrips(); // Re-enable to ensure data persistence check   
        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan sistem', 'error');
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'Gagal menghubungi server', 'error');
    } finally {
        isProcessing.value = false;
    }
};

const setMonthFilter = (offset) => {
    const d = new Date();
    d.setMonth(d.getMonth() + offset);
    
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    
    // Set global filter inputs (if they are bound to inputs, we might need to update them too)
    // Assuming filter.value is the source of truth for loadTrips
    
    const lastDay = new Date(year, d.getMonth() + 1, 0).getDate();
    
    filter.value.startDate = `${year}-${month}-01`;
    filter.value.endDate = `${year}-${month}-${lastDay}`;
    filter.value.type = 'custom'; // Switch to custom or month? 'month' type usually implies parsing specific inputs. 
    // Let's stick to 'custom' to be safe or if 'month' logic exists use that.
    // Looking at setDateFilter('month'), it sets start/end to current month.
    
    // Optimistic UI update or just reload
    loadTrips();
    
    // Optional: Scroll to top or show loading
};

const confirmBatchPayroll = async () => {
    if (isProcessing.value) return;
    
    console.log('Confirming Batch:', selectedBatch.value);

    if (!selectedBatch.value || !selectedBatch.value.trips || selectedBatch.value.trips.length === 0) {
        Swal.fire('Error', 'Tidak ada trip yang dipilih (No trips selected)', 'error');
        return;
    }

    if (paymentMethod.value === 'transfer') {
         // Fallback check
         if (!paymentProof.value && fileInputRef.value && fileInputRef.value.files.length > 0) {
            paymentProof.value = fileInputRef.value.files[0];
        }
        
        if (!paymentProof.value) {
            Swal.fire('Error', 'Harap upload bukti transfer', 'warning');
            return;
        }
    }
    
    isProcessing.value = true;
    try {
        const tripIds = selectedBatch.value.trips.map(t => t.id);
        
        const formData = new FormData();
        formData.append('action', 'batch_finish_payroll');
        formData.append('trips', JSON.stringify(tripIds));
        formData.append('method', paymentMethod.value);
        formData.append('notes', note.value);
        
        if (paymentMethod.value === 'transfer' && paymentProof.value) {
            formData.append('proof', paymentProof.value);
        }
        
        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            // Auto Print Receipt for these trips
            const driverName = selectedBatch.value.driverName;
            const paidTrips = [...selectedBatch.value.trips]; // Copy before clearing

            Swal.fire('Berhasil!', `Gaji untuk ${driverName} telah dilunasi.`, 'success');
            
            // Optimistic UI Update for Batch
            const tripIds = selectedBatch.value.trips.map(t => t.id);
            trips.value.forEach(t => {
                if (tripIds.includes(t.id)) {
                    t.status = 'Selesai';
                }
            });
            
            cancelModal();
            cancelModal();
            loadTrips(); 

            // Trigger Print
            setTimeout(() => {
                printRecapBatchStruk(driverName, paidTrips);
            }, 500);

        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan sistem', 'error');
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'Gagal menghubungi server', 'error');
    } finally {
         isProcessing.value = false;
    }
};

const printRecapBatchStruk = (driverName, tripsToPrint) => {
    if (!tripsToPrint || tripsToPrint.length === 0) return;
    
    // Sort oldest to newest
    const sortedTrips = tripsToPrint.sort((a,b) => new Date(a.date + ' ' + a.time) - new Date(b.date + ' ' + b.time));
    
    const total = sortedTrips.reduce((acc, t) => acc + parseFloat(t.payrollCalculated || 0), 0);
    const period = `${formatDate(sortedTrips[0]?.date)} - ${formatDate(sortedTrips[sortedTrips.length-1]?.date)}`;
    
    const printWindow = window.open('', '_blank', 'width=500,height=700');
    
    let itemsHtml = '';
    sortedTrips.forEach(t => {
        itemsHtml += `
            <div class="row" style="border-bottom: 0.5px solid #eee; padding-bottom: 2px; margin-bottom: 5px;">
                <div style="text-align:left;">
                    <span>${formatDate(t.date)}</span><br>
                    <span style="font-size:10px; color:#555;">${t.routeConfig?.origin}-${t.routeConfig?.destination}</span>
                </div>
                <div style="text-align:right;">
                     <span>Rp ${formatCurrency(t.payrollCalculated)}</span>
                </div>
            </div>
        `;
    });
    
    const html = `
        <html>
        <head>
            <title>Rekap Gaji - ${driverName}</title>
            <style>
                body { font-family: monospace; padding: 20px; text-align: center; }
                .header { font-weight: bold; margin-bottom: 20px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
                .row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; }
                .total { margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px; font-weight: bold; font-size: 14px; }
                .footer { margin-top: 30px; font-size: 10px; color: #555; }
            </style>
        </head>
        <body>
            <div class="header">
                 <img src="/ops/logo.png" style="width: 80px; height: auto; margin-bottom: 10px;" onerror="this.style.display='none'"><br>
                PT. FAJAR WISATA LANGGENG<br>
                SLIP GAJI SUPIR (KOLEKTIF)
            </div>
            
            <div class="row"><span>Nama:</span> <span>${driverName}</span></div>
            <div class="row"><span>Periode:</span> <span>${period}</span></div>
            <div class="row"><span>Jml Trip:</span> <span>${sortedTrips.length} Trip</span></div>
            <br>
            
            <div style="border-top: 1px solid #000; padding-top: 5px;">
                ${itemsHtml}
            </div>
            
            <div class="total row">
                <span>TOTAL DIBAYARKAN:</span>
                <span>Rp ${formatCurrency(total)}</span>
            </div>
            
            <div class="footer">
                Dicetak pada ${new Date().toLocaleDateString('id-ID')} ${new Date().toLocaleTimeString('id-ID')}<br><br>
                ( ..................... )
            </div>
            
            <script>
                window.print();
            <\/script>
        </body>
        </html>
    `;
    
    printWindow.document.write(html);
    printWindow.document.close();
};


const cancelModal = () => {
    showModal.value = false;
    selectedTrip.value = null;
    selectedBatch.value = null;
    paymentProof.value = null;
};

const printStruk = (trip) => {
    // Simple Print Logic
    // Create a hidden iframe or new window to print receipt
    const printWindow = window.open('', '_blank', 'width=400,height=600');
    
    // Receipt Content
    const html = `
        <html>
        <head>
            <title>Struk Gaji - ${trip.driver?.name}</title>
            <style>
                body { font-family: monospace; padding: 20px; text-align: center; }
                .header { font-weight: bold; margin-bottom: 20px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
                .row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; }
                .total { margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px; font-weight: bold; font-size: 14px; }
                .footer { margin-top: 30px; font-size: 10px; color: #555; }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="/ops/logo.png" style="width: 80px; height: auto; margin-bottom: 10px;" onerror="this.style.display='none'"><br>
                PT. FAJAR WISATA LANGGENG<br>
                SLIP GAJI SUPIR
            </div>
            
            <div class="row"><span>Tanggal:</span> <span>${trip.date}</span></div>
            <div class="row"><span>Jam:</span> <span>${trip.time}</span></div>
            <div class="row"><span>Supir:</span> <span>${trip.driver?.name}</span></div>
            <div class="row"><span>Armada:</span> <span>${trip.fleet?.name} (${trip.fleet?.plateNumber})</span></div>
            <br>
            <div class="row"><span>Rute:</span> <span>${trip.routeConfig?.origin} - ${trip.routeConfig?.destination}</span></div>
            <div class="row"><span>Penump:</span> <span>${trip.passengerCount} Org</span></div>
            
            <div class="total row">
                <span>TOTAL GAJI:</span>
                <span>Rp ${formatCurrency(trip.payrollCalculated)}</span>
            </div>
            
            <div class="footer">
                Tanda Terima<br><br><br>
                ( ..................... )
            </div>
            
            <script>
                window.print();
            <\/script>
        </body>
        </html>
    `;
    
    printWindow.document.write(html);
    printWindow.document.close();
};

const selectedTrips = ref([]); // For Batch Struk (Daily View)
const recapSelectedIds = ref(new Set()); // For Recap View (Driver Mode)

const isAllSelected = computed(() => {
    return filteredTrips.value.length > 0 && selectedTrips.value.length === filteredTrips.value.length;
});

const toggleAllSelection = () => {
    if (isAllSelected.value) {
        selectedTrips.value = [];
    } else {
        selectedTrips.value = [...filteredTrips.value];
    }
};

// --- Recap View Selection Logic ---
// --- Recap View Selection Logic ---
const isTripSelected = (tripId) => recapSelectedIds.value.has(tripId);

const toggleTripSelection = (tripId) => {
    // Prevent selecting paid trips
    const trip = trips.value.find(t => t.id === tripId);
    if (trip && trip.status === 'Selesai') return;

    if (recapSelectedIds.value.has(tripId)) {
        recapSelectedIds.value.delete(tripId);
    } else {
        recapSelectedIds.value.add(tripId);
    }
    // Trigger reactivity for Set
    recapSelectedIds.value = new Set(recapSelectedIds.value);
};

const isGroupAllSelected = (group) => {
    // Only check PENDING trips
    const pending = group.trips.filter(t => t.status !== 'Selesai');
    if (pending.length === 0) return false;
    return pending.every(t => recapSelectedIds.value.has(t.id));
};

const toggleGroupSelection = (group) => {
    const allSelected = isGroupAllSelected(group);
    
    group.trips.forEach(t => {
        if (t.status === 'Selesai') return; // Skip paid

        if (allSelected) {
            recapSelectedIds.value.delete(t.id);
        } else {
            recapSelectedIds.value.add(t.id);
        }
    });
    recapSelectedIds.value = new Set(recapSelectedIds.value);
};

const getSelectedCount = (group) => {
    return group.trips.filter(t => recapSelectedIds.value.has(t.id)).length;
};

const calculateSelectedTotal = (group) => {
    return group.trips
        .filter(t => recapSelectedIds.value.has(t.id))
        .reduce((sum, t) => sum + parseFloat(t.payrollCalculated || 0), 0);
};

const handleGroupPayment = (group) => {
    const count = getSelectedCount(group);
    let tripsToPay = [];
    
    if (count > 0) {
        // Pay Selected
         tripsToPay = group.trips.filter(t => recapSelectedIds.value.has(t.id));
    } else {
        // Pay All Pending
         tripsToPay = group.trips.filter(t => t.status !== 'Selesai');
    }

    if (tripsToPay.length === 0) {
         Swal.fire('Info', 'Tidak ada trip pending untuk dibayar.', 'info');
         return;
    }

    const totalAmount = tripsToPay.reduce((sum, t) => sum + parseFloat(t.payrollCalculated || 0), 0);

    openBatchModal({
        driverName: group.driverName,
        trips: tripsToPay,
        totalAmount: totalAmount
    });
};

const printBatchStruk = () => {
    if (selectedTrips.value.length === 0) return;
    
    // Sort logic if needed? Assume already list order.
    // Calculate total
    const total = selectedTrips.value.reduce((acc, t) => acc + (t.payrollCalculated || 0), 0);
    const driverName = selectedTrips.value[0]?.driver?.name || 'Multiple Drivers';
    const period = `${formatDate(selectedTrips.value[0]?.date)} - ${formatDate(selectedTrips.value[selectedTrips.value.length-1]?.date)}`;
    
    const printWindow = window.open('', '_blank', 'width=500,height=700');
    
    let itemsHtml = '';
    selectedTrips.value.forEach(t => {
        itemsHtml += `
            <div class="row" style="border-bottom: 0.5px solid #eee; padding-bottom: 2px; margin-bottom: 5px;">
                <div style="text-align:left;">
                    <span>${formatDate(t.date)}</span><br>
                    <span style="font-size:10px; color:#555;">${t.routeConfig?.origin}-${t.routeConfig?.destination}</span>
                </div>
                <div style="text-align:right;">
                     <span>Rp ${formatCurrency(t.payrollCalculated)}</span>
                </div>
            </div>
        `;
    });
    
    const html = `
        <html>
        <head>
            <title>Rekap Gaji - ${driverName}</title>
            <style>
                body { font-family: monospace; padding: 20px; text-align: center; }
                .header { font-weight: bold; margin-bottom: 20px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
                .row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; }
                .total { margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px; font-weight: bold; font-size: 14px; }
                .footer { margin-top: 30px; font-size: 10px; color: #555; }
            </style>
        </head>
        <body>
            <div class="header">
                 <img src="/ops/logo.png" style="width: 80px; height: auto; margin-bottom: 10px;" onerror="this.style.display='none'"><br>
                PT. FAJAR WISATA LANGGENG<br>
                REKAP GAJI SUPIR
            </div>
            
            <div class="row"><span>Nama:</span> <span>${driverName}</span></div>
            <div class="row"><span>Jml Trip:</span> <span>${selectedTrips.value.length} Trip</span></div>
            <br>
            
            <div style="border-top: 1px solid #000; padding-top: 5px;">
                ${itemsHtml}
            </div>
            
            <div class="total row">
                <span>TOTAL DIBAYARKAN:</span>
                <span>Rp ${formatCurrency(total)}</span>
            </div>
            
            <div class="footer">
                Dicetak pada ${new Date().toLocaleDateString('id-ID')} ${new Date().toLocaleTimeString('id-ID')}<br><br>
                ( ..................... )
            </div>
            
            <script>
                window.print();
            <\/script>
        </body>
        </html>
    `;
    
    printWindow.document.write(html);
    printWindow.document.close();
};

const viewProof = (imageUrl) => {
    Swal.fire({
        title: 'Bukti Transfer',
        imageUrl: imageUrl, // Path relative to ops/ e.g. 'uploads/payroll_proofs/x.jpg'
        imageAlt: 'Bukti Transfer',
        showCloseButton: true,
        showConfirmButton: false,
        width: 600
    });
};

onMounted(() => {
    setDateFilter('month');
});
</script>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-bounce-in {
    animation: bounceIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
@keyframes bounceIn {
    0% { transform: scale(0); opacity: 0; }
    80% { transform: scale(1.1); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
