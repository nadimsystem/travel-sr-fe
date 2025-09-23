// app.js

const { createApp, ref, onMounted, computed, watch } = Vue;

const statuses = [
  "On Time",
  "Boarding",
  "Berangkat",
  "Tertunda",
  "Tiba",
  "Batal",
  "Rental",
];
const locations = ["Padang", "Bukittinggi", "Payakumbuh"];

const fleetData = [];
for (let i = 1; i <= 30; i++) {
  const capacity = 7;
  fleetData.push({
    id: `H${i}`,
    name: `Hiace SR-${i.toString().padStart(2, "0")}`,
    type: "Hiace",
    plate: `BA ${8000 + i} XX`,
    capacity,
    status: "Tersedia",
    icon: "bi bi-truck-front-fill",
  });
}
for (let i = 1; i <= 11; i++) {
  const type = i <= 5 ? "Medium Bus" : "Big Bus";
  const capacity = i <= 5 ? 33 : 50;
  fleetData.push({
    id: `B${i}`,
    name: `Bus SR-${i.toString().padStart(2, "0")}`,
    type,
    plate: `BA ${9000 + i} XY`,
    capacity,
    status: "Tersedia",
    icon: "bi bi-bus-front-fill",
  });
}
fleetData.find((f) => f.id === "H10").status = "Perbaikan";

function getCode(city) {
  const map = { Padang: "PDG", Bukittinggi: "BKT", Payakumbuh: "PYK" };
  return map[city] || (city ? city.substring(0, 3).toUpperCase() : "");
}

function generateInitialTrips() {
  const now = new Date();
  const createTime = (h, m) =>
    new Date(now.getFullYear(), now.getMonth(), now.getDate(), h, m)
      .toISOString()
      .slice(0, 16);
  return [
    {
      id: 1,
      fleetId: "H1",
      type: "Rute",
      origin: "Padang",
      destination: "Bukittinggi",
      departureTime: createTime(14, 0),
      arrivalTime: createTime(16, 30),
      status: "On Time",
      assignee: "Rizky",
      pax: "7/7",
    },
    {
      id: 2,
      fleetId: "H2",
      type: "Rute",
      origin: "Padang",
      destination: "Payakumbuh",
      departureTime: createTime(16, 30),
      arrivalTime: createTime(19, 45),
      status: "On Time",
      assignee: "Dedi",
      pax: "5/7",
    },
    {
      id: 3,
      fleetId: "H3",
      type: "Rute",
      origin: "Bukittinggi",
      destination: "Padang",
      departureTime: createTime(11, 0),
      arrivalTime: createTime(13, 30),
      status: "On Time",
      assignee: "Siska",
      pax: "6/7",
    },
    {
      id: 4,
      fleetId: "B1",
      type: "Rute",
      origin: "Padang",
      destination: "Payakumbuh",
      departureTime: createTime(9, 0),
      arrivalTime: createTime(12, 0),
      status: "Berangkat",
      assignee: "Joni",
      pax: "25/33",
    },
    {
      id: 5,
      fleetId: "H5",
      type: "Rental",
      notes: "Sewa Harian - BPKP Sumbar",
      departureTime: createTime(8, 0),
      arrivalTime: createTime(17, 0),
      status: "Rental",
      assignee: "Andi",
      pax: "Carter",
    },
    {
      id: 6,
      fleetId: "B5",
      type: "Rute",
      origin: "Payakumbuh",
      destination: "Padang",
      departureTime: createTime(15, 0),
      arrivalTime: createTime(18, 0),
      status: "Boarding",
      assignee: "Ujang",
      pax: "30/33",
    },
    {
      id: 7,
      fleetId: "B3",
      type: "Rute",
      origin: "Bukittinggi",
      destination: "Padang",
      departureTime: new Date(new Date().setDate(new Date().getDate() - 1))
        .toISOString()
        .slice(0, 16),
      arrivalTime: new Date(new Date().setDate(new Date().getDate() - 1))
        .toISOString()
        .slice(0, 16),
      status: "Tiba",
      assignee: "Budi",
      pax: "31/33",
    },
    {
      id: 8,
      fleetId: "B10",
      type: "Rental",
      notes: "Sewa Mingguan - Bank Indonesia",
      departureTime: new Date(new Date().setDate(new Date().getDate() - 2))
        .toISOString()
        .slice(0, 16),
      arrivalTime: new Date(new Date().setDate(new Date().getDate() + 5))
        .toISOString()
        .slice(0, 16),
      status: "Rental",
      assignee: "Eko",
      pax: "Korporat",
    },
  ];
}

const app = createApp({
  setup() {
    const view = ref("dashboard");
    const trips = ref([]);
    const fleet = ref([]);
    const armadaSearchTerm = ref("");
    const historySearchTerm = ref("");
    const isTripModalVisible = ref(false);
    const isVehicleModalVisible = ref(false);
    const modal = ref({ mode: "add", trip: null });
    const vehicleModal = ref({ mode: "add", data: {} });
    const customOrigin = ref("");
    const customDestination = ref("");

    const loadData = () => {
      trips.value =
        JSON.parse(localStorage.getItem("sutanRayaTrips")) ||
        generateInitialTrips();
      fleet.value =
        JSON.parse(localStorage.getItem("sutanRayaFleet")) || fleetData;
    };
    const saveData = () => {
      localStorage.setItem("sutanRayaTrips", JSON.stringify(trips.value));
      localStorage.setItem("sutanRayaFleet", JSON.stringify(fleet.value));
    };
    watch([trips, fleet], saveData, { deep: true });
    onMounted(() => {
      loadData();
      setInterval(() => {
        const now = new Date();
        const clockEl = document.getElementById("clock");
        const dateEl = document.getElementById("date");
        if (clockEl)
          clockEl.textContent = now.toLocaleTimeString("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
          });
        if (dateEl)
          dateEl.textContent = now.toLocaleDateString("id-ID", {
            weekday: "long",
            day: "numeric",
            month: "long",
          });
      }, 1000);
    });

    const formatFullDate = (iso) =>
      iso
        ? new Date(iso).toLocaleString("id-ID", {
            dateStyle: "long",
            timeStyle: "short",
          })
        : "";
    const formatTime = (iso) =>
      iso
        ? new Date(iso).toLocaleTimeString("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
          })
        : "";
    const getStatusClass = (status) =>
      "status-" + status.toLowerCase().replace(" ", "-");
    const getCardColorClass = (status) =>
      ({
        "On Time": "green",
        Boarding: "orange",
        Berangkat: "blue",
        Rental: "purple",
        Tertunda: "red",
      }[status] || "grey");
    const getStatusColor = (status) =>
      ({
        "On Time": "success",
        Boarding: "warning",
        Berangkat: "info",
        Rental: "purple",
        Tertunda: "danger",
      }[status] || "secondary");
    const getVehicleStatusClass = (status) =>
      ({
        Tersedia: "bg-success",
        Beroperasi: "bg-info text-dark",
        Perbaikan: "bg-danger",
      }[status] || "bg-secondary");

    const currentViewTitle = computed(
      () =>
        ({
          dashboard: "Dashboard Operasional",
          inventaris: "Inventaris Kendaraan",
          history: "Riwayat Perjalanan",
          display: "Layar Display Publik",
        }[view.value] || "")
    );

    const activeTripFleetIds = computed(
      () =>
        new Set(
          trips.value
            .filter((t) => !["Tiba", "Batal"].includes(t.status))
            .map((t) => t.fleetId)
        )
    );
    const armadaStandby = computed(() =>
      fleet.value.filter(
        (f) => !activeTripFleetIds.value.has(f.id) && f.status === "Tersedia"
      )
    );
    const armadaKendala = computed(() =>
      fleet.value.filter((f) => f.status === "Perbaikan")
    );

    const departures = computed(() =>
      trips.value
        .filter(
          (t) => t.origin === "Padang" && !["Tiba", "Batal"].includes(t.status)
        )
        .sort((a, b) => new Date(a.departureTime) - new Date(b.departureTime))
        .map((t) => ({
          ...t,
          fleet: fleet.value.find((f) => f.id === t.fleetId),
        }))
    );
    const arrivals = computed(() =>
      trips.value
        .filter(
          (t) =>
            t.destination === "Padang" && !["Tiba", "Batal"].includes(t.status)
        )
        .sort((a, b) => new Date(a.arrivalTime) - new Date(b.arrivalTime))
        .map((t) => ({
          ...t,
          fleet: fleet.value.find((f) => f.id === t.fleetId),
        }))
    );
    const rental = computed(() =>
      trips.value
        .filter(
          (t) => t.type === "Rental" && !["Tiba", "Batal"].includes(t.status)
        )
        .sort((a, b) => new Date(a.departureTime) - new Date(b.departureTime))
        .map((t) => ({
          ...t,
          fleet: fleet.value.find((f) => f.id === t.fleetId),
        }))
    );

    const armadaBeroperasi = computed(() =>
      fleet.value
        .filter((f) => activeTripFleetIds.value.has(f.id))
        .map((f) => ({
          ...f,
          trip: trips.value.find(
            (t) => t.fleetId === f.id && !["Tiba", "Batal"].includes(t.status)
          ),
        }))
        .sort((a, b) => a.name.localeCompare(b.name))
    );
    const filteredArmadaBeroperasi = computed(() =>
      armadaBeroperasi.value.filter((f) =>
        JSON.stringify(f)
          .toLowerCase()
          .includes(armadaSearchTerm.value.toLowerCase())
      )
    );
    const filteredArmadaStandby = computed(() =>
      armadaStandby.value.filter((f) =>
        JSON.stringify(f)
          .toLowerCase()
          .includes(armadaSearchTerm.value.toLowerCase())
      )
    );

    const history = computed(() =>
      trips.value
        .filter((t) => ["Tiba", "Batal"].includes(t.status))
        .sort((a, b) => new Date(b.departureTime) - new Date(a.departureTime))
        .map((t) => ({
          ...t,
          fleet: fleet.value.find((f) => f.id === t.fleetId),
        }))
    );
    const filteredHistory = computed(() =>
      history.value.filter((t) =>
        JSON.stringify(t)
          .toLowerCase()
          .includes(historySearchTerm.value.toLowerCase())
      )
    );
    const filteredFleet = computed(() => {
      const activeFleet = fleet.value.map((f) => ({
        ...f,
        status: activeTripFleetIds.value.has(f.id) ? "Beroperasi" : f.status,
      }));
      return activeFleet.filter((f) =>
        JSON.stringify(f)
          .toLowerCase()
          .includes(armadaSearchTerm.value.toLowerCase())
      );
    });

    const upcomingTripsForDisplay = computed(() =>
      trips.value
        .filter((t) => !["Tiba", "Batal"].includes(t.status))
        .sort((a, b) => new Date(a.departureTime) - new Date(b.departureTime))
        .slice(0, 10)
        .map((t) => ({
          ...t,
          fleet: fleet.value.find((f) => f.id === t.fleetId),
          destinationCode: getCode(t.destination) || "RENTAL",
        }))
    );
    const isFleetOnTrip = (fleetId, currentTripId) =>
      trips.value.some(
        (t) =>
          t.fleetId === fleetId &&
          t.id !== currentTripId &&
          !["Tiba", "Batal"].includes(t.status)
      );

    const openTripModal = (trip, standbyFleetId = null) => {
      customOrigin.value = "";
      customDestination.value = "";
      if (trip) {
        modal.value = { mode: "edit", trip: JSON.parse(JSON.stringify(trip)) };
        if (
          modal.value.trip.origin &&
          !locations.includes(modal.value.trip.origin)
        ) {
          customOrigin.value = modal.value.trip.origin;
          modal.value.trip.origin = "custom";
        }
        if (
          modal.value.trip.destination &&
          !locations.includes(modal.value.trip.destination)
        ) {
          customDestination.value = modal.value.trip.destination;
          modal.value.trip.destination = "custom";
        }
      } else {
        modal.value = {
          mode: "add",
          trip: {
            id: null,
            fleetId: standbyFleetId,
            type: "Rute",
            status: "On Time",
            departureTime: new Date().toISOString().slice(0, 16),
            origin: "Padang",
          },
        };
      }
      isTripModalVisible.value = true;
    };
    const closeTripModal = () => (isTripModalVisible.value = false);

    const saveTrip = () => {
      let trip = modal.value.trip;
      if (trip.origin === "custom") trip.origin = customOrigin.value;
      if (trip.destination === "custom")
        trip.destination = customDestination.value;

      trip.originCode = getCode(trip.origin) || "RENTAL";
      trip.destinationCode = getCode(trip.destination) || "RENTAL";

      if (modal.value.mode === "add") {
        trip.id = Date.now();
        trips.value.push(trip);
      } else {
        const index = trips.value.findIndex((t) => t.id === trip.id);
        if (index !== -1) trips.value.splice(index, 1, trip);
      }
      closeTripModal();
    };

    const deleteTrip = (tripId) => {
      if (
        confirm(
          "Apakah Anda yakin ingin menghapus jadwal ini? Tindakan ini tidak bisa dibatalkan."
        )
      ) {
        trips.value = trips.value.filter((t) => t.id !== tripId);
        closeTripModal();
      }
    };

    const updateTripStatus = (tripId, status) => {
      const trip = trips.value.find((t) => t.id === tripId);
      if (trip) trip.status = status;
      closeTripModal();
    };

    const openVehicleModal = (vehicle) => {
      if (vehicle) {
        vehicleModal.value = {
          mode: "edit",
          data: JSON.parse(JSON.stringify(vehicle)),
        };
      } else {
        vehicleModal.value = {
          mode: "add",
          data: { type: "Hiace", status: "Tersedia" },
        };
      }
      isVehicleModalVisible.value = true;
    };
    const closeVehicleModal = () => (isVehicleModalVisible.value = false);

    const saveVehicle = () => {
      let vehicle = vehicleModal.value.data;
      const icon =
        vehicle.type === "Hiace"
          ? "bi bi-truck-front-fill"
          : "bi bi-bus-front-fill";
      vehicle.icon = icon;

      if (!vehicle.id) {
        const prefix = vehicle.type === "Hiace" ? "H" : "B";
        const newIdNumber =
          fleet.value.filter((f) => f.id.startsWith(prefix)).length + 1;
        vehicle.id = prefix + newIdNumber;
        fleet.value.push(vehicle);
      } else {
        const index = fleet.value.findIndex((f) => f.id === vehicle.id);
        if (index !== -1) fleet.value.splice(index, 1, vehicle);
      }
      closeVehicleModal();
    };

    return {
      view,
      armadaSearchTerm,
      historySearchTerm,
      isTripModalVisible,
      isVehicleModalVisible,
      modal,
      vehicleModal,
      statuses,
      fleet,
      locations,
      customOrigin,
      customDestination,
      currentViewTitle,
      departures,
      arrivals,
      rental,
      armadaStandby,
      armadaKendala,
      filteredArmadaBeroperasi,
      filteredArmadaStandby,
      filteredHistory,
      filteredFleet,
      history,
      upcomingTripsForDisplay,
      isFleetOnTrip,
      formatFullDate,
      formatTime,
      getStatusClass,
      getCardColorClass,
      getStatusColor,
      getVehicleStatusClass,
      openTripModal,
      closeTripModal,
      saveTrip,
      deleteTrip,
      updateTripStatus,
      openVehicleModal,
      closeVehicleModal,
      saveVehicle,
    };
  },
});

app.mount("#app");
