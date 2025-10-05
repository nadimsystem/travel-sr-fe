const { createApp } = Vue;

createApp({
  data() {
    return {
      view: "dashboard",
      isFullscreen: false,
      armadaSearchTerm: "",
      driverSearchTerm: "",
      driverSearchTermInModal: "",
      historySearchTerm: "",
      now: new Date(),
      currentTime: this.formatTime(new Date()),
      currentDate: this.getCurrentDate(),
      clockInterval: null,
      isTripWizardVisible: false,
      isTripModalVisible: false,
      isVehicleModalVisible: false,
      isDriverModalVisible: false,
      wizard: {
        trip: null,
        step: 1,
        date: { type: "today", raw: "" },
        time: "08:00",
        endDate: { raw: "" },
        endTime: "17:00",
      },
      modal: { trip: null },
      vehicleModal: { mode: "add", data: null },
      driverModal: { mode: "add", data: null },
      showDriverDropdown: false,
      pricesSavedMessage: "",
      commonTimes: [
        "07:00",
        "08:00",
        "09:00",
        "10:00",
        "13:00",
        "14:00",
        "15:00",
        "16:00",
        "19:00",
        "20:00",
        "21:00",
        "22:00",
      ],
      fleet: [
        {
          id: 1,
          name: "Hiace Premio SR-01",
          type: "Hiace Premio",
          plate: "BA 1001 HP",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1600000,
          hargaPerOrang: 150000,
          biayaOperasional: 600000,
          requiredLicense: "A Umum",
          lastService: "2025-08-01",
          nextService: "2026-02-01",
        },
        {
          id: 2,
          name: "Hiace Premio SR-02",
          type: "Hiace Premio",
          plate: "BA 1002 HP",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1600000,
          hargaPerOrang: 150000,
          biayaOperasional: 600000,
          requiredLicense: "A Umum",
          lastService: "2025-08-01",
          nextService: "2026-02-01",
        },
        {
          id: 3,
          name: "Hiace Commuter SR-03",
          type: "Hiace Commuter",
          plate: "BA 1003 HC",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1500000,
          hargaPerOrang: 130000,
          biayaOperasional: 550000,
          requiredLicense: "A Umum",
          lastService: "2025-09-15",
          nextService: "2026-03-15",
        },
        {
          id: 4,
          name: "Hiace Premio SR-04",
          type: "Hiace Premio",
          plate: "BA 1004 HP",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1600000,
          hargaPerOrang: 150000,
          biayaOperasional: 600000,
          requiredLicense: "A Umum",
          lastService: "2025-08-01",
          nextService: "2025-10-25",
        },
        {
          id: 5,
          name: "Hiace Commuter SR-05",
          type: "Hiace Commuter",
          plate: "BA 1005 HC",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1500000,
          hargaPerOrang: 130000,
          biayaOperasional: 550000,
          requiredLicense: "A Umum",
          lastService: "2025-09-15",
          nextService: "2026-03-15",
        },
        {
          id: 6,
          name: "Hiace Premio SR-06",
          type: "Hiace Premio",
          plate: "BA 1006 HP",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1600000,
          hargaPerOrang: 150000,
          biayaOperasional: 600000,
          requiredLicense: "A Umum",
          lastService: "2025-08-01",
          nextService: "2026-02-01",
        },
        {
          id: 7,
          name: "Hiace Premio SR-07",
          type: "Hiace Premio",
          plate: "BA 1007 HP",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1600000,
          hargaPerOrang: 150000,
          biayaOperasional: 600000,
          requiredLicense: "A Umum",
          lastService: "2025-08-01",
          nextService: "2026-02-01",
        },
        {
          id: 8,
          name: "Hiace Commuter SR-08",
          type: "Hiace Commuter",
          plate: "BA 1008 HC",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1500000,
          hargaPerOrang: 130000,
          biayaOperasional: 550000,
          requiredLicense: "A Umum",
          lastService: "2025-09-15",
          nextService: "2026-03-15",
        },
        {
          id: 9,
          name: "Hiace Premio SR-09",
          type: "Hiace Premio",
          plate: "BA 1009 HP",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1600000,
          hargaPerOrang: 150000,
          biayaOperasional: 600000,
          requiredLicense: "A Umum",
          lastService: "2025-08-01",
          nextService: "2026-02-01",
        },
        {
          id: 10,
          name: "Hiace Premio SR-10",
          type: "Hiace Premio",
          plate: "BA 1010 HP",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1600000,
          hargaPerOrang: 150000,
          biayaOperasional: 600000,
          requiredLicense: "A Umum",
          lastService: "2025-08-01",
          nextService: "2026-02-01",
        },
        {
          id: 11,
          name: "Hiace Commuter SR-11",
          type: "Hiace Commuter",
          plate: "BA 1011 HC",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1500000,
          hargaPerOrang: 130000,
          biayaOperasional: 550000,
          requiredLicense: "A Umum",
          lastService: "2025-09-15",
          nextService: "2026-03-15",
        },
        {
          id: 12,
          name: "Hiace Premio SR-12",
          type: "Hiace Premio",
          plate: "BA 1012 HP",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1600000,
          hargaPerOrang: 150000,
          biayaOperasional: 600000,
          requiredLicense: "A Umum",
          lastService: "2025-08-01",
          nextService: "2026-02-01",
        },
        {
          id: 13,
          name: "Hiace Premio SR-13",
          type: "Hiace Premio",
          plate: "BA 1013 HP",
          capacity: 7,
          status: "Perbaikan",
          icon: "bi-truck-front-fill",
          hargaSewa: 1600000,
          hargaPerOrang: 150000,
          biayaOperasional: 600000,
          requiredLicense: "A Umum",
          lastService: "2025-08-01",
          nextService: "2026-02-01",
        },
        {
          id: 14,
          name: "Medium Bus SR-21",
          type: "Medium Bus",
          plate: "BA 7021 MB",
          capacity: 33,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 2800000,
          biayaOperasional: 1100000,
          requiredLicense: "B1 Umum",
          lastService: "2025-07-20",
          nextService: "2025-11-20",
        },
        {
          id: 15,
          name: "Medium Bus SR-22",
          type: "Medium Bus",
          plate: "BA 7022 MB",
          capacity: 33,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 2800000,
          biayaOperasional: 1100000,
          requiredLicense: "B1 Umum",
          lastService: "2025-07-20",
          nextService: "2025-11-20",
        },
        {
          id: 16,
          name: "Medium Bus SR-23",
          type: "Medium Bus",
          plate: "BA 7023 MB",
          capacity: 33,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 2800000,
          biayaOperasional: 1100000,
          requiredLicense: "B1 Umum",
          lastService: "2025-07-20",
          nextService: "2025-11-20",
        },
        {
          id: 17,
          name: "Medium Bus SR-24",
          type: "Medium Bus",
          plate: "BA 7024 MB",
          capacity: 33,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 2800000,
          biayaOperasional: 1100000,
          requiredLicense: "B1 Umum",
          lastService: "2025-07-20",
          nextService: "2025-11-20",
        },
        {
          id: 18,
          name: "Medium Bus SR-25",
          type: "Medium Bus",
          plate: "BA 7025 MB",
          capacity: 33,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 2800000,
          biayaOperasional: 1100000,
          requiredLicense: "B1 Umum",
          lastService: "2025-07-20",
          nextService: "2025-11-20",
        },
        {
          id: 19,
          name: "Medium Bus SR-26",
          type: "Medium Bus",
          plate: "BA 7026 MB",
          capacity: 33,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 2800000,
          biayaOperasional: 1100000,
          requiredLicense: "B1 Umum",
          lastService: "2025-07-20",
          nextService: "2025-11-20",
        },
        {
          id: 20,
          name: "Medium Bus SR-27",
          type: "Medium Bus",
          plate: "BA 7027 MB",
          capacity: 33,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 2800000,
          biayaOperasional: 1100000,
          requiredLicense: "B1 Umum",
          lastService: "2025-07-20",
          nextService: "2025-11-20",
        },
        {
          id: 21,
          name: "Medium Bus SR-28",
          type: "Medium Bus",
          plate: "BA 7028 MB",
          capacity: 33,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 2800000,
          biayaOperasional: 1100000,
          requiredLicense: "B1 Umum",
          lastService: "2025-07-20",
          nextService: "2025-11-20",
        },
        {
          id: 22,
          name: "Medium Bus SR-29",
          type: "Medium Bus",
          plate: "BA 7029 MB",
          capacity: 33,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 2800000,
          biayaOperasional: 1100000,
          requiredLicense: "B1 Umum",
          lastService: "2025-07-20",
          nextService: "2025-11-20",
        },
        {
          id: 23,
          name: "Medium Bus SR-30",
          type: "Medium Bus",
          plate: "BA 7030 MB",
          capacity: 33,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 2800000,
          biayaOperasional: 1100000,
          requiredLicense: "B1 Umum",
          lastService: "2025-07-20",
          nextService: "2025-11-20",
        },
        {
          id: 24,
          name: "Medium Bus SR-31",
          type: "Medium Bus",
          plate: "BA 7031 MB",
          capacity: 33,
          status: "Perbaikan",
          icon: "bi-bus-front-fill",
          hargaSewa: 2800000,
          biayaOperasional: 1100000,
          requiredLicense: "B1 Umum",
          lastService: "2025-07-20",
          nextService: "2025-11-20",
        },
        {
          id: 25,
          name: "Big Bus SR-41",
          type: "Big Bus",
          plate: "BA 7041 BB",
          capacity: 45,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 4000000,
          biayaOperasional: 1500000,
          requiredLicense: "B2 Umum",
          lastService: "2025-09-01",
          nextService: "2025-12-01",
        },
        {
          id: 26,
          name: "Big Bus SR-42",
          type: "Big Bus",
          plate: "BA 7042 BB",
          capacity: 45,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 4000000,
          biayaOperasional: 1500000,
          requiredLicense: "B2 Umum",
          lastService: "2025-09-01",
          nextService: "2025-12-01",
        },
        {
          id: 27,
          name: "Big Bus SR-43",
          type: "Big Bus",
          plate: "BA 7043 BB",
          capacity: 45,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 4000000,
          biayaOperasional: 1500000,
          requiredLicense: "B2 Umum",
          lastService: "2025-09-01",
          nextService: "2025-12-01",
        },
        {
          id: 28,
          name: "Big Bus SR-44",
          type: "Big Bus",
          plate: "BA 7044 BB",
          capacity: 45,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 4000000,
          biayaOperasional: 1500000,
          requiredLicense: "B2 Umum",
          lastService: "2025-09-01",
          nextService: "2025-12-01",
        },
        {
          id: 29,
          name: "Big Bus SR-45",
          type: "Big Bus",
          plate: "BA 7045 BB",
          capacity: 45,
          status: "Tersedia",
          icon: "bi-bus-front-fill",
          hargaSewa: 4000000,
          biayaOperasional: 1500000,
          requiredLicense: "B2 Umum",
          lastService: "2025-09-01",
          nextService: "2025-12-01",
        },
      ],
      drivers: [
        {
          id: 101,
          name: "Budi Santoso",
          licenseType: "A Umum",
          phone: "081234567890",
          status: "Dalam Perjalanan",
        },
        {
          id: 102,
          name: "Joko Susilo",
          licenseType: "B1 Umum",
          phone: "081234567891",
          status: "Dalam Perjalanan",
        },
        {
          id: 103,
          name: "Anton Wijaya",
          licenseType: "A Umum",
          phone: "081234567892",
          status: "Standby",
        },
        {
          id: 104,
          name: "Eko Prasetyo",
          licenseType: "B2 Umum",
          phone: "081234567893",
          status: "Dalam Perjalanan",
        },
        {
          id: 105,
          name: "Slamet Riyadi",
          licenseType: "B2 Umum",
          phone: "081234567894",
          status: "Libur",
        },
        {
          id: 106,
          name: "Doni Firmansyah",
          licenseType: "A Umum",
          phone: "081234567895",
          status: "Standby",
        },
        {
          id: 107,
          name: "Agus Setiawan",
          licenseType: "B1 Umum",
          phone: "081234567896",
          status: "Tiba",
        },
        {
          id: 108,
          name: "Rahmat Hidayat",
          licenseType: "A Umum",
          phone: "081234567897",
          status: "Standby",
        },
        {
          id: 109,
          name: "Zainal Abidin",
          licenseType: "B1 Umum",
          phone: "081234567898",
          status: "Standby",
        },
        {
          id: 110,
          name: "Fajar Nugraha",
          licenseType: "B2 Umum",
          phone: "081234567899",
          status: "Dalam Perjalanan",
        },
        {
          id: 111,
          name: "Hendri Saputra",
          licenseType: "A Umum",
          phone: "081234567880",
          status: "Standby",
        },
        {
          id: 112,
          name: "Dedi Kurniawan",
          licenseType: "B1 Umum",
          phone: "081234567881",
          status: "Libur",
        },
      ],
      trips: [
        {
          id: 1,
          fleetId: 1,
          driverId: 101,
          type: "Rute",
          origin: "Padang",
          destination: "Bukittinggi",
          departureTime: this.getTodayISO(8, 0),
          arrivalTime: this.getTodayISO(10, 30),
          pax: "7/7",
          status: "Berangkat",
        },
        {
          id: 2,
          fleetId: 14,
          driverId: 102,
          type: "Rental",
          notes: "Dinas Pariwisata - Tour de Singkarak",
          departureTime: this.getTodayISO(7, 30),
          arrivalTime: this.getTodayISO(18, 0),
          pax: "Carter",
          status: "Rental",
        },
        {
          id: 3,
          fleetId: 3,
          driverId: 103,
          type: "Rute",
          origin: "Padang",
          destination: "Payakumbuh",
          departureTime: this.getTodayISO(10, 0),
          arrivalTime: this.getTodayISO(13, 0),
          pax: "5/7",
          status: "Boarding",
        },
        {
          id: 4,
          fleetId: 25,
          driverId: 104,
          type: "Rental",
          notes: "Rombongan Kemenkes RI (3 Hari)",
          departureTime: this.getTodayISO(9, 0),
          arrivalTime: this.getFutureISO(2, 17, 0),
          pax: "Carter",
          status: "Rental",
        },
        {
          id: 5,
          fleetId: 26,
          driverId: 110,
          type: "Rute",
          origin: "Bukittinggi",
          destination: "Padang",
          departureTime: this.getTodayISO(14, 0),
          arrivalTime: this.getTodayISO(16, 30),
          pax: "40/45",
          status: "Berangkat",
        },
        {
          id: 6,
          fleetId: 4,
          driverId: 106,
          type: "Rute",
          origin: "Padang",
          destination: "Solok",
          departureTime: this.getTodayISO(16, 0),
          arrivalTime: this.getTodayISO(18, 0),
          pax: "Full",
          status: "Standby",
        },
        {
          id: 7,
          fleetId: 2,
          driverId: 108,
          type: "Rute",
          origin: "Painan",
          destination: "Padang",
          departureTime: this.getYesterdayISO(18, 0),
          arrivalTime: this.getTodayISO(8, 30),
          pax: "6/7",
          status: "Tiba",
        },
        {
          id: 8,
          fleetId: 15,
          driverId: 107,
          type: "Rental",
          notes: "Trip Keluarga Bpk. Ahmad",
          departureTime: this.getYesterdayISO(9, 0),
          arrivalTime: this.getYesterdayISO(20, 0),
          pax: "Carter",
          status: "Tiba",
        },
      ],
      locations: [
        { name: "Padang", code: "PDG" },
        { name: "Bukittinggi", code: "BKT" },
        { name: "Payakumbuh", code: "PYK" },
        { name: "Solok", code: "SLK" },
        { name: "Pariaman", code: "PRM" },
        { name: "Painan", code: "PNN" },
        { name: "Sawahlunto", code: "SWL" },
        { name: "Batusangkar", code: "BTSK" },
      ],
      statuses: [
        "Standby",
        "Boarding",
        "Berangkat",
        "Tiba",
        "Tertunda",
        "Batal",
        "Rental",
      ],
      statusClasses: {
        "Standby": "bg-green-100 text-green-800",
        Boarding: "bg-yellow-100 text-yellow-800 animate-pulse",
        Berangkat: "bg-blue-100 text-blue-800",
        Tiba: "bg-gray-200 text-gray-800",
        Tertunda: "bg-red-100 text-red-800",
        Batal: "bg-black text-white",
        Rental: "bg-purple-100 text-purple-800",
      },
    };
  },
  computed: {
    currentViewTitle() {
      const titles = {
        dashboard: "Dashboard Operasional",
        inventaris: "Manajemen Inventaris Armada",
        drivers: "Manajemen Supir",
        history: "Riwayat Perjalanan",
        display: "Layar Informasi Publik",
        admin: "Pengaturan Admin",
        process: "Proses Pembuatan Jadwal",
      };
      return titles[this.view] || "Sutan Raya";
    },
    tripsWithFleet() {
      return this.trips.map((trip) => {
        const fleet = this.fleet.find((f) => f.id === trip.fleetId) || {};
        const driver = this.drivers.find((d) => d.id === trip.driverId) || {};
        const originInfo = this.locations.find((l) => l.name === trip.origin);
        const destinationInfo = this.locations.find(
          (l) => l.name === trip.destination
        );
        const durationDays = this.calculateDurationInDays(
          trip.departureTime,
          trip.arrivalTime
        );
        const tripCost = durationDays * (fleet.biayaOperasional || 0);

        let tripRevenue = 0;
        if (trip.status === "Tiba") {
          if (trip.type === "Rental") {
            tripRevenue = durationDays * (fleet.hargaSewa || 0);
          } else if (trip.type === "Rute" && fleet.hargaPerOrang) {
            const paxString = trip.pax || "0/0";
            const paxMatch = paxString.match(/^(\d+)/);
            const numberOfPassengers = paxMatch ? parseInt(paxMatch[1], 10) : 0;
            tripRevenue = numberOfPassengers * fleet.hargaPerOrang;
          }
        }
        const tripProfit =
          trip.status === "Tiba" && tripRevenue > 0
            ? tripRevenue - tripCost
            : 0;
        return {
          ...trip,
          fleet,
          driver,
          originCode:
            trip.type === "Rute"
              ? originInfo
                ? originInfo.code
                : "N/A"
              : trip.notes,
          destinationCode:
            trip.type === "Rute"
              ? destinationInfo
                ? destinationInfo.code
                : "N/A"
              : null,
          tripCost,
          tripRevenue,
          tripProfit,
        };
      });
    },
    activeTrips() {
      return this.tripsWithFleet.filter(
        (t) => t.status !== "Tiba" && t.status !== "Batal"
      );
    },
    departures() {
      return this.activeTrips
        .filter((t) => t.origin === "Padang")
        .sort((a, b) => new Date(a.departureTime) - new Date(b.departureTime));
    },
    arrivals() {
      return this.activeTrips
        .filter((t) => t.destination === "Padang")
        .sort((a, b) => new Date(a.arrivalTime) - new Date(b.arrivalTime));
    },
    rental() {
      return this.activeTrips
        .filter((t) => t.type === "Rental")
        .map((t) => ({
          ...t,
          totalDurationFormatted: this.calculateTotalDuration(
            t.departureTime,
            t.arrivalTime
          ),
        }));
    },
    fleetInOperationIds() {
      return new Set(this.activeTrips.map((t) => t.fleetId));
    },
    armadaStandby() {
      return this.fleet.filter(
        (f) => !this.fleetInOperationIds.has(f.id) && f.status === "Tersedia"
      );
    },
    standbyHiace() {
      return this.armadaStandby.filter((f) =>
        f.type.toLowerCase().includes("hiace")
      );
    },
    standbyMediumBus() {
      return this.armadaStandby.filter((f) =>
        f.type.toLowerCase().includes("medium bus")
      );
    },
    standbyBigBus() {
      return this.armadaStandby.filter((f) =>
        f.type.toLowerCase().includes("big bus")
      );
    },
    armadaKendala() {
      return this.fleet.filter((f) => f.status === "Perbaikan");
    },
    filteredFleet() {
      if (!this.armadaSearchTerm) return this.fleet;
      const term = this.armadaSearchTerm.toLowerCase();
      return this.fleet.filter(
        (v) =>
          v.name.toLowerCase().includes(term) ||
          v.plate.toLowerCase().includes(term) ||
          v.status.toLowerCase().includes(term)
      );
    },
    filteredHistory() {
      const historyTrips = this.tripsWithFleet
        .filter((t) => t.status === "Tiba" || t.status === "Batal")
        .sort((a, b) => new Date(b.departureTime) - new Date(a.departureTime));
      if (!this.historySearchTerm) return historyTrips;
      const term = this.historySearchTerm.toLowerCase();
      return historyTrips.filter(
        (t) =>
          t.fleet.name.toLowerCase().includes(term) ||
          (t.origin && t.origin.toLowerCase().includes(term)) ||
          (t.destination && t.destination.toLowerCase().includes(term)) ||
          (t.notes && t.notes.toLowerCase().includes(term))
      );
    },
    upcomingDeparturesForDisplay() {
      return this.tripsWithFleet
        .filter(
          (t) =>
            (t.origin === "Padang" || t.type === "Rental") &&
            t.status !== "Tiba" &&
            t.status !== "Batal" &&
            t.status !== "Berangkat"
        )
        .sort((a, b) => new Date(a.departureTime) - new Date(b.departureTime));
    },
    upcomingArrivalsForDisplay() {
      return this.tripsWithFleet
        .filter(
          (t) =>
            t.destination === "Padang" &&
            t.status !== "Tiba" &&
            t.status !== "Batal"
        )
        .sort((a, b) => new Date(a.arrivalTime) - new Date(b.arrivalTime));
    },
    selectedDriverNameForWizard() {
      if (!this.wizard.trip || !this.wizard.trip.driverId) return "";
      const driver = this.drivers.find(
        (d) => d.id === this.wizard.trip.driverId
      );
      return driver ? driver.name : "";
    },
    availableFleetForTrip() {
      let fleet = this.fleet.filter((f) => f.status === "Tersedia");
      const trip = this.wizard.trip || this.modal.trip;
      if (trip && trip.fleetId) {
        const current = this.fleet.find((f) => f.id === trip.fleetId);
        if (current && !fleet.some((f) => f.id === current.id))
          fleet.push(current);
      }
      return fleet;
    },
    availableDriversForTrip() {
      const trip = this.wizard.trip || this.modal.trip;
      if (!trip || !trip.fleetId) return [];
      const selectedFleet = this.fleet.find((f) => f.id === trip.fleetId);
      if (!selectedFleet) return [];
      let filtered = this.drivers.filter(
        (d) =>
          (d.status === "Standby" || d.id === trip.driverId) &&
          d.licenseType === selectedFleet.requiredLicense
      );
      if (this.driverSearchTermInModal) {
        filtered = filtered.filter((d) =>
          d.name
            .toLowerCase()
            .includes(this.driverSearchTermInModal.toLowerCase())
        );
      }
      return filtered;
    },
    standbyDrivers() {
      return this.drivers.filter((d) => d.status === "Standby");
    },
    filteredDrivers() {
      if (!this.driverSearchTerm) return this.drivers;
      const term = this.driverSearchTerm.toLowerCase();
      return this.drivers.filter((d) => d.name.toLowerCase().includes(term));
    },
  },
  methods: {
    toggleFullscreen() {
      this.isFullscreen = !this.isFullscreen;
    },
    formatRupiah(number) {
      if (isNaN(number)) return "Rp 0";
      return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
      }).format(number);
    },
    getCurrentDate() {
      return new Date().toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
      });
    },
    formatTime(date) {
      const d = new Date(date);
      if (isNaN(d)) return "--:--";
      return d.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
      });
    },
    formatFullDate(isoString) {
      if (!isoString) return "-";
      return new Date(isoString).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      });
    },
    formatDateTime(isoString) {
      if (!isoString) return "-";
      const d = new Date(isoString);
      return `${d.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
      })} ${this.formatTime(d)}`;
    },
    getTodayISO(h, m) {
      const d = new Date();
      d.setHours(h, m, 0, 0);
      return d.toISOString().slice(0, 16);
    },
    getYesterdayISO(h, m) {
      const d = new Date();
      d.setDate(d.getDate() - 1);
      d.setHours(h, m, 0, 0);
      return d.toISOString().slice(0, 16);
    },
    getFutureISO(days, h, m) {
      const d = new Date();
      d.setDate(d.getDate() + days);
      d.setHours(h, m, 0, 0);
      return d.toISOString().slice(0, 16);
    },
    isServiceDue(nextServiceDate) {
      const nextService = new Date(nextServiceDate);
      const thirtyDaysFromNow = new Date(this.now);
      thirtyDaysFromNow.setDate(this.now.getDate() + 30);
      return nextService < thirtyDaysFromNow;
    },
    getVehicleStatusClass(status) {
      return status === "Tersedia"
        ? "bg-green-100 text-green-800"
        : "bg-red-100 text-red-800";
    },
    getDriverStatusClass(status) {
      const classes = {
        Standby: "bg-green-100 text-green-800",
        "Dalam Perjalanan": "bg-blue-100 text-blue-800",
        Libur: "bg-gray-200 text-gray-700",
      };
      return classes[status] || "";
    },
    calculateDurationInDays(start, end) {
      const startDate = new Date(start);
      const endDate = new Date(end);
      if (isNaN(startDate) || isNaN(endDate) || endDate < startDate) return 0;
      const diffTime = endDate - startDate;
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      return diffDays < 1 ? 1 : diffDays;
    },
    calculateTotalDuration(start, end) {
      const diff = (new Date(end).getTime() - new Date(start).getTime()) / 1000;
      if (diff <= 0) return "0 menit";
      const days = Math.floor(diff / 86400);
      const hours = Math.floor((diff % 86400) / 3600);
      const minutes = Math.floor((diff % 3600) / 60);
      let result = "";
      if (days > 0) result += `${days} hari `;
      if (hours > 0) result += `${hours} jam `;
      if (minutes > 0) result += `${minutes} mnt`;
      return result.trim() || "0 mnt";
    },
    calculateElapsedTime(start) {
      const startDate = new Date(start);
      if (this.now < startDate) return "Belum Mulai";
      let diff = (this.now.getTime() - startDate.getTime()) / 1000;
      const hours = Math.floor(diff / 3600);
      const minutes = Math.floor((diff % 3600) / 60);
      const seconds = Math.floor(diff % 60);
      return [hours, minutes, seconds]
        .map((v) => (v < 10 ? "0" + v : v))
        .join(":");
    },
    calculateProgress(trip) {
      const start = new Date(trip.departureTime);
      const end = new Date(trip.arrivalTime);
      if (this.now < start) return 0;
      if (this.now >= end) return 100;
      const total = end.getTime() - start.getTime();
      if (total <= 0) return 100;
      const elapsed = this.now.getTime() - start.getTime();
      return (elapsed / total) * 100;
    },
    hideDriverDropdownWithDelay() {
      setTimeout(() => {
        this.showDriverDropdown = false;
      }, 200);
    },
    selectDriver(driver, context = "wizard") {
      let trip = context === "wizard" ? this.wizard.trip : this.modal.trip;
      if (trip) trip.driverId = driver.id;
      this.driverSearchTermInModal = driver.name;
      this.showDriverDropdown = false;
    },
    setWizardDate(type) {
      this.wizard.date.type = type;
      let newDate;
      if (type === "today") {
        newDate = new Date();
      } else if (type === "tomorrow") {
        newDate = new Date();
        newDate.setDate(newDate.getDate() + 1);
      } else {
        return;
      }
      this.wizard.date.raw = newDate.toISOString().slice(0, 10);
    },
    openTripWizard(standbyFleetId = null) {
      this.driverSearchTermInModal = "";
      this.wizard.trip = {
        id: Date.now(),
        fleetId: standbyFleetId || "",
        driverId: null,
        type: "Rute",
        origin: "Padang",
        destination: "Bukittinggi",
        notes: "",
        departureTime: "",
        arrivalTime: "",
        pax: "",
        status: "Standby",
      };
      this.setWizardDate("today");
      this.wizard.time = "08:00";
      const todayStr = new Date().toISOString().slice(0, 10);
      this.wizard.endDate.raw = todayStr;
      this.wizard.endTime = "17:00";
      this.wizard.step = 1;
      this.isTripWizardVisible = true;
    },
    closeTripWizard() {
      this.isTripWizardVisible = false;
    },
    saveTripFromWizard() {
      if (!this.wizard.trip.fleetId || !this.wizard.trip.driverId) {
        alert("Silakan pilih Armada dan Supir pada Langkah 3.");
        this.wizard.step = 3;
        return;
      }
      if (this.wizard.trip.type === "Rute") {
        this.wizard.trip.departureTime = `${this.wizard.date.raw}T${this.wizard.time}`;
        const departureDate = new Date(this.wizard.trip.departureTime);
        departureDate.setHours(departureDate.getHours() + 2);
        this.wizard.trip.arrivalTime = departureDate.toISOString().slice(0, 16);
      } else {
        this.wizard.trip.departureTime = `${this.wizard.date.raw}T${this.wizard.time}`;
        this.wizard.trip.arrivalTime = `${this.wizard.endDate.raw}T${this.wizard.endTime}`;
      }
      this.trips.push(this.wizard.trip);
      this.updateDriverStatusOnTripChange(
        null,
        this.wizard.trip.driverId,
        this.wizard.trip.status
      );
      this.closeTripWizard();
    },
    openTripModal(trip) {
      this.modal.trip = JSON.parse(
        JSON.stringify(this.tripsWithFleet.find((t) => t.id === trip.id))
      );
      this.isTripModalVisible = true;
    },
    closeTripModal() {
      this.isTripModalVisible = false;
    },
    saveTrip() {
      if (!this.modal.trip) return;
      const index = this.trips.findIndex((t) => t.id === this.modal.trip.id);
      if (index !== -1) {
        const { fleet, driver, ...originalTrip } = this.modal.trip;
        this.trips[index] = { ...this.trips[index], ...originalTrip };
      }
      this.closeTripModal();
    },
    updateTripStatus(tripId, newStatus) {
      const tripIndex = this.trips.findIndex((t) => t.id === tripId);
      if (tripIndex === -1) return;
      const trip = this.trips[tripIndex];
      const oldStatus = trip.status;
      if (oldStatus === newStatus) return;
      trip.status = newStatus;
      this.modal.trip.status = newStatus;
      if (
        (newStatus === "Tiba" || newStatus === "Batal") &&
        oldStatus !== "Tiba" &&
        oldStatus !== "Batal"
      ) {
        const driverIndex = this.drivers.findIndex(
          (d) => d.id === trip.driverId
        );
        if (driverIndex !== -1) this.drivers[driverIndex].status = "Standby";
      } else if (
        newStatus !== "Tiba" &&
        newStatus !== "Batal" &&
        (oldStatus === "Tiba" || oldStatus === "Batal")
      ) {
        const driverIndex = this.drivers.findIndex(
          (d) => d.id === trip.driverId
        );
        if (driverIndex !== -1)
          this.drivers[driverIndex].status = "Dalam Perjalanan";
      }
    },
    updateDriverStatusOnTripChange(originalDriverId, newDriverId, tripStatus) {
      if (originalDriverId && originalDriverId !== newDriverId) {
        const oldDriverIndex = this.drivers.findIndex(
          (d) => d.id === originalDriverId
        );
        if (oldDriverIndex !== -1)
          this.drivers[oldDriverIndex].status = "Standby";
      }
      const newDriverIndex = this.drivers.findIndex(
        (d) => d.id === newDriverId
      );
      if (newDriverIndex !== -1) {
        this.drivers[newDriverIndex].status =
          tripStatus === "Tiba" || tripStatus === "Batal"
            ? "Standby"
            : "Dalam Perjalanan";
      }
    },
    deleteTrip(tripId) {
      if (confirm("Apakah Anda yakin ingin menghapus jadwal perjalanan ini?")) {
        const trip = this.trips.find((t) => t.id === tripId);
        if (trip && trip.driverId) {
          const driverIndex = this.drivers.findIndex(
            (d) => d.id === trip.driverId
          );
          if (
            driverIndex !== -1 &&
            this.drivers[driverIndex].status === "Dalam Perjalanan"
          )
            this.drivers[driverIndex].status = "Standby";
        }
        this.trips = this.trips.filter((t) => t.id !== tripId);
        this.closeTripModal();
      }
    },
    openVehicleModal(vehicle) {
      if (vehicle) {
        this.vehicleModal.mode = "edit";
        this.vehicleModal.data = JSON.parse(JSON.stringify(vehicle));
      } else {
        this.vehicleModal.mode = "add";
        this.vehicleModal.data = {
          id: Date.now(),
          name: "",
          type: "Hiace Premio",
          plate: "",
          capacity: 7,
          status: "Tersedia",
          icon: "bi-truck-front-fill",
          hargaSewa: 1600000,
          hargaPerOrang: 150000,
          biayaOperasional: 600000,
          requiredLicense: "A Umum",
          lastService: this.getTodayISO(0, 0).slice(0, 10),
          nextService: this.getFutureISO(180, 0, 0).slice(0, 10),
        };
      }
      this.isVehicleModalVisible = true;
    },
    closeVehicleModal() {
      this.isVehicleModalVisible = false;
    },
    saveVehicle() {
      if (this.vehicleModal.data.type.toLowerCase().includes("bus")) {
        this.vehicleModal.data.icon = "bi-bus-front-fill";
      } else {
        this.vehicleModal.data.icon = "bi-truck-front-fill";
      }
      if (this.modal.mode === "add") {
        this.fleet.push(this.vehicleModal.data);
      } else {
        const index = this.fleet.findIndex(
          (f) => f.id === this.vehicleModal.data.id
        );
        if (index !== -1) {
          this.fleet[index] = this.vehicleModal.data;
        }
      }
      this.closeVehicleModal();
    },
    openDriverModal(driver) {
      if (driver) {
        this.driverModal.mode = "edit";
        this.driverModal.data = JSON.parse(JSON.stringify(driver));
      } else {
        this.driverModal.mode = "add";
        this.driverModal.data = {
          id: Date.now(),
          name: "",
          licenseType: "A Umum",
          phone: "",
          status: "Standby",
        };
      }
      this.isDriverModalVisible = true;
    },
    closeDriverModal() {
      this.isDriverModalVisible = false;
    },
    saveDriver() {
      if (this.driverModal.mode === "add") {
        this.drivers.push(this.driverModal.data);
      } else {
        const index = this.drivers.findIndex(
          (d) => d.id === this.driverModal.data.id
        );
        if (index !== -1) this.drivers[index] = this.driverModal.data;
      }
      this.closeDriverModal();
    },
    savePrices() {
      this.pricesSavedMessage = "Harga berhasil diperbarui!";
      setTimeout(() => {
        this.pricesSavedMessage = "";
      }, 3000);
    },
  },
  watch: {
    "wizard.date.raw"(newDate) {
      if (this.wizard.trip && this.wizard.trip.type === "Rental") {
        const startDate = new Date(newDate);
        const endDate = new Date(this.wizard.endDate.raw);
        if (!this.wizard.endDate.raw || startDate > endDate) {
          this.wizard.endDate.raw = newDate;
        }
      }
    },
  },
  mounted() {
    this.clockInterval = setInterval(() => {
      this.now = new Date();
      this.currentTime = this.formatTime(this.now, true);
    }, 1000);
    this.wizard.date.raw = new Date().toISOString().slice(0, 10);
    this.wizard.endDate.raw = new Date().toISOString().slice(0, 10);
  },
  beforeUnmount() {
    clearInterval(this.clockInterval);
  },
}).mount("#app");
