export const dataMixin = {
    data() {
        const getLocalDate = () => {
            const d = new Date();
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        };

        return {
            // -- State UI --
            view: window.initialView || 'dashboard',
            isDarkMode: false,
            isFullscreen: false,
            isLoading: false,
            currentTime: "",
            currentDate: "",
            
            // Security
            isLocked: true,
            accessCode: '',
            username: '',

            user: null, // Logged in user data
            
            // -- State Modals --
            isScheduleModalOpen: false,
            loadingSchedules: false,
            isProofModalVisible: false,
            isDispatchModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            isTripControlVisible: false,
            isTicketModalVisible: false,
            isRouteModalVisible: false,
            isMoveModalVisible: false,
            moveModalData: {
                passengerId: null,
                passengerName: null,
                routeId: null,
                currentDate: null,
                currentTime: null,
                seatNumbers: null, // Selected seat(s)
                
                targetTime: null,
                targetBatchIndex: 0,
                
                availableSchedules: [],
                allBatchesForTime: [] // Pre-calculated batches for target time
            },
            isManualAssignModalVisible: false,
            isScheduleModalVisible: false,
            isKtmModalVisible: false,
            activeKtmImage: '',
            activeBookingName: '',
            
            // -- Data Models --
            activeTripControl: null,
            validationData: null,
            ticketData: null,
            manualAssignments: {}, // { bookingId: { fleetId, driverId } }
            manualAssignForm: { bookingId: null, fleetId: '', driverId: '' },
            scheduleForm: { route: null, time: '', fleetId: '', driverId: '', isDefault: false },
            scheduleDefaults: [],
            
            // -- Forms --
            bookingBusForm: { type: 'Medium', routeId: '', seatCapacity: 33, duration: 1, date: '', passengerName: '', passengerPhone: '', totalPrice: 0, priceType: 'Kantor', packageType: 'Unit', paymentMethod: 'Cash', paymentLocation: '', paymentReceiver: '', paymentProof: '', downPaymentAmount: 0 },
            
            // Pagination
            currentPage: 1,
            itemsPerPage: 10,
            
            bookingManagementTab: 'travel',
            
            // Filters
            busSearchTerm: '',
            filterMethod: 'All',
            filterSort: 'Newest',
            filterDate: '',
            filterRoute: 'All',
            filterCategory: '',
            busViewMode: 'list',
            
            currentPaymentMethod: 'Cash',
            tempPayment: { loc: '', recv: '', proof: '', dpAmount: 0, dpMethod: 'Cash' },
            dispatchForm: { group: null, fleetId: "", driverId: "" },
            
            // Detail Modal State
            isDetailModalVisible: false,
            detailModalData: null,
            
            // Validation Modal State
            isValidationModalVisible: false,

            vehicleModal: { mode: "add", data: null },
            driverModal: { mode: "add", data: null },
            routeModal: { mode: 'add' },
            routeForm: { id: '', origin: '', destination: '', schedulesInput: '', prices: { umum: 0, pelajar: 0, dropping: 0, carter: 0 } },

            // Add Payment Modal State
            isAddPaymentModalVisible: false,
            activePaymentBooking: null,
            isAddPaymentModalVisible: false,
            activePaymentBooking: null,
            addPaymentForm: { amount: 0, payment_method: 'Cash', payment_location: '', payment_receiver: '', payment_proof: '', notes: '' },
            paymentDisplay: { name: '', total: '0', paid: '0', remaining: '0', remainingVal: 0 },

            // Input Memory
            savedReceivers: JSON.parse(localStorage.getItem('sr_receivers') || '[]'),
            savedLocations: JSON.parse(localStorage.getItem('sr_locations') || '[]'),
            
            // UI State
            isDragging: false,

            // -- Data from Server --
            bookings: [],
            fleet: [],
            drivers: [],
            trips: [], // Trips database (Manifest)
            routeConfig: [],
            reportData: { labels: [], revenue: [], pax: [], details: {} }, // corrected from duplicate lines
            
            // New Bus & Route Config
            busBookings: [],
            busRouteConfig: [],
            staffList: [{name:'Owner'}, {name:'Admin'}, {name:'Counter'}],       // Staff Users (Manual)
            
            // -- Static Config --
            seatLayout: [
                { row: 1, seats: [{id:"1", type:"seat"}, {id:"driver", type:"driver"}], label: "Depan" },
                { row: 2, seats: [{id:"2", type:"seat"}, {id:"3", type:"seat"}], label: "Tengah 1" },
                { row: 3, seats: [{id:"4", type:"seat"}, {id:"5", type:"seat"}], label: "Tengah 2" },
                { row: 4, seats: [{id:"6", type:"seat"}, {id:"7", type:"seat"}, {id:"8", type:"seat"}], label: "Belakang" }
            ],
            calendarMonth: new Date().getMonth(),
            calendarYear: new Date().getFullYear(),
            
            // -- Reports --
            period: 'daily',
            charts: { revenue: null, pax: null },
            manifestDate: getLocalDate(),
            detailModal: { isOpen: false, type: 'income', title: '', data: [] },
            
            // -- Package Features --
            packages: [],
            packageForm: {
                senderName: '',
                senderPhone: '',
                receiverName: '',
                receiverPhone: '',
                itemDescription: '',
                itemType: 'Surat / Dokumen',
                category: 'Pool to Pool',
                route: 'Padang - Bukittinggi',
                price: 30000,
                paymentMethod: 'Cash',
                paymentStatus: 'Lunas',
                status: 'Pending',
                pickupAddress: '',
                dropoffAddress: '',
                mapLink: '',
                bookingDate: getLocalDate(),
                adminName: ''
            },
            packageView: 'info', // 'info' or 'booking' or 'history'
            
            // -- Package UI State --
            activePackage: {},
            activePackageLogs: [],
            isTrackingModalVisible: false,
            isStatusModalVisible: false,
            statusForm: {
                id: '',
                status: '',
                location: '',
                description: '',
                adminName: ''
            },
            packageSearch: '', // Added Search Term

            // -- Dispatcher Filter --
            dispatcherRouteFilter: 'All', // 'All' or specific routeName
            
            // -- Payment Management --
            paymentHistory: [],
            isAddPaymentModalVisible: false,
            activePaymentBooking: null,
            addPaymentForm: {
                amount: 0,
                payment_method: 'Cash',
                payment_location: '',
                payment_receiver: '',
                notes: ''
            },
            
            // Explicitly include bookingForm which seems to be missing in the provided snippet but used in code
            bookingForm: { 
                data: { id: null, serviceType: 'Travel', routeId: '', date: '', time: '', passengerName: '', passengerPhone: '', passengerType: 'Umum', seatCount: 1, duration: 1, isMultiStop: false },
                selectedSeats: []
            }
        };
    }
};
